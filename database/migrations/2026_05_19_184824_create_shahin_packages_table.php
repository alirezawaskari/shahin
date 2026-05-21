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
        Schema::create('shahin_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->enum('type', ['store', 'medicine', 'general'])->default('general');
            $table->string('description');
            $table->decimal('price', 10, 2);
            $table->integer('duration_days')->default(30);
            $table->text('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('type');
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shahin_packages');
    }
};
