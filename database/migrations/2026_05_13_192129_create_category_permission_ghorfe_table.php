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
        Schema::create('category_permission_ghorfe_online_list', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ghorfe_online_list_id');

            $table->integer('category_permission_id');

            // نام پیش‌فرض کلید خارجی از حد ۶۴ کاراکتری MySQL بلندتر می‌شود، پس صریح نام‌گذاری شده است
            $table->foreign('ghorfe_online_list_id', 'cpgol_ghorfe_fk')
                ->references('id')
                ->on('ghorfe_online_lists')
                ->cascadeOnDelete();

            $table->foreign('category_permission_id', 'cpgol_category_fk')
                ->references('id')
                ->on('category_permissions')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_permission_ghorfe_online_list');
    }
};
