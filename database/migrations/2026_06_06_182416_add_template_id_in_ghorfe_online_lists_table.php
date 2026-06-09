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
            $table->foreignId('template_id')->after('user_id')->constrained('templates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ghorfe_online_lists', function (Blueprint $table) {
            $table->dropForeign('ghorfe_online_lists_template_id_foreign');
        });
    }
};
