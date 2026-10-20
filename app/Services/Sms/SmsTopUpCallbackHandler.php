<?php

namespace App\Services\Sms;

use App\Lib\BankVerifier;
use App\Models\SmsLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Called first by PaymentController@callback. If the bank token belongs to an SMS
 * top-up it is verified with the same bank call the invoice flow uses and settled
 * through SmsTopUpPaymentService; otherwise it returns null and the invoice code
 * runs exactly as before. It never throws (the caller must not fall into Factor code).
 */
class SmsTopUpCallbackHandler
{
    public function __construct(protected SmsTopUpPaymentService $service, protected BankVerifier $verifier)
    {
    }

    public function handleZarinpal(Request $request): ?RedirectResponse
    {
        $payment = $this->find((string) $request->get('Authority'));
        if (!$payment) {
            return null;
        }

        if ($payment->isPending()) {
            try {
                if ($request->get('Status') === 'OK') {
                    $this->verifyZarinpal($payment);
                } else {
                    $this->service->settleFailed($payment, 'cancelled_by_user', true);
                }
            } catch (\Throwable $e) {
                Log::error('SMS top-up zarinpal callback failed: ' . $e->getMessage(), ['payment' => $payment->id]);
            }
        }

        return $this->result($payment);
    }

    public function handleSaman(Request $request): ?RedirectResponse
    {
        $payment = $this->find((string) $request->get('Token'));
        if (!$payment) {
            return null;
        }

        if ($payment->isPending()) {
            try {
                if ($request->get('State') !== 'OK') {
                    $this->service->settleFailed($payment, 'cancelled_by_user', true);
                } else {
                    $refNum = $request->get('RefNum');
                    $data = $this->verifier->saman($refNum);
                    $detail = $data['TransactionDetail'] ?? null;
                    if (($data['Success'] ?? false) == true && $detail && ($detail['RefNum'] ?? null) == $refNum) {
                        // Amount reported by the bank (rials); the service rejects any mismatch.
                        $this->service->settleVerified($payment, (float) $detail['OrginalAmount'], (string) $detail['RefNum']);
                    } else {
                        $this->service->settleFailed($payment, 'verification_failed');
                    }
                }
            } catch (\Throwable $e) {
                Log::error('SMS top-up saman callback failed: ' . $e->getMessage(), ['payment' => $payment->id]);
            }
        }

        return $this->result($payment);
    }

    /**
     * Verify a Zarinpal payment with the bank and settle it. Also used by
     * verify:unverifiedTransaction. Zarinpal amounts are toman (rials / 10).
     */
    public function verifyZarinpal(SmsLog $payment): SmsLog
    {
        $tomans = (float) $payment->amount / 10;
        $result = $this->verifier->zarinpal((string) $payment->authority, $tomans);

        if (is_array($result) && ($result['Status'] ?? null) == 100) {
            return $this->service->settleVerified($payment, $tomans * 10, (string) ($result['RefID'] ?? ''));
        }

        return $this->service->settleFailed($payment, 'verification_failed');
    }

    public function find(string $authority): ?SmsLog
    {
        if ($authority === '') {
            return null;
        }

        return SmsLog::where('tracking_id', $authority)
            ->orWhere('response', 'like', "%{$authority}%")
            ->first();
    }

    protected function result(SmsLog $payment): RedirectResponse
    {
        return redirect()->route('admin.sms.financial.topup.result', $payment->id);
    }
}
