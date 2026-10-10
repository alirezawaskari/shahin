<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates SMS gateway callbacks with a shared secret sent in the
 * X-Webhook-Secret header. Fails closed: if SMS_WEBHOOK_SECRET is not
 * configured, every callback is rejected.
 */
class VerifySmsWebhookSecret
{
    public const HEADER = 'X-Webhook-Secret';

    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.sms.webhook_secret', '');
        $provided = (string) $request->header(self::HEADER, '');

        if ($expected === '' || !hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return $next($request);
    }
}
