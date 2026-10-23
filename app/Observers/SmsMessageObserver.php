<?php

namespace App\Observers;

use App\Models\Groupsms;
use App\Models\History;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SmsMessageObserver
{
    /**
     * Handle the Groupsms "created" event.
     */
    public function created(Groupsms $message): void
    {
        $this->recordRequestHistory($message);
    }

    /**
     * Handle the Groupsms "updated" event.
     */
    public function updated(Groupsms $message): void
    {
        if ($message->wasChanged('request_id') && $message->request_id) {
            $this->recordRequestHistory($message);
        }
    }

    /**
     * Record a History row for a Request when an SMS is tied to that request.
     */
    public function recordRequestHistory(Groupsms $message): void
    {
        if (!$message->request_id) {
            return;
        }

        // A bulk campaign's recipient rows carry request_id = the campaign's Groupsms id, not a customer
        // Request id. They are told apart by their own row type. The old check looked the request_id up
        // in the groupsms id sequence, and a request whose id matched any groupsms id lost its history line.
        if ($message->is_bulk || $message->type === 'bulk_recipient') {
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
