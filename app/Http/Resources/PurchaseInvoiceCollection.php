<?php

namespace App\Http\Resources;

use App\Models\PurchaseInvoice;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PurchaseInvoiceCollection extends ResourceCollection
{

    /**
     * @var
     */
    private $total_purchase_invoices;

    /**
     * @param $total_purchase_invoices
     * @param $collection
     */
    public function __construct($total_purchase_invoices , $collection)
    {
        parent::__construct($collection);
        $this->total_purchase_invoices = $total_purchase_invoices;
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => "created_at",
                    'id' => $item->id,
                    'supplier' => $item->supplier ? $item->supplier->name : 'بدون تامین کننده',
                    'representation' => $item->representation ? $item->representation->fa_name : 'نمایندگی کل',
                    'requester' => $item->user ? $item->user->name.' '.$item->user->family : 'ندارد',
                    'total_price' => number_format($item->total_price),
                    'created_at' => verta($item->created_at)->format('Y/m/d h:m:s'),
                    'status' => PurchaseInvoice::status($item->status),
                    'status_class' => PurchaseInvoice::getClassByStatus($item->status),
                ];
            }),
            'meta' =>  [
                'sum_price' => number_format($this->collection->sum('total_price')),
				'total_price' => number_format($this->total_purchase_invoices->sum('total_price')),
            ]
        ];
    }
}
