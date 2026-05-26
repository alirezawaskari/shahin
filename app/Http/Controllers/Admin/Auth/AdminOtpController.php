<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Exceptions\Auth\AdminAuthException;
use App\Exceptions\Auth\NotAdminException;
use App\Http\Controllers\Admin\Auth\Concerns\HandlesAdminAuthExceptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\RequestOtpRequest;
use App\Http\Requests\Admin\Auth\VerifyOtpRequest;
use App\Services\V1\Auth\AdminAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


final class AdminOtpController extends Controller
{
    use HandlesAdminAuthExceptions;

    public function __construct(
        private readonly AdminAuthService $adminAuthService,
    ) {
    }

    /**
     * درخواست ارسال کد یکبار مصرف.
     */
    public function requestCode(RequestOtpRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $ttl = $this->adminAuthService->requestOtp($request->validated('phone'));

            return $this->genericOtpSentResponse($request, $ttl);
        } catch (NotAdminException) {
            // گزینه الف: پاسخ همان‌طور است که گویا کد ارسال شده
            // (با همان TTL، تا UI نتواند تفاوتی ببیند)
            return $this->genericOtpSentResponse(
                $request,
                (int) config('admin_auth.otp.ttl_seconds', 120)
            );
        } catch (AdminAuthException $e) {
            // سایر استثناها (TooManyOtpRequests، OtpNotification) صریحاً به کاربر اعلام می‌شوند
            return $this->adminAuthErrorResponse($request, $e, errorKey: 'otp');
        }
    }

    /**
     * اعتبارسنجی کد یکبار مصرف و ورود.
     */
    public function verifyCode(VerifyOtpRequest $request): JsonResponse|RedirectResponse
    {
        try {
            $this->adminAuthService->loginByOtp($request->toDTO());

            $request->session()->regenerate();

            return $this->loginSuccessResponse($request);
        } catch (AdminAuthException $e) {
            return $this->adminAuthErrorResponse($request, $e, errorKey: 'otp');
        }
    }

    private function genericOtpSentResponse(Request $request, int $ttl): JsonResponse|RedirectResponse
    {
        $message = 'در صورت ثبت بودن شماره در سیستم، کد یکبار مصرف ارسال خواهد شد.';

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => $message,
                'ttl_seconds' => $ttl,
            ]);
        }

        return back()
            ->with('otp_sent', true)
            ->with('otp_ttl', $ttl)
            ->with('otp_phone', $request->validated('phone'))
            ->with('status', $message);
    }
    private function loginSuccessResponse(Request $request): JsonResponse|RedirectResponse
    {
        $defaultRedirect = 'dashboard/dailyDesktop';

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'ورود با موفقیت انجام شد.',
                'redirect' => url($defaultRedirect),
            ]);
        }

        return redirect()->intended($defaultRedirect);
    }
}
