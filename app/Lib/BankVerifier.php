<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;
use nusoap_client;

/**
 * The bank "verify payment" calls used by PaymentController@callback, kept in one
 * small injectable class so they can be faked in tests. Behaviour is exactly the
 * former inline code: raw bank responses are returned unchanged.
 */
class BankVerifier
{
    /** Zarinpal PaymentVerification. $amount is in toman. Returns the raw SOAP result. */
    public function zarinpal(string $authority, $amount)
    {
        $client = new nusoap_client(config('services.zarinpal.url') . '/pg/services/WebGate/wsdl', 'wsdl');
        $client->soap_defencoding = 'UTF-8';

        return $client->call('PaymentVerification', [
            [
                'MerchantID' => config('services.zarinpal.id'),
                'Authority'  => $authority,
                'Amount'     => $amount,
            ],
        ]);
    }

    /** Saman VerifyTranscation. Returns the raw (json-decoded) body. */
    public function saman($refNum)
    {
        $response = Http::post('https://sep.shaparak.ir/verifyTxnRandomSessionkey/ipg/VerifyTranscation', [
            'TerminalNumber' => '14541809',
            'RefNum' => $refNum,
            'IgnoreNationalcode' => true,
            'NationalCode' => '',
        ]);

        return json_decode($response->body(), true);
    }
}
