<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var array
     */
    protected $casts = [
        'values' => 'array',
        'factor_values' => 'array',
        'factor_image' => 'array',
		'contract_values' => 'array',
        'prepayment' => 'array',
        'confirms' => 'array',
		'price_difference' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

	protected static function type($type)
    {
        if($type == 0){
            return 'قرارداد';
        }elseif ($type == 1) {
            return 'چک';
        } elseif ($type == 2) {
            return 'سفته';
        }
        elseif ($type == 3) {
            return 'رسید';
        }elseif ($type == 4) {
            return 'گواهی کسر از حقوق';
        }else {
            return 'رسید';
        }
    }

    /**
     * @param $status_mali
     * @return string|void
     */
    protected static function statusMali($status_mali)
    {
        //get status mali file
        if ($status_mali == 0) {
            return 'جدید';
        } elseif ($status_mali == 1) {
            return 'جاری';
        } elseif ($status_mali == 2) {
            return 'اخطار اول';
        } elseif ($status_mali == 3) {
            return 'اخطار دوم';
        } elseif ($status_mali == 4) {
            return 'ارجاع به حقوقی';
        } elseif ($status_mali == 5) {
            return 'اتمام اقساط';
        } elseif ($status_mali == 6) {
            return 'تسویه حساب';
        }
    }

    /**
     * @param FactorItem $factorItem
     */
    protected static function checkInstallments(FactorItem $factorItem)
    {
        $expire_installments = count($factorItem->FactorItemInstallments->where('status', 0)->where('date', '<', Carbon::now()));
        if(!$expire_installments)
        {
            $factorItem->update(['status_mali' => 1]);
        }

        $unpaid_installments = count($factorItem->FactorItemInstallments->where('status', 0));
        if(!$unpaid_installments)
        {
            $factorItem->update(['status_mali' => 5]);
        }
    }
}
