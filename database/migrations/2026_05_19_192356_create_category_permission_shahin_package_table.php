<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_permission_shahin_package', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shahin_package_id');

            $table->integer('category_permission_id');

            $table->foreign('shahin_package_id', 'cpsp_package_fk')
                ->references('id')
                ->on('shahin_packages')
                ->cascadeOnDelete();

            $table->foreign('category_permission_id', 'cpsp_category_fk')
                ->references('id')
                ->on('category_permissions')
                ->cascadeOnDelete();

            $table->unique(
                ['shahin_package_id', 'category_permission_id'],
                'cpsp_unique'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_permission_shahin_package');
    }
};
