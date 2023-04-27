<?php

namespace App\Http\Resources;

use App\Models\UserPerformance;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PerformanceDetailCollection extends ResourceCollection
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
                    'id' => $item->id,
                    'resnumber' => isset($item->factorItem) ? $item->factorItem->resnumber : 'ندارد',
                    'name' => isset($item->factorItem) ? $item->factorItem->factor->user->name. ' ' . $item->factorItem->factor->user->family : 'ندارد',
                    'phone' => isset($item->factorItem) ? $item->factorItem->factor->user->phone : 'ندارد',
                    'price' => isset($item->factorItem) ? number_format($item->factorItem->total_price) : 'ندارد',
                    'type' => $this->setType($item),
                    'session_number' => isset($item->factorItem) ?  $item->factorItem->products->sum('pivot.session_number') : '0',
                    'done_session_number' => isset($item->factorItem) ? $item->factorItem->sessions->where('status' , true)->count() : '0',
                ];
            }),
			'meta' =>  [
                'sum_price' => number_format($this->collection->sum('total_price')),
            ]
        ];
    }
    /**
     * @param UserPerformance $item
     * @return string
     */
    private function setType(UserPerformance $item)
    {
        if(isset($item->factorItem) && $item->factorItem->type)
        {
            $type = 'اقساط';
        }
        else {
            $type = 'نقد';
        }

        return $type;
    }
}
