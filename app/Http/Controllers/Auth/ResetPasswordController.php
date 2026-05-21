<?php

namespace App\Http\Controllers\Auth;

use App\Events\userActivation;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
        $this->middleware('guest');
    }

    public function resetform()
    {
        return view('auth.passwords.reset');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $phone = Helper::numberConverter($request->input('phone'));

        $user = User::where('phone' , $phone)->first();
        if ($user){
            $pass = rand(123456 , 999999);
            $update = $user->update([
                'password' => Hash::make($pass)
            ]);
            if ($update){

                event(new UserActivation($user , $pass));

                return redirect(route('login'))->with([
                    'reset_pass' => 'success'
                ]);
            }else{
                return redirect()->back()->with([
                    'reset_pass' => 'error'
                ]);
            }
        }else{
            return redirect()->back()->with([
                'reset_pass' => 'error'
            ]);
        }
    }
}
