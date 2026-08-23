<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `addresses` is an address BOOK - a user has many - but on installs built from
 * create_addresses_table the `user_id` index ended up UNIQUE, so the second address of any
 * user dies with SQLSTATE[23000] 1062 "Duplicate entry '<user id>' for key
 * 'addresses_user_id_foreign'". Soft deleted rows keep occupying that index too, so a user
 * whose only address was deleted could never get another one.
 *
 * The same migration also declared `$table->integer('id')` with no key and no
 * AUTO_INCREMENT, so those installs have no primary key on the table either and
 * Address::create() cannot hand back a usable id.
 *
 * Both are repaired here, each guarded so an install that is already correct (local dev,
 * where the table was rebuilt) is left untouched.
 *
 * Existing ids are never renumbered: `factors.address_id` and `factor_addresses.address_id`
 * point at them. If ids are somehow not unique the migration stops with an explicit message
 * rather than silently repointing those references.
 */
return new class extends Migration
{
    private const TABLE = 'addresses';

    public function up(): void
    {
        $this->ensurePrimaryKey();
        $this->dropUniqueOnUserId();
    }

    public function down(): void
    {
        //Not reversed: re-adding a UNIQUE on user_id would fail against any user who legitimately
        //has more than one address, and dropping the primary key is never the right move.
    }

    /**
     * Make `id` an AUTO_INCREMENT primary key if it is not one already.
     *
     * @return void
     */
    private function ensurePrimaryKey(): void
    {
        $column = $this->idColumn();

        if (!$column) {
            return;
        }

        $has_primary = $this->indexes()->where('INDEX_NAME', 'PRIMARY')->isNotEmpty();
        $has_auto_increment = str_contains(strtolower((string) $column->EXTRA), 'auto_increment');

        if ($has_primary && $has_auto_increment) {
            return;
        }

        if (!$has_primary) {
            $duplicates = DB::table(self::TABLE)
                ->select('id')
                ->groupBy('id')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            if ($duplicates->isNotEmpty()) {
                throw new RuntimeException(
                    'addresses.id holds duplicate values, so a primary key cannot be added automatically. '
                    . 'Renumbering would break factors.address_id / factor_addresses.address_id, so these rows '
                    . 'need to be resolved by hand first: ' . $duplicates->pluck('id')->implode(', ')
                );
            }
        }

        //keep whatever width/signedness the column already has
        $type = $column->COLUMN_TYPE;
        $primary = $has_primary ? '' : ', ADD PRIMARY KEY (`id`)';

        DB::statement('ALTER TABLE `' . self::TABLE . "` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT{$primary}");
    }

    /**
     * Replace any UNIQUE index over `user_id` with a plain one.
     *
     * A non-unique index is added FIRST when the table still needs one, because the unique
     * index may be the very index the user_id foreign key depends on and MySQL refuses to
     * drop the last index supporting a constraint.
     *
     * @return void
     */
    private function dropUniqueOnUserId(): void
    {
        $user_id_indexes = $this->indexes()->where('COLUMN_NAME', 'user_id');
        $unique = $user_id_indexes->where('NON_UNIQUE', 0)->where('INDEX_NAME', '!=', 'PRIMARY');

        if ($unique->isEmpty()) {
            return;
        }

        if ($user_id_indexes->where('NON_UNIQUE', 1)->isEmpty()) {
            Schema::table(self::TABLE, function ($table) {
                $table->index('user_id', 'addresses_user_id_index');
            });
        }

        foreach ($unique->pluck('INDEX_NAME')->unique() as $index) {
            DB::statement('ALTER TABLE `' . self::TABLE . "` DROP INDEX `{$index}`");
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    private function indexes()
    {
        return collect(DB::select(
            'SELECT INDEX_NAME, COLUMN_NAME, NON_UNIQUE FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
            [DB::getDatabaseName(), self::TABLE]
        ));
    }

    /**
     * @return object|null
     */
    private function idColumn()
    {
        return DB::selectOne(
            'SELECT COLUMN_TYPE, EXTRA FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [DB::getDatabaseName(), self::TABLE, 'id']
        );
    }
};
