<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که کاربر ادمین است اما حسابش غیرفعال شده
 * (مقدار ستون active برابر 0 است).
 */
class InactiveAccountException extends AdminAuthException
{
    public function statusCode(): int
    {
        return 403;
    }

    public function userMessage(): string
    {
        return 'حساب کاربری شما غیرفعال است. لطفاً با پشتیبانی تماس بگیرید.';
    }
}
