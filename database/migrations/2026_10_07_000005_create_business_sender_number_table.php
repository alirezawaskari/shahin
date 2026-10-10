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
        Schema::create('business_sender_number', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ghorfe_online_list_id')->constrained('ghorfe_online_lists')->cascadeOnDelete();
            $table->foreignId('sender_number_id')->constrained('sender_numbers')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_sender_number');
    }
};
