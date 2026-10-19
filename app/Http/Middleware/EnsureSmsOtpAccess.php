<?php

namespace App\Http\Middleware;

use App\Services\Sms\SmsAccessTierService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OTP credit and OTP reports are internal-only (Titanium / superAdmin): a lower-tier user has
 * no access to them at all, not even read-only. See SmsAccessTierService::canUseOtp().
 */
class EnsureSmsOtpAccess
{
    public function __construct(protected SmsAccessTierService $accessTierService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->accessTierService->canUseOtp($request->user())) {
            $message = 'دسترسی به سرویس پیامک اعتبارسنجی (OTP) برای سطح دسترسی شما مجاز نیست.';

            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
