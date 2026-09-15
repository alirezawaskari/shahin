<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * ویزارد ایجاد دسته‌بندی ← کالا/خدمت ← تنوع کالا.
 *
 * این کنترلر هیچ صفحه‌ای را بازنویسی نمی‌کند؛ فقط صفحات موجود (ثبت دسته‌بندی،
 * ثبت کالا، ثبت خدمت و قیمت‌گذاری/تنوع) را پشت سر هم زنجیر می‌کند و شناسه رکورد
 * هر مرحله را به مرحله بعد می‌برد تا کاربر مجبور به انتخاب دوباره نشود.
 *
 * پرچم عبور بین مراحل، کوئری‌استرینگ `wizard=1` است. صفحات موجود وقتی آن را
 * ببینند نوار مراحل را نشان می‌دهند و در پایان به مرحله بعد ویزارد برمی‌گردند.
 */
class ProductWizardController extends Controller
{
    /** ریشه درخت دسته‌بندی‌ها به ازای هر نوع — در این سیستم با شناسه ثابت مشخص می‌شود. */
    public const ROOTS = ['product' => 1, 'service' => 2];

    public const LABELS = ['product' => 'کالا', 'service' => 'خدمت'];

    /**
     * قدم اول: تشخیص نوع کسب‌وکار.
     *
     * فقط کسب‌وکاری که هم کالا و هم خدمت دارد سوال را می‌بیند؛ بقیه مستقیم وارد
     * بخش دسته‌بندی خودشان می‌شوند تا صفحه اضافه‌ای بین راه نباشد.
     */
    public function index()
    {
        $kind = $this->kindOfBusiness();

        // یک‌نوعی‌ها بدون پرش اضافه مستقیم به بخش دسته‌بندی خودشان می‌روند.
        if ($kind) {
            return $this->category($kind);
        }

        return view('Admin.productWizard.chooseType');
    }

    /**
     * قدم دوم: ورود مستقیم به بخش دسته‌بندی همان نوع.
     *
     * صفحه لیست زیرمجموعه‌ها خودش درخت دسته‌بندی را مدیریت می‌کند، پس ویزارد فقط
     * کاربر را با پرچم wizard به همان صفحه می‌فرستد.
     */
    public function category($kind)
    {
        abort_unless(isset(self::ROOTS[$kind]), 404);

        return redirect()->route('category.subCategory', [
            'cat_id' => self::ROOTS[$kind],
            'wizard' => 1,
        ]);
    }

    /**
     * قدم دوم - پایان: دسته‌بندی ساخته شد؛ ادامه به ثبت کالا/خدمت در همین دسته‌بندی.
     */
    public function categoryCreated($category_id)
    {
        $category = Category::findOrFail($category_id);
        $kind = self::kindOfCategory($category);

        return view('Admin.productWizard.categoryCreated', [
            'category'  => $category,
            'kind'      => $kind,
            'label'     => self::LABELS[$kind],
            // برای کالا، قدم بعد تعریف ویژگی‌های دسته‌بندی است؛ خدمت ویژگی ندارد و
            // مستقیم به فرم ثبت خدمت می‌رود.
            'next_url'  => $kind === 'service'
                ? self::createUrl($kind, $category->id)
                : route('productWizard.attributes', $category->id),
            'skip_url'  => $kind === 'service' ? null : self::createUrl($kind, $category->id),
            'back_url'  => route('category.subCategory', [
                'cat_id' => $category->parent_id ?: self::ROOTS[$kind],
                'wizard' => 1,
            ]),
        ]);
    }

    /**
     * قدم سوم (فقط کالا): تعریف ویژگی‌های دسته‌بندی.
     *
     * ویژگی‌ها (مثل رنگ و سایز) یک بار روی خودِ دسته‌بندی تعریف می‌شوند؛ بعد هر کالایی
     * که در آن دسته‌بندی ثبت شود، همین ویژگی‌ها را در قدم «درج ویژگی‌های کالا» می‌بیند و
     * فقط مقدارشان را انتخاب می‌کند (مثلا رنگ = سبز). ویژگی «قابلیت قیمت‌گذاری» هم همان
     * چیزی است که در قدم بعد محور تنوع کالا می‌شود.
     */
    public function attributes($category_id)
    {
        $category = Category::findOrFail($category_id);

        $attribute_groups = AttributeGroup::where('category_id', $category->id)
            ->with(['attributes' => fn($q) => $q->with('attributeItems')])
            ->orderBy('id')
            ->get();

        return view('Admin.productWizard.attributes', [
            'category'         => $category,
            'attribute_groups' => $attribute_groups,
            'next_url'         => self::createUrl('product', $category->id),
            'back_url'         => route('category.subCategory', [
                'cat_id' => $category->parent_id ?: self::ROOTS['product'],
                'wizard' => 1,
            ]),
        ]);
    }

    /**
     * جایی که فرم‌های گروه ویژگی/ویژگی/مقدار بعد از ثبت باید به آن برگردند.
     * وقتی دسته‌بندی معلوم نباشد، null یعنی «رفتار عادی خودت را داشته باش».
     */
    public static function attributesUrl($category_id): ?string
    {
        return $category_id ? route('productWizard.attributes', $category_id) : null;
    }

    /**
     * قدم آخر: اتمام فرآیند و بازگشت به لیست مربوطه.
     */
    public function done(Request $request)
    {
        $kind = in_array($request->kind, ['product', 'service'], true) ? $request->kind : 'product';
        $product = $request->product_id ? Product::find($request->product_id) : null;
        $category_id = $request->category_id ?: optional($product)->root_category_id;

        return view('Admin.productWizard.done', [
            'kind'      => $kind,
            'label'     => self::LABELS[$kind],
            'product'   => $product,
            'list_url'  => $kind === 'service'
                ? route('service.index')
                : route('products.index', array_filter(['root_category_id' => $category_id])),
            'again_url' => route('productWizard.category', $kind),
        ]);
    }

    /**
     * نوع کسب‌وکار جاری، یا null وقتی هر دو نوع را دارد (و باید از کاربر پرسید).
     */
    public function kindOfBusiness(): ?string
    {
        $type_work = optional(ghorfe())->type_work;

        return in_array($type_work, ['product', 'service'], true) ? $type_work : null;
    }

    /**
     * دسته‌بندی زیر کدام ریشه است. مسیر (path) همیشه با شناسه ریشه شروع می‌شود.
     */
    public static function kindOfCategory(Category $category): string
    {
        $root = (int) (explode('/', (string) $category->path)[0] ?: $category->id);

        return $root === self::ROOTS['service'] ? 'service' : 'product';
    }

    /**
     * آدرس فرم ثبت کالا/خدمت با دسته‌بندی از پیش انتخاب‌شده.
     */
    public static function createUrl(string $kind, $category_id): string
    {
        $params = ['wizard' => 1, 'wizard_category' => $category_id];

        return $kind === 'service'
            ? route('service.create', $params)
            : route('createGood', $params);
    }
}
