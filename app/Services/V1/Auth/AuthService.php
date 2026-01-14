<?php

namespace App\Services\V1\Auth;

use App\Helpers\Helper;
use App\Models\GhorfeOnlineListUser;

class AuthService
{
    public function checkUserGhorfe()
    {
        $ghorfe_online_list = Helper::getGhorfe();
        $ghorfe_online_list_user = GhorfeOnlineListUser
            ::where('user_id',auth()->id())
            ->where('ghorfe_online_list_id',$ghorfe_online_list->id)
            ->exists();
        return $ghorfe_online_list_user;
    }
}
