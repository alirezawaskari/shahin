<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class PromissoryCollection extends ResourceCollection
{
    /**
     * @var
     */
    public $promissories;

    /**
     * @param $promissories
     * @param $collection
     */
    public function __construct($promissories, $collection)
    {
        parent::__construct($collection);
        $this->promissories = $promissories;
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
                    'sort_by' => $item->receipt_date,
                    'id' => $item->id,
                    'status' => $item->status,
                    'number_amount' => number_format($item->amount),
                    'amount' =>$item->amount,
                    'jalali_date' => verta($item->date)->format('Y/m/d'),
                    'date' => $item->date,
                    'serial_code' => $item->serial_code,
                    'pay_to' => $item->pay_to,
                    'exporter' => $item->exporter,

                    'exporter_fullname'=>$item->exportable->name .' '.$item->exportable->family,
                    'exporter_id'=>$item->exportable->id,
                    'exporter_phone'=>$item->exportable->phone,

                    'applicant_fullname'=>$item->applicantable->name .' '.$item->applicantable->family,
                    'applicant_id'=>$item->applicantable->id,
                    'applicant_phone'=>$item->applicantable->phone,

                    'pay_to_fullname'=>$item->paytoable->name .' '.$item->paytoable->family,
                    'pay_to_id'=>$item->paytoable->id,
                    'pay_to_phone'=>$item->paytoable->phone,

                    'description' => !is_null($last_history)?$last_history->description:'',
                    'title' => $item->title,
                    'total_factor_item_id' => $item->checkable_id,
                    'front_image' => !is_null($front_image) ?$front_image->path: '',
                    'back_image' => !is_null($back_image) ? $back_image->path : '',
                    'asset_transaction'=>  $item->assetTransactions()->exists()? new AssetTransactionResource($item->assetTransactions()->latest()->first()):null,
                ];
            }),
            'meta' => [
                'sum_price' => number_format($this->collection->sum('amount')),
                'total_price' => number_format($this->promissories->sum('amount')),
            ]
        ];
    }

}

