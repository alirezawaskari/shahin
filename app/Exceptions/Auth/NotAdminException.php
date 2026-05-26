<?php

namespace App\Exceptions\Auth;

/**
 * زمانی پرتاب می‌شود که credentials صحیح است اما کاربر سطح دسترسی ادمین ندارد
 * (یعنی isAdmin() مقدار false برمی‌گرداند).
 */
class NotAdminException extends AdminAuthException
{
    public function statusCode(): int
    {
        return 403;
    }

    public function userMessage(): string
    {
        return 'شما اجازه‌ی ورود به پنل مدیریت را ندارید.';
    }
}
