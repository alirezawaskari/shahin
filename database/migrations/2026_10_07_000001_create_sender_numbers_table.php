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
        Schema::create('sender_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->enum('type', ['otp', 'advertising']);
            $table->string('sms_panel'); // Gateway/panel it's tied to, e.g. IranSms, NeginSms
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('contract_status')->nullable();
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
        Schema::dropIfExists('sender_numbers');
    }
};
