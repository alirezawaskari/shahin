<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که کاربر کدی وارد می‌کند که در دیتابیس نیست
 * (یا هرگز ساخته نشده، یا حذف شده).
 *
 * این با OtpExpiredException و OtpAlreadyUsedException متفاوت است
 * تا UI بتواند پیام‌های متفاوت نشان دهد.
 */
class InvalidOtpException extends AdminAuthException
{
    public function statusCode(): int
    {
        return 422;
    }

    public function userMessage(): string
    {
        return 'کد وارد شده معتبر نیست.';
    }
}
