<?php
namespace App\Lib;
use DB;
/*require_once 'nusoap.php';
*/

use Illuminate\Support\Facades\Http;
use nusoap_client;

class saman
{
    public function token(int $amount)
    {
        $resNum = session('resnumber') ?: uniqid();
        session(['resnumber' => $resNum]);

        $payload = [
            'action'      => 'token',
            'TerminalId'  => '14541809',
            'Amount'      => $amount,
            'ResNum'      => $resNum,
            'RedirectUrl' => route('pay.callback'),
            'CellNumber'  => auth()->user()->phone,
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
