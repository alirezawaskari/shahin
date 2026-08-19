<?php

use App\Models\Address;
use App\Models\Factor;
use App\Models\FactorAddress;
use Illuminate\Database\Migrations\Migration;

/**
 * Orders that were already paid before `factor_addresses` existed may still carry a live
 * `factors.address_id`. Copy that address into the snapshot table once, so those orders open
 * with their address already selected instead of "آدرس ثبت نشده".
 *
 * Only paid invoices carrying کالا rows are touched - a services-only invoice is not an order
 * and has nothing to ship. Rows that already have a snapshot are skipped, so re-running is
 * harmless. A no-op on installs where no such order exists.
 */
return new class extends Migration
{
    public function up(): void
    {
        Factor::productOrder()
            ->whereNotNull('address_id')
            ->whereDoesntHave('factorAddress')
            ->chunkById(200, function ($factors) {
                foreach ($factors as $factor) {
                    //the source address may have been soft deleted in the meantime; there is
                    //nothing left to copy in that case
                    $address = Address::find($factor->address_id);

                    if (!$address) {
                        continue;
                    }

                    FactorAddress::create(array_merge(
                        FactorAddress::snapshotOf($address),
                        ['factor_id' => $factor->id]
                    ));
                }
            });
    }

    public function down(): void
    {
        //the snapshots are indistinguishable from ones an admin created afterwards, and
        //dropping them would lose real data - the table drop in the create migration is the
        //only rollback for this.
    }
};
