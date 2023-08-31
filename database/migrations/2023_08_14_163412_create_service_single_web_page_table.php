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
        Schema::create('service_single_web_page', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained();
            $table->foreignId('single_web_page_id')->constrained();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_single_web_page');
    }
};
