<?php

namespace App\Lib;

use App\Models\Factor;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class saman
{
    public function token(Factor $factor , int $amount)
    {
        $resNum = session('resnumber') ?: uniqid();
        session(['resnumber' => $resNum]);
        $shaba = Setting::first()->shaba_number;
        $IBANInfo = [
            [
            "IBAN" => $shaba,
            "Amount" => $amount,
            ]
        ];
        $payload = [
            'action'      => 'token',
            'TerminalId'  => '14541809',
            'Amount'      => $amount,
            'ResNum'      => $resNum,
            'RedirectUrl' => 'https://lalecard.ir/callback',
            'CellNumber'  => $factor->user->phone,
            'SettlementIBANInfo'  => $IBANInfo,
        ];
        $response = Http::asForm()->post('https://sep.shaparak.ir/onlinepg/onlinepg', $payload);

        if (! $response->ok()) {
            return [
                'errorCode' => $response->status(),
                'errorDesc' => 'ارتباط با سرویس سامان برقرار نشد.',
            ];
        }

        $body = $response->json();

        if (isset($body['status']) && $body['status'] == 1 && isset($body['token'])) {
            return $body['token'];
        }

        return [
            'errorCode' => $body['errorCode'] ?? 'unknown',
            'errorDesc' => $body['errorDesc'] ?? 'خطای ناشناخته در درگاه سامان.',
        ];
    }
}
