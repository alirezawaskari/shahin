<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->redirectTo = url()->previous();
    }

    public function username()
    {
        return 'phone';
    }

    public function showLoginForm()
    {
        session(['url.intended' => url()->previous()]);
        return view('auth.login');
    }


    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */

    protected function authenticated(Request $request, $user)
    {
        $representation = auth()->user()->level;
        if ($representation == 'admin') {
            return redirect('dashboard/dailyDesktop');
        }
        if ($representation == 'organ') {
            return redirect('profile');
        }
        if ($request->has('representation')) {
            return redirect()->back();
        }
    }
}
