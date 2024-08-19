<?php

namespace App\Http\Resources;

use App\Models\ServiceTariff;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceTariffCollection extends ResourceCollection
{

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
                    'price' => auth()->user()->isAbleTo('service_tariff_selling_price') ? number_format($item->purchase_price).' - '.number_format($item->price) : number_format($item->purchase_price),
                    'tariff' => $item->tariff->title,
                    'jalali_created_at' => verta($item->created_at)->format('%Y/%m/%d ')
                ];
            })
        ];
    }
}
