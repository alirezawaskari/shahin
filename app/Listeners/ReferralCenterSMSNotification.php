<?php

namespace App\Listeners;

use App\Classes\External\SendSms;
use App\Events\referralCenterNotification;
use App\Models\Employee;
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
     * @param referralCenterNotification $event
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(referralCenterNotification $event)
    {
        $center = $event->center;
        $center_creator_representation = $center->creator_representation_id;
        $center_employees = Employee::where('status' , true)->where('center_id' , $center->id)
        ->orWhere('center_id' , null)->whereHas('user',function ($query) use ($center_creator_representation){
              $query->where('admin_representation_id' , $center_creator_representation)->where('admin_representation_id' ,'!=', null);
        })->where('status' , true)->get();
        if($center_employees)
        {
            foreach($center_employees as $center_employee)
            {
                $user = $center_employee->user;
                if($user->hasPermission('received_referral_notification'))
                {
                    $userMobile = $user->phone;
                    if (isset($userMobile)) {
                        $name = $user->name .' '. $user->family;
                        $body = [
                            'param1' => $name,
                        ];
                        SendSms::sendSingleSms('referralCenterNotification',$userMobile,$body);
                    }
                }
            }
        }
    }
}
