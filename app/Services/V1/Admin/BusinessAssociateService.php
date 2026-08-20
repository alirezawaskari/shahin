<?php

namespace App\Services\V1\Admin;

use App\Models\GhorfeOnlineList;
use App\Models\User;

/**
 * "همکار کسب و کار" — the association between a person and a business.
 *
 * Membership and association are two different things and are deliberately kept apart:
 *
 *  - a MEMBER is anyone the business has ever served. Nothing here records that, and a
 *    business associate can find and invoice a person who belongs to no business at all —
 *    the quick-action search (CallcenterController::fetchUser) is global on purpose.
 *  - an ASSOCIATE is staff: an `employees` row whose user is attached to the business
 *    through `ghorfe_online_list_user`. That pivot is what `ghorfe()` reads, so attaching it
 *    is exactly what turns panel access on for a `ghorfeAdmin` (see User::isAdmin).
 *
 * One user can be the associate of ONE business only. The DB enforces it with a unique index
 * on ghorfe_online_list_user.user_id; this class is where the same rule produces a sentence a
 * person can read instead of a driver error.
 */
class BusinessAssociateService
{
    /** The business this user is an associate of, if any. */
    public function businessOf(User $user): ?GhorfeOnlineList
    {
        return $user->ghorfeOnlineLists()->first();
    }

    /**
     * Whether $user may be made an associate of $business.
     *
     * Already an associate of that same business is a pass, not a conflict: the caller then
     * writes nothing (see assign) instead of failing a legitimate re-save.
     *
     * @return array{success: bool, message: string}
     */
    public function check(User $user, GhorfeOnlineList $business): array
    {
        $current = $this->businessOf($user);

        if ($current && $current->id !== $business->id) {
            return [
                'success' => false,
                'message' => 'این کاربر هم اکنون همکار کسب و کار «' . $current->title . '» است. هر کاربر تنها می‌تواند همکار یک کسب و کار باشد.',
            ];
        }

        return ['success' => true, 'message' => ''];
    }

    /**
     * Make $user an associate of $business, unless they already belong to another one.
     *
     * Idempotent — re-running it for the same pair writes nothing, so the caller never has to
     * ask whether the association already exists (requirement 7).
     *
     * @return array{success: bool, message: string}
     */
    public function assign(User $user, GhorfeOnlineList $business): array
    {
        $result = $this->check($user, $business);

        if (! $result['success']) {
            return $result;
        }

        $user->ghorfeOnlineLists()->syncWithoutDetaching([$business->id]);

        return ['success' => true, 'message' => ''];
    }
}
