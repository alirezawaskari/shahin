<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/**
 * Single decision point for the SMS "simulator" gateway: no external provider is ever called.
 * A send is simulated when config('services.sms.simulate') is true or the sender's panel
 * (trimmed, case-insensitive) is listed in config('services.sms.simulated_panels').
 */
class SmsGatewaySimulator
{
    public static function shouldSimulate(?string $panel): bool
    {
        if ((bool) config('services.sms.simulate', false)) {
            return true;
        }

        $panel = strtolower(trim((string) $panel));
        if ($panel === '') {
            return false;
        }

        $simulated = array_map(
            fn ($p) => strtolower(trim((string) $p)),
            (array) config('services.sms.simulated_panels', ['simulator', 'testsmspanel'])
        );

        return in_array($panel, $simulated, true);
    }

    /**
     * Pretend to send. Returns a synthetic provider message id (callers of the real gateways ignore it).
     */
    public static function dispatch(string $panel, string $phone, string $message): string
    {
        $providerId = 'SIM-' . uniqid();

        Log::info('SMS simulator: message not sent to any provider', [
            'panel' => $panel,
            'phone' => $phone,
            'provider_message_id' => $providerId,
            'length' => mb_strlen($message),
        ]);

        return $providerId;
    }
}
