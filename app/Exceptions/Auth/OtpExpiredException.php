<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که کد در دیتابیس هست اما expires_at آن گذشته است.
 */
class OtpExpiredException extends AdminAuthException
{
    public function statusCode(): int
    {
        return 422;
    }

    public function userMessage(): string
    {
        return 'کد یکبار مصرف منقضی شده است. لطفاً کد جدیدی درخواست کنید.';
    }
}
