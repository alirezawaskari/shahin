<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\Groupsms;
use App\Models\Setting;
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
     * @return Groupsms|null the outbound reply, or null when nothing was sent
     */
    public function handleInbound(Groupsms $inbound, array $payload = []): ?Groupsms
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

    protected function process(Groupsms $inbound, array $payload): ?Groupsms
    {
        if (!$inbound->ghorfe_online_list_id) {
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
    public function findRule(int $businessId, string $normalizedContent): ?Setting
    {
        $rules = Setting::where('key', 'sms_auto_responder')
            ->where('settingable_id', $businessId)
            ->where('status', 'active')
            ->get();

        $exact = $rules->first(fn ($r) => $r->match_type === Setting::MATCH_EXACT && $r->normalized_keyword === $normalizedContent);
        if ($exact) {
            return $exact;
        }

        return $rules
            ->filter(fn ($r) => $r->match_type === Setting::MATCH_STARTS_WITH
                && $r->normalized_keyword !== ''
                && str_starts_with($normalizedContent, $r->normalized_keyword))
            ->sortByDesc(fn ($r) => mb_strlen($r->normalized_keyword))
            ->first();
    }

    protected function recentlyAnswered(Setting $rule, string $sender): bool
    {
        $minutes = max(0, (int) config('services.sms.auto_reply_window_minutes', 10));
        if ($minutes === 0) {
            return false;
        }

        return Groupsms::query()
            ->where('auto_responder_id', $rule->id)
            ->where('recipient_phone', $sender)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }

    protected function isOwnLine(string $phone): bool
    {
        return $phone !== '' && Setting::where('key', 'sms_sender_number')->where('number', $phone)->exists();
    }

    protected function isOtpLine(string $phone): bool
    {
        return $phone !== '' && Setting::otp()->where('number', $phone)->exists();
    }

    /**
     * Rule's own line, else the line the customer texted (when it is one of the business' advertising lines),
     * else null (the send service picks the business' first advertising line).
     */
    protected function resolveSenderNumberId(Setting $rule, GhorfeOnlineList $business, string $recipientLine): ?int
    {
        if ($rule->sender_number_id) {
            return (int) $rule->sender_number_id;
        }

        if ($recipientLine === '') {
            return null;
        }

        $line = $business->senderNumbers()
            ->where('type', 'advertising')
            ->where('status', 'active')
            ->where('number', $recipientLine)
            ->first();

        return $line?->id;
    }
}
