<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsAutoResponder;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Answers a stored inbound SMS with the business' matching auto-responder rule.
 *
 * The reply goes through SingleSmsSendService::send() so the balance guard, billing (SmsTransaction),
 * gateway dispatch and the audit row all apply exactly as for a hand-written single SMS.
 *
 * Service type: a reply to a message the customer started is a transactional «عادی» message, never
 * OTP and not unsolicited advertising (so no «لغو 11» is appended). It still debits the business'
 * shared advertising credit, because non-OTP sends always use that balance.
 *
 * Safety: never replies to our own lines, to inbound OTP traffic, to reserved/opt-out words, and at most
 * once per sender per rule per window (services.sms.auto_reply_window_minutes). It never throws.
 */
class SmsAutoResponderEngine
{
    /**
     * @param array<string, mixed> $payload raw webhook payload (only service_type is consulted)
     * @return SmsMessage|null the outbound reply, or null when nothing was sent
     */
    public function handleInbound(SmsMessage $inbound, array $payload = []): ?SmsMessage
    {
        try {
            return $this->process($inbound, $payload);
        } catch (\Throwable $e) {
            Log::error('SMS auto-responder failed: ' . $e->getMessage(), [
                'inbound_sms_message_id' => $inbound->id,
                'business_id' => $inbound->ghorfe_online_list_id,
            ]);

            return null;
        }
    }

    protected function process(SmsMessage $inbound, array $payload): ?SmsMessage
    {
        if (!$inbound->ghorfe_online_list_id || !Schema::hasTable('sms_auto_responders')) {
            return null;
        }

        // Inbound OTP-service traffic is never answered.
        if (($payload['service_type'] ?? $inbound->service_type) === 'otp') {
            return null;
        }

        $sender = (string) $inbound->sender_phone;
        $content = (string) $inbound->content;
        if ($sender === '' || trim($content) === '') {
            return null;
        }

        // Loop protection: our own outbound lines (SMS lines of any type, business line numbers).
        if ($this->isOwnLine($sender) || $this->isOtpLine((string) $inbound->recipient_phone)) {
            return null;
        }

        // Reserved / opt-out / industry words belong to the system, not to auto-responders.
        if (SmsReservedCodes::isProtectedInbound($content)) {
            return null;
        }

        $rule = $this->findRule((int) $inbound->ghorfe_online_list_id, SmsKeywordNormalizer::normalize($content));
        if (!$rule) {
            return null;
        }

        if ($this->recentlyAnswered($rule, $sender)) {
            return null;
        }

        $business = GhorfeOnlineList::find($inbound->ghorfe_online_list_id);
        if (!$business) {
            return null;
        }

        $reply = app(SingleSmsSendService::class)->send([
            'recipient_phone' => $sender,
            'message' => $rule->reply_text,
            'service_type' => 'normal',
            'sender_number_id' => $this->resolveSenderNumberId($rule, $business, (string) $inbound->recipient_phone),
        ], $business, null);

        $reply->forceFill(['auto_responder_id' => $rule->id])->save();

        return $reply;
    }

    /**
     * Exact match first, then the longest starts_with prefix.
     */
    public function findRule(int $businessId, string $normalizedContent): ?SmsAutoResponder
    {
        $rules = SmsAutoResponder::query()
            ->where('ghorfe_online_list_id', $businessId)
            ->where('is_active', true)
            ->get();

        $exact = $rules->first(fn ($r) => $r->match_type === SmsAutoResponder::MATCH_EXACT && $r->normalized_keyword === $normalizedContent);
        if ($exact) {
            return $exact;
        }

        return $rules
            ->filter(fn ($r) => $r->match_type === SmsAutoResponder::MATCH_STARTS_WITH
                && $r->normalized_keyword !== ''
                && str_starts_with($normalizedContent, $r->normalized_keyword))
            ->sortByDesc(fn ($r) => mb_strlen($r->normalized_keyword))
            ->first();
    }

    protected function recentlyAnswered(SmsAutoResponder $rule, string $sender): bool
    {
        $minutes = max(0, (int) config('services.sms.auto_reply_window_minutes', 10));
        if ($minutes === 0) {
            return false;
        }

        return SmsMessage::query()
            ->where('auto_responder_id', $rule->id)
            ->where('recipient_phone', $sender)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }

    protected function isOwnLine(string $phone): bool
    {
        // Own lines live in sender_numbers only (ghorfe_online_lists has no line-number column).
        return $phone !== '' && SenderNumber::where('number', $phone)->exists();
    }

    protected function isOtpLine(string $phone): bool
    {
        return $phone !== '' && SenderNumber::where('number', $phone)->where('type', 'otp')->exists();
    }

    /**
     * Rule's own line, else the line the customer texted (when it is one of the business' advertising lines),
     * else null (the send service picks the business' first advertising line).
     */
    protected function resolveSenderNumberId(SmsAutoResponder $rule, GhorfeOnlineList $business, string $recipientLine): ?int
    {
        if ($rule->sender_number_id) {
            return (int) $rule->sender_number_id;
        }

        if ($recipientLine === '') {
            return null;
        }

        $line = $business->senderNumbers()
            ->where('sender_numbers.type', 'advertising')
            ->where('sender_numbers.status', 'active')
            ->where('sender_numbers.number', $recipientLine)
            ->first();

        return $line?->id;
    }
}
