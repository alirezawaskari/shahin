<?php

namespace App\Http\Resources;

use App\Models\CenterServiceTariff;
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
                    'company_profit' => auth()->user()->isAbleTo('service_tariff_selling_price') ? CenterServiceTariff::setCompanyProfit($item , $item->price) : 'ثبت نشده',
                    'jalali_created_at' => verta($item->created_at)->format('%Y/%m/%d '),
                ];
            }),
            'meta' =>  [
				'average_company_profit' => CenterServiceTariff::setAverageCompanyProfit($this->service_tariffs->get() , $this->service_tariffs->get()->sum('price')),
            ]
        ];
    }


}