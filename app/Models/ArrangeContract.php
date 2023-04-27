<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArrangeContract extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     *
     */
    const STATUS = [
        '0' => 'پیش نویس', '1' => 'منتظر تایید مدیر', '2' => 'تایید شده'
    ];

    /**
     * @var string[]
     */
    protected $fillable=['contract_id','file','status','user_id','owner_id','no'];


    /**
     * @param $id
     * @return bool
     */
    public static function ownerArrange($id)
    {
        $arrange_contract = ArrangeContract::where('id',$id)->where('user_id',\Auth::user()->id)->count();
        if($arrange_contract == 1){ return true; }
        return false;
    }

}
