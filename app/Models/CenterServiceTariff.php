<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CenterServiceTariff extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'center_service_tariff';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function center()
    {
        return $this->belongsTo(Center::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service_tariff()
    {
        return $this->belongsTo(ServiceTariff::class);
    }

     /**
     * @param mixed $item
     * @return string
     */
    public static function setCompanyProfit($item , $seller_price)
    {
        $amount_company_profit = $seller_price - $item->purchase_price;
        $percent_company_profit = $item->purchase_price ? ($amount_company_profit/$item->purchase_price*100) : 0;
        return number_format($amount_company_profit).' ریال - '.round($percent_company_profit).'%';
    }
    /**
     * @param mixed $service_tariffs
     * @return int|float
     */
    public static function setAverageCompanyProfit($service_tariffs , $sum_seller_price)
    {
        $sum_purchase_price = $service_tariffs->sum('purchase_price');

        $average_company_profit = $sum_purchase_price ? (($sum_seller_price - $sum_purchase_price)/$sum_purchase_price*100) : 0;
        return round($average_company_profit);
    }
}
