<?php

namespace App\Listeners;

use App\Classes\External\SendSms;
use App\Events\referralCenterNotification;
use App\Models\User;

class ReferralCenterSMSNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param factorItemActivation $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(referralCenterNotification $event)
    {
        $center = $event->center;
        $center_manager = User::where('level' , 'admin')->where('center_id' , $center->id)->whereHas('roles',function ($query){
            $query->where('name','superadmin_center');
        })->first();
        if($center_manager)
        {
            $userMobile = $center_manager->phone;

            if (isset($userMobile)) {
                $name = $center_manager->name .' '. $center_manager->family;

                $body = [
                    'param1' => $name,
                ];
                SendSms::sendSingleSms('referralCenterNotification',$userMobile,$body);
            }
        }
    }
}
