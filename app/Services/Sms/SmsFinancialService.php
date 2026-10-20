<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\SmsLog;
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
        $balance = (float) ($otpBalance->otp_balance ?? 0);
        $threshold = (float) ($otpBalance->low_balance_threshold ?? 1000000);
        $isLowBalance = $balance <= $threshold;

        $hasChargeCol = \Illuminate\Support\Facades\Schema::hasColumn('sms_logs', 'charge_amount');

        // Financial aggregates from sms_logs
        $totalCharged = $hasChargeCol ? (float) SmsLog::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'otp')
            ->sum('charge_amount') : 0.0;

        $totalConsumed = $hasChargeCol ? (float) SmsLog::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'otp')
            ->sum('consume_amount') : 0.0;

        // Message count from groupsms
        $messageCount = Groupsms::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('service_type', 'otp')
            ->count();

        // Charge / consumption history grouped by date
        $history = $hasChargeCol ? SmsLog::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'otp')
            ->selectRaw("DATE(created_at) as date, SUM(charge_amount) as total_charged, SUM(consume_amount) as total_consumed, COUNT(*) as tx_count")
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderByDesc('date')
            ->limit(30)
            ->get() : collect();

        // Paginated transactions
        $transactions = SmsLog::query()
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
     * Get advertising financial data for a business. Credit belongs to the business'
     * advertising panel, so there is a single balance shared by all of its advertising lines.
     *
     * @return array<string, mixed>
     */
    public function getAdvertisingFinancialData(GhorfeOnlineList $business, int $perPage = 20): array
    {
        $advBalanceModel = $this->balanceGuard->getAdvertisingBalance($business);
        $balance = (float) ($advBalanceModel->advertising_balance ?? 0);

        $hasChargeCol = \Illuminate\Support\Facades\Schema::hasColumn('sms_logs', 'charge_amount');

        $base = fn () => SmsLog::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('credit_type', 'advertising');

        $totalCharged = $hasChargeCol ? (float) $base()->sum('charge_amount') : 0.0;
        $totalConsumed = $hasChargeCol ? (float) $base()->sum('consume_amount') : 0.0;

        $messageCount = Groupsms::query()
            ->where('ghorfe_online_list_id', $business->id)
            ->where('service_type', 'advertising')
            ->count();

        $history = $hasChargeCol ? $base()
            ->selectRaw("DATE(created_at) as date, SUM(charge_amount) as total_charged, SUM(consume_amount) as total_consumed, COUNT(*) as tx_count")
            ->groupBy(DB::raw("DATE(created_at)"))
            ->orderByDesc('date')
            ->limit(30)
            ->get() : collect();

        $transactions = $base()
            ->with(['creator', 'message', 'campaign', 'senderNumber'])
            ->orderByDesc('id')
            ->paginate($perPage);

        return [
            'business' => $business,
            'balance_model' => $advBalanceModel,
            'balance' => $balance,
            'total_charged' => $totalCharged,
            'total_consumed' => $totalConsumed,
            'message_count' => $messageCount,
            'history' => $history,
            'transactions' => $transactions,
        ];
    }

    /**
     * Top-up OTP balance for a business.
     * Strictly credits ONLY that business's otp_balance.
     *
     * @throws DomainException
     */
    public function topUpOtp(
        GhorfeOnlineList $business,
        float $amount,
        ?User $actor = null,
        ?string $trackingId = null
    ): SmsLog {
        if ($amount <= 0) {
            throw new DomainException('مبلغ شارژ باید بیشتر از صفر باشد.');
        }

        return DB::transaction(function () use ($business, $amount, $actor, $trackingId) {
            // Row-locked atomic credit: concurrent top-ups and debits serialize here.
            $balanceBefore = $business->lockAndCredit($amount, 'otp');
            $balanceAfter = $balanceBefore + $amount;

            return SmsLog::create([
                'ghorfe_online_list_id' => $business->id,
                'credit_type' => 'otp',
                'type' => 'transaction',
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
     * Top-up the advertising balance of a business.
     * Strictly credits ONLY that business's advertising_balance. Never cross-credits.
     *
     * @throws DomainException
     */
    public function topUpAdvertising(
        GhorfeOnlineList $business,
        float $amount,
        ?User $actor = null,
        ?string $trackingId = null
    ): SmsLog {
        if ($amount <= 0) {
            throw new DomainException('مبلغ شارژ باید بیشتر از صفر باشد.');
        }

        return DB::transaction(function () use ($business, $amount, $actor, $trackingId) {
            // Row-locked atomic credit: concurrent top-ups and debits serialize here.
            $balanceBefore = $business->lockAndCredit($amount, 'advertising');
            $balanceAfter = $balanceBefore + $amount;

            return SmsLog::create([
                'ghorfe_online_list_id' => $business->id,
                'credit_type' => 'advertising',
                'type' => 'transaction',
                'sender_number_id' => null,
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
