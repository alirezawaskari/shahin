<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contract_usages')) {
            return;
        }

        Schema::create('contract_usages', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');

            // contracts.id is a SIGNED int (legacy `$table->integer('id')`), so this
            // column has to be signed too or the foreign key is rejected.
            $table->integer('contract_id');

            // The owner of the usage: Service / Role / Product.
            // BOTH NULL means "applies to this location globally" — that is how product
            // purchase invoices get a contract without a per-product association screen.
            $table->string('usable_type', 120)->nullable()->default(null);
            $table->unsignedBigInteger('usable_id')->nullable()->default(null);

            // ContractUsage::LOCATIONS — adding a location is one array entry, no schema change.
            $table->string('location', 50);

            // The contract is issued at the point of association, so number and date
            // live on the relation, not on the bank contract.
            $table->string('contract_number', 100)->nullable()->default(null);
            $table->date('contract_date')->nullable()->default(null);

            $table->tinyInteger('is_active')->default(1);
            $table->integer('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Guards against attaching the same contract to the same owner twice in one
            // location. NULL usable_id is "distinct" to MySQL, so the global-row case is
            // enforced in ContractUsageController instead.
            $table->unique(
                ['contract_id', 'usable_type', 'usable_id', 'location'],
                'contract_usages_unique_idx'
            );

            $table->index(
                ['location', 'usable_type', 'usable_id', 'is_active'],
                'contract_usages_lookup_idx'
            );

            $table->foreign('contract_id', 'contract_usages_contract_fk')
                ->references('id')->on('contracts')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_usages');
    }
};
