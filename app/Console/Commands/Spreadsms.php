<?php

namespace App\Console\Commands;

use App\Models\Callcenter;
use App\Models\Representation;
use App\Models\sms;
use App\Models\User;
use Illuminate\Console\Command;

class Spreadsms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:spread';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'spread sms to callcenter';

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
     */
    public function handle()
    {
        //allocate of received SMS messages that have not been allocated before to users that there is their sms_code in the sms text
		$user_marketers = User::whereNotNull('sms_code')->get();
		if (count($user_marketers) > 0) {
            foreach ($user_marketers as $user_marketer) {
                $sms = sms::whereStatus(false)->whereIn('body', $user_marketer->sms_code)->get();
                $users = User::where('id', $user_marketer->id)->get();
                $callcenterusers = [];
                foreach ($users as $user) {
                        array_push($callcenterusers, $user);
                }
                self::spread($sms, count($callcenterusers) > 0 ? $callcenterusers : $users);
            }
        }
        //allocate of received SMS messages that have not been allocated before to representations that there is their sms_code in the sms text
		$representations = Representation::whereNotNull('sms_code')->get();
        if (count($representations) > 0) {
            foreach ($representations as $representation) {
                $sms = sms::whereStatus(false)->whereIn('body', $representation->sms_code)->get();
                $users = User::where('level', 'admin')->where('admin_representation_id', $representation->id)->get();
                $callcenterusers = [];
                foreach ($users as $user) {
                    if ($user->hasRole('callcenter')) {
                        array_push($callcenterusers, $user);
                    }
                }
                self::spread($sms, count($callcenterusers) > 0 ? $callcenterusers : $users);
            }
        }
        //allocate of received SMS messages that have not been allocated before to admin users
        $sms = sms::whereStatus(false)->get();
        $users = User::where('level', 'admin')->where('admin_representation_id', null)->get();
        $callcenterusers = [];
        foreach ($users as $user) {
            if ($user->hasRole('callcenter')) {
                array_push($callcenterusers, $user);
            }
        }
        self::spread($sms, $callcenterusers);

    }
    /**
     * @param mixed $sms
     * @param mixed $callcenters //from users table
     * @return string
     * create record in callcenters table for evry sms recived
     */
    private static function spread($sms = null, $callcenters = null)
    {
        if (count($sms) > 0 && count($callcenters) > 0) {
            if (count($sms) >= count($callcenters)) {
                $size = (integer)round(count($sms) / count($callcenters));
                $items = array_chunk($sms->toArray(), $size, true);

                foreach ($callcenters as $key => $call) {
                    foreach ($items[$key] as $i) {
                        $smsitem = (object)$i;
                        $task = Callcenter::create([
                            'sms_id' => $smsitem->id,
                            'user_id' => $call->id,
                            'type' => 1,
                            'status' => '0',
                            'representation_id' => !is_null($call->admin_representation_id) ? $call->admin_representation_id : null,
                        ]);

                        $user = User::where('phone' , $task->sms->sender_number)->first();
                        $user ? $task->update(['reg_id' => $user->id]) : '';

                        sms::where('id', $smsitem->id)->update([
                            'status' => true
                        ]);
                    }
                }
            } else {
                foreach ($sms as $key => $s) {
                    $number = rand(0, count($callcenters));
                    $user = $callcenters[$number == 0 ? 0 : $number - 1];
                    $task = Callcenter::create([
                        'sms_id' => $s->id,
                        'user_id' => $user->id,
                        'type' => 1,
                        'status' => '0',
                        'representation_id' => !is_null($user->admin_representation_id) ? $user->admin_representation_id : null,
                    ]);

                    $user = User::where('phone' , $task->sms->sender_number)->first();
                    $user ? $task->update(['reg_id' => $user->id]) : '';

                    sms::where('id', $s->id)->update([
                        'status' => true
                    ]);
                }
            }
            return 'command done!';
        } else {
            return 'command done!';
        }
    }
}
