<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The shipping address of a product order, frozen onto the order itself.
 *
 * `factors.address_id` already points at one of the user's `addresses` rows, but that is a
 * live pointer: the user (or an admin) can edit or delete that address afterwards, and the
 * address printed on an order that already shipped would silently change with it. So the
 * selected address is COPIED here, once, per order — this table is what the order detail
 * page, the packing slip and every later reader use.
 *
 * `address_id` is kept only as provenance ("this snapshot came from that address"), never
 * as the read path, and deliberately carries no foreign key: the source row may be soft
 * deleted while this snapshot must survive untouched.
 *
 * Province / city / area names are stored alongside their ids for the same reason — the
 * snapshot has to render standalone even if the geo tables are re-seeded.
 *
 * One row per factor (unique), replaced in place whenever the admin picks a different
 * address, and frozen once the order reaches "ارسال شده".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('factor_addresses')) {
            return;
        }

        Schema::create('factor_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('factor_id')->unique();
            //provenance only - the user address this copy was taken from, which may later
            //be edited or deleted without affecting anything below
            $table->unsignedInteger('address_id')->nullable();
            $table->unsignedInteger('province_id')->nullable();
            $table->string('province_name', 191)->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('city_name', 191)->nullable();
            $table->unsignedInteger('area_id')->nullable();
            $table->string('area_name', 191)->nullable();
            $table->text('address');
            $table->string('plaque', 191)->nullable();
            $table->string('unit', 191)->nullable();
            $table->string('postal_code', 191)->nullable();
            $table->string('recipient_name', 191)->nullable();
            $table->string('recipient_family', 191)->nullable();
            $table->string('recipient_mobile', 191)->nullable();
            $table->string('lat', 191)->nullable();
            $table->string('lon', 191)->nullable();
            $table->timestamps();

            $table->foreign('factor_id')->references('id')->on('factors')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factor_addresses');
    }
};
