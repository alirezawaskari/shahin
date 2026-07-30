<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "خدمات" — the services a partner personally offers, as cards on their profile page.
 *
 * Employee::service() already existed as a belongsTo(Service::class), but there is no
 * service_id column on `employees` — it was dead. And one service would be wrong anyway:
 * the design shows a doctor offering ارتودنسی, زیبایی and ایمپلنت side by side.
 *
 * The two extra pivot columns are what makes each card different per partner:
 *   subtitle   - the line under the title ("تک دندان، کامل، فوری")
 *   from_price - "از ۳٬۰۰۰٬۰۰۰ تومان"; the partner's own starting price, which is not the
 *                global services.price nor a center tariff
 * Both nullable, so the frontend can fall back to the service's own title/price.
 *
 * employees.id is a SIGNED `int` and services.id is `int unsigned` — the FK columns match
 * each side exactly, a mismatch is rejected outright.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_service', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->unsignedInteger('service_id');
            $table->string('subtitle', 255)->nullable();
            $table->unsignedBigInteger('from_price')->nullable();
            $table->unsignedSmallInteger('sort')->nullable();

            $table->unique(['employee_id', 'service_id'], 'employee_service_unique');

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_service');
    }
};
