<?php

namespace App\Services\Sms;

use App\Models\GhorfeOnlineList;
use App\Models\SenderNumber;
use App\Models\SmsMessage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SmsWebhookService
{
    /**
     * Process delivery report callback.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handleDeliveryReport(array $payload): array
    {
        $trackingId = $this->extractTrackingId($payload);

        if (!$trackingId) {
            return [
                'success' => false,
                'error' => 'شناسه رهگیری (tracking_id) در درخواست یافت نشد.',
                'code' => 422,
            ];
        }

        $message = SmsMessage::where('tracking_id', $trackingId)->first();

        // If not found by tracking_id, check if numeric and matches ID
        if (!$message && is_numeric($trackingId)) {
            $message = SmsMessage::find((int) $trackingId);
        }

        if (!$message) {
            Log::warning('SMS delivery callback message not found', ['tracking_id' => $trackingId, 'payload' => $payload]);
            return [
                'success' => false,
                'error' => "پیامک با شناسه {$trackingId} یافت نشد.",
                'code' => 404,
            ];
        }

        $rawStatus = $this->extractStatus($payload);
        $normalized = $this->normalizeDeliveryStatus($rawStatus);
        $deliveredAt = $this->extractTimestamp($payload) ?? now();

        $updateData = [
            'delivery_status' => $normalized['delivery_status'],
        ];

        if ($normalized['is_delivered']) {
            $updateData['status'] = 'delivered';
            $updateData['delivered_at'] = $message->delivered_at ?: $deliveredAt;
        } elseif ($normalized['is_failed']) {
            $updateData['status'] = 'failed';
            if (!empty($payload['error_reason']) || !empty($payload['reason']) || !empty($payload['message'])) {
                $updateData['error_reason'] = $payload['error_reason'] ?? $payload['reason'] ?? $payload['message'];
            }
        } elseif (!empty($normalized['status'])) {
            $updateData['status'] = $normalized['status'];
        }

        $message->update($updateData);

        return [
            'success' => true,
            'message' => 'وضعیت تحویل پیامک با موفقیت بروزرسانی شد.',
            'tracking_id' => $message->tracking_id,
            'sms_message_id' => $message->id,
            'delivery_status' => $message->delivery_status,
            'status' => $message->status,
        ];
    }

    /**
     * Process inbound incoming SMS from gateway.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handleInboundMessage(array $payload): array
    {
        $senderPhone = $this->extractSenderPhone($payload);
        $recipientPhone = $this->extractRecipientPhone($payload);
        $content = $this->extractContent($payload);
        $trackingId = $this->extractTrackingId($payload) ?: ('INB-' . strtoupper(Str::random(12)));
        $requestId = !empty($payload['request_id']) ? (int) $payload['request_id'] : null;

        if (empty($senderPhone) || empty($content)) {
            return [
                'success' => false,
                'error' => 'شماره فرستنده و متن پیام برای ثبت پیامک دریافتی الزامی است.',
                'code' => 422,
            ];
        }

        // Resolve user by sender phone
        $user = User::where('phone', $senderPhone)->first();

        // Resolve business by recipient number or sender number relation
        $businessId = null;
        if (!empty($recipientPhone)) {
            $senderNumber = SenderNumber::where('number', $recipientPhone)->first();
            if ($senderNumber) {
                $business = $senderNumber->businesses()->first();
                $businessId = $business?->id;
            }
        }

        if (!$businessId && !empty($payload['ghorfe_online_list_id'])) {
            $businessId = (int) $payload['ghorfe_online_list_id'];
        }

        $receivedAt = $this->extractTimestamp($payload) ?? now();

        // Same counter as outbound; always 'normal' so no opt-out suffix is appended to inbound text.
        $segmentation = (new SingleSmsSendService(new DynamicParameterResolver()))->calculateSegmentation($content, 'normal');

        $smsMessage = SmsMessage::create([
            'ghorfe_online_list_id' => $businessId,
            'direction' => 'inbound',
            'service_type' => $payload['service_type'] ?? 'normal',
            'sender_phone' => $senderPhone,
            'recipient_phone' => $recipientPhone,
            'content' => $content,
            'character_count' => $segmentation['character_count'],
            'page_count' => $segmentation['page_count'],
            'cost' => 0,
            'status' => 'delivered',
            'delivery_status' => 'delivered',
            'delivered_at' => $receivedAt,
            'tracking_id' => $trackingId,
            'user_id' => $user?->id,
            'request_id' => $requestId,
            'created_at' => $receivedAt,
        ]);

        // Auto-responder: never allowed to break webhook processing (the engine swallows and logs its own errors).
        $autoReply = app(SmsAutoResponderEngine::class)->handleInbound($smsMessage, $payload);

        return [
            'success' => true,
            'message' => 'پیامک دریافتی با موفقیت ذخیره شد.',
            'sms_message_id' => $smsMessage->id,
            'tracking_id' => $smsMessage->tracking_id,
            'auto_reply_sms_message_id' => $autoReply?->id,
        ];
    }

    /**
     * Universal webhook handler - automatically routes to delivery or inbound.
     *
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(array $payload): array
    {
        // 1. Explicit action or direction in payload
        if (isset($payload['action']) && $payload['action'] === 'inbound') {
            return $this->handleInboundMessage($payload);
        }
        if (isset($payload['direction']) && $payload['direction'] === 'inbound') {
            return $this->handleInboundMessage($payload);
        }

        // 2. If tracking ID exists and matches an existing message -> delivery report
        $trackingId = $this->extractTrackingId($payload);
        if ($trackingId && SmsMessage::where('tracking_id', $trackingId)->exists()) {
            return $this->handleDeliveryReport($payload);
        }

        // 3. If payload has inbound markers (message/body/content + sender/from) and no existing tracking_id
        if (!empty($this->extractContent($payload)) && !empty($this->extractSenderPhone($payload))) {
            return $this->handleInboundMessage($payload);
        }

        // 4. Fallback to delivery report
        return $this->handleDeliveryReport($payload);
    }

    /**
     * Extract tracking / message ID flexibly across gateway formats.
     */
    protected function extractTrackingId(array $payload): ?string
    {
        $id = $payload['tracking_id']
            ?? $payload['trackingId']
            ?? $payload['mid']
            ?? $payload['MessageID']
            ?? $payload['message_id']
            ?? $payload['messageId']
            ?? $payload['msg_id']
            ?? $payload['id']
            ?? $payload['bulk_id']
            ?? null;

        return $id ? (string) $id : null;
    }

    /**
     * Extract status string flexibly.
     */
    protected function extractStatus(array $payload): string
    {
        return (string) ($payload['delivery_status']
            ?? $payload['status']
            ?? $payload['delivery_code']
            ?? $payload['state']
            ?? $payload['result']
            ?? 'delivered');
    }

    /**
     * Extract sender phone.
     */
    protected function extractSenderPhone(array $payload): ?string
    {
        return $payload['sender_phone']
            ?? $payload['sender']
            ?? $payload['from']
            ?? $payload['SenderNumber']
            ?? $payload['mobile']
            ?? $payload['cellphone']
            ?? null;
    }

    /**
     * Extract recipient phone.
     */
    protected function extractRecipientPhone(array $payload): ?string
    {
        return $payload['recipient_phone']
            ?? $payload['recipient']
            ?? $payload['to']
            ?? $payload['RecipientNumber']
            ?? $payload['line']
            ?? $payload['linenumber']
            ?? null;
    }

    /**
     * Extract message text content.
     */
    protected function extractContent(array $payload): ?string
    {
        return $payload['content']
            ?? $payload['message']
            ?? $payload['text']
            ?? $payload['body']
            ?? $payload['Body']
            ?? null;
    }

    /**
     * Extract timestamp if provided.
     */
    protected function extractTimestamp(array $payload): ?Carbon
    {
        $ts = $payload['delivered_at']
            ?? $payload['date']
            ?? $payload['timestamp']
            ?? $payload['ReceiveDate']
            ?? null;

        if (!$ts) {
            return null;
        }

        try {
            if (is_numeric($ts)) {
                return Carbon::createFromTimestamp((int) $ts);
            }
            return Carbon::parse($ts);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Normalize gateway status codes to standard delivery_status and status.
     *
     * @param string $raw
     * @return array{delivery_status: string, status: string, is_delivered: bool, is_failed: bool}
     */
    public function normalizeDeliveryStatus(string $raw): array
    {
        $normalized = strtolower(trim($raw));

        // Delivery indicators across ParsaSms / Ghasedak / Negin / Telecom
        $deliveredValues = ['1', 'delivered', 'success', 'ok', 'delivered_to_phone', 'true'];
        $failedValues = ['2', 'failed', 'undelivered', 'reject', 'rejected', 'black_list', 'error', 'false', 'expired'];

        if (in_array($normalized, $deliveredValues, true)) {
            return [
                'delivery_status' => 'delivered',
                'status' => 'delivered',
                'is_delivered' => true,
                'is_failed' => false,
            ];
        }

        if (in_array($normalized, $failedValues, true)) {
            return [
                'delivery_status' => 'failed',
                'status' => 'failed',
                'is_delivered' => false,
                'is_failed' => true,
            ];
        }

        return [
            'delivery_status' => $raw,
            'status' => $normalized,
            'is_delivered' => false,
            'is_failed' => false,
        ];
    }
}
