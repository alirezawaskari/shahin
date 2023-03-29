<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPerformance extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    **/

    public function factorItem()
    {
        return $this->belongsTo(FactorItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function callcenter()
    {
        return $this->belongsTo(User::class, 'callcenter_id');
    }

    public function walletTransaction()
    {
        return $this->hasOne(WalletTransaction::class , 'reason_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function scopeFilter($query)
    {
        $phone = request('phone');
        $family = request('family');
        $name = request('name');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $type = request('type');

        if (isset($family) && trim($family) != '') {
            $query->whereHas('userPerformance', function ($query) use ($family) {
                $query->whereHas('factorItem', function ($query) use ($family) {
                    $query->whereHas('factor', function ($query) use ($family) {
                        $query->whereHas('user', function ($query) use ($family) {
                            $query->where('family', 'LIKE', '%' . $family . '%');
                        });
                    });
                });
            });
        }

        if (isset($name) && trim($name) != '') {
            $query->whereHas('userPerformance', function ($query) use ($name) {
                $query->whereHas('factorItem', function ($query) use ($name) {
                    $query->whereHas('factor', function ($query) use ($name) {
                        $query->whereHas('user', function ($query) use ($name) {
                            $query->where('name', 'LIKE', '%' . $name . '%');
                        });
                    });
                });
            });
        }

        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('userPerformance', function ($query) use ($phone) {
                $query->whereHas('factorItem', function ($query) use ($phone) {
                    $query->whereHas('factor', function ($query) use ($phone) {
                        $query->whereHas('user', function ($query) use ($phone) {
                            $query->where('phone', $phone);
                        });
                    });
                });
            });
        }

        if (isset($type) && trim($type) != '' && $type != 'all') {
            $query->whereType($type);
        }

        if (isset($startDate) && trim($startDate) != '' && isset($endDate) && trim($endDate) != '') {
            $query->whereDate('created_at', '>=' ,$startDate)->whereDate('created_at', '<=' ,$endDate);
        }

        return $query;
    }

    public static function hasUserInviteByType($id,$type,$identifier_code,$user_id)
    {
        $user_identifier = User::where('identifier_code',$identifier_code)->select('id','phone')->first();

        $user = User::where('id',$user_id)->first();
        $user_invite = InviteCategory::with('userInvites')->whereHas('userInvites', function ($query) use ($user_identifier,$user)  {
            $query->where('user_id',$user_identifier->id)->where('mobile',$user->phone);
        })->where('invitecategoryable_id',$id)->where('invitecategoryable_type',$type)->first();

        return $user_invite ? $user_invite->percent : false;
    }
}
