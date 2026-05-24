<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->enum('type', ['default', 'custom'])->default('default');
            $table->softDeletes();
            $table->dropColumn('template_name');
        });
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropSoftDeletes();
            $table->string('template_name')->nullable();

        });
    }
};
