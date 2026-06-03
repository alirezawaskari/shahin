<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که کاربر تلاش می‌کند کدی را وارد کند که قبلاً
 * با موفقیت verify شده و used_at آن set شده است.
 *
 * این جلوگیری از replay attack است.
 */
class OtpAlreadyUsedException extends AdminAuthException
{
    public function statusCode(): int
    {
        return 422;
    }

    public function userMessage(): string
    {
        return 'این کد قبلاً استفاده شده است. لطفاً کد جدیدی درخواست کنید.';
    }
}
