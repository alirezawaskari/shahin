<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->integer('is_complete_content')->default(0)->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('service_items', function (Blueprint $table) {
            $table->dropColumn('is_complete_content');
        });
    }
};
