<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که کاربر پشت سر هم درخواست ارسال کد می‌دهد
 * و هنوز cooldown قبلی به پایان نرسیده است.
 *
 * مقدار retryAfter به کاربر اعلام می‌کند چند ثانیه دیگر می‌تواند تلاش کند.
 */
class TooManyOtpRequestsException extends AdminAuthException
{
    public function __construct(private readonly int $retryAfterSeconds)
    {
        parent::__construct();
    }

    public function statusCode(): int
    {
        return 429;
    }

    public function userMessage(): string
    {
        return "تعداد درخواست‌های ارسال کد بیش از حد مجاز است. لطفاً {$this->retryAfterSeconds} ثانیه دیگر تلاش کنید.";
    }

    public function retryAfterSeconds(): int
    {
        return $this->retryAfterSeconds;
    }
}
