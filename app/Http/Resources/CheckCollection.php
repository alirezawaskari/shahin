<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class CheckCollection extends ResourceCollection
{
    /**
     * @var
     */
    public $checks;

    /**
     * @param $checks
     * @param $collection
     */
    public function __construct($checks, $collection)
    {
        parent::__construct($collection);
        $this->checks = $checks;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function ($item) {
                $front_image = $item->files()->where('title', 'front')->first();
                $back_image = $item->files()->where('title', 'back')->first();
                $last_history=$item->histories->last();
                return [
                    'sort_by' => $item->date,
                    'id' => $item->id,
                    'status' => $item->status,
                    'number_amount' => number_format($item->amount),
                    'amount' =>$item->amount,
                    'jalali_date' => verta($item->date)->format('Y/m/d'),
                    'date' => $item->date,
                    'serial_code' => $item->serial_code,
                    'pay_to' => $item->pay_to,
                    'exporter' => $item->exporter,
                    'description' => !is_null($last_history)?$last_history->description:'',
                    'title' => $item->title,
                    'total_factor_item_id' => $item->checkable_id,
                    'front_image' => !is_null($front_image) ?$front_image->path: '',
                    'back_image' => !is_null($back_image) ? $back_image->path : '',
                ];
            }),
            'meta' => [
                'sum_price' => number_format($this->collection->sum('amount')),
                'total_price' => number_format($this->checks->sum('amount')),
            ]
        ];
    }

}

