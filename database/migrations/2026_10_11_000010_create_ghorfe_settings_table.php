<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * App\Models\Setting targets table `ghorfe_settings`, which was missing a migration.
     */
    public function up(): void
    {
        if (!Schema::hasTable('ghorfe_settings')) {
            Schema::create('ghorfe_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->nullable();
                $table->text('value')->nullable();
                $table->nullableMorphs('settingable');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ghorfe_settings');
    }
};
