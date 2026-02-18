<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();

            $table->integer('ghorfe_online_list_id')->nullable();

            $table->string('title', 255);
            $table->text('logo');
            $table->text('footer_logo');
            $table->text('fav');
            $table->text('samandehi')->nullable();
            $table->text('enamad')->nullable();
            $table->text('kasbvakar')->nullable();
            $table->text('extra_logo')->nullable();
            $table->text('copyright');
            $table->string('call', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->text('telegram')->nullable();
            $table->text('instagram')->nullable();
            $table->text('linkedin')->nullable();
            $table->text('twitter')->nullable();
            $table->text('map')->nullable();

            $table->text('index_title');
            $table->text('index_description');

            $table->text('address')->nullable();
            $table->text('video')->nullable();
            $table->text('agency')->nullable();

            $table->string('shaba_number', 50)->nullable();
            $table->string('card_number', 20)->nullable();
            $table->string('card_holder', 110)->nullable();
            $table->text('content_campaign')->nullable();
            $table->string('tax_free_shaba_number', 50)->nullable();
            $table->string('tax_free_card_number', 20)->nullable();
            $table->string('tax_free_card_holder', 20)->nullable();
            $table->string('sms_panel', 12)->nullable();
            $table->string('iranSms_apikey', 110);
            $table->integer('iranSms_linenumber');
            $table->string('saman_id', 110);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

        });

    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
