<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TotalFactorItemInstallment extends Model
{
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function totalFactorItem()
    {
        return $this->belongsTo(TotalFactorItem::class , 'total_factor_item_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function factor()
    {
        return $this->belongsTo(Factor::class , 'factor_id');
    }

    /**
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'resnumber';
    }
    /**
     * @param TotalFactorItemInstallment $item
     * @return array
     * get extra pay for one totalFactoitem installment
     */
    public static function getExtraPay(TotalFactorItemInstallment $item)
    {
        $total_factor_item = $item->totalFactoritem;
        $extra_pay = [];
        $start = Carbon::parse($item->date);
        $end = $item->status == 1 ? Carbon::parse($item->pay_date) : now();
        $extra_pay['extra_pay_day'] = $start < $end ? intval($start->diffInDays($end)): 0;
        $extra_pay['extra_pay_price'] =  $extra_pay['extra_pay_day'] > 3 ? ($extra_pay['extra_pay_day']*$item->price*($total_factor_item->percent_fines/100)): '0';
        $extra_pay['appropriate_format'] = $extra_pay['extra_pay_day'] > 3 ? $extra_pay['extra_pay_day'].'روز - '.number_format($extra_pay['extra_pay_price']): 'ندارد';

        return $extra_pay;
    }
    /**
     * @param mixed $status
     * @return string
     * get status installment
     */
    public static function status($status)
    {
        return [
            '0' => 'پرداخت نشده',
            '1' =>'پرداخت شده',
            //in case of closing the case
            '2' => 'بسته شده',
        ][$status];
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
        $start_date = request('start_date');
        $end_date = request('end_date');

		$query->whereHas('totalFactorItem', function ($query) {
			$query->where('status_mali', '!=' , 0);
		});

        if (auth()->user()->admin_representation) {
			$representation = auth()->user()->admin_representation_id;
				$query->whereHas('totalFactorItem', function ($query) use ($representation) {
					$query->whereHas('factor', function ($query) use ($representation) {
						$query->where('representation_id', $representation);
				});
			});
		}

        if (auth()->user()->center) {
			$center = auth()->user()->center_id;
				$query->whereHas('totalFactorItem', function ($query) use ($center) {
					$query->whereHas('factorItems', function ($query) use ($center) {
						$query->where('center_id', $center);
				});
			});
		}

        if (isset($name) && trim($name) != '') {
            $query->whereHas('totalFactorItem', function ($query) use ($name) {
                $query->whereHas('factor', function ($query) use ($name) {
                    $query->whereHas('user', function ($query) use ($name) {
                        $query->where('name', 'LIKE', '%' . $name . '%');
                    });
                });
            });
        }

        if (isset($family) && trim($family) != '') {
            $query->whereHas('totalFactorItem', function ($query) use ($family) {
                $query->whereHas('factor', function ($query) use ($family) {
                    $query->whereHas('user', function ($query) use ($family) {
                        $query->where('family', 'LIKE', '%' . $family . '%');
                    });
                });
            });
        }

        if (isset($phone) && trim($phone) != '') {
            $query->whereHas('totalFactorItem', function ($query) use ($phone) {
                $query->whereHas('factor', function ($query) use ($phone) {
                    $query->whereHas('user', function ($query) use ($phone) {
                        $query->where('phone', '=', $phone);
                    });
                });
            });
        }

        if (isset($start_date) && trim($start_date) != '' && isset($end_date) && trim($end_date) != '') {
            $query->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date);
        }

        return $query;
    }

}
