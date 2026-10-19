<?php

namespace App\Services\Sms;

use App\Models\SmsAuditLog;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * One place that records who sent an outgoing SMS. Called from every send path (single, bulk, OTP).
 * Never throws: a failed audit write is logged loudly but must not break the send.
 */
class SmsAuditService
{
    public const CHANNEL_SINGLE = 'single';
    public const CHANNEL_BULK = 'bulk';
    public const CHANNEL_OTP = 'otp';

    /**
     * The role label stored on the message and audit row: superadmin, or the user's level ('system' with no user).
     */
    public function resolveSenderRole(?User $sender): string
    {
        if (!$sender) {
            return 'system';
        }

        return $sender->isSuperAdmin() ? 'superadmin' : ((string) ($sender->level ?: 'user'));
    }

    /**
     * Write one audit row. $context keys: sms_message (SmsMessage), sms_campaign_id, business_id,
     * request_id, callcenter_id, service_type.
     *
     * @param array<string, mixed> $context
     */
    public function record(?User $sender, string $channel, array $context = []): ?SmsAuditLog
    {
        try {
            /** @var SmsMessage|null $message */
            $message = $context['sms_message'] ?? null;

            return SmsAuditLog::create([
                'sms_message_id' => $message?->id,
                'sms_campaign_id' => $context['sms_campaign_id'] ?? $message?->sms_campaign_id,
                'ghorfe_online_list_id' => $context['business_id'] ?? $message?->ghorfe_online_list_id,
                'sender_id' => $sender?->id,
                'sender_role' => $this->resolveSenderRole($sender),
                'access_tier' => $sender?->access_tier,
                'request_id' => $context['request_id'] ?? $message?->request_id,
                'callcenter_id' => $context['callcenter_id'] ?? $message?->callcenter_id,
                'channel' => $channel,
                'service_type' => $context['service_type'] ?? $message?->service_type,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('SMS audit write failed: ' . $e->getMessage(), [
                'channel' => $channel,
                'sender_id' => $sender?->id,
                'sms_message_id' => isset($message) ? $message?->id : null,
            ]);

            return null;
        }
    }
}
