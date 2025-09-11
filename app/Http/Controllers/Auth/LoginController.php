<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller {
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

    public function __construct() {
        $this->middleware( 'guest' )->except( 'logout' );
        $this->redirectTo = url()->previous();
    }

    public function username() {
        return 'phone';
    }

    public function showLoginForm() {
        session( [ 'url.intended' => url()->previous() ] );
        return view( 'auth.login' );
    }

    /**
    * The user has been authenticated.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  mixed  $user
    * @return mixed
    */

    protected function authenticated( Request $request, $user ) {
        $level = auth()->user()->level;
        if ( $level == 'admin' ) {
            if ( auth()->user()->employee->status == 2 && !auth()->user()->employee->confirm ) {
                return redirect( '/dashboard/employee/contract/'.auth()->user()->employee->id );
            }
            return redirect( 'dashboard/dailyDesktop' );
        }
        if ( $level == 'organ' ) {
            return redirect( 'profile' );
        }
        if ( $level == 'blockUser' ) {
            $this->guard()->logout();
        }
        if ( $request->has( 'representation' ) ) {
            return redirect()->back();
        }
    }

    /**
    * Log the user out of the application.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    */

    public function logout( Request $request ) {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ( $response = $this->loggedOut( $request ) ) {
            return $response;
        }

        return $request->wantsJson()
        ? new JsonResponse( [], 204 )
        : redirect()->back();
    }
}
