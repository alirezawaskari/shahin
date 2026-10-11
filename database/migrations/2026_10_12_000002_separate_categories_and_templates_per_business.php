<?php

use App\Models\GhorfeOnlineList;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add ghorfe_online_list_id to sms_message_groups
        if (Schema::hasTable('sms_message_groups') && !Schema::hasColumn('sms_message_groups', 'ghorfe_online_list_id')) {
            Schema::table('sms_message_groups', function (Blueprint $table) {
                $table->unsignedBigInteger('ghorfe_online_list_id')->nullable()->after('parent_id')->index();
                $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->cascadeOnDelete();
            });
        }

        // 2. Add ghorfe_online_list_id to sms_message_templates
        if (Schema::hasTable('sms_message_templates') && !Schema::hasColumn('sms_message_templates', 'ghorfe_online_list_id')) {
            Schema::table('sms_message_templates', function (Blueprint $table) {
                $table->unsignedBigInteger('ghorfe_online_list_id')->nullable()->after('sms_message_group_id')->index();
                $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->cascadeOnDelete();
            });
        }

        $adminUserId = User::first()?->id ?? 3;

        // 3. Migrate existing data:
        // Assign existing groups and templates without business_id to Business ID 2 (Business A)
        $firstBusiness = GhorfeOnlineList::first();
        if ($firstBusiness) {
            SmsMessageGroup::whereNull('ghorfe_online_list_id')->update([
                'ghorfe_online_list_id' => $firstBusiness->id,
            ]);

            SmsMessageTemplate::whereNull('ghorfe_online_list_id')->update([
                'ghorfe_online_list_id' => $firstBusiness->id,
            ]);
        }

        // 4. Ensure other businesses have their own isolated groups and templates
        $allBusinesses = GhorfeOnlineList::all();
        foreach ($allBusinesses as $biz) {
            if ($firstBusiness && $biz->id === $firstBusiness->id) {
                continue;
            }

            // Seed independent base groups for this business
            $defaultGroups = [
                [
                    'name' => 'مالی',
                    'slug' => 'mali',
                    'subgroups' => [
                        ['name' => 'واریز پیش‌پرداخت', 'slug' => 'variz-pish-pardakht'],
                        ['name' => 'پرداخت فاکتور', 'slug' => 'pardakht-factor'],
                    ],
                ],
                [
                    'name' => 'حقوقی',
                    'slug' => 'hoghoghi',
                    'subgroups' => [
                        ['name' => 'اخطار', 'slug' => 'ekhtar'],
                        ['name' => 'ارجاع به حقوقی', 'slug' => 'erja-be-hoghoghi'],
                    ],
                ],
                [
                    'name' => 'پشتیبانی',
                    'slug' => 'poshtibani',
                    'subgroups' => [
                        ['name' => 'عدم پاسخگویی به تماس', 'slug' => 'adam-pasokhgooei-be-tamas'],
                    ],
                ],
                [
                    'name' => 'اجرایی',
                    'slug' => 'ejraei',
                    'subgroups' => [
                        ['name' => 'نوبت‌دهی', 'slug' => 'nobat-dehi'],
                        ['name' => 'تأیید جلسه', 'slug' => 'taeed-jalase'],
                    ],
                ],
                [
                    'name' => 'فروش',
                    'slug' => 'foroush',
                    'subgroups' => [
                        ['name' => 'پیگیری مشتری', 'slug' => 'peygiri-moshtari'],
                    ],
                ],
            ];

            $groupMap = [];
            foreach ($defaultGroups as $groupData) {
                $parent = SmsMessageGroup::firstOrCreate(
                    [
                        'ghorfe_online_list_id' => $biz->id,
                        'slug' => $groupData['slug'],
                    ],
                    [
                        'name' => $groupData['name'],
                        'parent_id' => null,
                    ]
                );
                $groupMap[$groupData['slug']] = $parent->id;

                foreach ($groupData['subgroups'] as $sub) {
                    SmsMessageGroup::firstOrCreate(
                        [
                            'ghorfe_online_list_id' => $biz->id,
                            'parent_id' => $parent->id,
                            'slug' => $sub['slug'],
                        ],
                        [
                            'name' => $sub['name'],
                        ]
                    );
                }
            }

            // Seed default templates for this business
            $defaultTemplates = [
                [
                    'title' => 'کد تایید ورود (OTP)',
                    'content' => "کد تایید شما: {کد}\nکسب‌وکار: {نام کسب‌وکار}\nلغو ۱۱",
                    'group_slug' => null,
                    'service_type' => 'otp',
                ],
                [
                    'title' => 'صدور فاکتور جدید',
                    'content' => "مشتری گرامی {نام} {نام خانوادگی}،\nفاکتور شماره {شماره فاکتور} برای شما صادر شد.\nمبلغ: {مبلغ فاکتور} ریال\nلینک پرداخت: {لینک پرداخت}",
                    'group_slug' => 'mali',
                    'service_type' => 'normal',
                ],
                [
                    'title' => 'یادآوری نوبت و جلسه',
                    'content' => "کاربر گرامی {نام}،\nنوبت شما برای تاریخ {تاریخ} ساعت {ساعت} ثبت گردید.\nکسب‌وکار: {نام کسب‌وکار}",
                    'group_slug' => 'poshtibani',
                    'service_type' => 'normal',
                ],
                [
                    'title' => 'جشنواره تخفیف ویژه مشتریان',
                    'content' => "تخفیف ویژه تا ۵۰ درصد!\nجشنواره اختصاصی محصولات {نام کسب‌وکار}\nورود به فروشگاه: {لینک}",
                    'group_slug' => 'foroush',
                    'service_type' => 'advertising',
                ],
            ];

            foreach ($defaultTemplates as $tData) {
                $groupId = !empty($tData['group_slug']) ? ($groupMap[$tData['group_slug']] ?? null) : null;
                SmsMessageTemplate::firstOrCreate(
                    [
                        'ghorfe_online_list_id' => $biz->id,
                        'title' => $tData['title'],
                    ],
                    [
                        'content' => $tData['content'],
                        'sms_message_group_id' => $groupId,
                        'service_type' => $tData['service_type'],
                        'status' => 'active',
                        'created_by' => $adminUserId,
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('sms_message_templates') && Schema::hasColumn('sms_message_templates', 'ghorfe_online_list_id')) {
            Schema::table('sms_message_templates', function (Blueprint $table) {
                $table->dropForeign(['ghorfe_online_list_id']);
                $table->dropColumn('ghorfe_online_list_id');
            });
        }

        if (Schema::hasTable('sms_message_groups') && Schema::hasColumn('sms_message_groups', 'ghorfe_online_list_id')) {
            Schema::table('sms_message_groups', function (Blueprint $table) {
                $table->dropForeign(['ghorfe_online_list_id']);
                $table->dropColumn('ghorfe_online_list_id');
            });
        }
    }
};
