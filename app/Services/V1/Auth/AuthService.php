<?php

namespace App\Services\V1\Auth;

use App\Helpers\Helper;
use App\Models\GhorfeOnlineList;
use App\Models\GhorfeOnlineListUser;

class AuthService
{
    /**
     * Summary of checkUserGhorfe
     * @return bool
     */
    public function checkUserGhorfe()
    {
        $ghorfe_online_list = self::getGhorfe();
        $ghorfe_online_list_user_exist = GhorfeOnlineListUser
            ::where('user_id',auth()->id())
            ->where('ghorfe_online_list_id',$ghorfe_online_list->id)
            ->exists();
        return $ghorfe_online_list_user_exist;
    }
    /**
     * Summary of getGhorfe
     * @return GhorfeOnlineList|null
     */
    public static function getGhorfe() : GhorfeOnlineList| null
    {
        $GhorfeOnlineList = GhorfeOnlineList::where('domain_active' , config('services.ghorfeOnline.domain_active'))->first();
        return $GhorfeOnlineList ?? null;
    }
}
