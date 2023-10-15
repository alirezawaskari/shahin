<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class FinancialPlansTypeCollection extends ResourceCollection
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
                    'price' => number_format($item->price),
                    'payment_type' => $item::TYPE[$item->payment_type],
                    'prepayment_percent' => $item->prepayment_percent.' %',
                    'discount_percent' => $item->discount_percent.' %',
                    'capacity' => $item->capacity,
                    'reserved' => $item->reserved,
                    'start_date' => verta($item->start_date)->format('Y/m/d'),
                    'end_date' => verta($item->end_date)->format('Y/m/d'),
                    'status' => $item->status,
                    'is_show' => $item->is_show,
                    'applicant' => $item->applicant ? $item->applicant->family : 'ثبت نشده',
                    'created_at' => verta($item->created_at)->format('Y/m/d'),
                ];
            }),
//            'meta' => [
////                'sum_price' => number_format($this->collection->sum('amount')),
////                'total_price' => number_format($this->checks->sum('amount')),
//            ]
        ];
    }

}

