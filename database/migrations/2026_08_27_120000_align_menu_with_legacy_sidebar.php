<?php

use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/**
 * Rebuild the menu stored in category_permissions / permissions so it matches the legacy
 * hardcoded sidebar, resources/views/Admin/layouts/sidem.blade.php.
 *
 * SPEC below is that file read top to bottom: the outer order is its group order, and each
 * group lists its items in the order they appear, paired with the route the <a href> used.
 *
 * What this changes on an existing permission: cat_id, position, link, isMenu — and
 * display_name only when the row has none. `name` is never touched: it is the Laratrust key
 * that roles and @permission() checks match on.
 *
 * Items sidem showed that have no permission row are created (a new English name is not a
 * rename). Everything is looked up by name, so this is re-runnable.
 *
 * Deliberately not represented, because the model cannot express them:
 *   - اتوماسیون اداری (tickets): every entry is route('ticket.index', ['type' => …]),
 *     and a menu link has no parameters.
 *   - حساب کاربری من: رزومه کاری / قرارداد همکاری / عملکرد ماهانه من are all
 *     route(..., [auth()->user()->employee->id]) — per-user, so not a stored link.
 *   - سایت ← کالا ها ← مدیریت کالا ها / تنوع و قیمت‌گذاری, and دسته بندی ← نمایش در منو:
 *     products.show / editInformation / categoryPosition all need an id.
 *   - sidem's duplicated groups (انبارداری, پرونده ها, فاکتور خرید and سفارشات each appear
 *     twice, listing the same routes); the first occurrence wins.
 *   - sidem's third level (کالا ها, دسته بندی, مقاله …) is flattened into its parent group,
 *     since the sidebar renders two levels.
 */
return new class extends Migration
{
    /**
     * sidem's group name => the name that group already has in category_permissions.
     *
     * Without this the migration would create a second category alongside the existing one
     * ("امور مالی" next to "تراکنش ها") and leave the original holding the permissions that
     * are not menu entries — two half-groups for one concept. Renaming keeps the id, so the
     * shahin-package pivot and every permission that is only an access key stay together.
     */
    private const ALIASES = [
        'امور مالی'          => 'تراکنش ها',
        'پرونده ها'          => 'پرونده',
        'انبارداری'          => 'انبار',
        'عاملیت ها'          => 'نمایندگی',
        'تسهیم درآمد'        => 'تسهیم درصدی درآمد',
        'مغازه های دیجیتال'  => 'مغازه دیجیتال',
        'قراردادها'          => 'قرارداد',
        'رزومه ها'           => 'رزومه',
        'نقش ها'             => 'امنیت',
        'سایت'               => 'محتوای سایت',
        'حساب کاربری من'     => 'همکاران',
        'پویش و جشنواره'     => 'سایر',
    ];

    /** group => [icon, items[]]; items are [permission name, route name, fallback display]. */
    private const SPEC = [
        ['داشبورد', 'ti-home', []],

        ['درخواست ها', 'ti-control-record', [
            ['callcenter-detail', 'callcenter.index', 'درخواست ها'],
            ['callcenter-cancellation', 'callcenter.cancellation', 'انصراف از دریافت پیامک'],
            ['callcenter-desktop', 'callcenter.desktop', 'میزکار درخواست ها'],
            ['callcenter-paid', 'callcenter.paid', 'پرداخت شده ها'],
            ['callcenter-suspension', 'callcenter.suspension', 'معلق ها'],
            ['callcenter-delete', 'callcenter.delete', 'سطل زباله'],
            ['suspensionStandard_list', 'suspensionStandard.index', 'استانداردهای تعلیق'],
        ]],

        ['محاسبه اقساط', 'ti-control-record', [
            ['installment-calculate', 'totalFactorItem.calculate', 'محاسبه اقساط'],
        ]],

        ['کاربران', 'ti-control-record', [
            ['sellers', 'sellers', 'فروشندگان'],
            ['user-detail', 'user.index', 'فهرست کاربران'],
            ['ghorfe_online_list_users', 'ghorfeOnlineList.users', 'فهرست کاربران مغازه دیجیتال'],
            ['employee-detail', 'employee.index', 'فهرست همکاران'],
            ['user-delete', 'user.delete', 'سطل زباله'],
            ['cash-debtors-list', 'accounting.all', 'حسابرسی'],
            ['user-attributes', 'disease.index', 'ویژگی ها'],
        ]],

        ['امور مالی', 'ti-control-record', [
            ['factor-detail', 'factor.index', 'فهرست تراکنش ها'],
            ['wallet', 'WalletTransaction.index', 'فهرست شارژ کیف پول'],
            ['factor-desktop', 'factor.desktop', 'میزکار تراکنش ها'],
            ['system-income', 'system.income', 'درآمد ماهانه و سالانه سیستم'],
            ['factor-delete', 'factor.delete', 'سطل زباله'],
        ]],

        ['خزانه', 'ti-control-record', [
            ['treasury-calendar', 'treasury.calendar', 'تقویم'],
            ['list-check', 'check.index', 'چک ها'],
            ['list-promissory', 'promissory.index', 'سفته ها'],
        ]],

        ['حساب کاربری من', 'ti-control-record', [
            ['financialReport_employee_list', 'employee.financialReport.list', 'عملکرد مالی همکاران'],
        ]],

        ['جلسات', 'ti-control-record', [
            ['session-desktop-confirm', 'session.desktop.confirm', 'میزکار تایید جلسات'],
            ['session-desktop-complete', 'session.desktop.complete', 'میزکار مراجعات'],
            ['session-detail', 'session.index', 'جلسات'],
        ]],

        ['پرونده ها', 'ti-control-record', [
            ['installments-file-desktop', 'totalFactorItem.desktop', 'میزکار تایید پرونده های اقساطی'],
            ['file-desktop', 'factorItem.desktop', 'پرونده های قابل اجرا'],
            ['file-detail', 'factorItem.index', 'کل پرونده ها'],
            ['installments-file-list', 'totalFactorItem.index', 'پرونده های اقساطی'],
            ['file-report', 'totalFactorItem.reports', 'گزارش مالی پرونده ها'],
            ['file-report-installments', 'totalFactorItemInstallment.index', 'بدهکاری اقساط'],
        ]],

        ['تسهیم درآمد', 'ti-control-record', [
            ['percentageAllocationEmployee_all', 'percentageAllocationEmployee.index', 'فهرست تسهیم'],
            ['percentageAllocationEmployee_report', 'percentageAllocationEmployee.report', 'گزارش تسهیم'],
        ]],

        ['انبارداری', 'ti-control-record', [
            ['store-room-detail', 'storeRoom.index', 'فهرست انبارها'],
            ['supplier', 'supplier.index', 'فهرست تامین کنندگان'],
            ['change-store-room', 'changeStoreRoom.index', 'خروج از انبار'],
            ['request', 'request.create', 'ایجاد درخواست'],
            ['request-detail', 'request.index', 'درخواست ها'],
        ]],

        ['فاکتور های خرید', 'ti-control-record', [
            ['purchase-invoice', 'purchaseInvoice.desktop', 'میزکار فاکتورهای خرید'],
            ['purchase-invoice-detail', 'purchaseInvoice.index', 'فهرست فاکتورهای خرید'],
            ['purchase-invoice-create', 'purchaseInvoice.create', 'فاکتور خرید جدید'],
        ]],

        ['خدمات', 'ti-control-record', [
            ['service', 'service.index', 'خدمات'],
            ['service-tariff', 'tariff.index', 'دوره تعرفه'],
        ]],

        ['مراکز', 'ti-control-record', [
            ['center-request-detail', 'centerRequest.index', 'درخواست مراکز'],
            ['center', 'center.index', 'فهرست مراکز'],
        ]],

        ['تماس با ما', 'ti-control-record', [
            ['contact-detail', 'contact.index', 'تماس با ما'],
        ]],

        ['طرح های مالی', 'ti-control-record', [
            ['financial-plans-type', 'financialPlansType.index', 'طرح های مالی'],
        ]],

        ['عاملیت ها', 'ti-control-record', [
            ['agency', 'agency.index', 'درخواست عاملیت'],
            ['representation', 'representation.index', 'فهرست عاملیت ها'],
            ['agency-type', 'agencyType.index', 'انواع عاملیت'],
        ]],

        ['مغازه های دیجیتال', 'ti-control-record', [
            ['ghorfe_online_list', 'ghorfeOnlineList.index', 'فهرست مغازه های دیجیتال'],
            ['ghorfe_online_list_create', 'ghorfeOnlineList.create', 'ایجاد مغازه های دیجیتال'],
        ]],

        ['قالب های پیامکی', 'ti-control-record', [
            ['sms-template', 'smsTemplate.index', 'فهرست قالب های پیامکی'],
            ['sms-template-create', 'smsTemplate.create', 'ایجاد قالب پیامکی'],
        ]],

        ['سازمان ها', 'ti-control-record', [
            ['organization-list', 'organization.index', 'فهرست سازمان ها'],
            ['create_organ', 'organization.create', 'سازمان جدید'],
        ]],

        ['تک صفحه ها', 'ti-control-record', [
            ['single_web_page', 'singleWebPage.index', 'فهرست تک صفحه ها'],
        ]],

        ['قراردادها', 'ti-control-record', [
            ['arrange-contract-detail', 'contract.index', 'فهرست قراردادها'],
            ['contract', 'contract.create', 'میزکار'],
            ['contract-form', 'contractForm.index', 'فرم قرارداد های پرونده های اقساطی'],
        ]],

        ['رزومه ها', 'ti-control-record', [
            ['resume-detail', 'resume.index', 'فهرست رزومه ها'],
            ['resume-desktop', 'resume.desktop', 'میزکار رزومه ها'],
        ]],

        ['موقعیت شغلی', 'ti-control-record', [
            ['recruitment_notice', 'recruitmentNotice.index', 'موقعیت های شغلی'],
            ['recruitment-notice-create', 'recruitmentNotice.create', 'موقعیت شغلی جدید'],
        ]],

        ['نقش ها', 'ti-control-record', [
            ['role', 'role.index', 'فهرست نقش ها'],
            ['role-create', 'role.create', 'نقش جدید'],
        ]],

        ['راهنمای سایت', 'ti-control-record', [
            ['help', 'help.index', 'راهنمای سایت'],
        ]],

        ['پویش و جشنواره', 'ti-control-record', [
            ['campaign', 'campaign.index', 'فهرست پویش ها'],
            ['coupon', 'coupon.index', 'فهرست جشنواره'],
            ['coupon-create', 'coupon.create', 'جشنواره جدید'],
        ]],

        ['پیامک', 'ti-control-record', [
            ['sms-list', 'groupsms.index', 'فهرست پیامک ها'],
            ['send-group-sms', 'groupsms.create', 'پیامک گروهی'],
            ['send-single-sms', 'sms.single', 'پیامک تکی'],
        ]],

        ['لاله کارت', 'ti-control-record', [
            ['lalecard', 'lalecard.index', 'لاله کارت'],
        ]],

        ['شماره های ورودی', 'ti-control-record', [
            ['phone', 'phone.index', 'شماره های ورودی'],
        ]],

        ['ایمیل ها', 'ti-control-record', [
            ['newsletter', 'newsletter.index', 'ایمیل ها'],
        ]],

        ['قوانین سایت', 'ti-control-record', [
            ['update_site_rules', 'siteRules.index', 'قوانین سایت'],
        ]],

        ['لینک های کوتاه', 'fa fa-link', [
            ['access_short_link', 'short-link.index', 'لینک های کوتاه'],
        ]],

        ['قوانین پرسنل', 'fa fa-file', [
            ['employee-terms', 'employee.terms.index', 'قوانین پرسنل'],
        ]],

        ['تنظیمات', 'fa fa-gears', [
            ['setting', 'settings.index', 'تنظیمات'],
            ['menu_setting', 'menuSetting.index', 'لیست منوها'],
        ]],

        ['سایت', 'ti-control-record', [
            ['products', 'products.index', 'مواد اولیه'],
            ['periodic-prices', 'periodicPrices.index', 'تخفیف های هوشمند'],
            ['pack', 'pack.index', 'پک کالا ها'],
            ['product-order', 'productOrder.index', 'مدیریت سفارشات کالا'],
            ['categories', 'category.index', 'لیست دسته بندی'],
            ['attributes-category', 'attributeGroup.index', 'گروه های ویژگی'],
            ['attribute-request', 'attributeRequest.suggest', 'ویژگی های درخواستی'],
            ['color', 'color.index', 'رنگ'],
            ['brand', 'brand.index', 'برند'],
            ['slider', 'slider.index', 'اسلایدر'],
            ['about-page', 'about.index', 'درباره ما'],
            ['user-invite', 'userInvite.index', 'دعوت از کاربران'],
            ['invite-category', 'inviteCategory.index', 'دسته بندی دعوت'],
            ['article', 'categoryArticle.index', 'دسته بندی مقاله'],
            ['article-list', 'article.index', 'مقاله ها'],
            ['news', 'categoryNews.index', 'دسته بندی اخبار'],
            ['news-list', 'news.index', 'اخبار'],
            ['validation', 'validation.index', 'اعتبارسنجی کالا ها'],
            ['comment-detail', 'comment.index', 'نظرات'],
            ['coupon-variety', 'coupons.discountVarietyProduct', 'کد تخفیف تنوع کالا ها توسط مراکز'],
        ]],
    ];

    public function up(): void
    {
        $placed = [];
        $groupPosition = 0;

        foreach (self::SPEC as [$groupName, $icon, $items]) {

            $groupPosition++;

            $categoryId = $this->resolveCategory($groupName, $icon, $groupPosition);

            $itemPosition = 0;

            foreach ($items as [$permissionName, $routeName, $displayName]) {

                // Never point the menu at something that cannot be resolved into a URL.
                if (! Route::has($routeName) || MenuService::linkToUrl($routeName) === null) {
                    continue;
                }

                $itemPosition++;

                $existing = DB::table('permissions')->where('name', $permissionName)->first();

                if ($existing) {
                    DB::table('permissions')->where('id', $existing->id)->update([
                        'cat_id'       => $categoryId,
                        'position'     => $itemPosition,
                        'link'         => $routeName,
                        'isMenu'       => 1,
                        // The Persian label an administrator may have edited wins; this only
                        // fills the gap when there is none.
                        'display_name' => filled($existing->display_name) ? $existing->display_name : $displayName,
                        'updated_at'   => now(),
                    ]);

                    $placed[] = $existing->id;
                    continue;
                }

                $placed[] = DB::table('permissions')->insertGetId([
                    'name'         => $permissionName,
                    'display_name' => $displayName,
                    'description'  => $displayName,
                    'link'         => $routeName,
                    'cat_id'       => $categoryId,
                    'position'     => $itemPosition,
                    'isMenu'       => 1,
                    'is_active'    => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // Everything the legacy sidebar did not show stops being a menu entry. The permission
        // itself, its name and every role grant are untouched — only its visibility.
        DB::table('permissions')->whereNotIn('id', $placed)->update(['isMenu' => 0]);

        // Groups that ended up with nothing to show are pushed below the ones that do.
        DB::table('category_permissions')
            ->whereNotIn('name', array_column(self::SPEC, 0))
            ->update(['position' => 900]);

        $this->grantNewPermissionsToSuperadmin();

        MenuService::flush();
    }

    /**
     * Find the row this sidem group should use, renaming or merging rather than duplicating.
     *
     * Handles a re-run over a database where a previous pass already created the twin: the
     * alias's permissions are moved across and the emptied row is dropped.
     */
    private function resolveCategory(string $groupName, string $icon, int $position): int
    {
        $target = DB::table('category_permissions')->where('name', $groupName)->value('id');
        $alias  = self::ALIASES[$groupName] ?? null;
        $aliasId = $alias ? DB::table('category_permissions')->where('name', $alias)->value('id') : null;

        if ($target && $aliasId && $target !== $aliasId) {
            DB::table('permissions')->where('cat_id', $aliasId)->update(['cat_id' => $target]);
            DB::table('category_permission_shahin_package')->where('category_permission_id', $aliasId)
                ->update(['category_permission_id' => $target]);
            DB::table('category_permissions')->where('id', $aliasId)->delete();
        }

        if (! $target && $aliasId) {
            DB::table('category_permissions')->where('id', $aliasId)->update(['name' => $groupName]);
            $target = $aliasId;
        }

        if (! $target) {
            return DB::table('category_permissions')->insertGetId([
                'name'       => $groupName,
                'icon'       => $icon,
                'position'   => $position,
                'is_active'  => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('category_permissions')->where('id', $target)->update([
            'position'   => $position,
            // Only fill an icon in; never blank one an administrator has chosen.
            'icon'       => DB::raw("COALESCE(NULLIF(icon, ''), " . DB::getPdo()->quote($icon) . ")"),
            'is_active'  => 1,
            'updated_at' => now(),
        ]);

        return $target;
    }

    /**
     * A permission created here belongs to no role, so nothing could pass an @permission()
     * check against it. superadmin is meant to hold everything.
     */
    private function grantNewPermissionsToSuperadmin(): void
    {
        $roleId = DB::table('roles')->where('name', 'superadmin')->value('id');

        if (! $roleId) {
            return;
        }

        $names = [];

        foreach (self::SPEC as [, , $items]) {
            foreach ($items as [$permissionName]) {
                $names[] = $permissionName;
            }
        }

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');

        $held = DB::table('permission_role')->where('role_id', $roleId)->pluck('permission_id')->all();

        $rows = $ids->diff($held)
            ->map(fn($id) => ['permission_id' => $id, 'role_id' => $roleId])
            ->values()
            ->all();

        if ($rows) {
            DB::table('permission_role')->insert($rows);
        }
    }

    /**
     * There is no meaningful inverse: the previous positions/links were the unordered state
     * this migration exists to replace, and reversing would only restore that. Menu layout is
     * editable from تنظیمات منو, so it is recovered there rather than by rolling back.
     */
    public function down(): void
    {
        MenuService::flush();
    }
};
