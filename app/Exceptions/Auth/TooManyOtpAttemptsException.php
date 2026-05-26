<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که کاربر تعداد دفعات بیش از حد مجاز (max_attempts)
 * کد اشتباه وارد کرده است؛ کد سوزانده شده و باید درخواست جدید بدهد.
 *
 * این مهم‌ترین لایه‌ی دفاعی در برابر brute-force روی کد ۶ رقمی است.
 */
class TooManyOtpAttemptsException extends AdminAuthException
{
    public function statusCode(): int
    {
        return 429;
    }

    public function userMessage(): string
    {
        return 'تعداد دفعات وارد کردن کد اشتباه بیش از حد مجاز است. لطفاً کد جدیدی درخواست کنید.';
    }
}
