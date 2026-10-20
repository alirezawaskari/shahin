<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\SmsLog;
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

        $payment = SmsLog::create([
            'ghorfe_online_list_id' => $business->id,
            'user_id' => $user?->id,
            'credit_type' => $creditType,
            'type' => 'top_up_payment',
            'charge_amount' => $amount,
            'status' => SmsLog::STATUS_PENDING,
            'response' => json_encode(['return_url' => $returnUrl]),
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
    public function settleVerified(SmsLog|int|string $payment, float $paidAmount, ?string $refNumber = null): SmsLog
    {
        return DB::transaction(function () use ($payment, $paidAmount, $refNumber) {
            $row = SmsLog::query()
                ->when(
                    $payment instanceof SmsLog || is_int($payment),
                    fn ($q) => $q->whereKey($payment instanceof SmsLog ? $payment->id : $payment),
                    fn ($q) => $q->where('tracking_id', $payment)
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ($row->isPaid()) {
                return $row; // replay / double callback
            }
            if ($row->status !== SmsLog::STATUS_PENDING) {
                throw new DomainException('این پرداخت قبلاً بسته شده است و قابل تایید نیست.');
            }
            if (abs($paidAmount - (float) $row->amount) > 0.001) {
                // Never credit a different amount; close the row as failed for manual review.
                $row->update(['status' => SmsLog::STATUS_FAILED, 'response' => json_encode(['failure_reason' => 'amount_mismatch'])]);

                return $row->refresh();
            }

            $business = GhorfeOnlineList::findOrFail($row->ghorfe_online_list_id);
            $actor = $row->user_id ? User::find($row->user_id) : null;
            $tracking = 'PAY-' . $row->id . ($refNumber ? '-' . $refNumber : '');

            $transaction = $row->credit_type === 'otp'
                ? $this->financialService->topUpOtp($business, (float) $row->amount, $actor, $tracking)
                : $this->financialService->topUpAdvertising($business, (float) $row->amount, $actor, $tracking);

            $row->update([
                'status' => SmsLog::STATUS_PAID,
                'tracking_id' => $refNumber ?: $tracking,
            ]);

            return $row->refresh();
        });
    }

    /** Failed / cancelled payment: never credits. A paid row is left untouched. */
    public function settleFailed(SmsLog|int|string $payment, ?string $reason = null, bool $cancelled = false): SmsLog
    {
        return DB::transaction(function () use ($payment, $reason, $cancelled) {
            $row = SmsLog::query()
                ->when(
                    $payment instanceof SmsLog || is_int($payment),
                    fn ($q) => $q->whereKey($payment instanceof SmsLog ? $payment->id : $payment),
                    fn ($q) => $q->where('tracking_id', $payment)
                )
                ->lockForUpdate()
                ->firstOrFail();

            if ($row->isPending()) {
                $row->update([
                    'status' => $cancelled ? SmsLog::STATUS_CANCELLED : SmsLog::STATUS_FAILED,
                    'response' => json_encode(['failure_reason' => $reason]),
                ]);
            }

            return $row->refresh();
        });
    }
}
