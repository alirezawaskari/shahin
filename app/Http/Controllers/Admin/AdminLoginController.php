<?php

declare( strict_types = 1 );

namespace App\Http\Controllers\Admin;

use App\DTOs\Auth\PasswordCredentialsDTO;
use App\Exceptions\Auth\AdminAuthException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\PasswordLoginRequest;
use App\Services\V1\Auth\AdminAuthService;
use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class AdminLoginController extends Controller {

    public function __construct(
        private readonly AdminAuthService $adminAuthService
    ) {
    }

    public function showLoginForm(): View {
        return view( 'Admin.auth.login' );
    }

    public function loginWithPassword( PasswordLoginRequest $request ) {
        $throttleKey = $this->throttleKey( $request );
        $maxAttempts = ( int ) config( 'admin_auth.password_login.throttle_max_attempts', 5 );
        $decaySeconds = ( int ) config( 'admin_auth.password_login.throttle_decay_minutes', 15 ) * 60;

        if ( RateLimiter::tooManyAttempts( $throttleKey, $maxAttempts ) ) {
            return $this->throttleErrorResponse(
                $request,
                RateLimiter::availableIn( $throttleKey )
            );
        }
        try {
            $this->adminAuthService->loginByPassword( $request->toDTO() );

            // 3 ) موفقیت
            RateLimiter::clear( $throttleKey );
            $request->session()->regenerate();

            return $this->loginSuccessResponse( $request );
        } catch ( AdminAuthException $e ) {
            // 4 ) شکست — یک hit به throttle می‌خورد
            RateLimiter::hit( $throttleKey, $decaySeconds );

            return $this->adminAuthErrorResponse( $request, $e );
        }

    }

    private function throttleKey( PasswordLoginRequest $request ) {
        return 'admin-login|' .( string )$request->input( 'phone' ) . '|' . $request->ip();
    }

    private function throttleErrorResponse( PasswordLoginRequest $request, int $retryAfterSeconds ): JsonResponse|RedirectResponse {
        $message = "تعداد تلاش‌های ناموفق ورود بیش از حد مجاز است. لطفاً {$retryAfterSeconds} ثانیه دیگر تلاش کنید.";

        if ( $request->wantsJson() ) {
            return response()
            ->json( [
                'success' => false,
                'message' => $message,
            ], 429 )
            ->header( 'Retry-After', ( string ) $retryAfterSeconds );
        }

        return back()
        ->withErrors( [ 'auth' => $message ] )
        ->withInput( $request->except( 'password' ) );
    }

    private function loginSuccessResponse( PasswordLoginRequest $request ) {

        $defaultRedirect = self::getDefaultRedirect();
        if ( $request->wantsJson() ) {
            return response()->json( [
                'success'  => true,
                'message'  => 'ورود با موفقیت انجام شد.',
                'redirect' => url( $defaultRedirect ),
            ] );
        }

        return redirect()->intended( $defaultRedirect );
    }
    private function getDefaultRedirect()
    {
        $defaultRedirect = 'dashboard/dailyDesktop';
        $level = auth()->user()->level;
        if ( $level == 'admin' ) {
            if ( auth()->user()->employee->status == 2 && !auth()->user()->employee->confirm ) {
                $defaultRedirect = '/dashboard/employee/contract/'.auth()->user()->employee->id;
            }
        } else if ( $level == 'ghorfeAdmin' ) {
            $ghorfe = ghorfe();
            $defaultRedirect = 'dashboard/ghorfeOnlineList/'.$ghorfe->slug.'/edit';
        }
        return $defaultRedirect;
    }
    private function adminAuthErrorResponse( PasswordLoginRequest $request, AdminAuthException|\Exception $e,   string $errorKey = 'auth' ) {
        if ( $request->wantsJson() ) {
            return $this->adminAuthJsonError( $e );
        }

        return back()
        ->withErrors( [ $errorKey => $e->userMessage() ] )
        ->withInput( $request->except( 'password' ) );

    }

    private function adminAuthJsonError( AdminAuthException|\Exception $e ) {
        $response = response()->json( [
            'success' => false,
            'message' => $e->userMessage(),
        ], $e->statusCode() );

        if ( $e instanceof TooManyOtpRequestsException ) {
            $response->header( 'Retry-After', ( string ) $e->retryAfterSeconds() );
        }

        return $response;
    }

}
