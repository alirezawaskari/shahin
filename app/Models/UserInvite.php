<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GuzzleHttp\Client;

class UserInvite extends Model
{
    use SoftDeletes ;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var string[]
     */
    protected $fillable=['invite_category_id', 'mobile','name','family', 'user_id','status'];

    /**
     *
     */
    const STATUS = [ 1 => 'ثبت شده', 2 => 'دعوت موفق'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inviteCategory()
    {
        return $this->belongsTo(InviteCategory::class);
    }

    /**
     * @param $phone
     * @param $name
     * @param $family
     * @param $url
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function sendSms($phone, $name, $family, $url)
    {
        $headers = ['apikey' => config('services.iranSms.apikey'), 'Content-Type' => 'application/x-www-form-urlencoded'];
        $client = new Client(['headers' => $headers]);
        $body = ['receptor' => $phone, 'type' => 1, 'template' => 'Invitefriends', 'param1' => $name .' ' .$family, 'param2' => $url];
        $res = $client->request('POST', 'http://api.parsasms.com/v2/send/verify', ['form_params' => $body]);

        if ($res) {
            return true;
        }
        return false;
    }

}
