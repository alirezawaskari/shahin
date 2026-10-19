<?php
namespace App\lib;
use DB;
/*require_once 'nusoap.php';
*/
use nusoap_client;

class zarinpal {
    public function pay( $amount, $description = "هزینه ثبت نام" ) {
        $client = new nusoap_client( config( 'services.zarinpal.url' ).'/pg/services/WebGate/wsdl', 'wsdl' );
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call( 'PaymentRequest', [
            [
                'MerchantID'     => config( 'services.zarinpal.id' ),
                'Amount'         => $amount,
                'Currency'       => "IRR",
                'Description'    => $description,
                'CallbackURL'    => route( 'pay.callback' ),
            ],
        ] );
        //Redirect to URL You can do it also by creating a form
        if ( isset($result[ 'Status' ]) && $result[ 'Status' ] == 100 ) {
            return $result[ 'Authority' ];
        } else {
            return false;
        }

    }

}
