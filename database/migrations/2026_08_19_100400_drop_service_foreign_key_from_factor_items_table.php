<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `factor_items.factoritemable_id` is one half of a morph (Service **or** Category - see the
 * `morphs('factoritemable')` call in create_factor_items_table), so it must not be tied to a
 * single table. On installs old enough to predate that morph the column was `service_id` with
 * a foreign key onto `services`; renaming the column carried the constraint along under its
 * original name, e.g. `facto_items_service_id_foreign`.
 *
 * The result is that saving a کالا line - `factoritemable_type = Category`, id = a category -
 * dies with SQLSTATE[23000] 1452, because that id does not exist in `services`. It blocks the
 * callcenter send-invoice form and therefore every product order downstream.
 *
 * The constraint is looked up by column rather than by name: it is named after whatever the
 * column used to be called, which differs per install, and newer installs have no such key at
 * all (this migration is then a no-op).
 *
 * Structurally this is a narrow change - DROP FOREIGN KEY removes the referential constraint
 * only. Primary key, AUTO_INCREMENT (and its current value), rows, the `factoritemable_index`
 * morph index and even the helper index MySQL created for the constraint are all left in
 * place; that helper index survives under the constraint's old `..._foreign` name, so seeing
 * a KEY by that name afterwards does not mean the drop failed.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->foreignKeysOnMorphColumn() as $constraint) {
            DB::statement("ALTER TABLE `factor_items` DROP FOREIGN KEY `{$constraint}`");
        }
    }

    public function down(): void
    {
        //Deliberately not restored: the column is polymorphic, so the constraint was wrong
        //to begin with and re-adding it would fail on every Category row already stored.
    }

    /**
     * @return array
     */
    private function foreignKeysOnMorphColumn(): array
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'factor_items')
            ->where('COLUMN_NAME', 'factoritemable_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->pluck('CONSTRAINT_NAME')
            ->all();
    }
};
