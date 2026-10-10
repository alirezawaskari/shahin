<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simple key-value text store for super-admin-editable SMS module
     * content (task.md §2-4/§2-6): the regulatory rules text and the usage
     * contract text. A dedicated table rather than reusing App\Models\Setting
     * (table `ghorfe_settings`), which has no migration anywhere in this
     * codebase creating that table — only test files create it inline —
     * so it cannot be relied on to exist.
     */
    public function up(): void
    {
        Schema::create('sms_cms_contents', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_cms_contents');
    }
};
