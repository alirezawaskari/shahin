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

                    'exporter_fullname'=>$item->exportable ? $item->exportable->name .' '.$item->exportable->family : 'ثبت نشده',
                    'exporter_id'=>$item->exportable ? $item->exportable->id : 'ثبت نشده',
                    'exporter_phone'=>$item->exportable ? $item->exportable->phone : 'ثبت نشده',

                    'applicant_fullname'=>$item->applicantable ? $item->applicantable->name .' '.$item->applicantable->family : 'ثبت نشده',
                    'applicant_id'=>$item->applicantable ? $item->applicantable->id : 'ثبت نشده',
                    'applicant_phone'=>$item->applicantable ? $item->applicantable->phone : 'ثبت نشده',

                    'pay_to_fullname'=>$item->paytoable ? $item->paytoable->name .' '.$item->paytoable->family : 'ثبت نشده',
                    'pay_to_id'=>$item->paytoable ? $item->paytoable->id : 'ثبت نشده',
                    'pay_to_phone'=>$item->paytoable ? $item->paytoable->phone : 'ثبت نشده',

                    'description' => !is_null($last_history)?$last_history->description:'',
                    'title' => $item->title,
                    'total_factor_item_id' => $item->checkable_id,
                    // Spatie media if present, else the legacy File row (Check::getFrontImageAttribute)
                    'front_image' => $item->front_image,
                    'back_image' => $item->back_image,
                    // still legacy? (drives the "migrates on save" hint in check.js)
                    'front_image_legacy' => $item->fileMediaIsLegacy('front'),
                    'back_image_legacy' => $item->fileMediaIsLegacy('back'),
                    'asset_transaction'=>  $item->assetTransactions()->exists()? new AssetTransactionResource($item->assetTransactions()->latest()->first()):null,
                ];
            }),
            'meta' => [
                'sum_price' => number_format($this->collection->sum('amount')),
                'total_price' => number_format($this->checks->sum('amount')),
            ]
        ];
    }

}

