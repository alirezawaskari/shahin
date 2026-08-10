<?php

/*
|--------------------------------------------------------------------------
| Main company (Lale) info
|--------------------------------------------------------------------------
|
| Fallback sender information used in SMS templates when the acting user is
| the main super admin (no shop / representation), or when there is no
| authenticated user at all (cron jobs, queued jobs). These values back the
| {company_name}, {phone} and {address} placeholders. See
| App\Services\Sms\SmsSenderInfoResolver.
|
*/

return [
    'name'    => env('COMPANY_NAME', 'شبکه خدمات رفاهی ، درمانی و اقساطی لاله'),
    'phone'   => env('COMPANY_PHONE', '02191095745'),
    'address' => env('COMPANY_ADDRESS', ''),
];
