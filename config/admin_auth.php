<?php

return [

    /*
    |--------------------------------------------------------------------------
    | تنظیمات احراز هویت پنل ادمین
    |--------------------------------------------------------------------------
    |
    | این فایل تمام عددهای جادویی مربوط به ورود ادمین را در یک‌جا
    | متمستاجرین و شرکای تجاری می‌کند. هیچ Service ای نباید این مقادیر را hard-code کند.
    |
    | هر مقدار از طریق متغیر محیطی (env) قابل override است تا برای
    | محیط‌های مختلف (local / staging / production) قابل تنظیم باشد.
    |
    */

    'otp' => [

        /*
        | طول کد یکبار مصرف به صورت تعداد رقم.
        | پیش‌فرض ۶ رقم؛ فضای حالت ۱ میلیونی که با throttle و max_attempts
        | برای brute-force عملاً غیرقابل نفوذ می‌شود.
        */
        'length' => (int) env('ADMIN_OTP_LENGTH', 6),

        /*
        | مدت اعتبار کد به ثانیه.
        | پیش‌فرض ۱۲۰ ثانیه (۲ دقیقه) — تعادل بین UX و امنیت.
        */
        'ttl_seconds' => (int) env('ADMIN_OTP_TTL_SECONDS', 120),

        /*
        | حداکثر تعداد تلاش اشتباه برای وارد کردن یک کد.
        | پس از رسیدن به این عدد، کد سوزانده می‌شود و کاربر باید کد جدید بگیرد.
        */
        'max_attempts' => (int) env('ADMIN_OTP_MAX_ATTEMPTS', 5),

        /*
        | فاصله‌ی مجاز بین دو درخواست متوالی ارسال کد برای یک شماره (ثانیه).
        | از spam ارسال SMS جلوگیری می‌کند.
        */
        'resend_cooldown_seconds' => (int) env('ADMIN_OTP_RESEND_COOLDOWN', 90),

        /*
        | شناسه‌ی تمپلیت پیامک OTP در پنل ارسال.
        | برای IranSms عددی است (template id در parsasms)؛
        | برای سایر provider ها می‌تواند نام تمپلیت در جدول sms_templates باشد.
        */
        'sms_template' => env('ADMIN_OTP_SMS_TEMPLATE', 'admin-login-otp'),

    ],

    'password_login' => [

        /*
        | حداکثر تلاش‌های ورود با پسورد قبل از قفل موقت
        | (بر اساس ترکیب IP و شماره موبایل).
        */
        'throttle_max_attempts' => (int) env('ADMIN_LOGIN_THROTTLE_MAX', 5),

        /*
        | مدت قفل شدن بعد از رسیدن به حد throttle (دقیقه).
        */
        'throttle_decay_minutes' => (int) env('ADMIN_LOGIN_THROTTLE_DECAY', 15),

    ],

];
