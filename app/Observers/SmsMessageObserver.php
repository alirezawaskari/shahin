<?php

namespace App\Observers;

use App\Models\History;
use App\Models\Request;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SmsMessageObserver
{
    /**
     * Handle the SmsMessage "created" event.
     */
    public function created(SmsMessage $message): void
    {
        $this->recordRequestHistory($message);
    }

    /**
     * Handle the SmsMessage "updated" event.
     */
    public function updated(SmsMessage $message): void
    {
        if ($message->wasChanged('request_id') && $message->request_id) {
            $this->recordRequestHistory($message);
        }
    }

    /**
     * Record a History row for a Request when an SMS is tied to that request.
     */
    public function recordRequestHistory(SmsMessage $message): void
    {
        if (!$message->request_id) {
            return;
        }

        try {
            $request = Request::find($message->request_id);
            if (!$request) {
                return;
            }

            $userId = Auth::id() ?: $message->created_by ?: $request->user_id ?: 1;
            $callcenterId = $userId;
            $directionText = $message->direction === 'inbound' ? 'دریافت' : 'ارسال';
            $phone = $message->direction === 'inbound' ? $message->sender_phone : $message->recipient_phone;
            $desc = "{$directionText} پیامک (#{$message->id}) - شماره: {$phone} - متن: " . mb_substr($message->content, 0, 100);

            History::query()->create([
                'historiable_type' => Request::class,
                'historiable_id' => $request->id,
                'user_id' => $userId,
                'callcenter_id' => $callcenterId,
                'status' => 0,
                'description' => $desc,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to record SMS request history: ' . $e->getMessage(), [
                'message_id' => $message->id,
                'request_id' => $message->request_id,
            ]);
        }
    }
}
