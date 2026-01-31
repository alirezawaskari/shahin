<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id'];
    use SoftDeletes;

    /**
     *
     */
    const TYPE = [
        'pay_end_work' => 'پرداخت بعد از اتمام کار',
        'pay_end_session' => 'پرداخت با تکمیل جلسه',
    ];

     /**
     * @param $status
     * @return string
     * get employee status text
     */
    public static function status($status)
    {
        return [
            '0' => 'غیرفعال',
            '1' => 'فعال',
        ][$status];
    }
    /**
     * Summary of contractStatus
     * @param mixed $status
     * @return string
     */
    public static function contractStatus($status)
    {
        return [
            '0' => 'تنظیم نشده',
            '3' => 'پیش نویس',
            '1' => 'تایید شده',
            '2' => 'منتظر تایید قرارداد توسط همکار',
            '4' => 'منتظر تایید قرارداد توسط مدیر',
        ][$status];
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class , 'user_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function histories()
    {
        return $this->morphMany(History::class , 'historiable');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function parents()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function card()
    {
        return $this->belongsTo(Card::class , 'card_id');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeFilter($query)
    {
        $name = request('name');
        $family = request('family');
        $phone = request('phone');
        $work_title = request('work_title');
        $status=request('status');
        $role=request('role');

        $query->whereHas('user', function ($query) {
            $query->whereDoesntHave('ghorfeOnlineLists');
        });

        if ((!isset($status) || $status == 'all') && trim($name) == '' && trim($family) == '' && trim($phone) == '') {
            $query->whereHas('user', function ($query) use ($status) {
                $query->where('status',true);
            });
        }
        if (auth()->user()->admin_representation) {
            $representation = auth()->user()->admin_representation->id;
            $query->whereHas('user', function ($query) use ($representation) {
                $query->where('admin_representation_id', $representation);
            });
        }
        if (isset(request()->parents)){
            $query->whereIn('parent_id',request()->parents);
        }
        if (auth()->user()->center_id) {
            $center = auth()->user()->center_id;
            $query->whereHas('user', function ($query) use ($center) {
                $query->where('center_id', $center);
            });
        }

        if (isset($name) && trim($name) != '') {
            $query->whereHas('user', function ($query) use ($name) {
                $query->where('name', 'LIKE', '%' . $name . '%');
            });
        }

        if (isset($family) && trim($family) != '') {
            $query->whereHas('user', function ($query) use ($family) {
                $query->where('family', 'LIKE', '%' . $family . '%');
            });
        }
        if (isset($role) && trim($role) != 'all') {
            $query->whereHas('user', function ($query) use ($role) {
                $query->whereHas('roles',function ($query)use ($role){
                    $query->where('id',$role);
                });
            });
        }

        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('user', function ($query) use ($phone) {
                $query->where('phone', 'LIKE', '%' . $phone . '%');
            });
        }

        if (isset($status) && $status != 'all') {
            $query->whereHas('user', function ($query) use ($status) {
                $query->where('status',$status);
            });
        }

        if (isset($work_title) && trim($work_title) != '') {
            $query->where('work_title', 'LIKE', '%' . $work_title . '%');
        }

        return $query;
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Employee::class, 'parent_id');
    }

// بارگذاری بازگشتی
    public function childrenRecursive()
    {
        return $this->hasMany(Employee::class, 'parent_id')->with('childrenRecursive');
    }
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function center()
    {
        return $this->belongsTo(Center::class);
    }
    public function percentageAllocations()
    {
        return $this->hasManyThrough(
            PercentageAllocationEmployee::class,
            User::class,
            'id',           // foreign key in User table...
            'user_id',      // foreign key in PercentageAllocationEmployee table...
            'user_id',      // local key on Employee table...
            'id'            // local key on User table...
        );
    }

}
