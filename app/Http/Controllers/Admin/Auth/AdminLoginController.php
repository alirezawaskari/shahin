<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Auth;

use App\Exceptions\Auth\AdminAuthException;
use App\Http\Controllers\Admin\Auth\Concerns\HandlesAdminAuthExceptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\PasswordLoginRequest;
use App\Services\V1\Auth\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

final class AdminLoginController extends Controller
{

    use HandlesAdminAuthExceptions;

    public function __construct(
        private readonly AdminAuthService $adminAuthService
    )
    {
    }

    public function showLoginForm(): View
    {
        return view('Admin.auth.login');
    }

    public function loginWithPassword(PasswordLoginRequest $request)
    {
        $throttleKey = $this->throttleKey($request);
        $maxAttempts = (int)config('admin_auth.password_login.throttle_max_attempts', 5);
        $decaySeconds = (int)config('admin_auth.password_login.throttle_decay_minutes', 15) * 60;

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            return $this->throttleErrorResponse(
                $request,
                RateLimiter::availableIn($throttleKey)
            );
        }
        try {
            $this->adminAuthService->loginByPassword($request->toDTO());

            // 3) موفقیت
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return $this->loginSuccessResponse($request);
        } catch (AdminAuthException $e) {
            // 4) شکست — یک hit به throttle می‌خورد
            RateLimiter::hit($throttleKey, $decaySeconds);

            return $this->adminAuthErrorResponse($request, $e);
        }


    }

    private function throttleKey(PasswordLoginRequest $request)
    {
        return 'admin-login|' . (string)$request->input('phone') . '|' . $request->ip();
    }

    private function throttleErrorResponse(PasswordLoginRequest $request, int $retryAfterSeconds): JsonResponse|RedirectResponse
    {
        $message = "تعداد تلاش‌های ناموفق ورود بیش از حد مجاز است. لطفاً {$retryAfterSeconds} ثانیه دیگر تلاش کنید.";

        if ($request->wantsJson()) {
            return response()
                ->json([
                    'success' => false,
                    'message' => $message,
                ], 429)
                ->header('Retry-After', (string)$retryAfterSeconds);
        }

        return back()
            ->withErrors(['auth' => $message])
            ->withInput($request->except('password'));
    }

    private function loginSuccessResponse(PasswordLoginRequest $request)
    {

        $defaultRedirect = 'dashboard/dailyDesktop';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ورود با موفقیت انجام شد.',
                'redirect' => url($defaultRedirect),
            ]);
        }

        return redirect()->intended($defaultRedirect);
    }

    public function logout(Request $request): JsonResponse|RedirectResponse
    {
        $this->adminAuthService->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.login.show');
    }


}
