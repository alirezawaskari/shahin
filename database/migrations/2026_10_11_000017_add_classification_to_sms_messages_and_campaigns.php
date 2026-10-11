<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sms_messages')) {
            Schema::table('sms_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('sms_messages', 'classification')) {
                    $table->string('classification', 30)->default('regular')->after('sms_message_group_id');
                }
            });
        }

        if (Schema::hasTable('sms_campaigns')) {
            Schema::table('sms_campaigns', function (Blueprint $table) {
                if (!Schema::hasColumn('sms_campaigns', 'sms_message_group_id')) {
                    $table->unsignedBigInteger('sms_message_group_id')->nullable()->after('sms_message_template_id');
                }
                if (!Schema::hasColumn('sms_campaigns', 'classification')) {
                    $table->string('classification', 30)->default('regular')->after('sms_message_group_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sms_messages')) {
            Schema::table('sms_messages', function (Blueprint $table) {
                if (Schema::hasColumn('sms_messages', 'classification')) {
                    $table->dropColumn('classification');
                }
            });
        }

        if (Schema::hasTable('sms_campaigns')) {
            Schema::table('sms_campaigns', function (Blueprint $table) {
                if (Schema::hasColumn('sms_campaigns', 'classification')) {
                    $table->dropColumn('classification');
                }
                if (Schema::hasColumn('sms_campaigns', 'sms_message_group_id')) {
                    $table->dropColumn('sms_message_group_id');
                }
            });
        }
    }
};
