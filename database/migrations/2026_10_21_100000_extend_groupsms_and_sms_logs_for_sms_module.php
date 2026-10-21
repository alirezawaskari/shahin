<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The SMS module reads and writes many more attributes than the legacy `groupsms` and `sms_logs`
 * tables have columns for (cost, sender, panel type, page count, campaign counters, ledger amounts,
 * payment / contract-OTP payloads...). The models used to silently drop those attributes on save,
 * so they were never stored and always showed up empty.
 *
 * Every column is added only when missing, so the migration is safe to run on any environment.
 * Existing rows are kept; nothing is dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->extendGroupsms();
        $this->extendSmsLogs();
        $this->backfillGroupsms();
    }

    public function down(): void
    {
        // Intentionally left empty: dropping these columns would destroy stored SMS history and ledger data.
    }

    private function extendGroupsms(): void
    {
        if (!Schema::hasTable('groupsms')) {
            return;
        }

        // `status` was a legacy integer (0/1/2). The module stores readable states
        // (queued, sent, delivered, failed, pending_queue ...). Legacy values 0/1/2 are kept as '0'/'1'/'2'.
        $status = collect(Schema::getColumns('groupsms'))->firstWhere('name', 'status');
        if ($status && str_contains(strtolower((string) ($status['type_name'] ?? $status['type'] ?? '')), 'int')) {
            Schema::table('groupsms', function (Blueprint $table) {
                $table->string('status', 30)->default('0')->change();
            });
        }

        Schema::table('groupsms', function (Blueprint $table) {
            $add = function (string $column, callable $definition) use ($table) {
                if (!Schema::hasColumn('groupsms', $column)) {
                    $definition($table);
                }
            };

            $add('ghorfe_online_list_id', fn ($t) => $t->unsignedBigInteger('ghorfe_online_list_id')->nullable()->index());
            $add('direction', fn ($t) => $t->string('direction', 10)->default('outbound'));
            $add('service_type', fn ($t) => $t->string('service_type', 20)->nullable()->index());
            $add('name', fn ($t) => $t->string('name')->nullable());
            $add('sender_number_id', fn ($t) => $t->unsignedBigInteger('sender_number_id')->nullable());
            $add('sender_number', fn ($t) => $t->string('sender_number', 30)->nullable());
            $add('sender_phone', fn ($t) => $t->string('sender_phone', 30)->nullable());
            $add('recipient_phone', fn ($t) => $t->string('recipient_phone', 30)->nullable());
            $add('template_id', fn ($t) => $t->unsignedBigInteger('template_id')->nullable());
            $add('group', fn ($t) => $t->string('group', 100)->nullable());
            $add('classification', fn ($t) => $t->string('classification', 30)->default('regular'));
            $add('otp_category', fn ($t) => $t->string('otp_category', 30)->nullable());
            $add('character_count', fn ($t) => $t->unsignedInteger('character_count')->default(0));
            $add('page_count', fn ($t) => $t->unsignedInteger('page_count')->default(1));
            $add('cost', fn ($t) => $t->decimal('cost', 14, 2)->nullable());
            $add('cost_estimate', fn ($t) => $t->decimal('cost_estimate', 14, 2)->nullable());
            $add('tracking_id', fn ($t) => $t->string('tracking_id', 120)->nullable()->index());
            $add('delivery_status', fn ($t) => $t->string('delivery_status', 30)->nullable());
            $add('delivered_at', fn ($t) => $t->timestamp('delivered_at')->nullable());
            $add('error_reason', fn ($t) => $t->text('error_reason')->nullable());
            $add('user_id', fn ($t) => $t->unsignedInteger('user_id')->nullable()->index());
            $add('request_id', fn ($t) => $t->integer('request_id')->nullable()->index());
            $add('callcenter_id', fn ($t) => $t->unsignedInteger('callcenter_id')->nullable()->index());
            $add('created_by', fn ($t) => $t->unsignedInteger('created_by')->nullable());
            $add('sender_role', fn ($t) => $t->string('sender_role', 64)->nullable());
            $add('channel', fn ($t) => $t->string('channel', 32)->nullable());
            $add('is_shared', fn ($t) => $t->boolean('is_shared')->default(false));
            $add('is_bulk', fn ($t) => $t->boolean('is_bulk')->default(false));
            $add('ai_goal_prompt', fn ($t) => $t->text('ai_goal_prompt')->nullable());
            $add('filters', fn ($t) => $t->longText('filters')->nullable());
            $add('required_tier', fn ($t) => $t->string('required_tier', 20)->nullable());
            $add('recipient_count', fn ($t) => $t->unsignedInteger('recipient_count')->default(0));
            $add('total_recipients', fn ($t) => $t->unsignedInteger('total_recipients')->default(0));
            $add('sent_count', fn ($t) => $t->unsignedInteger('sent_count')->default(0));
            $add('success_count', fn ($t) => $t->unsignedInteger('success_count')->default(0));
            $add('failed_count', fn ($t) => $t->unsignedInteger('failed_count')->default(0));
            $add('pending_count', fn ($t) => $t->unsignedInteger('pending_count')->default(0));
            $add('scheduled_at', fn ($t) => $t->timestamp('scheduled_at')->nullable());
            $add('sent_at', fn ($t) => $t->timestamp('sent_at')->nullable());
            $add('completed_at', fn ($t) => $t->timestamp('completed_at')->nullable());
        });
    }

    private function extendSmsLogs(): void
    {
        if (!Schema::hasTable('sms_logs')) {
            return;
        }

        Schema::table('sms_logs', function (Blueprint $table) {
            $add = function (string $column, callable $definition) use ($table) {
                if (!Schema::hasColumn('sms_logs', $column)) {
                    $definition($table);
                }
            };

            $add('ghorfe_online_list_id', fn ($t) => $t->unsignedBigInteger('ghorfe_online_list_id')->nullable()->index());
            $add('user_id', fn ($t) => $t->unsignedInteger('user_id')->nullable()->index());
            $add('created_by', fn ($t) => $t->unsignedInteger('created_by')->nullable());
            $add('credit_type', fn ($t) => $t->string('credit_type', 20)->nullable()->index());
            $add('sender_number_id', fn ($t) => $t->unsignedBigInteger('sender_number_id')->nullable());
            $add('sender_number', fn ($t) => $t->string('sender_number', 30)->nullable());
            $add('charge_amount', fn ($t) => $t->decimal('charge_amount', 14, 2)->default(0));
            $add('consume_amount', fn ($t) => $t->decimal('consume_amount', 14, 2)->default(0));
            $add('balance_before', fn ($t) => $t->decimal('balance_before', 14, 2)->nullable());
            $add('balance_after', fn ($t) => $t->decimal('balance_after', 14, 2)->nullable());
            $add('sms_message_id', fn ($t) => $t->unsignedBigInteger('sms_message_id')->nullable()->index());
            $add('sms_campaign_id', fn ($t) => $t->unsignedBigInteger('sms_campaign_id')->nullable()->index());
            $add('tracking_id', fn ($t) => $t->string('tracking_id', 120)->nullable()->index());
            $add('message', fn ($t) => $t->text('message')->nullable());
            $add('status', fn ($t) => $t->string('status', 30)->nullable());
            $add('response', fn ($t) => $t->longText('response')->nullable());
        });
    }

    /**
     * Rows written before this migration have no panel type, page count or recipient.
     * Fill what can be derived from the stored text so the logs table is not full of blanks.
     */
    private function backfillGroupsms(): void
    {
        if (!Schema::hasTable('groupsms')) {
            return;
        }

        DB::table('groupsms')->whereNull('service_type')->update(['service_type' => 'normal']);
        DB::table('groupsms')->whereNull('recipient_phone')->whereNotNull('phones')->where('type', 'single')
            ->update(['recipient_phone' => DB::raw('phones')]);

        DB::table('groupsms')->where('character_count', 0)->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $text = (string) ($row->message ?? '');
                $length = mb_strlen($text);
                if ($length === 0) {
                    continue;
                }

                $unicode = preg_match('/[^\x20-\x7E\r\n\t]/u', $text) === 1;
                if ($unicode) {
                    $pages = $length <= 70 ? 1 : ($length <= 134 ? 2 : 2 + (int) ceil(($length - 134) / 67));
                } else {
                    $pages = $length <= 160 ? 1 : ($length <= 306 ? 2 : (int) ceil($length / 153));
                }

                DB::table('groupsms')->where('id', $row->id)->update([
                    'character_count' => $length,
                    'page_count' => $pages,
                ]);
            }
        });
    }
};
