<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WalletTransactionCollection extends ResourceCollection
{

    /**
     * @var
     */
    private $wallet_transactions;

    /**
     * @param $wallet_transactions
     * @param $collection
     */
    public function __construct($wallet_transactions , $collection)
    {
        parent::__construct($collection);
        $this->wallet_transactions = $wallet_transactions;
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
                    'name' => $item->userPerformance && isset($item->userPerformance->factorItem) ? $item->userPerformance->factorItem->factor->user->name. ' ' . $item->userPerformance->factorItem->factor->user->family : 'ندارد',
                    'phone' => $item->userPerformance && isset($item->userPerformance->factorItem) ? $item->userPerformance->factorItem->factor->user->phone : 'ندارد',
                    'price' => number_format($item->price),
                    'type' => $item->type,
                    'date' => verta($item->created_at)->format('Y/m/d'),
                    'description' => $item->userPerformance ? $item->userPerformance->description : 'ندارد',
                ];
            }),
			'meta' =>  [
                'sum_price' => number_format($this->collection->sum('price')),
				'total_price' => number_format($this->wallet_transactions->sum('price')),
            ]
        ];
    }
}
