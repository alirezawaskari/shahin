<?php

namespace App\Services\V1\Admin;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\Product;

/**
 * ویزارد ایجاد محصول: دسته‌بندی ← ویژگی‌ها ← کالا/خدمت ← تنوع ← اتمام.
 *
 * ویزارد صفحه جدیدی جای صفحات موجود نمی‌سازد؛ صفحات موجود وقتی `?wizard=1` داشته باشند
 * نوار مراحل را نشان می‌دهند و بعد از ثبت به مرحله بعد برمی‌گردند.
 *
 * برای اینکه نوار مراحل قابل کلیک باشد و «قبلی/بعدی» بدانند به کجا بروند، ویزارد
 * زمینه‌اش (نوع، دسته‌بندی، کالا/خدمتِ جاری) را در session نگه می‌دارد. هر صفحه‌ای که
 * یکی از این‌ها را می‌شناسد آن را remember می‌کند؛ عوض شدن یک حلقه بالادستی، حلقه‌های
 * پایین‌دستی را پاک می‌کند تا مثلا کالای دسته‌بندی قبلی به دسته‌بندی جدید نچسبد.
 */
class ProductWizardService
{
    /** ریشه درخت دسته‌بندی‌ها به ازای هر نوع — در این سیستم با شناسه ثابت مشخص می‌شود. */
    public const ROOTS = ['product' => 1, 'service' => 2];

    public const LABELS = ['product' => 'کالا', 'service' => 'خدمت'];

    private const SESSION_KEY = 'product_wizard';

    /**
     * نوع کسب‌وکار جاری، یا null وقتی هر دو نوع را دارد (و باید از کاربر پرسید).
     */
    public static function kindOfBusiness(): ?string
    {
        $type_work = optional(ghorfe())->type_work;

        return in_array($type_work, ['product', 'service'], true) ? $type_work : null;
    }

    /**
     * دسته‌بندی زیر کدام ریشه است. مسیر (path) همیشه با شناسه ریشه شروع می‌شود.
     */
    public static function kindOfCategory(Category $category): string
    {
        return self::rootIdOf($category) === self::ROOTS['service'] ? 'service' : 'product';
    }

    /**
     * شناسه ریشه درختی که دسته‌بندی در آن است.
     *
     * اول از path (سریع)؛ اگر path خالی بود — خود ریشه، یا ردیفی که path آن هیچ‌وقت
     * ذخیره نشده — از زنجیره parent_id بالا می‌رویم. بدون این، دسته‌بندی خدمتی که path
     * ندارد «کالا» تشخیص داده می‌شد.
     */
    private static function rootIdOf(Category $category): int
    {
        $first = (int) explode('/', (string) $category->path)[0];
        if ($first) {
            return $first;
        }

        $node = $category;
        for ($guard = 0; $node->parent_id && $guard < 10; $guard++) {
            $parent = Category::select('id', 'parent_id', 'path')->find($node->parent_id);
            if (! $parent) {
                break;
            }
            // یک سطح بالاتر path سالم دارد؟ همان کافی است
            $first = (int) explode('/', (string) $parent->path)[0];
            if ($first) {
                return $first;
            }
            $node = $parent;
        }

        return (int) $node->id;
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

    /**
     * مرحله ویژگی‌های یک دسته‌بندی؛ null وقتی دسته‌بندی معلوم نیست.
     */
    public static function attributesUrl($category_id): ?string
    {
        return $category_id ? route('productWizard.attributes', $category_id) : null;
    }

    /**
     * لیست دسته‌بندی‌هایی که این دسته‌بندی در آن است (همان جایی که کاربر آن را می‌بیند).
     */
    public static function categoryListUrl(Category $category): string
    {
        return route('category.subCategory', [
            'cat_id' => $category->parent_id ?: self::ROOTS[self::kindOfCategory($category)],
            'wizard' => 1,
        ]);
    }

    /* ---------------------------------------------------------------- زمینه ویزارد */

    public static function context(): array
    {
        return (array) session(self::SESSION_KEY, []);
    }

    /**
     * ثبت آنچه صفحه جاری می‌داند. مقادیر null نادیده گرفته می‌شوند.
     *
     * عوض شدن نوع همه‌چیز را پاک می‌کند و عوض شدن دسته‌بندی کالا/خدمتِ قبلی را.
     */
    public static function remember(array $values): void
    {
        $values = array_filter($values, fn($value) => $value !== null && $value !== '');
        $context = self::context();

        if (isset($values['kind']) && ($context['kind'] ?? null) !== $values['kind']) {
            $context = ['kind' => $values['kind']];
        }

        if (isset($values['category_id']) && (int) ($context['category_id'] ?? 0) !== (int) $values['category_id']) {
            unset($context['product_id'], $context['service_id']);
            $context['category_id'] = (int) $values['category_id'];
        }

        foreach (['product_id', 'service_id'] as $key) {
            if (isset($values[$key])) {
                $context[$key] = (int) $values[$key];
            }
        }

        session()->put(self::SESSION_KEY, $context);
    }

    /** دسته‌بندی را به همراه نوعش ثبت می‌کند. */
    public static function rememberCategory(?Category $category): void
    {
        if ($category) {
            self::remember(['kind' => self::kindOfCategory($category), 'category_id' => $category->id]);
        }
    }

    /** زمینه صفحات ویژگی: دسته‌بندیِ صاحب گروه ویژگی. */
    public static function rememberAttributeGroup(?AttributeGroup $group): void
    {
        self::rememberCategory($group ? Category::find($group->category_id) : null);
    }

    /** زمینه صفحات ویژگی/مقدار: دسته‌بندیِ صاحب ویژگی. */
    public static function rememberAttribute(?Attribute $attribute): void
    {
        self::rememberCategory($attribute ? Category::find($attribute->category_id) : null);
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /* ---------------------------------------------------------------- مراحل */

    /**
     * مراحل ویزارد با آدرس هر مرحله از روی زمینه فعلی.
     *
     * مرحله‌ای که هنوز پیش‌نیازش معلوم نیست (مثلا ثبت کالا بدون دسته‌بندی) آدرس ندارد و
     * قابل کلیک نیست.
     *
     * @return array<int, array{key:string, number:int, title:string, url:?string, state:string}>
     */
    public static function steps(string $current, ?string $kind = null): array
    {
        $context = self::context();

        $category = ! empty($context['category_id']) ? Category::find($context['category_id']) : null;
        $kind = $kind ?: ($context['kind'] ?? null) ?: ($category ? self::kindOfCategory($category) : null)
            ?: self::kindOfBusiness();

        // دسته‌بندیِ نوع دیگر (مثلا دسته‌بندی کالا وقتی حالا روی خدمت هستیم) به کار نمی‌آید.
        if ($category && $kind && self::kindOfCategory($category) !== $kind) {
            $category = null;
        }

        $product = $kind !== 'service' && $category && ! empty($context['product_id'])
            ? Product::select('id')->find($context['product_id'])
            : null;
        $service_saved = $kind === 'service' && $category && ! empty($context['service_id']);

        $steps = [];

        if (self::kindOfBusiness() === null) {
            $steps['type'] = [
                'title' => 'انتخاب کالا یا خدمت',
                'url'   => route('productWizard.index', ['keep' => 1]),
            ];
        }

        $steps['category'] = [
            'title' => 'ایجاد دسته‌بندی',
            'url'   => $category
                ? self::categoryListUrl($category)
                : ($kind ? route('productWizard.category', $kind) : null),
        ];

        if ($kind !== 'service') {
            $steps['attributes'] = [
                'title' => 'ویژگی‌های دسته‌بندی',
                'url'   => $category ? self::attributesUrl($category->id) : null,
            ];
        }

        $steps['create'] = [
            'title' => 'ثبت ' . self::LABELS[$kind ?: 'product'],
            'url'   => $category ? self::createUrl($kind ?: 'product', $category->id) : null,
        ];

        if ($kind !== 'service') {
            $steps['variation'] = [
                'title' => 'ثبت تنوع کالا',
                'url'   => $product ? route('price.show', ['price' => $product->id, 'wizard' => 1]) : null,
            ];
        }

        $steps['done'] = [
            'title' => 'اتمام',
            'url'   => $product
                ? route('productWizard.done', ['kind' => 'product', 'product_id' => $product->id])
                : ($service_saved ? route('productWizard.done', ['kind' => 'service', 'category_id' => $category->id]) : null),
        ];

        $keys = array_keys($steps);
        $current_index = array_search($current, $keys, true);

        $list = [];
        foreach ($keys as $index => $key) {
            $list[] = $steps[$key] + [
                'key'    => $key,
                'number' => $index + 1,
                'state'  => $key === $current
                    ? 'active'
                    : ($current_index !== false && $index < $current_index ? 'done' : 'todo'),
            ];
        }

        return $list;
    }
}
