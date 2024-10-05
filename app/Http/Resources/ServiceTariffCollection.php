<?php

namespace App\Http\Resources;

use App\Models\ServiceTariff;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceTariffCollection extends ResourceCollection
{
     /**
     * @var
     */
    public $service_tariffs;
    /**
     * @param $service_tariffs
     * @param $collection
     */
    public function __construct($service_tariffs , $collection)
    {
        parent::__construct($collection);
        $this->service_tariffs = $service_tariffs;
    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => "created_at",
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'price' => number_format($item->purchase_price),
                    'selling_price' => auth()->user()->isAbleTo('service_tariff_selling_price') ? number_format($item->price) : 'ثبت نشده',
                    'tariff' => $item->tariff->title,
                    'company_profit' => auth()->user()->isAbleTo('service_tariff_selling_price') ? self::setCompanyProfit($item) : 'ثبت نشده',
                    'jalali_created_at' => verta($item->created_at)->format('%Y/%m/%d '),
                ];
            }),
            'meta' =>  [
				'average_company_profit' => self::setAverageCompanyProfit($this->service_tariffs->get()),
            ]
        ];
    }

    /**
     * @param mixed $item
     * @return string
     */
    private function setCompanyProfit($item)
    {
        $amount_company_profit = $item->price - $item->purchase_price;
        $percent_company_profit = ($amount_company_profit * 100)/$item->price;
        return number_format($amount_company_profit).' ریال - '.round($percent_company_profit).'%';
    }
    /**
     * @param mixed $service_tariffs
     * @return int|float
     */
    private function setAverageCompanyProfit($service_tariffs)
    {
        $percent_company_profit = 0;
        foreach($service_tariffs as $item)
        {
            $amount_company_profit = $item->price - $item->purchase_price;
            $percent_company_profit = $percent_company_profit + (($amount_company_profit * 100)/$item->price);
        }
        $average_company_profit = $percent_company_profit / $service_tariffs->count();
        return round($average_company_profit);
    }
}
