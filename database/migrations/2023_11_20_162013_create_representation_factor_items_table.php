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
        Schema::create('representation_factor_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('users');
            $table->unsignedInteger('factor_item_id');
            $table->foreign('factor_item_id')->references('id')->on('factor_items');
            $table->unsignedInteger('representation_id');
            $table->foreign('representation_id')->references('id')->on('representations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('representation_factor_items');
    }
};
