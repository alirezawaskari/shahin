<?php

namespace App\Jobs;

use App\Models\SmsLinkFactorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;

class installmentLinkSmsJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
    * Create a new job instance.
    *
    * @return void
    */
    private $detail;

    public function __construct( $detail ) {
        $this->detail = $detail;
    }

    /**
    * Execute the job.
    *
    * @return void
    */

    public function handle() {
        $already_send_sms = self::checkAlreadySendSmsLog( $this->detail[ 'receptor' ], 'installmentSms' );
        if (!$already_send_sms) {
            $log = new SmsLinkFactorLog();
            $log->phone = $this->detail[ 'receptor' ];
            $log->type = 1;
            $log->title = 'installmentSms';
            $log->save();

            $headers = [
                'apikey' => config( 'services.sms.apikey' ),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client( [
                'headers' => $headers
            ] );

            $body = [
                'receptor' => $this->detail[ 'receptor' ],
                'type' => 1,
                'template' => $this->detail[ 'template' ],
                'param1' => $this->detail[ 'param1' ],
                'param2' => $this->detail[ 'param2' ],
                'param3' => config( 'app.url' ).'/pf/' . $this->detail[ 'resnumber' ],
            ];

            $client->request( 'POST', 'http://api.parsasms.com/v2/send/verify', [
                'form_params' => $body
            ] );
        }
    }

    private function checkAlreadySendSmsLog($phone , $title)
    {
        return SmsLinkFactorLog::where('phone' , $phone)->where('title' , $title)->whereDate('created_at', '=', date('Y-m-d'))->exists();
    }
}
