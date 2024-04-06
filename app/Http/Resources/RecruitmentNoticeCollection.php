<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class RecruitmentNoticeCollection extends ResourceCollection
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
                    'status' => $item->status,
                    'province' =>  $item->province ? $item->province->name : 'استخدام کشوری (دورکاری)',
                    'created_at' =>  $item->created,
                    'representation' => !is_null($item->representation) ?$item->representation->fa_name:'نمایندگی',
                ];
            }),
//            'meta' => [
////                'sum_price' => number_format($this->collection->sum('amount')),
////                'total_price' => number_format($this->checks->sum('amount')),
//            ]
        ];
    }

}

