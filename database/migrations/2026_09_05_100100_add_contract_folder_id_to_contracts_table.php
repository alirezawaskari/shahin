<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('contracts', 'contract_folder_id')) {
            return;
        }

        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedInteger('contract_folder_id')->nullable()->default(null)->after('id');

            $table->index('contract_folder_id', 'contracts_contract_folder_idx');

            // contracts.id is int(10) unsigned AUTO_INCREMENT PRI and the table is InnoDB,
            // so this FK holds. Contracts with no folder read as «دسته‌بندی نشده».
            $table->foreign('contract_folder_id', 'contracts_contract_folder_fk')
                ->references('id')->on('contract_folders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('contracts', 'contract_folder_id')) {
            return;
        }

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign('contracts_contract_folder_fk');
            $table->dropIndex('contracts_contract_folder_idx');
            $table->dropColumn('contract_folder_id');
        });
    }
};
