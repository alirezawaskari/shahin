<?php

namespace App\Models;

use Carbon\Carbon;
use OwenIt\Auditing\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class UserPerformance extends Model implements AuditableContract
{
    use Auditable;
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    **/

    public function factorItem()
    {
        return $this->belongsTo(FactorItem::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    **/
    public function resaons()
    {
        return $this->belongsToMany(PercentageAllocationEmployee::class);
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function callcenter()
    {
        return $this->belongsTo(User::class, 'callcenter_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function walletTransaction()
    {
        return $this->hasOne(WalletTransaction::class , 'reason_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * @param $query
     * @return mixed
     */
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

    /**
     * @param $id
     * @param $type
     * @param $identifier_code
     * @param $user_id
     * @return false|\Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed
     */
    public static function hasUserInviteByType($id, $type, $identifier_code, $user_id)
    {
        $user_identifier = User::where('identifier_code',$identifier_code)->select('id','phone')->first();

        $user = User::where('id',$user_id)->first();
        $user_invite = InviteCategory::with('userInvites')->whereHas('userInvites', function ($query) use ($user_identifier,$user)  {
            $query->where('user_id',$user_identifier->id)->where('mobile',$user->phone);
        })->where('invitecategoryable_id',$id)->where('invitecategoryable_type',$type)->first();

        return $user_invite ? $user_invite->percent : false;
    }
    /**
     * @param mixed $percentage_allocation_employee
     * @param mixed $description
     * @param mixed $price
     */
    public static function store($percentage_allocation_employee , $description , $price , $factor_item)
    {
        $user = $percentage_allocation_employee->user;
        $user_performance = UserPerformance::create( [
            'factor_item_id' => $factor_item,
            'description' => $description,
            'price' => $price,
            'tax' => $price * ( $percentage_allocation_employee->percent / 100 ),
            'ancillary_costs' => 0,
            'date' => now(),
            'callcenter_id' => auth()->user()->id,
            'user_id' => $user->id,
        ] );

        $resNum = rand( 12, 99 ) . substr( Carbon::now()->timestamp, 6, 11 ) . substr( $user->phone, 7, 11 );
        $WalletTransaction = WalletTransaction::create( [
            'user_id' => $user->id,
            'reason_id' => $user_performance->id,
            'resnumber' => $resNum,
            'price' => $price * ( $percentage_allocation_employee->percent / 100 ),
            'status' => '1',
            'type' => '1',
            'created_at' => $user_performance->date,
        ] );

        $user_performance->resaons()->attach(['percentage_allocation_employee_id' => $percentage_allocation_employee->id]);
        $user ? $user->update( [ 'wallet' => $user->wallet + $WalletTransaction->price ] ) : '';
    }
}
