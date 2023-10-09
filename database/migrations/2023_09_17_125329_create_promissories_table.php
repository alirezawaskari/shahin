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
        Schema::create('promissories', function (Blueprint $table) {
             $table->id();
            $table->unsignedInteger('applicantable_id');
            $table->string('applicantable_type');

            $table->unsignedInteger('promissoryable_id');
            $table->string('promissoryable_type');

            $table->unsignedInteger('exportable_id');
            $table->string('exportable_type');

            $table->unsignedInteger('guarantorable_id')->nullable();
            $table->string('guarantorable_type')->nullable();

            $table->unsignedInteger('paytoable_id');
            $table->string('paytoable_type');

            $table->tinyInteger('status');
            $table->double('amount');
            $table->unsignedInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('users');
            $table->string('title');
            $table->string('serial_code');
            $table->timestamp('receipt_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promissories');
    }
};
