<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SMS_CATEGORY_ID = 28;
    private const DASHBOARD_LINK = 'admin.sms.dashboard.index';
    private const DASHBOARD_ORIGINAL_CAT_ID = 1;
    private const DASHBOARD_ORIGINAL_POSITION = 1;

    /** Pages that exist under the SMS category but were hidden from the sidebar. */
    private const SHOW_LINKS = [
        'admin.sms.ai.index',
        'admin.sms.reports.otp.index',
        'admin.sms.financial.otp.index',
        'admin.sms.settings.index',
    ];

    /** Page that was intentionally hidden and must stay out of the sidebar. */
    private const HIDE_LINKS = [
        'smsTemplate.create',
    ];

    /**
     * Wires the SMS dashboard, AI, reports, financial and settings pages into the
     * sidebar menu and hides the template-create page. Matches rows by `link`.
     *
     * The dashboard row (name "sms-dashboard") already exists under category 1 and
     * `permissions.name` is unique, so it is moved into the SMS category instead of
     * inserting a duplicate.
     */
    public function up(): void
    {
        DB::table('permissions')
            ->whereIn('link', self::SHOW_LINKS)
            ->update(['isMenu' => 1]);

        DB::table('permissions')
            ->whereIn('link', self::HIDE_LINKS)
            ->update(['isMenu' => 0]);

        DB::table('permissions')
            ->where('link', self::DASHBOARD_LINK)
            ->update([
                'cat_id' => self::SMS_CATEGORY_ID,
                'isMenu' => 1,
                'position' => 0,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverses the isMenu flips and moves the dashboard row back to its original category.
     */
    public function down(): void
    {
        DB::table('permissions')
            ->whereIn('link', self::SHOW_LINKS)
            ->update(['isMenu' => 0]);

        DB::table('permissions')
            ->whereIn('link', self::HIDE_LINKS)
            ->update(['isMenu' => 1]);

        DB::table('permissions')
            ->where('link', self::DASHBOARD_LINK)
            ->update([
                'cat_id' => self::DASHBOARD_ORIGINAL_CAT_ID,
                'isMenu' => 0,
                'position' => self::DASHBOARD_ORIGINAL_POSITION,
                'updated_at' => now(),
            ]);
    }
};
