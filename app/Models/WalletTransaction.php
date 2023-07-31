<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalletTransaction extends Model
{
    /**
     * @var array
     */
    protected $guarded = [];

    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     *types of walletTransaction
     */
    const TYPE = [
        //0 => when create factor and pay amount to employee
        '0' => 'پرداخت به کاربر',
        '1' => 'واریز',
        '2' => 'برداشت',
    ];

    /**
     *status of walletTransaction
     */
    const STATUS = [
        '0' => 'انجام نشده',
        '1' => 'انجام شده',
    ];

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
    public function userPerformance()
    {
        return $this->belongsTo(UserPerformance::class , 'reason_id');
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
}
