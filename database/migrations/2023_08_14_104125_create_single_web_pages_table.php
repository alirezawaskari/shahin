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
        Schema::create('single_web_pages', function (Blueprint $table) {
            $table->id();
            $table->string('fa_name' , 191);
            $table->string('en_name' , 191);
            $table->string('agent_id' , 191);
            $table->string('slug' , 191);
            $table->text('image');
            $table->integer('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('single_web_pages');
    }
};
