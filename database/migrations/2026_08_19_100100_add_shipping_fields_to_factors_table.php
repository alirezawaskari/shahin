<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shipment state for product orders (a paid factor that carries factor_item_products rows).
 *
 * The lifecycle the admin panel drives is: پرداخت موفق -> در انتظار ارسال -> تعیین آدرس و
 * هزینه ارسال -> ارسال شده. `shipping_status` is that state (Factor::SHIPPING_STATUS), and
 * once it reaches ارسال شده the order is closed: address, shipping cost and tracking code
 * stop being editable.
 *
 * `shipping_cost` is kept apart from `factors.price` on purpose — `price` is the amount the
 * user actually paid through the gateway and must not move afterwards; the shipping fee is
 * decided later by the admin and is only summed into the displayed order total.
 *
 * Factors are Auditable, so every one of these transitions is recorded in `audits` without
 * a separate history table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('factors', function (Blueprint $table) {
            if (!Schema::hasColumn('factors', 'shipping_status')) {
                $table->unsignedTinyInteger('shipping_status')->default(0)->after('address_id');
            }
            if (!Schema::hasColumn('factors', 'shipping_cost')) {
                $table->unsignedBigInteger('shipping_cost')->default(0)->after('shipping_status');
            }
            if (!Schema::hasColumn('factors', 'tracking_code')) {
                $table->string('tracking_code', 191)->nullable()->after('shipping_cost');
            }
            if (!Schema::hasColumn('factors', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('tracking_code');
            }
            if (!Schema::hasColumn('factors', 'shipped_by')) {
                //admin who pressed "ارسال سفارش"
                $table->unsignedInteger('shipped_by')->nullable()->after('shipped_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('factors', function (Blueprint $table) {
            $table->dropColumn(['shipping_status', 'shipping_cost', 'tracking_code', 'shipped_at', 'shipped_by']);
        });
    }
};
