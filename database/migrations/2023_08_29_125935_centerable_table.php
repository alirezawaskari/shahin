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
        Schema::create('centerables', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('center_id');
            $table->unsignedInteger('centerable_id');
            $table->string('centerable_type',120);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('centerables');
    }
};
