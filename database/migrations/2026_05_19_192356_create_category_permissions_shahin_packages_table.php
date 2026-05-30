<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_permissions_shahin_packages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shahin_package_id')
                ->constrained('shahin_packages')
                ->onDelete('cascade');

            $table->foreignId('category_permission_id')
                ->constrained('category_permissions')
                ->onDelete('cascade');

            $table->unique(['shahin_package_id', 'category_permission_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_permissions_shahin_packages');
    }
};
