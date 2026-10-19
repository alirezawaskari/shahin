<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\SmsTopUpPayment;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * SMS balance purchase flow: initiate (pending row + gateway redirect) and
 * settle (credit exactly once, only after the payment module verified it).
 */
class SmsTopUpPaymentService
{
    public function __construct(protected SmsFinancialService $financialService)
    {
    }

    protected function gateway(): SmsPaymentGateway
    {
        return app()->bound(SmsPaymentGateway::class)
            ? app(SmsPaymentGateway::class)
            : new UnconfiguredSmsPaymentGateway();
    }

    /**
     * Create a pending payment and return [payment, redirectUrl].
     * Credits nothing.
     *
     * @throws DomainException (SmsPaymentUnavailableException when the gateway cannot start)
     */
    public function initiate(GhorfeOnlineList $business, string $creditType, float $amount, ?User $user, ?string $returnUrl = null): array
    {
        if (!in_array($creditType, ['otp', 'advertising'], true)) {
            throw new DomainException('نوع اعتبار نامعتبر است.');
        }
        if ($amount < 1000) {
            throw new DomainException('حداقل مبلغ شارژ ۱,۰۰۰ ریال است.');
        }

        $payment = SmsTopUpPayment::create([
            'ghorfe_online_list_id' => $business->id,
            'user_id' => $user?->id,
            'credit_type' => $creditType,
            'amount' => $amount,
            'status' => SmsTopUpPayment::STATUS_PENDING,
            'return_url' => $returnUrl,
        ]);

        try {
            $url = $this->gateway()->start($payment);
        } catch (\Throwable $e) {
            $this->settleFailed($payment, $e->getMessage());
            throw $e;
        }

        return [$payment->refresh(), $url];
    }

    /**
     * Called by the payment module after it VERIFIED the payment with the bank.
     * Business and balance type come from the stored row, never from the caller.
     * Idempotent: a replay returns the paid row without crediting.
     * The paid amount must equal the stored amount, otherwise the row is closed as failed.
     */
    public function settleVerified(SmsTopUpPayment|int|string $payment, float $paidAmount, ?string $refNumber = null): SmsTopUpPayment
    {
        return DB::transaction(function () use ($payment, $paidAmount, $refNumber) {
            $row = SmsTopUpPayment::query()
                ->when(
                    $payment instanceof SmsTopUpPayment || is_int($payment),
                    fn ($q) => $q->whereKey($payment instanceof SmsTopUpPayment ? $payment->id : $payment),
                    fn ($q) => $q->where('authority', $payment)
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ($row->isPaid()) {
                return $row; // replay / double callback
            }
            if ($row->status !== SmsTopUpPayment::STATUS_PENDING) {
                throw new DomainException('این پرداخت قبلاً بسته شده است و قابل تایید نیست.');
            }
            if (abs($paidAmount - (float) $row->amount) > 0.001) {
                // Never credit a different amount; close the row as failed for manual review.
                $row->update(['status' => SmsTopUpPayment::STATUS_FAILED, 'failure_reason' => 'amount_mismatch']);

                return $row->refresh();
            }

            $business = GhorfeOnlineList::findOrFail($row->ghorfe_online_list_id);
            $actor = $row->user_id ? User::find($row->user_id) : null;
            $tracking = 'PAY-' . $row->id . ($refNumber ? '-' . $refNumber : '');

            $transaction = $row->credit_type === 'otp'
                ? $this->financialService->topUpOtp($business, (float) $row->amount, $actor, $tracking)
                : $this->financialService->topUpAdvertising($business, (float) $row->amount, $actor, $tracking);

            $row->update([
                'status' => SmsTopUpPayment::STATUS_PAID,
                'ref_number' => $refNumber,
                'sms_transaction_id' => $transaction->id,
                'paid_at' => now(),
            ]);

            return $row->refresh();
        });
    }

    /** Failed / cancelled payment: never credits. A paid row is left untouched. */
    public function settleFailed(SmsTopUpPayment|int|string $payment, ?string $reason = null, bool $cancelled = false): SmsTopUpPayment
    {
        return DB::transaction(function () use ($payment, $reason, $cancelled) {
            $row = SmsTopUpPayment::query()
                ->when(
                    $payment instanceof SmsTopUpPayment || is_int($payment),
                    fn ($q) => $q->whereKey($payment instanceof SmsTopUpPayment ? $payment->id : $payment),
                    fn ($q) => $q->where('authority', $payment)
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ($row->isPending()) {
                $row->update([
                    'status' => $cancelled ? SmsTopUpPayment::STATUS_CANCELLED : SmsTopUpPayment::STATUS_FAILED,
                    'failure_reason' => $reason,
                ]);
            }

            return $row->refresh();
        });
    }
}
