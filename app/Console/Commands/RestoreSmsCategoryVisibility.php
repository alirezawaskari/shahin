<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Undoes collateral damage from 2026_10_07_120000_unify_sms_menu_category:
 * because category_permissions.id has no real auto_increment, every row
 * shares id=0, so Eloquent's ->update() on "the SMS category" silently
 * cascaded to all 37 rows and flipped is_active off for every category,
 * not just the SMS one. This restores visibility without touching any
 * other main code/schema.
 */
class RestoreSmsCategoryVisibility extends Command
{
    protected $signature = 'sms:restore-category-visibility';

    protected $description = 'Set is_active=true on every category_permissions row, undoing the cascade caused by the id=0 bug.';

    public function handle(): int
    {
        $count = DB::table('category_permissions')->update(['is_active' => 1]);
        $this->info("Restored is_active=1 on {$count} category_permissions rows.");

        return self::SUCCESS;
    }
}
