<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables/columns that point at sms_message_groups.id (besides sms_message_groups.parent_id). */
    private const GROUP_REFERENCES = [
        ['sms_message_templates', 'sms_message_group_id'],
        ['sms_messages', 'sms_message_group_id'],
        ['sms_campaigns', 'sms_message_group_id'],
        ['groupsms', 'sms_message_group_id'],
    ];

    /** Tables/columns that point at sms_message_templates.id. */
    private const TEMPLATE_REFERENCES = [
        ['sms_messages', 'sms_message_template_id'],
        ['sms_campaigns', 'sms_message_template_id'],
        ['sms_message_template_histories', 'sms_message_template_id'],
    ];

    /** Old spellings (no ZWNJ) written by the former SmsMessageGroupSeeder -> canonical spelling. */
    private const CANONICAL_NAMES = [
        'nobat-dehi' => ['from' => 'نوبتدهی', 'to' => 'نوبت‌دهی'],
        'variz-pish-pardakht' => ['from' => 'واریز پیشپرداخت', 'to' => 'واریز پیش‌پرداخت'],
    ];

    /**
     * Merge EXACT duplicates only, per business:
     *  - groups with the same normalized name (ZWNJ / spaces / Arabic ye+kaf ignored) under the same
     *    parent AND business: keep the oldest, repoint every reference, delete the duplicate only
     *    once nothing references it any more;
     *  - templates with identical business, title, content, type, service type, status, OTP category
     *    and group: keep the oldest, repoint references, soft-delete the duplicate.
     * Rows of DIFFERENT businesses, or that differ in content, are never touched. Idempotent.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sms_message_groups')) {
            return;
        }

        DB::transaction(function () {
            $this->mergeGroups();
            $this->canonicalizeGroupNames();

            if (Schema::hasTable('sms_message_templates')) {
                $this->mergeTemplates();
            }
        });
    }

    public function down(): void
    {
        // Data clean-up only; merged duplicates cannot be restored.
    }

    private function normalize(?string $value): string
    {
        $value = str_replace(["\u{200C}", "\u{200D}", "\u{200F}", "\u{200E}"], '', (string) $value);
        $value = strtr($value, ['ي' => 'ی', 'ك' => 'ک', 'ۀ' => 'ه']);
        $value = preg_replace('/\s+/u', '', $value) ?? $value;

        return mb_strtolower($value);
    }

    private function mergeGroups(): void
    {
        $hasBusiness = Schema::hasColumn('sms_message_groups', 'ghorfe_online_list_id');

        // Merging parents can make their (re-parented) children duplicates of each other: repeat.
        for ($pass = 0; $pass < 10; $pass++) {
            $buckets = [];
            foreach (DB::table('sms_message_groups')->orderBy('created_at')->orderBy('id')->get() as $row) {
                $key = ($hasBusiness ? ($row->ghorfe_online_list_id ?? 'null') : 'x')
                    . '|' . ($row->parent_id ?? 'null') . '|' . $this->normalize($row->name);
                $buckets[$key][] = $row->id;
            }

            $merged = false;
            foreach ($buckets as $ids) {
                if (count($ids) < 2) {
                    continue;
                }
                $keepId = array_shift($ids);
                foreach ($ids as $dupId) {
                    foreach (self::GROUP_REFERENCES as [$table, $column]) {
                        if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                            DB::table($table)->where($column, $dupId)->update([$column => $keepId]);
                        }
                    }
                    DB::table('sms_message_groups')->where('parent_id', $dupId)->update(['parent_id' => $keepId]);

                    if (!$this->groupStillReferenced($dupId)) {
                        DB::table('sms_message_groups')->where('id', $dupId)->delete();
                        $merged = true;
                    }
                }
            }

            if (!$merged) {
                break;
            }
        }
    }

    private function groupStillReferenced(int $id): bool
    {
        if (DB::table('sms_message_groups')->where('parent_id', $id)->exists()) {
            return true;
        }
        foreach (self::GROUP_REFERENCES as [$table, $column]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)
                && DB::table($table)->where($column, $id)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function canonicalizeGroupNames(): void
    {
        foreach (self::CANONICAL_NAMES as $slug => $names) {
            DB::table('sms_message_groups')
                ->where('slug', $slug)
                ->where('name', $names['from'])
                ->update(['name' => $names['to']]);
        }
    }

    private function mergeTemplates(): void
    {
        $columns = ['ghorfe_online_list_id', 'title', 'content', 'type', 'service_type', 'status', 'otp_category', 'sms_message_group_id'];
        $columns = array_values(array_filter($columns, fn ($c) => Schema::hasColumn('sms_message_templates', $c)));
        $softDeletes = Schema::hasColumn('sms_message_templates', 'deleted_at');

        $query = DB::table('sms_message_templates')->orderBy('created_at')->orderBy('id');
        if ($softDeletes) {
            $query->whereNull('deleted_at');
        }

        $buckets = [];
        foreach ($query->get() as $row) {
            $key = md5(json_encode(array_map(
                fn ($c) => in_array($c, ['title', 'content'], true) ? trim((string) $row->{$c}) : ($row->{$c} ?? null),
                $columns
            )));
            $buckets[$key][] = $row->id;
        }

        foreach ($buckets as $ids) {
            if (count($ids) < 2) {
                continue;
            }
            $keepId = array_shift($ids);
            foreach ($ids as $dupId) {
                foreach (self::TEMPLATE_REFERENCES as [$table, $column]) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                        DB::table($table)->where($column, $dupId)->update([$column => $keepId]);
                    }
                }

                $stillReferenced = false;
                foreach (self::TEMPLATE_REFERENCES as [$table, $column]) {
                    if (Schema::hasTable($table) && Schema::hasColumn($table, $column)
                        && DB::table($table)->where($column, $dupId)->exists()) {
                        $stillReferenced = true;
                    }
                }
                if ($stillReferenced) {
                    continue;
                }

                if ($softDeletes) {
                    DB::table('sms_message_templates')->where('id', $dupId)->update(['deleted_at' => now()]);
                } else {
                    DB::table('sms_message_templates')->where('id', $dupId)->delete();
                }
            }
        }
    }
};
