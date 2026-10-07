<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Sms\SmsWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsWebhookController extends Controller
{
    public function __construct(
        protected SmsWebhookService $webhookService
    ) {
    }

    /**
     * Handle universal incoming SMS gateway webhook (delivery callback or inbound message).
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $result = $this->webhookService->handle($payload);

        $status = ($result['success'] ?? false) ? 200 : ($result['code'] ?? 400);

        return response()->json($result, $status);
    }

    /**
     * Handle delivery status callback specifically.
     */
    public function delivery(Request $request): JsonResponse
    {
        $payload = $request->all();
        $result = $this->webhookService->handleDeliveryReport($payload);

        $status = ($result['success'] ?? false) ? 200 : ($result['code'] ?? 400);

        return response()->json($result, $status);
    }

    /**
     * Handle inbound received SMS callback specifically.
     */
    public function inbound(Request $request): JsonResponse
    {
        $payload = $request->all();
        $result = $this->webhookService->handleInboundMessage($payload);

        $status = ($result['success'] ?? false) ? 200 : ($result['code'] ?? 400);

        return response()->json($result, $status);
    }
}
