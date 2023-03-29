<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InviteCategory;
use App\Models\UserInvite;
use Illuminate\Http\Request;

class UserInviteController extends Controller
{
    public function index()
    {
        $user_invites=UserInvite::orderBy('id','DESC')->paginate(15);
        foreach ($user_invites as $user_invite){
            $invite_category = InviteCategory::find($user_invite->invite_category_id);
            $user_invite['invite_category_title'] = $invite_category->invitecategoryable->title;
        }
        return view('Admin.userInvite.lists',compact('user_invites'));
    }
}
