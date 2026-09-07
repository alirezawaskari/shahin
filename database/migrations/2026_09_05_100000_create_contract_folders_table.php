<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contract_folders')) {
            return;
        }

        Schema::create('contract_folders', function (Blueprint $table) {
            // Explicit engine: several tables in this schema were created as MyISAM,
            // which silently disables the foreign keys below.
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable()->default(null);
            $table->string('title', 191);
            $table->string('description', 500)->nullable()->default(null);
            $table->integer('position')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'is_active'], 'contract_folders_parent_active_idx');

            $table->foreign('parent_id', 'contract_folders_parent_fk')
                ->references('id')->on('contract_folders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_folders');
    }
};
