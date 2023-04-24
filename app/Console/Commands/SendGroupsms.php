<?php

namespace App\Console\Commands;

use App\Models\Factor;
use App\Models\Groupsms;
use App\Models\Phone;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class SendGroupsms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:sendgroup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $carbon = Carbon::now();
        $hour = $carbon->format('H');
        $date = $carbon->toDateString();

     /*   if ($hour < 12){
            //morning
            $sms = Groupsms::whereStatus(1)->whereFlag('0')->where('send_at' , $date)->first();
        }else{
            //afternon
            $sms = Groupsms::whereStatus(1)->whereFlag('1')->where('send_at' , $date)->first();
        }*/
        $sms = Groupsms::whereStatus(1)->where('send_at' , $date)->where('type' , '!=' , 'single')->first();

        if ($sms){
            if ($sms->type == 'groupService'){
                if ($sms->flag == 1){
                    if ($sms->services->count() > 0){
                        $phones = [];
                        foreach ($sms->services as $service){
                            $factors = Factor::where('factorable_type' , $service->relation_slug)->whereStatus(1)->get();
                            foreach ($factors as $factor) {
                                array_push($phones , $factor->user->phone);
                            }
                        }

                        $phones = implode("," , array_unique($phones));
                    }else{
                        //all
                        /*$user = User::where('level', 'user')->pluck('phone');
                        $phones = implode("," , $user->toArray());
                        $phones = array_unique($phones);*/
                        $phones = [];
                        foreach ($sms->services as $service){
                            $factors = Factor::whereStatus(1)->get();
                            foreach ($factors as $factor) {
                                array_push($phones , $factor->user->phone);
                            }
                        }
                        $phones = implode("," , array_unique($phones));
                    }
                }else{
                    if ($sms->services->count() > 0){
                        $phones = [];
                        foreach ($sms->services as $service){
                            $factors = Factor::where('factorable_type' , $service->relation_slug)->whereStatus(0)->get();
                            foreach ($factors as $factor) {
                                array_push($phones , $factor->user->phone);
                            }
                        }
                        $phones = implode("," , array_unique($phones));
                    }else{
                        //all
                        /*$user = User::where('level', 'user')->pluck('phone');
                        $phones = implode("," , $user->toArray());
                        $phones = array_unique($phones);*/
                        $phones = [];
                        foreach ($sms->services as $service){
                            $factors = Factor::whereStatus(0)->get();
                            foreach ($factors as $factor) {
                                array_push($phones , $factor->user->phone);
                            }
                        }
                        $phones = implode("," , array_unique($phones));
                    }
                }
            }else{
                if ($sms->phone_list_id != null){
                    $phones = Phone::where('phone_list_id' , $sms->phone_list_id)->distinct('phone')->pluck('phone')->toArray();
                    $phones = implode("," , $phones);
                }else{
                    $phones = Phone::where('phone_list_id' , null)->distinct('phone')->pluck('phone')->toArray();
                    $phones = implode("," , $phones);
                }

            }
            //send sms
            $headers = [
                'apikey' => 'cpCIuUbmtBhYFw41+kpYs0mSNw+mkN4aZBELwgUMN+A',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ];
            $client = new Client([
                'headers' => $headers
            ]);
            $body = [
                'sender' => '10002622',
                'receptor' => $phones,
            ];
            $r = $client->request('POST', 'http://api.parsasms.com/v2/sms/send/bulk2', [
                'form_params' => $body
            ]);
            $sms->update(['status' => 2]);
            dd('success');

        }else{
            dd('notfound ready to send groupsms!');
        }
    }
}
