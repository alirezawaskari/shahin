<?php

namespace App\Helpers;

use App\Models\Agency;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Category;
use App\Models\CategoryArticle;
use App\Models\GhorfeOnlineList;
use App\Models\Product;
use App\Models\Resume;
use App\Models\User;
use App\Models\VipUser;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Hekmatinasser\Verta\Verta;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class Helper {

    /**
     * @param $file
     * @param $path
     * @return string
     */
    public static function uploadFile($file, $path ) {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        //        $filepath = "/upload/{$path}/{$year}/{$month}/";
        $filepath = "/upload/{$year}/{$month}/{$path}/";

        $rand = rand( 1, 999 );
        $filename = "{$rand}_" . $file->getClientOriginalName();
        $file->move( public_path( $filepath ), $filename );

        return $filepath . $filename;
    }


    /**
     * @param $file
     * @param $path
     * @return string
     */
    public static function uploadImage($file, $path)
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $filepath = "/upload/{$year}/{$month}/{$path}/";
        $rand = rand(1, 999);
        $filename = "{$rand}_" . $file->getClientOriginalName();
        // image max size
       // $max_file_size = 2 * 1024 * 1024;
        $max_file_size =700 * 1024;
        // Image quality
        $quality = 80;
        if($file->getSize() > $max_file_size ) {
            $image = Image::make($file->getRealPath());
            $image->resize(null, 1500, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $image->save(public_path($filepath) . $filename, $quality);
        }
        else {
            $file->move(public_path($filepath), $filename);
        }

        return $filepath . $filename;
    }

    /**
     * @param $file
     * @return string
     */
    public static function deleteImage($file ) {
        if ( \File::exists( public_path( $file ) ) ) {
            \File::delete( public_path( $file ) );
            return 'true';
        }
        return 'false';
    }

    /**
     * @param $id
     * @param $field
     * @return null
     */
    public static function user($id, $field ) {
        if ( $id ) {
            $rec = User::find( $id );
            if ( $rec ) {
                return $rec->$field ;
            }
        }
        return null;
    }

    /**
     * @param $value
     * @return string
     */
    public static function iconIsItOrNot($value ) {
        if ( $value != '' && $value == 1 ) {
            return '<i class="fa fa-check" aria-hidden="true"></i>' ;
        } else if ( $value == 0 ) {
            return '<i class="fa fa-close" aria-hidden="true"></i>' ;
        }
        return '<i class="fa fa-minus" aria-hidden="true"></i>' ;
    }

    /**
     * @param $date
     * @return string
     */
    public static function vertaTocarbon($date ) {
        return Carbon::instance( $date->DateTime() )->toDateString();
    }

    /**
     * @param $date
     * @return mixed
     */
    public static function vertaToMiladi($date ) {
        $v = Verta::parse( Helper::numberConverter( $date ) );
        return $v->formatGregorian( 'Y-m-d H:i:s' );
    }

    /**
     * @param $number
     * @return array|string|string[]
     */
    public static function numberConverter($number ) {
        $persian = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );
        $num = range( 0, 9 );
        return str_replace( $persian, $num, $number );
    }

    /**
     * @param $register
     * @return array
     */
    public static function userHistories($register ) {
        $user = auth()->user();
        $histories = [];
        $histories_sort = [];
        //callcenter historeies
        if ( $user->isAbleTo( 'history-callcenter' ) || $user->isAbleTo( 'employee-related-history' ) ) {
            if ( isset( $register->callcenters ) )
            {
                foreach ( $register->callcenters as $task ) {
                    $histories = self::historyCollection($histories , $task->histories);
                }
            }
        }
        //factor historeies
        if ( $user->isAbleTo( 'history-factor' ) || $user->isAbleTo( 'employee-related-history' )) {
            if ( isset( $register->factors ) )
            {
                foreach ( $register->factors as $factor ) {
                    foreach ( $factor->comments as $comment ) {
                        $histories[] = [
                            'user' => $comment->user[ 'name' ] . ' ' . $comment->user[ 'family' ],
                            'callcenter' => $comment->user[ 'name' ] . ' ' . $comment->user[ 'family' ],
                            'description' => $comment->comment,
                            'date' => verta( $comment->created_at )->format( '%d %B ، %Y - H:i' ),
                            'created_at' => $comment->created_at,
                        ];
                    }
                }
            }
        }
        //agency historeies
        if (isset($register->phone) && ($user->isAbleTo( 'history-agency' ) || $user->isAbleTo( 'employee-related-history' )) ) {
            $agencies = Agency::where( 'phone', $register->phone )->get();
            if ( $agencies )
            {
                foreach ( $agencies as $agency )
                {
                    $histories = self::historyCollection($histories , $agency->histories);
                }
            }
        }
        //resume historeies
        if (isset($register->phone) && ($user->isAbleTo( 'history-resume' ) || $user->isAbleTo( 'employee-related-history' ))) {
            $resumes = Resume::where( 'phone', $register->phone )->get();
            if ($resumes)
            {
                foreach ( $resumes as $resume ) {
                    $histories = self::historyCollection($histories , $resume->histories);
                }
            }
        }

        //employee related historeies
        if ($user->isAbleTo( 'employee-related-history' ) )
        {
            $histories = collect( $histories )->where( 'user_id' , auth()->user()->id );
        }
        $histories = collect( $histories )->sortBy( 'created_at' )->reverse()->toArray();
        $number=1;
        foreach ( $histories as $history ) {
             $history['number']=$number++;
            $histories_sort[] = $history;
        }
        return $histories_sort;
    }

    /**
     * @param $histories
     * @param $item_histories
     * @return mixed
     */
    public static function historyCollection($histories , $item_histories) {
        foreach ( $item_histories as $history ) {
            $histories[] = [
                'user_id' => $history->user_id ? $history->user_id : 'ثبت نشده',
                'user' => $history->user ? ($history->user[ 'name' ] . ' ' . $history->user[ 'family' ]) : 'ثبت نشده',
                'callcenter' => $history->callcenter ? ($history->callcenter[ 'name' ] . ' ' . $history->callcenter[ 'family' ]) : ($history->user ? ($history->user[ 'name' ] . ' ' . $history->user[ 'family' ]) : 'ثبت نشده'),
                'description' => $history->description,
                'date' => verta( $history->created_at )->format( '%d %B ، %Y - H:i' ),
                'created_at' => $history->created_at,
            ];
        }

        return $histories;
    }

    /**
     * @param $vip
     * @param $user
     */
    public static function vipUser($vip, $user ) {
        if ( $vip ) {
            VipUser::firstOrCreate( [ 'user_id' => $user, 'type' => 'VIP' ] );
            VipUser::where( 'user_id', $user )->where( 'type', 'SOS' )->delete();
        } else {
            VipUser::where( 'user_id', $user )->where( 'type', 'VIP' )->delete();
        }
    }

    /**
     * @param $sos
     * @param $user
     */
    public static function sosUser($sos, $user ) {
        if ( $sos ) {
            VipUser::firstOrCreate( [ 'user_id' => $user, 'type' => 'SOS' ] );
            VipUser::where( 'user_id', $user )->where( 'type', 'VIP' )->delete();
        } else {
            VipUser::where( 'user_id', $user )->where( 'type', 'SOS' )->delete();
        }
    }

    /**
     * @param $province
     * @return mixed
     */
    public static function getCallcenter($province , $representation) {
        if($representation)
        {
            $admin_users = User::whereDoesntHave('ghorfeOnlineLists')->where( 'level', 'admin' )->where('admin_representation_id' , $representation)->with('tasks')->get()->sortBy(function($admin_users)
            {
                return $admin_users->tasks->count();
            });
        }
        else
        {
            $admin_users = User::where( 'level', 'admin' )->where( 'admin_representation_id', null )->whereDoesntHave('ghorfeOnlineLists')->get();
        }

        if (is_null($admin_users)){
            $admin_users = User::where( 'level', 'admin' )->where( 'admin_representation_id', null )->whereDoesntHave('ghorfeOnlineLists')->get();
        }
        if($admin_users)
        {
            $callcenterusers = [];
            foreach ( $admin_users as $user ) {
                if ( $user->hasPermission('get_new_task') ) {
                    $callcenterusers[] = $user->id;
                }
            }
			$key = array_rand( $callcenterusers );
            return $callcenterusers[$key];
        }
    }

    /**
     * @param $temp_path
     * @param $image_path
     * @return string
     */
    public static function moveImage($temp_path, $image_path ) {
        //image_path=>must be format = > 'upload/test/'
        $img_detail = explode( '/', $temp_path );
        if ( isset( $img_detail[ 2 ] ) )$year = $img_detail[ 2 ];
        if ( isset( $img_detail[ 3 ] ) )$month = $img_detail[ 3 ];
        if ( isset( $img_detail[ 5 ] ) )$img_name = $img_detail[ 5 ];

        if ( isset( $year ) && isset( $month ) ) {
            $path_file = 'upload/'.$year.'/'.$month.'/'.$image_path;
            if ( !is_dir( $path_file ) ) {
                File::makeDirectory( $path_file, 0777, true, true );
            }
            File::move( public_path( $temp_path ), public_path( $path_file.'/'.$img_name ) );
            return '/'.$path_file.'/'.$img_name;
        }

        return '';
    }

    /**
     * @param $value
     * @return string
     */
    public static function isItOrNot($value ) {
        if ( $value != '' && $value == 1 ) {
            return 'دارد' ;
        } else if ( $value == 0 ) {
            return 'ندارد' ;
        }
        return '';
    }

    /**
     * @param $value
     * @return string
     */
    public static function activeOrNot($value ) {
        if ( $value != '' && $value == 1 ) {
            return 'فعال' ;
        } else if ( $value == 0 ) {
            return 'غیر فعال' ;
        }
        return '';
    }

    /**
     * @param $id
     * @param $field
     * @return null
     */
    public static function attributeGroups($id, $field ) {
        if ( $id ) {
            $rec = AttributeGroup::find( $id );
            if ( $rec ) {
                return $rec->$field ;
            }
        }
        return null;
    }

    /**
     * @param $id
     * @param $field
     * @return null
     */
    public static function attribute($id, $field ) {
        if ( $id ) {
            $rec = Attribute::find( $id );
            if ( $rec ) {
                return $rec->$field ;
            }
        }
        return null;
    }

    /**
     * @param $id
     * @param $field
     * @return null
     */
    public static function categoryArticle($id, $field ) {
        if ( $id ) {
            $rec = CategoryArticle::find( $id );
            if ( $rec ) {
                return $rec->$field ;
            }
        }
        return null;
    }

    /**
     * @param $string
     * @return array|string|string[]
     */
    public static function convert($string ) {
        $persian = [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ];
        $arabic = [ '٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠' ];

        $num = range( 0, 9 );
        $convertedPersianNums = str_replace( $persian, $num, $string );
        $englishNumbersOnly = str_replace( $arabic, $num, $convertedPersianNums );

        return $englishNumbersOnly;
    }

    /**
     * @param $phones
     * @param $message
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sms($phones, $message ) {
        $headers = [
            'apikey' => 'cpCIuUbmtBhYFw41+kpYs0mSNw+mkN4aZBELwgUMN+A',
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $client = new Client( [
            'headers' => $headers
        ] );
        $body = [
            //30005006003675
            'sender' => '10002622',
            'receptor' => $phones,
            'message' => $message
        ];
        $r = $client->request( 'POST', 'http://api.parsasms.com/v2/sms/send/bulk2', [
            'form_params' => $body
        ] );

        return 'true';
    }

    /**
     * @param $id
     * @param $field
     * @return null
     */
    public static function product($id, $field ) {
        if ( $id ) {
            $rec = Product::where( 'id', $id )->first();
            if ( $rec && isset( $rec->$field ) ) {
                return $rec->$field ;
            }
        }
        return null;
    }

    /**
     * @param $id
     * @param $field
     * @return null
     */
    public static function rootCategoryByProductId($id, $field ) {
        $field_val = self::product( $id, $field );
        if ( $id ) {
            $rec = Category::where( 'id', $field_val )->select( 'id', 'title' )->first();
            if ( $rec ) {
                return $rec->title ;
            }
        }
        return null;
    }

    /**
     * @param $text
     * @return array|mixed|string|string[]
     */
    public static function replaceDateTimeWithPersian($text) {
        $matches = [];
        preg_match_all('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $text, $matches);
        for ($i = 0; $i < count($matches[0]); $i++) {
            $miladi = Carbon::create($matches[1][$i], $matches[2][$i], $matches[3][$i], $matches[4][$i], $matches[5][$i], $matches[6][$i]);
            $jalali = Verta::instance($miladi);
            $persianDateTime = $jalali->format('Y-m-d H:i:s');
            $text = str_replace($matches[0][$i], $matches[0][$i] . ' (' . $persianDateTime . ')', $text);
        }
        return $text;
    }
    public static function filterWithDosentHaveGhorfeOnline($query)
    {
        $query->whereDoesntHave('ghorfeOnlineLists');
    }
    public static function filterWithHaveGhorfeOnline($query)
    {
        $ghorfe_online_list = Helper::getGhorfe();
        $query->whereHas('ghorfeOnlineLists',function ($query) use ($ghorfe_online_list){
            $query->Where('ghorfe_online_list_id', $ghorfe_online_list->id);
        });
    }
    /**
     * Summary of getGhorfe
     * @return GhorfeOnlineList|null
     */
    public static function getGhorfe() : GhorfeOnlineList| null
    {
        $GhorfeOnlineList = GhorfeOnlineList::where('slug' , config('services.ghorfeOnline.slug'))->first();
        return $GhorfeOnlineList ?? null;
    }

}
