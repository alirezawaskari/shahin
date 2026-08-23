<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_contents', function (Blueprint $table) {
            $table->id();

            // رابطه پلی‌مورفیک
            $table->unsignedBigInteger('seoable_id');
            $table->string('seoable_type');
            $table->unique(['seoable_id', 'seoable_type']);

            // تنظیمات اصلی SEO
            $table->string('seo_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('robots')->default('index, follow');
            $table->string('canonical_url')->nullable();

            // Redirect 301
            $table->string('redirect_from')->nullable();
            $table->string('redirect_to')->nullable();
            $table->boolean('redirect_enabled')->default(false);

            // Open Graph
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->string('og_site_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_contents');
    }
};
