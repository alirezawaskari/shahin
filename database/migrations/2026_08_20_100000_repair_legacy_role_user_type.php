<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * A role assigned under the pre-namespace class name is a role nobody has.
 *
 * `role_user.user_type` holds the morph class and Laratrust matches on it exactly, so a row
 * left on `App\User` while the app resolves `App\Models\User` is invisible to the relation:
 * the panel shows the person as having a role, and every `hasPermission()` says no.
 *
 * User 63 — the admin of دیجی بیوتی — was in exactly that state: `ghorfe_online_manager` in
 * the table, no permissions in practice, and therefore no «ثبت خدمات سریع» on
 * /dashboard/callcenter, no فهرست همکاران, and a 401 on their own business page.
 *
 * The column DEFAULT is the same stale class, so it is corrected too — otherwise the next
 * insert that omits the column quietly reintroduces the bug.
 */
return new class extends Migration
{
    private const LEGACY = 'App\User';

    public function up(): void
    {
        DB::table('role_user')->where('user_type', self::LEGACY)->update(['user_type' => User::class]);

        $this->setUserTypeDefault(User::class);
    }

    /**
     * Only the column default is restored. The repaired rows are deliberately left correct:
     * putting them back on the legacy class would only re-break the accounts they belong to.
     */
    public function down(): void
    {
        $this->setUserTypeDefault(self::LEGACY);
    }

    private function setUserTypeDefault(string $class): void
    {
        DB::statement(
            "ALTER TABLE `role_user` MODIFY `user_type` VARCHAR(255) NOT NULL DEFAULT '" . addslashes($class) . "'"
        );
    }
};
