<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که ترکیب شماره موبایل و رمز عبور با هیچ کاربری مطابقت نکند.
 *
 * نکته‌ی امنیتی: پیام عمداً مبهم است (نمی‌گوید شماره اشتباه است یا رمز عبور)
 * تا مهاجم نتواند با enumeration بفهمد چه شماره‌هایی در سیستم ثبت‌اند.
 */
class InvalidAdminCredentialsException extends AdminAuthException
{
    public function statusCode(): int
    {
        return 401;
    }

    public function userMessage(): string
    {
        return 'شماره موبایل یا رمز عبور وارد شده اشتباه است.';
    }
}
