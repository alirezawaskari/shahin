<?php

namespace App\Http\Resources;

use App\Models\Factor;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\TotalFactorItem;

class FactorCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public $factors;
    public function __construct($factors , $collection)
    {
        parent::__construct($collection);
        $this->factors = $factors;
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => $item->date,
                    'id' => $item->id,
                    'name' => $item->user->name. ' ' . $item->user->family,
                    'phone' => $item->user->phone,
                    'price' => number_format($item->price),
                    'total_price' => number_format($item->price + $this->setPrice($item)),
                    'resnumber' => $item->resnumber,
                    'user' => $item->callcenter ? $item->callcenter->family : 'سایت',
                    'wallet' => number_format($item->user->wallet),
                    'credit' => number_format($item->user->credit),
                    'representation' => $item->representation ? $item->representation->fa_name : 'م تهران',
                    'organization' => $item->organization ? $item->organization->fa_name : 'ندارد',
                    'confirm' => $item->confirm,
                    'status_factor' => $this->setStatus($item),
                    'date' => verta($item->created_at)->format('Y/m/d'),
                    'date_time' => $this->setDateTime($item),
                    'status' => $item->status,
                    'items' => $this->setItems($item),
                    'payment_type' => $item::TYPE[$item->type],
                    'per_detail' => $this->setPerDetail(),
                    'per_delete' => $this->setPerDelete(),
                ];
            }),
            'meta' =>  [
                'sum_price' => number_format($this->collection->sum('price')),
                'total_price' => number_format($this->factors->sum('price')),
            ]
        ];
    }

    public function setPerDetail()
    {
        return auth()->user()->isAbleTo('factor-detail');
    }

    public function setPerDelete()
    {
        return auth()->user()->isAbleTo('factor-delete');
    }
    /**
     * @param Factor $item
     * @return string
     */
    private function setItems(Factor $item)
    {
        $temp = [];
        foreach ($item->factorItems as $factor_item)
        {
            if(isset($factor_item->factoritemable))
            {
                array_push($temp ,$factor_item->factoritemable->title);
            }
            elseif(isset($factor_item->category))
            {
                array_push($temp ,$factor_item->category->title);
            }
        }
        isset($item->factorItem) ? array_push($temp , $item->factorItem->factoritemable->title) : '';
        isset($item->totalFactorItem) ? array_push($temp , TotalFactorItem::setFactorItemable($item->totalFactorItem)) : '';

        array_push($temp ,$item::TITLE[$item->title]);
        return implode(' - ' , $temp);
    }
    /**
     * @param Factor $item
     * @return int|float
     */
    private function setPrice(Factor $item)
    {
         $temp = 0;
        foreach ($item->factorItems as $item){
            $temp = $temp + $item->total_price - isset($item->propery->prepayment_price)?$item->propery->prepayment_price:0;
        }
        return $temp;
    }
    /**
     * @param Factor $item
     * @return string
     */
    private function setDateTime(Factor $item)
    {
        $temp = [];
        foreach ($item->factorItems as $item){
            array_push($temp ,  $item->date);
        }
        return implode(',' , $temp);
    }
}
