<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One PDF terms file per role category, moved off the Context key/value store.
     *
     * No data backfill here on purpose: the pre-existing file (still in `contexts`) is
     * wired onto the model as its legacy `file_path` at save time, and AdoptsLegacyMedia
     * copies it into Spatie on that save (touch-to-migrate, same as the rest of the app).
     */
    public function up(): void
    {
        Schema::create('employee_terms', function (Blueprint $table) {
            $table->id();
            $table->string('role_category')->unique();
            // Legacy path (fed from the old Context value on save) so the accessor keeps
            // serving the old file until AdoptsLegacyMedia adopts it into Spatie.
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_terms');
    }
};
