<?php

namespace App\Http\Controllers\Admin\Auth\Concerns;

use App\Exceptions\Auth\AdminAuthException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Trait مشترک بین AdminLoginController و AdminOtpController برای تبدیل
 * AdminAuthException به پاسخ HTTP مناسب (هم JSON هم HTML).
 *
 * چرا Trait؟
 *  - این منطق در هر دو Controller تکرار می‌شد (DRY).
 *  - یک تغییر در فرمت پاسخ خطا، فقط در یک‌جا اعمال می‌شود (OCP).
 *  - منطق نسبتاً جزئی است و یک کلاس مستقل برایش over-engineering بود.
 */
trait HandlesAdminAuthExceptions
{
    /**
     * ساخت پاسخ مناسب برای یک AdminAuthException.
     *
     * نوع پاسخ بر اساس Accept header تصمیم گرفته می‌شود:
     *  - JSON برای ajax/api
     *  - Redirect with errors برای فرم HTML معمولی
     *
     * @param  string  $errorKey  کلید خطا در session (مثلاً 'auth' یا 'otp')
     *                            تا UI بداند کجا پیام را نمایش دهد.
     */
    protected function adminAuthErrorResponse(
        Request $request,
        AdminAuthException $e,
        string $errorKey = 'auth'
    ): JsonResponse|RedirectResponse {
        if ($request->wantsJson()) {
            return $this->adminAuthJsonError($e);
        }

        return back()
            ->withErrors([$errorKey => $e->userMessage()])
            ->withInput($request->except('password'));
    }

    /**
     * ساخت پاسخ JSON یکپارچه برای AdminAuthException.
     *
     * در صورت TooManyOtpRequestsException، هدر Retry-After هم اضافه می‌شود.
     */
    private function adminAuthJsonError(AdminAuthException $e): JsonResponse
    {
        $response = response()->json([
            'success' => false,
            'message' => $e->userMessage(),
        ], $e->statusCode());

        if ($e instanceof TooManyOtpRequestsException) {
            $response->header('Retry-After', (string) $e->retryAfterSeconds());
        }

        return $response;
    }
}
