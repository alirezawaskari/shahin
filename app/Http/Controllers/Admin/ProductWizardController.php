<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Product;
use App\Services\V1\Admin\ProductWizardService as Wizard;
use Illuminate\Http\Request;

/**
 * ویزارد ایجاد دسته‌بندی ← ویژگی‌ها ← کالا/خدمت ← تنوع کالا.
 *
 * این کنترلر هیچ صفحه‌ای را بازنویسی نمی‌کند؛ فقط صفحات موجود را پشت سر هم زنجیر
 * می‌کند. منطق مراحل و زمینه ویزارد در ProductWizardService است.
 */
class ProductWizardController extends Controller
{
    /**
     * قدم اول: تشخیص نوع کسب‌وکار.
     *
     * ورود از منو یعنی شروع تازه، پس زمینه قبلی پاک می‌شود؛ کلیک روی همین مرحله در
     * نوار ویزارد (`keep=1`) زمینه را نگه می‌دارد.
     */
    public function index(Request $request)
    {
        if (! $request->keep) {
            Wizard::forget();
        }

        $kind = Wizard::kindOfBusiness();

        // یک‌نوعی‌ها بدون پرش اضافه مستقیم به بخش دسته‌بندی خودشان می‌روند.
        if ($kind) {
            return $this->category($kind);
        }

        return view('Admin.productWizard.chooseType');
    }

    /**
     * قدم دوم: ورود مستقیم به بخش دسته‌بندی همان نوع.
     */
    public function category($kind)
    {
        abort_unless(isset(Wizard::ROOTS[$kind]), 404);

        Wizard::remember(['kind' => $kind]);

        return redirect()->route('category.subCategory', [
            'cat_id' => Wizard::ROOTS[$kind],
            'wizard' => 1,
        ]);
    }

    /**
     * قدم دوم - پایان: دسته‌بندی ساخته شد؛ ادامه به ویژگی‌ها (کالا) یا ثبت خدمت.
     */
    public function categoryCreated($category_id)
    {
        $category = Category::findOrFail($category_id);
        $kind = Wizard::kindOfCategory($category);
        Wizard::rememberCategory($category);

        return view('Admin.productWizard.categoryCreated', [
            'category'  => $category,
            'kind'      => $kind,
            'label'     => Wizard::LABELS[$kind],
            // برای کالا، قدم بعد تعریف ویژگی‌های دسته‌بندی است؛ خدمت ویژگی ندارد و
            // مستقیم به فرم ثبت خدمت می‌رود.
            'next_url'  => $kind === 'service'
                ? Wizard::createUrl($kind, $category->id)
                : Wizard::attributesUrl($category->id),
            'skip_url'  => $kind === 'service' ? null : Wizard::createUrl($kind, $category->id),
            'back_url'  => Wizard::categoryListUrl($category),
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
        Wizard::rememberCategory($category);

        $attribute_groups = AttributeGroup::where('category_id', $category->id)
            ->with(['attributes' => fn($q) => $q->with('attributeItems')])
            ->orderBy('id')
            ->get();

        return view('Admin.productWizard.attributes', [
            'category'         => $category,
            'attribute_groups' => $attribute_groups,
            'next_url'         => Wizard::createUrl('product', $category->id),
            'back_url'         => Wizard::categoryListUrl($category),
        ]);
    }

    /**
     * قدم آخر: اتمام فرآیند و بازگشت به لیست مربوطه.
     */
    public function done(Request $request)
    {
        $kind = in_array($request->kind, ['product', 'service'], true) ? $request->kind : 'product';
        $product = $request->product_id ? Product::find($request->product_id) : null;
        $category_id = $request->category_id ?: optional($product)->root_category_id;

        Wizard::remember(['kind' => $kind, 'product_id' => optional($product)->id]);

        return view('Admin.productWizard.done', [
            'kind'      => $kind,
            'label'     => Wizard::LABELS[$kind],
            'product'   => $product,
            'list_url'  => $kind === 'service'
                ? route('service.index')
                : route('products.index', array_filter(['root_category_id' => $category_id])),
            // «ایجاد مورد جدید» یعنی شروع تازه، نه ادامه با دسته‌بندی و کالای قبلی
            'again_url' => route('productWizard.index'),
        ]);
    }
}
