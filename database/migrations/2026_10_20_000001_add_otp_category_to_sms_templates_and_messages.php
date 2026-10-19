<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['sms_message_templates', 'sms_messages'] as $tableName) {
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'otp_category')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('otp_category', 30)->nullable()->index();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['sms_message_templates', 'sms_messages'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'otp_category')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropIndex($tableName . '_otp_category_index');
                    $table->dropColumn('otp_category');
                });
            }
        }
    }
};
