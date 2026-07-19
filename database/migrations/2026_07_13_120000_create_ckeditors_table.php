<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Owner rows for files uploaded through CKEditor. Each upload creates one
     * Ckeditor row; the file itself lives in Spatie's media table (collection
     * 'file') under storage/app/public/ckeditor/.
     */
    public function up(): void
    {
        Schema::create('ckeditors', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ckeditors');
    }
};
