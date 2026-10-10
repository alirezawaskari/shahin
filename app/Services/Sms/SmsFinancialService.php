<?php

namespace App\Services\Sms;

use App\Models\AdvertisingBalance;
use App\Models\GhorfeOnlineList;
use App\Models\OtpBalance;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\SmsTransaction;
use App\Models\User;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmsFinancialService
{
    public function __construct(
        protected BalanceGuard $balanceGuard
    ) {
    }

    /**
     * Get complete financial statistics, balances, history and transactions for OTP of a business.
     *
     * @return array<string, mixed>
     */
    public function getOtpFinancialData(GhorfeOnlineList $business, int $perPage = 20): array
    {
        $otpBalance = $this->balanceGuard->getOtpBalance($business);
        $balance = (float) $otpBalance->balance;
        $threshold = (float) $otpBalance->low_balance_threshold;
        $isLowBalance = $balance <= $threshold;

        // Financial aggregates from sms_transactions
        $totalCharged = (float) SmsTransaction::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'otp')
            ->sum('charge_amount');

        $totalConsumed = (float) SmsTransaction::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'otp')
            ->sum('consume_amount');

        // Message count from sms_messages
        $messageCount = SmsMessage::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('service_type', 'otp')
            ->count();

        // Charge / consumption history grouped by date
        $history = SmsTransaction::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'otp')
            ->selectRaw("DATE(created_at) as date, SUM(charge_amount) as total_charged, SUM(consume_amount) as total_consumed, COUNT(*) as tx_count")
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        // Paginated transactions
        $transactions = SmsTransaction::query()
            ->with(['creator', 'message'])
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'otp')
            ->orderByDesc('id')
            ->paginate($perPage);

        return [
            'business' => $business,
            'otp_balance_model' => $otpBalance,
            'balance' => $balance,
            'low_balance_threshold' => $threshold,
            'is_low_balance' => $isLowBalance,
            'total_charged' => $totalCharged,
            'total_consumed' => $totalConsumed,
            'message_count' => $messageCount,
            'history' => $history,
            'transactions' => $transactions,
        ];
    }

    /**
     * Get per-sender advertising financial data (or summary across all assigned advertising senders).
     *
     * @return array<string, mixed>
     */
    public function getAdvertisingFinancialData(
        GhorfeOnlineList $business,
        ?SenderNumber $senderNumber = null,
        int $perPage = 20
    ): array {
        // All active advertising senders assigned to this business
        $assignedSenders = $business->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->where('sender_numbers.status', 'active')
            ->get();

        // If no sender specified, pick the first assigned sender or null
        $activeSender = $senderNumber ?? $assignedSenders->first();

        // Build per-sender summaries
        $sendersSummary = $assignedSenders->map(function ($sender) use ($business) {
            $balance = (float) (AdvertisingBalance::where('ghorfe_online_list_id', $business->id)
                ->where('sender_number_id', $sender->id)
                ->value('balance') ?? 0);

            $totalCharged = (float) SmsTransaction::where('ghorfe_online_list_id', $business->id)
                ->where('credit_type', 'advertising')
                ->where('sender_number_id', $sender->id)
                ->sum('charge_amount');

            $totalConsumed = (float) SmsTransaction::where('ghorfe_online_list_id', $business->id)
                ->where('credit_type', 'advertising')
                ->where('sender_number_id', $sender->id)
                ->sum('consume_amount');

            $msgCount = SmsMessage::where('ghorfe_online_list_id', $business->id)
                ->where('sender_number_id', $sender->id)
                ->where('service_type', 'advertising')
                ->count();

            return [
                'sender' => $sender,
                'balance' => $balance,
                'total_charged' => $totalCharged,
                'total_consumed' => $totalConsumed,
                'message_count' => $msgCount,
            ];
        });

        // Detail for selected sender
        $senderDetails = null;
        if ($activeSender) {
            $advBalanceModel = $this->balanceGuard->getAdvertisingBalance($business, $activeSender);
            $balance = (float) $advBalanceModel->balance;

            $totalCharged = (float) SmsTransaction::query()
                ->where('ghorfe_online_list_id', $business->id)
                ->where('credit_type', 'advertising')
                ->where('sender_number_id', $activeSender->id)
                ->sum('charge_amount');

            $totalConsumed = (float) SmsTransaction::query()
                ->where('ghorfe_online_list_id', $business->id)
                ->where('credit_type', 'advertising')
                ->where('sender_number_id', $activeSender->id)
                ->sum('consume_amount');

            $messageCount = SmsMessage::query()
                ->where('ghorfe_online_list_id', $business->id)
                ->where('sender_number_id', $activeSender->id)
                ->where('service_type', 'advertising')
                ->count();

            $history = SmsTransaction::query()
                ->where('ghorfe_online_list_id', $business->id)
                ->where('credit_type', 'advertising')
                ->where('sender_number_id', $activeSender->id)
                ->selectRaw("DATE(created_at) as date, SUM(charge_amount) as total_charged, SUM(consume_amount) as total_consumed, COUNT(*) as tx_count")
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderByDesc('date')
                ->limit(30)
                ->get();

            $transactions = SmsTransaction::query()
                ->with(['creator', 'message', 'campaign', 'senderNumber'])
                ->where('ghorfe_online_list_id', $business->id)
                ->where('credit_type', 'advertising')
                ->where('sender_number_id', $activeSender->id)
                ->orderByDesc('id')
                ->paginate($perPage);

            $senderDetails = [
                'sender' => $activeSender,
                'balance_model' => $advBalanceModel,
                'balance' => $balance,
                'total_charged' => $totalCharged,
                'total_consumed' => $totalConsumed,
                'message_count' => $messageCount,
                'history' => $history,
                'transactions' => $transactions,
            ];
        }

        return [
            'business' => $business,
            'assigned_senders' => $assignedSenders,
            'active_sender' => $activeSender,
            'senders_summary' => $sendersSummary,
            'sender_details' => $senderDetails,
        ];
    }

    /**
     * Top-up OTP balance for a business.
     * Strictly credits ONLY that business's otp_balances row.
     *
     * @throws DomainException
     */
    public function topUpOtp(
        GhorfeOnlineList $business,
        float $amount,
        ?User $actor = null,
        ?string $trackingId = null
    ): SmsTransaction {
        if ($amount <= 0) {
            throw new DomainException('مبلغ شارژ باید بیشتر از صفر باشد.');
        }

        return DB::transaction(function () use ($business, $amount, $actor, $trackingId) {
            $otpBalance = $this->balanceGuard->getOtpBalance($business);

            // Row-locked atomic credit: concurrent top-ups and debits serialize here.
            $balanceBefore = $otpBalance->lockAndCredit($amount);
            $balanceAfter = $balanceBefore + $amount;

            return SmsTransaction::create([
                'ghorfe_online_list_id' => $business->id,
                'credit_type' => 'otp',
                'sender_number_id' => null,
                'charge_amount' => $amount,
                'consume_amount' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'tracking_id' => $trackingId ?: ('TOPUP-OTP-' . strtoupper(Str::random(10))),
                'created_by' => $actor?->id ?? auth()->id(),
            ]);
        });
    }

    /**
     * Top-up Advertising balance for a specific sender number of a business.
     * Strictly credits ONLY the chosen sender's advertising_balances row for that business.
     * Never cross-credits!
     *
     * @throws DomainException
     */
    public function topUpAdvertising(
        GhorfeOnlineList $business,
        SenderNumber $senderNumber,
        float $amount,
        ?User $actor = null,
        ?string $trackingId = null
    ): SmsTransaction {
        if ($amount <= 0) {
            throw new DomainException('مبلغ شارژ باید بیشتر از صفر باشد.');
        }

        // Verify sender is assigned to this business or is active advertising
        $isAssigned = $business->senderNumbers()
            ->where('sender_numbers.id', $senderNumber->id)
            ->where('sender_numbers.type', 'advertising')
            ->exists();

        if (!$isAssigned) {
            throw new DomainException('شماره فرستنده انتخابی به این کسب‌وکار تخصیص نیافته است یا نوع آن تبلیغاتی نیست.');
        }

        return DB::transaction(function () use ($business, $senderNumber, $amount, $actor, $trackingId) {
            $advBalance = $this->balanceGuard->getAdvertisingBalance($business, $senderNumber);

            // Row-locked atomic credit: concurrent top-ups and debits serialize here.
            $balanceBefore = $advBalance->lockAndCredit($amount);
            $balanceAfter = $balanceBefore + $amount;

            return SmsTransaction::create([
                'ghorfe_online_list_id' => $business->id,
                'credit_type' => 'advertising',
                'sender_number_id' => $senderNumber->id,
                'charge_amount' => $amount,
                'consume_amount' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'tracking_id' => $trackingId ?: ('TOPUP-ADV-' . strtoupper(Str::random(10))),
                'created_by' => $actor?->id ?? auth()->id(),
            ]);
        });
    }
}
