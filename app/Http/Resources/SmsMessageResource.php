<?php

namespace App\Http\Resources;

use App\Services\Sms\SmsConfidentialityGuard;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * JSON representation of an SmsMessage that enforces confidentiality at the
 * response-shaping layer: for a message in a confidential group (مالی/حقوقی)
 * whose viewer lacks the "view-confidential-sms" permission, the `content`
 * field is OMITTED from the payload entirely (not masked/blanked client
 * side), so the text never reaches the browser.
 */
class SmsMessageResource extends JsonResource
{
    public function toArray($request): array
    {
        $viewer = $request->user();
        $isConfidential = $this->resource->isConfidential();
        $canView = SmsConfidentialityGuard::canViewConfidential($viewer);
        $contentVisible = !$isConfidential || $canView;

        return [
            'id' => $this->id,
            'direction' => $this->direction,
            'service_type' => $this->service_type,
            'is_bulk' => (bool) $this->is_bulk,
            'sender_phone' => $this->sender_phone,
            'recipient_phone' => $this->recipient_phone,
            'content' => $contentVisible ? $this->content : null,
            'content_redacted' => !$contentVisible,
            'is_confidential' => $isConfidential,
            'character_count' => $this->character_count,
            'page_count' => $this->page_count,
            'status' => $this->status,
            'cost' => $this->cost,
            'tracking_id' => $this->tracking_id,
            'error_reason' => $this->error_reason,
            'sms_message_group_id' => $this->sms_message_group_id,
            'group_name' => $this->whenLoaded('group', fn () => $this->group?->name),
            'sender_number' => $this->whenLoaded('senderNumber', fn () => $this->senderNumber?->number),
            'business' => $this->whenLoaded('business', fn () => $this->business?->title),
            'user' => $this->whenLoaded('user', fn () => $this->user ? trim($this->user->name . ' ' . $this->user->family) : null),
            'created_at' => $this->created_at,
        ];
    }
}
