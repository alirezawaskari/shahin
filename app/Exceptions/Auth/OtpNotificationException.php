<?php

namespace App\Exceptions\Auth;

use Throwable;

/**
 * زمانی پرتاب می‌شود که Notifier (مثلاً سرویس پیامک) در ارسال کد شکست می‌خورد.
 *
 * این استثنا توسط پیاده‌سازی‌های OtpNotifierInterface پرتاب می‌شود
 * و معمولاً wrapper روی استثنای زیرین (Guzzle exception یا ...) است.
 */
class OtpNotificationException extends AdminAuthException
{
    public function __construct(string $message = 'خطا در ارسال کد', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function statusCode(): int
    {
        return 500;
    }

    public function userMessage(): string
    {
        return 'ارسال کد یکبار مصرف با خطا مواجه شد. لطفاً مجدداً تلاش کنید.';
    }
}
