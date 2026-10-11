<?php

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
        Schema::table('ai_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_settings', 'provider')) {
                $table->string('provider', 50)->default('gemini')->after('id');
            }
            if (!Schema::hasColumn('ai_settings', 'api_key')) {
                $table->text('api_key')->nullable()->after('model');
            }
            if (!Schema::hasColumn('ai_settings', 'base_url')) {
                $table->string('base_url', 500)->nullable()->after('api_key');
            }
            if (!Schema::hasColumn('ai_settings', 'temperature')) {
                $table->decimal('temperature', 3, 2)->default(0.70)->after('base_url');
            }
            if (!Schema::hasColumn('ai_settings', 'max_tokens')) {
                $table->integer('max_tokens')->default(2000)->after('temperature');
            }
            if (!Schema::hasColumn('ai_settings', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('max_tokens');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_settings', function (Blueprint $table) {
            $columns = ['provider', 'api_key', 'base_url', 'temperature', 'max_tokens', 'is_active'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ai_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
