<?php

namespace App\Http\Controllers;

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
        })->get();
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
            'imgmeli' => 'mimes:jpg,jpeg,png|max:4096',
            'imgshenasname' => 'mimes:jpg,jpeg,png|max:4096',
			'avatar' => 'mimes:jpg,jpeg,png|max:4096',
            'imgcatch' => 'mimes:jpg,jpeg,png|max:4096',
            'imglease' => 'mimes:jpg,jpeg,png|max:4096',
            'imgczech' => 'mimes:jpg,jpeg,png|max:4096',
			'password' => 'nullable|string|min:6|confirmed',
        ]);

        $inputs = $request->all();
        $user = auth()->user();

        $imgmeli = $request->file('imgmeli');
        $imgshenasname = $request->file('imgshenasname');
		$avatar = $request->file('avatar');
        $imgcatch = $request->file('imgcatch');
        $imglease = $request->file('imglease');
        $imgczech = $request->file('imgczech');

        if ($imgmeli) {
            $urlimgmeli = $this->upload($imgmeli);
            $user->update(['imgmeli' => $urlimgmeli]);
        }
        if ($imgshenasname) {
            $urlimgshenasname = $this->upload($imgshenasname);
            $user->imgshenasname = $urlimgshenasname;
            $user->update(['imgshenasname' => $urlimgshenasname]);

        }
		if ($avatar) {
            $urlavatar = $this->upload($avatar);
            $user->avatar = $urlavatar;
            $user->update(['avatar' => $urlavatar]);

        }
        if ($imgcatch) {
            $urlimgcatch = $this->upload($imgcatch);
            $user->imgcatch = $urlimgcatch;
            $user->update(['imgcatch' => $urlimgcatch]);
        }
        if ($imglease) {
            $urlimglease = $this->upload($imglease);
            $user->imglease = $urlimgcatch;
            $user->update(['imglease' => $urlimglease]);
        }
        if ($imgczech) {
            $urlimgczech = $this->upload($imgczech);
            $user->imgczech = $urlimgczech;
            $user->update(['imglease' => $urlimgczech]);
        }

        unset($inputs['imgmeli']);
        unset($inputs['imgshenasname']);
		unset($inputs['avatar']);
        unset($inputs['imgcatch']);
        unset($inputs['imglease']);
        unset($inputs['imgczech']);
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
            $url = $this->upload($img);
            $user->update(['avatar' => $url]);
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

}
