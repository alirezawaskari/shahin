<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TEMPLATE_CATEGORY_NAME = 'قالب های پیامکی';
    private const CRM_CATEGORY_NAME = 'مدیریت پیامک (SMS) CRM';
    private const TEMPLATE_PERMISSION_NAMES = ['sms-template', 'sms-template-create'];

    /**
     * Moves every permission from the legacy "قالب های پیامکی" category into the
     * unified "مدیریت پیامک (SMS) CRM" category and deactivates the legacy row
     * (kept, not deleted, so the change stays reversible).
     */
    public function up(): void
    {
        $templateId = DB::table('category_permissions')
            ->where('name', self::TEMPLATE_CATEGORY_NAME)
            ->value('id');
        $crmId = DB::table('category_permissions')
            ->where('name', self::CRM_CATEGORY_NAME)
            ->value('id');

        if (!$templateId || !$crmId) {
            return;
        }

        DB::table('permissions')
            ->where('cat_id', $templateId)
            ->update(['cat_id' => $crmId]);

        DB::table('category_permissions')
            ->where('id', $templateId)
            ->update(['is_active' => 0]);
    }

    /**
     * Re-activates the legacy template category and moves the template
     * permissions back to it.
     */
    public function down(): void
    {
        $templateId = DB::table('category_permissions')
            ->where('name', self::TEMPLATE_CATEGORY_NAME)
            ->value('id');

        if (!$templateId) {
            return;
        }

        DB::table('permissions')
            ->whereIn('name', self::TEMPLATE_PERMISSION_NAMES)
            ->update(['cat_id' => $templateId]);

        DB::table('category_permissions')
            ->where('id', $templateId)
            ->update(['is_active' => 1]);
    }
};
