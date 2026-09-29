<?php

use App\Services\V1\Admin\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Give every menu entry the label the legacy sidebar used.
 *
 * 2026_08_27_120000 put the entries in sidem.blade.php's groups, order and links but left
 * display_name alone, so the sidebar still read with the old permission descriptions —
 * "مشاهده تمامی درخواست ها" where sidem said "درخواست ها", "کیف پول" where it said
 * "فهرست شارژ کیف پول". This is the text of each <a> in that file.
 *
 * Only the entries the sidebar shows are touched. A permission that is purely an access key
 * keeps its descriptive title, and no `name` is touched — that is the Laratrust key.
 *
 * Where sidem nested a third level, the leaf's text is used, disambiguated by its parent
 * when the leaf alone would be ambiguous ("دسته بندی" under مقاله -> "دسته بندی مقاله").
 */
return new class extends Migration
{
    /** permission name => the label sidem.blade.php rendered for it. */
    private const LABELS = [
        // درخواست ها
        'callcenter-detail'                   => 'درخواست ها',
        'callcenter-cancellation'             => 'انصراف از دریافت پیامک',
        'callcenter-desktop'                  => 'میزکار درخواست ها',
        'callcenter-paid'                     => 'پرداخت شده ها',
        'callcenter-suspension'               => 'معلق ها',
        'callcenter-delete'                   => 'سطل زباله',
        'suspensionStandard_list'             => 'استانداردهای تعلیق',

        // محاسبه اقساط
        'installment-calculate'               => 'محاسبه اقساط',

        // کاربران
        'sellers'                             => 'فروشندگان',
        'user-detail'                         => 'فهرست کاربران',
        'ghorfe_online_list_users'            => 'فهرست کاربران مغازه دیجیتال',
        'employee-detail'                     => 'فهرست همکاران',
        'user-delete'                         => 'سطل زباله',
        'cash-debtors-list'                   => 'حسابرسی',
        'user-attributes'                     => 'سلامتی',

        // امور مالی
        'factor-detail'                       => 'فهرست تراکنش ها',
        'wallet'                              => 'فهرست شارژ کیف پول',
        'factor-desktop'                      => 'میزکار تراکنش ها',
        'system-income'                       => 'درآمد ماهانه و سالانه سیستم',
        'factor-delete'                       => 'سطل زباله',

        // خزانه
        'treasury-calendar'                   => 'تقویم',
        'list-check'                          => 'چک ها',
        'list-promissory'                     => 'سفته ها',

        // حساب کاربری من
        'financialReport_employee_list'       => 'عملکرد مالی همکاران',

        // جلسات
        'session-desktop-confirm'             => 'میزکار تایید جلسات',
        'session-desktop-complete'            => 'میزکار مراجعات',
        'session-detail'                      => 'جلسات',

        // پرونده ها
        'installments-file-desktop'           => 'میزکار تایید پرونده های اقساطی',
        'file-desktop'                        => 'پرونده های قابل اجرا',
        'file-detail'                         => 'کل پرونده ها',
        'installments-file-list'              => 'پرونده های اقساطی',
        'file-report'                         => 'گزارش مالی پرونده ها',
        'file-report-installments'            => 'بدهکاری اقساط',

        // تسهیم درآمد
        'percentageAllocationEmployee_all'    => 'فهرست تسهیم',
        'percentageAllocationEmployee_report' => 'گزارش تسهیم',

        // انبارداری
        'store-room-detail'                   => 'فهرست انبارها',
        'supplier'                            => 'فهرست تامین کنندگان',
        'change-store-room'                   => 'خروج از انبار',
        'request'                             => 'ایجاد درخواست',
        'request-detail'                      => 'درخواست ها',

        // فاکتور های خرید
        'purchase-invoice'                    => 'میزکار فاکتورهای خرید',
        'purchase-invoice-detail'             => 'فهرست فاکتورهای خرید',
        'purchase-invoice-create'             => 'فاکتور خرید جدید',

        // خدمات
        'service'                             => 'خدمات',
        'service-tariff'                      => 'دوره تعرفه',

        // مستاجرین و شرکای تجاری
        'center-request-detail'               => 'درخواست مستاجرین و شرکای تجاری',
        'center'                              => 'فهرست مستاجرین و شرکای تجاری',

        // تماس با ما / طرح های مالی
        'contact-detail'                      => 'تماس با ما',
        'financial-plans-type'                => 'طرح های مالی',

        // نمایندگی ها
        'agency'                              => 'درخواست نمایندگی',
        'representation'                      => 'فهرست نمایندگی ها',
        'agency-type'                         => 'انواع نمایندگی',

        // مغازه های دیجیتال
        'ghorfe_online_list'                  => 'فهرست مغازه های دیجیتال',
        'ghorfe_online_list_create'           => 'ایجاد مغازه های دیجیتال',

        // قالب های پیامکی
        'sms-template'                        => 'فهرست قالب های پیامکی',
        'sms-template-create'                 => 'ایجاد قالب پیامکی',

        // سازمان ها / تک صفحه ها
        'organization-list'                   => 'فهرست سازمان ها',
        'create_organ'                        => 'سازمان جدید',
        'single_web_page'                     => 'فهرست تک صفحه ها',

        // قراردادها
        'arrange-contract-detail'             => 'فهرست قراردادها',
        'contract'                            => 'میزکار',
        'contract-form'                       => 'فرم قرارداد های پرونده های اقساطی',

        // رزومه ها / موقعیت شغلی
        'resume-detail'                       => 'فهرست رزومه ها',
        'resume-desktop'                      => 'میزکار رزومه ها',
        'recruitment_notice'                  => 'موقعیت های شغلی',
        'recruitment-notice-create'           => 'موقعیت شغلی جدید',

        // نقش ها / راهنما
        'role'                                => 'فهرست نقش ها',
        'role-create'                         => 'نقش جدید',
        'help'                                => 'راهنمای سایت',

        // پویش و جشنواره
        'campaign'                            => 'فهرست پویش ها',
        'coupon'                              => 'فهرست جشنواره',
        'coupon-create'                       => 'جشنواره جدید',

        // پیامک
        'sms-list'                            => 'فهرست پیامک ها',
        'send-group-sms'                      => 'پیامک گروهی',
        'send-single-sms'                     => 'پیامک تکی',

        // top-level single links
        'lalecard'                            => 'لاله کارت',
        'phone'                               => 'شماره های ورودی',
        'newsletter'                          => 'ایمیل ها',
        'update_site_rules'                   => 'قوانین سایت',
        'access_short_link'                   => 'لینک های کوتاه',
        'employee-terms'                      => 'قوانین پرسنل',
        'setting'                             => 'تنظیمات',

        // سایت
        'products'                            => 'مواد اولیه',
        'periodic-prices'                     => 'تخفیف های هوشمند',
        'pack'                                => 'پک کالا ها',
        'categories'                          => 'لیست دسته بندی',
        'attributes-category'                 => 'گروه های ویژگی',
        'attribute-request'                   => 'ویژگی های درخواستی',
        'color'                               => 'رنگ',
        'brand'                               => 'برند',
        'slider'                              => 'اسلایدر',
        'about-page'                          => 'درباره ما',
        'user-invite'                         => 'دعوت از کاربران',
        'invite-category'                     => 'دسته بندی دعوت',
        'article'                             => 'دسته بندی مقاله',
        'article-list'                        => 'مقاله ها',
        'news'                                => 'دسته بندی اخبار',
        'news-list'                           => 'اخبار',
        'validation'                          => 'اعتبارسنجی کالا ها',
        'comment-detail'                      => 'نظرات',
        'coupon-variety'                      => 'کد تخفیف تنوع کالا ها توسط مستاجرین و شرکای تجاری',
    ];

    public function up(): void
    {
        foreach (self::LABELS as $name => $label) {
            DB::table('permissions')
                ->where('name', $name)
                ->update(['display_name' => $label, 'updated_at' => now()]);
        }

        MenuService::flush();
    }

    /**
     * The labels this replaced were permission descriptions rather than menu titles, and the
     * screen exists to edit them, so there is nothing meaningful to restore.
     */
    public function down(): void
    {
        MenuService::flush();
    }
};
