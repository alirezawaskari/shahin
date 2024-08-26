<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceIndexTariffCollection extends ResourceCollection
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
                    'title' => $item->title,
                    'agent' => $item->agent->name.' '.$item->agent->family,
                    'purchase_price' => number_format($item->purchase_price),
                    'jalali_created_at' => verta($item->created_at)->format('%Y/%m/%d '),
                ];
            })
        ];
    }
}
