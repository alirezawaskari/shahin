<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\TotalFactorItemInstallment;
use Carbon\Carbon;

class TotalFactorItemInstallmentCollection extends ResourceCollection
{
    private $total_factor_item_installments;

    public function __construct($total_factor_item_installments , $collection)
    {
        parent::__construct($collection);
        $this->total_factor_item_installments = $total_factor_item_installments;
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
                    'sort_by' => "date",
                    'id' => $item->id,
                    'total_factor_item_id' => $item->total_factor_item_id,
                    'name' => $item->totalFactoritem ? $item->totalFactoritem->factor->user->name : '',
                    'family' => $item->totalFactoritem ? $item->totalFactoritem->factor->user->family : '',
                    'phone' => $item->totalFactoritem ? $item->totalFactoritem->factor->user->phone : '',
                    'class_name' => $this->setClassName($item),
                    'level' => $item->level,
                    'date' => verta( $item->date)->format('%d %B ، %Y - H:i:s'),
                    'georgian_date' =>$item->date,
                    'pay_date' => $item->status == 1 ? verta($item->pay_date)->format('%d %B ، %Y - H:i:s') : '----',
                    'price' => number_format($item->price),
                    'extra_pay' => $item->status == 0 ? number_format($this->setExtraPay($item)) : $this->setExtraPay($item),
                    'amount_payable' => $item->status == 0 ? number_format($this->setExtraPay($item) + $item->price) : number_format($item->price),
                    'status' => $item->status
                ];
            }),
            'meta' =>  [
                'sum_price' => number_format($this->collection->sum('price')),
				'total_price' => number_format($this->total_factor_item_installments->sum('price')),
            ]
        ];
    }
    /**
     * @param totalFactorItemInstallment $item
     * @return mixed
     */
    private function setExtraPay(totalFactorItemInstallment $item)
    {

        if($item->status == 0)
        {
            $extra_pay = TotalFactorItemInstallment::getExtraPay($item);
            return $extra_pay['extra_pay_price'];
        }
        if($item->status == 1)
        {
            $extra_pay = TotalFactorItemInstallment::getExtraPay($item);
            return $extra_pay['appropriate_format'];
        }
        if($item->status == 2)
        {
            return 'ندارد';
        }
    }
    /**
     * @param totalFactorItemInstallment $item
     * @return string
     */
    private function setClassName(totalFactorItemInstallment $item)
    {
        //if installment paid
        if($item->status == 1)
        {
            return 'table-success';
        }
        //if installment closed
        if($item->status == 2)
        {
            return 'table-dark';
        }
        //if installment unpaid && expired
        if($item->status == 0 && $item->date <= Carbon::now())
        {
            return 'table-danger';
        }
        //if installment unpaid && not expired
        else
        {
            return '';
        }
    }
}
