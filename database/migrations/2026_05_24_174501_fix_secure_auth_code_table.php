<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('auth_code', function (Blueprint $table) {

            if (!Schema::hasColumn('auth_code', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('code');
            }

            if (!Schema::hasColumn('auth_code', 'attempts')) {
                $table->unsignedTinyInteger('attempts')->default(0)->after('expires_at');
            }

            if (!Schema::hasColumn('auth_code', 'used_at')) {
                $table->timestamp('used_at')->nullable()->after('attempts');
            }
        });

        if (!$this->indexExistsCheck('auth_code', 'auth_code_phone_index')) {
            Schema::table('auth_code', function (Blueprint $table) {
                $table->index('phone');
            });
        }

        if (!$this->isIdAutoIncrement('auth_code')) {
            if ($this->hasPrimaryKey('auth_code')) {
                DB::statement('ALTER TABLE `auth_code` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
            } else {
                DB::statement('ALTER TABLE `auth_code` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`)');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_code', function (Blueprint $table) {

            if (Schema::hasColumn('auth_code', 'used_at')) {
                $table->dropColumn('used_at');
            }

            if (Schema::hasColumn('auth_code', 'attempts')) {
                $table->dropColumn('attempts');
            }

            if (Schema::hasColumn('auth_code', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });

        if ($this->indexExistsCheck('auth_code', 'auth_code_phone_index')) {
            Schema::table('auth_code', function (Blueprint $table) {
                $table->dropIndex('auth_code_phone_index');
            });
        }
    }

    private function indexExistsCheck(string $table, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT COUNT(1) AS cnt
             FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName]
        );

        return (int) ($result->cnt ?? 0) > 0;
    }

    private function isIdAutoIncrement(string $table): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            'SELECT EXTRA
             FROM information_schema.columns
             WHERE table_schema = ? AND table_name = ? AND column_name = ?',
            [$database, $table, 'id']
        );

        $extra = strtolower((string) ($result->EXTRA ?? ''));

        return str_contains($extra, 'auto_increment');
    }

    private function hasPrimaryKey(string $table): bool
    {
        $database = DB::getDatabaseName();

        $result = DB::selectOne(
            "SELECT COUNT(1) AS cnt
             FROM information_schema.table_constraints
             WHERE table_schema = ? AND table_name = ? AND constraint_type = 'PRIMARY KEY'",
            [$database, $table]
        );

        return (int) ($result->cnt ?? 0) > 0;
    }

};
