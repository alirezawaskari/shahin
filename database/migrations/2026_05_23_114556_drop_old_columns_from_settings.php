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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'ghorfe_online_list_id',
                'title',
                'description',
                'logo',
                'footer_logo',
                'address',
                'phone',
                'fav',
                'email',
                'samandehi',
                'enamad',
                'kasbvakar',
                'extra_logo',
                'copyright',
                'call',
                'index_description',
                'card_number',
                'card_holder',
                'tax_free_shaba_number',
                'tax_free_card_number',
                'tax_free_card_holder',
                'shaba_number',
                'sms_panel',
                'iranSms_linenumber',
                'saman_id',
                'agency',
                'telegram',
                'instagram',
                'linkedin',
                'twitter',
                'map',
                'index_title',
                'video',
                'content_campaign',
                'iranSms_apikey'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'ghorfe_online_list_id',
                'title',
                'description',
                'logo',
                'footer_logo',
                'address',
                'phone',
                'fav',
                'email',
                'samandehi',
                'enamad',
                'kasbvakar',
                'extra_logo',
                'copyright',
                'call',
                'index_description',
                'card_number',
                'card_holder',
                'tax_free_shaba_number',
                'tax_free_card_number',
                'tax_free_card_holder',
                'shaba_number',
                'sms_panel',
                'iranSms_linenumber',
                'saman_id',
                'agency',
                'telegram',
                'instagram',
                'linkedin',
                'twitter',
                'map',
                'index_title',
                'video',
                'content_campaign',
                'iranSms_apikey'
            ]);
        });
    }
};
