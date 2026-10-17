<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = Permission::whereIn('name', ['send-group-sms', 'send-single-sms'])->get();
        $ghorfeAdmin = Role::where('name', 'ghorfeAdmin')->first();

        if ($ghorfeAdmin && $permissions->isNotEmpty()) {
            foreach ($permissions as $permission) {
                $exists = DB::table('permission_role')
                    ->where('permission_id', $permission->id)
                    ->where('role_id', $ghorfeAdmin->id)
                    ->exists();

                if (!$exists) {
                    DB::table('permission_role')->insert([
                        'permission_id' => $permission->id,
                        'role_id' => $ghorfeAdmin->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep permissions intact
    }
};
