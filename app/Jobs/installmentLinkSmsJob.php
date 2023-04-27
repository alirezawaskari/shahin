<?php

namespace App\Jobs;

use App\Classes\External\IranSms;
use App\Models\SmsLinkFactorLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class installmentLinkSmsJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
    * Create a new job instance.
    *
    * @return void
    */
    private $detail;

    /**
     * @param $detail
     */
    public function __construct($detail ) {
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


            $body = [
                'param1' => $this->detail[ 'param1' ],
                'param2' => $this->detail[ 'param2' ],
                'param3' => config( 'app.url' ).'/pf/' . $this->detail[ 'resnumber' ],
            ];
            IranSms::sendSingleSms($this->detail[ 'template' ],$this->detail[ 'receptor' ],$body);
        }
    }

    /**
     * @param $phone
     * @param $title
     * @return mixed
     */
    private function checkAlreadySendSmsLog($phone , $title)
    {
        return SmsLinkFactorLog::where('phone' , $phone)->where('title' , $title)->whereDate('created_at', '=', date('Y-m-d'))->exists();
    }
}
