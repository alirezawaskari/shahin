<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\FactorItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * @param $file
     * @return string
     */
    public function upload($file)
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $filepath = "/upload/user/{$year}/{$month}/";

        $rand = rand(1, 999);
        $filename = "{$rand}_" . $file->getClientOriginalName();
        $file->move(public_path($filepath), $filename);
        return $filepath . $filename;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        return view('site.profile.layouts.index', compact('user'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail()
    {
        $user = auth()->user();
        return view('site.profile.layouts.detail', compact('user'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function service()
    {
        $user = auth()->user();
        $factors = $user->factors()->where('status' , true)->get();
        return view('site.profile.layouts.service', compact('user' , 'factors'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function wallet()
    {
        $user = auth()->user();
        $wallets = $user->wallets()->latest()->get();
        return view('site.profile.layouts.wallet', compact('user' , 'wallets'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function factor()
    {
        $user = auth()->user();
        $factors = $user->factors()->latest()->get();
        return view('site.profile.layouts.factor', compact('user' , 'factors'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function factorItem()
    {
        $user = auth()->user();
        $factor_items = FactorItem::whereHas('factor', function($q){
            $q->where('user_id', auth()->user()->id)->where('status' , 1);
        })->latest()->get();
        return view('site.profile.layouts.factoritem.index', compact('user' , 'factor_items'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function laleCard()
    {
		$user = auth()->user();
        return view('site.profile.layouts.lalecard' , compact('user'));
    }
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
       public function update(Request $request)
    {

        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'father' => 'string',
            'melicode' => 'required|numeric',
            'call' => 'numeric',
            'shenasnamecode' => 'numeric',
            'postalcode' => 'numeric',

			'password' => 'nullable|string|min:6|confirmed',
            'avatar' => 'mimes:jpg,jpeg,png|max:4096',
        ]);

        $inputs = $request->all();
        $user = auth()->user();

        // Spatie: avatar may arrive as a direct file (avatar) or a temp media id
        // (urlpersonalPicture); KYC docs arrive as temp media ids. attachTo/attachUpload
        // replace the single file in each collection (native old-file cleanup, no deleteImage).
        if ($request->file('avatar')) {
            \App\Helpers\MediaLibraryHelper::attachUpload($user, $request->file('avatar'), 'avatar');
        } elseif ($request->urlpersonalPicture) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlpersonalPicture, 'avatar');
        }
        if ($request->urlimgshenasname) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlimgshenasname, 'imgshenasname');
        }
        if ($request->urlimgmeli) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlimgmeli, 'imgmeli');
        }
        if ($request->urlimgturnover) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlimgturnover, 'imgturnover');
        }
        if ($request->urlimgsignature) {
            \App\Helpers\MediaLibraryHelper::attachTo($user, $request->urlimgsignature, 'imgsignature');
        }
        // Keep image fields out of the mass-update — they're Spatie collections/accessors now.
        unset(
            $inputs['avatar'], $inputs['imgshenasname'], $inputs['imgmeli'], $inputs['imgturnover'], $inputs['imgsignature'], $inputs['personalPicture'],
            $inputs['urlpersonalPicture'], $inputs['urlimgshenasname'], $inputs['urlimgmeli'], $inputs['urlimgturnover'], $inputs['urlimgsignature']
        );
        unset($inputs['phone']);
        if ($inputs['password'] == '') {
            unset($inputs['password']);
            unset($inputs['password_confirmation']);
            $user->update($inputs);
        } else {
            $user->update(array_merge($inputs, ['password' => Hash::make($inputs['password'])]));
        }
        return redirect()->back()->with([
            'profile_update' => 'success'
        ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function useravatar(Request $request)
    {
        $user = auth()->user();
        $img = $request->file('image');
        if ($img) {
            \App\Helpers\MediaLibraryHelper::attachUpload($user, $img, 'avatar');
        }

        return redirect()->back()->with([
            'avatar_update' => 'success'
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function userdetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'birth_day' => 'required',
            'melicode' => ['required', 'digits:10'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->with([
                'update_detail' => 'error',
            ]);
        }

        auth()->user()->update($request->all());

        return redirect()->back()->with([
            'update_detail' => 'success',
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function notifications()
    {
        $notifications = auth()->user()->unreadNotifications;
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return view('site.profile.layouts.notifications' ,compact('notifications'));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function notifications_read()
    {
        $notifications = auth()->user()->readNotifications;
        return view('site.profile.layouts.notifications-read' ,compact('notifications'));
    }

    /**
     * Display SMS messages for customer account area, split into normal vs confidential.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function messages()
    {
        $user = auth()->user();
        $allMessages = \App\Models\SmsMessage::where('user_id', $user->id)
            ->orWhere(function ($q) use ($user) {
                if (!empty($user->phone)) {
                    $q->where('recipient_phone', $user->phone)
                      ->orWhere('sender_phone', $user->phone);
                }
            })
            ->with(['group', 'template', 'senderNumber'])
            ->latest()
            ->get();

        // Canonical permission (see database/migrations/2026_10_11_000001_*):
        // the older guessed names are kept for backward compatibility with
        // any role that may already have been granted one of them.
        $canViewConfidential = \App\Services\Sms\SmsConfidentialityGuard::canViewConfidential($user)
            || $user->isAbleTo('view-confidential-messages')
            || $user->isAbleTo('confidential-messages')
            || $user->isAbleTo('sms-confidential');

        $normalMessages = $allMessages->filter(function ($msg) {
            return !$msg->isConfidential();
        });

        $confidentialMessages = $canViewConfidential
            ? $allMessages->filter(function ($msg) {
                return $msg->isConfidential();
            })
            : collect();

        return view('site.profile.layouts.messages', compact(
            'user',
            'normalMessages',
            'confidentialMessages',
            'canViewConfidential'
        ));
    }
}
