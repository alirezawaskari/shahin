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
        Schema::table('ghorfe_online_lists', function (Blueprint $table) {
            $table->foreignId('shahin_package_id')
                ->constrained('shahin_packages')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ghorfe_online_lists', function (Blueprint $table) {
            $table->dropForeign(['shahin_package_id']);
        });
    }
};
