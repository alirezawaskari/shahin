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
        Schema::create('suspension_standard_callcenters', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('suspension_standard_id')->nullable();
            $table->foreign('suspension_standard_id')->references('id')->on('suspension_standards');
            $table->unsignedInteger('callcenter_id');
            $table->foreign('callcenter_id')->references('id')->on('callcenters');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suspension_standard_callcenters');
    }
};
