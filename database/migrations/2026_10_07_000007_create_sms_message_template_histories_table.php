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
        Schema::create('sms_message_template_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sms_message_template_id')->constrained('sms_message_templates')->cascadeOnDelete();
            $table->text('content');
            $table->unsignedInteger('changed_by');
            $table->foreign('changed_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_message_template_histories');
    }
};
