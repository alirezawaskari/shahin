<?php

namespace App\Services\Sms;

use App\Exceptions\Sms\SmsPaymentUnavailableException;
use App\Lib\saman;
use App\Lib\zarinpal;
use App\Models\SmsTopUpPayment;

/**
 * Wires SMS top-ups to the project's payment module: same gateways and same
 * pay.callback route as PaymentController@pay. Gateway = the request's `gateway`
 * input when it is 'zarinpal' or 'saman', otherwise 'saman' (the project default).
 * SMS amounts are rials; Zarinpal takes toman (rials / 10), Saman takes rials.
 */
class PaymentModuleSmsGateway implements SmsPaymentGateway
{
    public const DESCRIPTION = 'شارژ اعتبار پیامک';

    public function start(SmsTopUpPayment $payment): string
    {
        $gateway = request()->input('gateway');
        $gateway = in_array($gateway, ['zarinpal', 'saman'], true) ? $gateway : 'saman';

        try {
            $url = $gateway === 'zarinpal' ? $this->startZarinpal($payment) : $this->startSaman($payment);
        } catch (SmsPaymentUnavailableException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new SmsPaymentUnavailableException('اتصال به درگاه پرداخت برقرار نشد. لطفاً دوباره تلاش کنید.');
        }

        return $url;
    }

    protected function startZarinpal(SmsTopUpPayment $payment): string
    {
        if (!config('services.zarinpal.id')) {
            throw new SmsPaymentUnavailableException('درگاه زرین‌پال پیکربندی نشده است.');
        }

        $authority = app(zarinpal::class)->pay((float) $payment->amount / 10, self::DESCRIPTION);
        if (!$authority) {
            throw new SmsPaymentUnavailableException('خطا در ایجاد تراکنش زرین‌پال.');
        }

        $payment->update(['gateway' => 'zarinpal', 'authority' => $authority]);

        return config('services.zarinpal.url') . '/pg/StartPay/' . $authority;
    }

    protected function startSaman(SmsTopUpPayment $payment): string
    {
        $token = app(saman::class)->requestToken(
            (int) round((float) $payment->amount),
            'SMS' . $payment->id . '-' . uniqid(),
            $payment->user?->phone
        );
        if (is_array($token) || !$token) {
            throw new SmsPaymentUnavailableException('خطا در دریافت توکن سامان.');
        }

        $payment->update(['gateway' => 'saman', 'authority' => $token]);

        return 'https://sep.shaparak.ir/OnlinePG/SendToken?token=' . $token;
    }
}
