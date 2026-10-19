<?php

use App\Models\GhorfeOnlineList;
use App\Models\SmsMessageGroup;
use App\Models\SmsMessageTemplate;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SLUG = 'monasebati';

    private const SUBGROUPS = [
        ['name' => 'تبریک مناسبت‌ها', 'slug' => 'tabrik-monasebat'],
        ['name' => 'تعطیلات و جشن‌ها', 'slug' => 'tatilat-va-jashn'],
    ];

    private const TEMPLATES = [
        [
            'title' => 'تبریک نوروز',
            'content' => "مشتری گرامی، فرارسیدن سال نو را به شما تبریک می‌گوییم.\n{نام کسب‌وکار}",
        ],
        [
            'title' => 'تبریک مناسبت‌های ملی و مذهبی',
            'content' => "با آرزوی بهترین‌ها به مناسبت این روز خجسته.\n{نام کسب‌وکار}",
        ],
    ];

    /**
     * Seed the «مناسبتی/تبریک» (event / holiday) group, its subgroups and two promotional
     * templates for every business. Idempotent: everything goes through firstOrCreate.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sms_message_groups') || !Schema::hasTable('sms_message_templates')) {
            return;
        }

        $adminId = User::where('level', 'superAdmin')->value('id') ?? User::value('id');

        foreach (GhorfeOnlineList::all() as $biz) {
            $parent = SmsMessageGroup::firstOrCreate(
                ['ghorfe_online_list_id' => $biz->id, 'parent_id' => null, 'slug' => self::SLUG],
                ['name' => 'مناسبتی/تبریک']
            );

            foreach (self::SUBGROUPS as $sub) {
                SmsMessageGroup::firstOrCreate(
                    ['ghorfe_online_list_id' => $biz->id, 'parent_id' => $parent->id, 'slug' => $sub['slug']],
                    ['name' => $sub['name']]
                );
            }

            if (!$adminId) {
                continue;
            }

            foreach (self::TEMPLATES as $tpl) {
                SmsMessageTemplate::firstOrCreate(
                    ['ghorfe_online_list_id' => $biz->id, 'title' => $tpl['title']],
                    [
                        'content' => $tpl['content'],
                        'sms_message_group_id' => $parent->id,
                        'service_type' => 'advertising',
                        'status' => 'active',
                        'created_by' => $adminId,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('sms_message_groups') || !Schema::hasTable('sms_message_templates')) {
            return;
        }

        $groupIds = SmsMessageGroup::where('slug', self::SLUG)->pluck('id');
        SmsMessageTemplate::whereIn('sms_message_group_id', $groupIds)
            ->whereIn('title', array_column(self::TEMPLATES, 'title'))
            ->delete();
    }
};
