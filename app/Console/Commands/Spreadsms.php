<?php

namespace App\Console\Commands;

use App\Models\Callcenter;
use App\Models\Representation;
use App\Models\Service;
use App\Models\sms;
use App\Models\User;
use App\Events\fastserviceCallcenter;
use App\Helpers\Helper;
use Illuminate\Console\Command;

class Spreadsms extends Command {
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

    public function __construct() {
        parent::__construct();
    }

    /**
    * Execute the console command.
    *
    * @return mixed
    */

    public function handle() {
        //allocate of received SMS messages that have not been allocated before to users that there is their sms_code in the sms text
        $user_marketers = User::filter()->whereNotNull( 'sms_code' )->where( 'level', 'admin' )->get();
        if ( count( $user_marketers ) > 0 ) {
            foreach ( $user_marketers as $user_marketer ) {
                $sms = sms::whereStatus( false )->whereIn( 'body', $user_marketer->sms_code )->get();
                $users = User::where( 'id', $user_marketer->id )->get();
                $callcenterusers = [];
                foreach ( $users as $user ) {
                    array_push( $callcenterusers, $user );
                }
                self::spread( $sms, count( $callcenterusers ) > 0 ? $callcenterusers : $users );
            }
        }
        //allocate of received SMS messages that have not been allocated before to representations that there is their sms_code in the sms text
        $representations = Representation::whereNotNull( 'sms_code' )->where( 'status', true )->get();
        if ( count( $representations ) > 0 ) {
            foreach ( $representations as $representation ) {
                $sms = sms::whereStatus( false )->whereIn( 'body', $representation->sms_code )->get();
                $users = User::filter()->where( 'level', 'admin' )->where( 'admin_representation_id', $representation->id )->get();
                $callcenterusers = [];
                foreach ( $users as $user ) {
                    if ( $user->hasPermission( 'get_new_task' ) ) {
                        array_push( $callcenterusers, $user );
                    }
                }
                self::spread( $sms, count( $callcenterusers ) > 0 ? $callcenterusers : $users );
            }
        }
        //allocate of received SMS messages that have not been allocated before to admin users
        $sms = sms::whereStatus( false )->get();
        $users = User::filter()->where( 'level', 'admin' )->where( 'admin_representation_id', null )->get();
        $callcenterusers = [];
        foreach ( $users as $user ) {
            if ( $user->hasPermission( 'get_new_task' ) ) {
                array_push( $callcenterusers, $user );
            }
        }
        self::spread( $sms, $callcenterusers );

    }
    /**
    * @param mixed $sms
    * @param mixed $callcenters //from users table
    * @return string
    * create record in callcenters table for evry sms recived
    */
    private static function spread( $sms = null, $callcenters = null ) {
        if ( count( $sms ) > 0 && count( $callcenters ) > 0 ) {
            if ( count( $sms ) >= count( $callcenters ) ) {
                $size = ( integer )round( count( $sms ) / count( $callcenters ) );
                $items = array_chunk( $sms->toArray(), $size, true );
                foreach ( $callcenters as $key => $call ) {
                    foreach ( $items[ $key ] as $i ) {
                        $smsitem = ( object )$i;
                        $sms_body_type_numberic = var_dump( is_numeric( $smsitem->body ) );
                        $user_id = $smsitem->body == 11 ? null : $call->id;
                        $task = Callcenter::create( [
                            'sms_id' => $smsitem->id,
                            'user_id' => $user_id,
                            'type' => 1,
                            'status' => $smsitem->body == 11 ? 11 : 0,
                            'representation_id' => !is_null( $call->admin_representation_id ) ? $call->admin_representation_id : null,
                        ] );
                        $service = Service::find( $smsitem->body );
                        $user = User::where( 'phone', $task->sms->sender_number )->first();
                        if ( $service && $service->id != 11 ) {
                            $task->services()->attach( $service );
                            event( new fastserviceCallcenter( $task->sms->sender_number, $service, null, $task, 'sms' ) );
                        } elseif ( $smsitem->body == 11 ) {
                            self::answerCancelReceivingSms( $task->sms->sender_number );
                        }
                        $user ? $task->update( [ 'reg_id' => $user->id ] ) : '';

                        sms::where( 'id', $smsitem->id )->update( [
                            'status' => true
                        ] );
                    }
                }
            } else {
                foreach ( $sms as $key => $s ) {
                    $number = rand( 0, count( $callcenters ) );
                    $user = $callcenters[ $number == 0 ? 0 : $number - 1 ];
                    $sms_body_type_numberic = var_dump( is_numeric( $s->body ) );
                    $user_id = $s->body == 11 ? null : $user->id;
                    $task = Callcenter::create( [
                        'sms_id' => $s->id,
                        'user_id' => $user_id,
                        'type' => 1,
                        'status' => $s->body == 11 ? 11 : 0,
                        'representation_id' => !is_null( $user->admin_representation_id ) ? $user->admin_representation_id : null,
                    ] );
                    $user = User::where( 'phone', $task->sms->sender_number )->first();
                    $service = Service::find( $s->body );
                    if ( $service && $service->id != 11 ) {
                        $task->services()->attach( $service );
                        event( new fastserviceCallcenter( $task->sms->sender_number, $service, null, $task, 'sms' ) );
                    } elseif ( $s->body == 11 ) {
                        self::answerCancelReceivingSms( $task->sms->sender_number );
                    }
                    $user ? $task->update( [ 'reg_id' => $user->id ] ) : '';

                    sms::where( 'id', $s->id )->update( [
                        'status' => true
                    ] );
                }
            }
            return 'command done!';
        } else {
            return 'command done!';
        }
    }
    /**
    * @param mixed $user
    * @return void
    * answer cancel receiving sms
    */
    private static function answerCancelReceivingSms( $user ) {
        $message = 'شهروند گرامی ، در خواست عدم ارسال پیامک اطلاع رسانی سامانه سجا تنها مرجع رسمی و الکترونیکی اعطاء وامهای قرض الحسنه خرید کالا و خدمات بدون سود و ربا در کشور‌ و مجموعه حمایتی از کسب و کارها و بنگاه های اقتصادی و اعطاء تسهیلات و اشتغالزایی در شهرها و استانهای کشور و خدمات متنوع درمانی و رفاهی از طریق شما ثبت گردیده و منبعد ارتباط سامانه با شما قطع خواهد شد. در صورت تصمیم قطعی پاسخی به این پیام ندهید و در صورت تصمیم به دریافت مجدد پیامهای رایگان سجا عدد ۱۲ را به همان سامانه پیامکی ارسال نمائید. اداره کل فناوری و اطلاعات سجا.لغو 11';
        Helper::sms( $user, $message );
    }
}
