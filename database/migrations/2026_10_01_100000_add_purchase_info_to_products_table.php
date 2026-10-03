<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Purchase terms shown on the storefront's single-product page (the box beside the gallery):
 * whether the product can be bought in cash and/or in installments, plus a short line of
 * text under each («۵٪ تخفیف خرید نقدی», «اقساط ۳ تا ۱۲ ماهه با پیش‌پرداخت ۳۰٪», ...).
 *
 * Kept apart from products.purchase_type / pay_type on purpose — those integers are an
 * older, undocumented encoding still written by the store-room flow, and reusing them would
 * give one column two meanings. The shipping methods offered for the product live in
 * product_shipping_method.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'cash_purchase')) {
                $table->boolean('cash_purchase')->default(true)->after('pay_type');
            }
            if (!Schema::hasColumn('products', 'cash_purchase_note')) {
                $table->string('cash_purchase_note', 500)->nullable()->after('cash_purchase');
            }
            if (!Schema::hasColumn('products', 'installment_purchase')) {
                $table->boolean('installment_purchase')->default(false)->after('cash_purchase_note');
            }
            if (!Schema::hasColumn('products', 'installment_purchase_note')) {
                $table->string('installment_purchase_note', 500)->nullable()->after('installment_purchase');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cash_purchase', 'cash_purchase_note', 'installment_purchase', 'installment_purchase_note']);
        });
    }
};
