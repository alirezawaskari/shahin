<?php

namespace App\Listeners;

use App\Classes\External\SendSms;
use App\Events\factorItemClosing;
use Illuminate\Support\Facades\URL;

class FactorItemClosingSMSNotificaion {
    /**
    * Create the event listener.
    *
    * @return void
    */

    public function __construct() {
        //
    }

    /**
    * Handle the event.
    *
    * @param factorItemClosing $event
    * @return void
    * @throws \GuzzleHttp\Exception\GuzzleException
    */

    public function handle( factorItemClosing $event ) {
        $userMobile = $event->factorItem->factor->user->phone;
        $supporter = $event->factorItem->supporter ? ( $event->factorItem->supporter->name.' '. $event->factorItem->supporter->family ) : 'مریم حسینی';

        if ( isset( $userMobile ) ) {
            $body = [
                'param1' => $supporter,
                'param2' => URL::to( '/profile/factorItem' ),
            ];
            SendSms::sendSingleSms( 'factoritemClosing', $userMobile, $body );
        }
    }
}
