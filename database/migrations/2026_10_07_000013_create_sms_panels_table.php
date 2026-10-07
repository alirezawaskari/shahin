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
        Schema::create('sms_panels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. 'IranSms', 'NeginSms', 'ParsaSms'
            $table->string('slug')->nullable();
            $table->enum('type', ['otp', 'normal-advertising', 'advertising', 'all'])->default('normal-advertising');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('connection_info')->nullable(); // api_key, username, password, base_url, etc.
            $table->json('send_settings')->nullable(); // default_sender, timeout, max_retries, auto_append_cancel
            $table->json('receive_settings')->nullable(); // webhook_url, is_receive_active
            $table->json('delivery_settings')->nullable(); // callback_url, check_interval, auto_check_delivery
            $table->unsignedBigInteger('ghorfe_online_list_id')->nullable(); // optional tenant scope
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_panels');
    }
};
