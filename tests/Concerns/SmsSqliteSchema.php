<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columns the SMS module needs on top of the test's own tables, for in-memory SQLite only.
 *
 * The migration that used to add them (2026_10_07_000001_add_sms_module_columns_to_existing_tables)
 * was deleted from the project. Its replacement is the current extension migration for groupsms and
 * sms_logs, and the ghorfe_online_lists balance and contract columns are added here with the same
 * names the app reads (see SeedsSmsBalances and the SMS contract services). Every column is added only
 * when missing, so a test that already declares a column keeps its own definition.
 */
trait SmsSqliteSchema
{
    protected function applySmsModuleColumns(): void
    {
        $this->registerSqliteJsonFunctions();
        $this->createStoredSettingsTable();
        $this->useStoredSettingsShape();
        $this->useHistoriesTable();

        if (Schema::hasTable('groupsms') || Schema::hasTable('sms_logs')) {
            (require base_path('database/migrations/2026_10_21_100000_extend_groupsms_and_sms_logs_for_sms_module.php'))->up();
        }

        $this->useStoredTemplateShape();

        if (Schema::hasTable('ghorfe_online_lists')) {
            Schema::table('ghorfe_online_lists', function (Blueprint $table) {
                $add = fn (string $column, callable $definition) => Schema::hasColumn('ghorfe_online_lists', $column) ? null : $definition($table);

                $add('otp_balance', fn ($t) => $t->decimal('otp_balance', 14, 2)->default(0));
                $add('otp_low_balance_threshold', fn ($t) => $t->decimal('otp_low_balance_threshold', 14, 2)->nullable());
                $add('advertising_balance', fn ($t) => $t->decimal('advertising_balance', 14, 2)->default(0));
                $add('sms_contract_accepted', fn ($t) => $t->boolean('sms_contract_accepted')->default(false));
                $add('sms_contract_accepted_at', fn ($t) => $t->timestamp('sms_contract_accepted_at')->nullable());
                $add('sms_contract_signature_name', fn ($t) => $t->string('sms_contract_signature_name')->nullable());
                $add('sms_contract_text', fn ($t) => $t->longText('sms_contract_text')->nullable());
            });
        }
    }

    /** SmsTemplate keeps the Persian name in fa_name; service type and category are derived, not stored. */
    protected function useStoredTemplateShape(): void
    {
        if (Schema::hasTable('sms_templates') && ! Schema::hasColumn('sms_templates', 'fa_name')) {
            Schema::table('sms_templates', function (Blueprint $table) {
                $table->string('fa_name')->nullable();
            });
        }
    }

    /** The settings table as the Setting model stores it: the rule and sender attributes live in the JSON value. */
    protected function createStoredSettingsTable(): void
    {
        if (Schema::hasTable('ghorfe_settings')) {
            return;
        }

        Schema::create('ghorfe_settings', function (Blueprint $table) {
            $table->id();
            $table->text('value')->nullable();
            $table->string('key')->nullable();
            $table->nullableMorphs('settingable');
            $table->timestamps();
        });
    }

    /** The signing and contract history line the business pages read (same columns as production). */
    protected function useHistoriesTable(): void
    {
        if (Schema::hasTable('histories')) {
            return;
        }

        Schema::create('histories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('historiable_type');
            $table->unsignedBigInteger('historiable_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('callcenter_id')->nullable();
            $table->integer('status');
            $table->unsignedInteger('reason_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * ghorfe_settings keeps number, type, sms_panel, status and the other rule attributes inside its
     * JSON `value` (the Setting model moves them there on save), so the real table has no such columns.
     * A column of that name in a test table would take the model's default and hide the JSON value.
     */
    protected function useStoredSettingsShape(): void
    {
        if (! Schema::hasTable('ghorfe_settings')) {
            return;
        }

        $virtual = array_values(array_filter(
            ['number', 'type', 'line_type', 'sms_panel', 'status', 'name', 'slug', 'parent_id', 'match_type', 'keyword', 'response_content', 'is_active', 'description', 'contract_status'],
            fn (string $column) => Schema::hasColumn('ghorfe_settings', $column)
        ));

        if ($virtual) {
            Schema::table('ghorfe_settings', fn (Blueprint $table) => $table->dropColumn($virtual));
        }
    }

    /**
     * MySQL's JSON helpers, registered on the test's SQLite PDO so the app's JSON queries run.
     *
     * json_unquote: MySQL returns a JSON string without its quotes.
     * json_type: MySQL types a scalar that json_extract returned as text (a plain string is 'STRING');
     * SQLite raises "malformed JSON" on that input, so the type is decided here in PHP.
     */
    protected function registerSqliteJsonFunctions(): void
    {
        $pdo = DB::connection('sqlite')->getPdo();

        $pdo->sqliteCreateFunction('json_unquote', function ($value) {
            if (! is_string($value)) {
                return $value;
            }
            $decoded = json_decode($value, true);

            return is_string($decoded) ? $decoded : $value;
        }, 1);

        $pdo->sqliteCreateFunction('json_type', function ($value) {
            if ($value === null) {
                return null;
            }
            if (is_bool($value)) {
                return 'BOOLEAN';
            }
            if (is_int($value) || is_float($value)) {
                return is_int($value) ? 'INTEGER' : 'DOUBLE';
            }
            if (! is_string($value)) {
                return 'STRING';
            }
            $decoded = json_decode($value, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return 'STRING';
            }

            return match (true) {
                is_array($decoded) && array_is_list($decoded) => 'ARRAY',
                is_array($decoded) => 'OBJECT',
                is_null($decoded) => 'NULL',
                is_bool($decoded) => 'BOOLEAN',
                is_int($decoded) => 'INTEGER',
                is_float($decoded) => 'DOUBLE',
                default => 'STRING',
            };
        }, 1);
    }
}
