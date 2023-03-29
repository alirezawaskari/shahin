<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactorItemInstallment extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    public function factoritem()
    {
        return $this->belongsTo(FactorItem::class , 'factor_item_id');
    }

    public function factor()
    {
        return $this->belongsTo(Factor::class , 'factor_id');
    }

    public function getRouteKeyName()
    {
        return 'resnumber';
    }

	protected static function getExtraPay($item)
    {
        $factor_item = $item->factoritem;
        $temp = '';
        $extra_pay_day = 0;
        $start = Carbon::parse($item->date);
        $end = Carbon::parse($item->pay_date);
        $extra_pay_day = $start < $end ? $end->diffInDays($start): 0;
        $temp = $extra_pay_day > 3 ? $extra_pay_day.'روز - '.number_format($extra_pay_day*$item->price*($factor_item->percent_fines/100)): 'ندارد';
        return $temp;
    }

    //get status installment
    protected static function status($status)
    {
        switch($status)
        {
            case 0:
                return 'پرداخت نشده';
                break;
            case 1:
                return 'پرداخت شده';
                break;
            case 2:
                //in case of closing the case
                return 'بسته شده';
                break;
            default:
                return 'پرداخت نشده';
        }
    }
}
