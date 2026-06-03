<?php

namespace App\Http\Controllers\Admin\Auth\Concerns;

use App\Exceptions\Auth\AdminAuthException;
use App\Exceptions\Auth\TooManyOtpRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;


trait HandlesAdminAuthExceptions
{

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
