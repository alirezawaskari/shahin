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

    /**
     * @param $factors
     * @param $collection
     */
    public function __construct($factors , $collection)
    {
        parent::__construct($collection);
        $this->factors = $factors;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => "date",
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
                    'date' => verta($item->created_at)->format('Y/m/d'),
                    'deleted_at' => verta($item->deleted_at)->format('Y/m/d'),
                    'delete_user' => $item->deleted_at ? $this->getDeleteUser($item) : null,
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

    /**
     * @return mixed
     */
    public function setPerDetail()
    {
        return auth()->user()->isAbleTo('factor-detail');
    }

    /**
     * @return mixed
     */
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
            $temp = $temp + $item->total_price - (isset($item->propery)?$item->propery->prepayment_price:0);
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
    /**
     * @param Factor $item
     * @return string
     */
    private function getDeleteUser(Factor $item)
    {
        $deleted_log = $item->audits()->where('event' , 'deleted')->latest()->first();
        $delete_user = 'ثبت نشده';
        if (!is_null($deleted_log)) {
            $delete_user = $deleted_log->user->name.' '.$deleted_log->user->family;
        }
        return $delete_user;
    }
}

