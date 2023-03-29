<?php
namespace App\lib;
use DB;
/*require_once 'nusoap.php';
*/
use nusoap_client;

class zarinpal {
    public function pay( $amount ) {
        $client = new nusoap_client( 'https://www.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl' );
        $client->soap_defencoding = 'UTF-8';
        $result = $client->call( 'PaymentRequest', [
            [
                'MerchantID'     => config( 'services.zarinpal.id' ),
                'Amount'         => $amount,
                'Currency'       => "IRR",
                'Description'    => "هزینه ثبت نام",
                'CallbackURL'    => route( 'pay.callback' ),
            ],
        ] );

        //Redirect to URL You can do it also by creating a form
        if ( $result[ 'Status' ] == 100 ) {
            return $result[ 'Authority' ];
        } else {
            return false;
        }

    }

}
