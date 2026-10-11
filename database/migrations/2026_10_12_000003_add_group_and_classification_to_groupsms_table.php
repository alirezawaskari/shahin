<?php

use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\SmsMessage;
use App\Models\SmsMessageGroup;
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
        if (Schema::hasTable('groupsms')) {
            Schema::table('groupsms', function (Blueprint $table) {
                if (!Schema::hasColumn('groupsms', 'ghorfe_online_list_id')) {
                    $table->unsignedBigInteger('ghorfe_online_list_id')->nullable()->after('id');
                    $table->foreign('ghorfe_online_list_id')->references('id')->on('ghorfe_online_lists')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('groupsms', 'sms_message_group_id')) {
                    $table->unsignedBigInteger('sms_message_group_id')->nullable()->after('ghorfe_online_list_id');
                    $table->foreign('sms_message_group_id')->references('id')->on('sms_message_groups')->nullOnDelete();
                }
                if (!Schema::hasColumn('groupsms', 'classification')) {
                    $table->string('classification', 30)->default('regular')->after('sms_message_group_id');
                }
                if (!Schema::hasColumn('groupsms', 'sms_message_id')) {
                    $table->unsignedBigInteger('sms_message_id')->nullable()->after('classification');
                    $table->foreign('sms_message_id')->references('id')->on('sms_messages')->nullOnDelete();
                }
            });

            // Backfill existing groupsms records from matching sms_messages
            $defaultBusinessId = GhorfeOnlineList::first()?->id ?? 2;

            foreach (Groupsms::all() as $item) {
                // Try to find matching SmsMessage
                $match = SmsMessage::where('content', $item->message)
                    ->orWhere(function ($q) use ($item) {
                        $q->where('recipient_phone', $item->phones)
                          ->whereDate('created_at', $item->created_at ? $item->created_at->toDateString() : now()->toDateString());
                    })
                    ->first();

                if ($match) {
                    $item->ghorfe_online_list_id = $match->ghorfe_online_list_id ?: $defaultBusinessId;
                    $item->sms_message_group_id = $match->sms_message_group_id;
                    $item->classification = $match->classification ?: 'regular';
                    $item->sms_message_id = $match->id;

                    // If group is financial or legal, ensure classified
                    if ($match->group && ($match->group->isConfidential() || in_array($match->group->name, ['مالی', 'حقوقی'], true))) {
                        $item->classification = 'classified';
                    }
                } else {
                    $item->ghorfe_online_list_id = $defaultBusinessId;
                }

                $item->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('groupsms')) {
            Schema::table('groupsms', function (Blueprint $table) {
                if (Schema::hasColumn('groupsms', 'sms_message_id')) {
                    $table->dropForeign(['sms_message_id']);
                    $table->dropColumn('sms_message_id');
                }
                if (Schema::hasColumn('groupsms', 'classification')) {
                    $table->dropColumn('classification');
                }
                if (Schema::hasColumn('groupsms', 'sms_message_group_id')) {
                    $table->dropForeign(['sms_message_group_id']);
                    $table->dropColumn('sms_message_group_id');
                }
                if (Schema::hasColumn('groupsms', 'ghorfe_online_list_id')) {
                    $table->dropForeign(['ghorfe_online_list_id']);
                    $table->dropColumn('ghorfe_online_list_id');
                }
            });
        }
    }
};
