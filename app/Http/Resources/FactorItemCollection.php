<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\FactorItem;
use App\Models\TotalFactorItem;

class FactorItemCollection extends ResourceCollection
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
                    'total_factor_item_id' => $item->supervisor->total_factor_item_id ? $item->supervisor->total_factor_item_id : '',
                    'name' => $item->factor ? $item->factor->user->name. ' ' . $item->factor->user->family: null,
                    'phone' => $item->factor ? $item->factor->user->phone: null,
                    'total_price' => number_format($this->setTotalPrice($item)),
                    'prepayment_price' =>$item->property()->exists()? number_format($item->property->prepayment_price):null,
                    'installments_num' => $item->installments_num,
                    'installments_num_paid' => $item->FactorItemInstallments ? count($item->FactorItemInstallments->where('status' , true)):'',
                    'installments_price' => number_format($item->installments_price),
                    'resnumber' => $item->resnumber,
                    'user' => isset($item->factor->callcenter) ? $item->factor->callcenter->family : 'ندارد',
                    'organization' => isset($item->factor->organization) ? $item->factor->organization->fa_name : 'ندارد',
                    'confirm' => $item->confirm,
                    'date' => verta($item->created_at)->format('Y/m/d H:i:s'),
                    'date_time' => $item->date ? verta($item->date)->format('Y/m/d H:i:s') : 'ندارد',
                    'start_end_date' => $this->startEndDate($item),
                    'supporter' => ($item->supporter ? $item->supporter->family.' - ': 'ندارد - ').($item->file_number ? $item->file_number : 'ندارد'),
                    'type' => $this->setType($item),
                    'payment_type' => $item->type,
                    'status' => $item->status_factor,
                    'representation' => $item->representation ? $item->representation->representation->fa_name : 'م تهران',
                    'status_mali' => $item->status_mali,
                    'items' => $this->setItems($item),
                    'per_detail' => $this->setPerDetail(),
                    'per_edit' => $this->setPerEdit(),
                    'per_show' => $this->setPerShow(),
                    'has_payment' => FactorItem::checkPayment($item),
                    'credit_debt' => number_format($item->supervisor->type ? TotalFactorItem::calculateCredit($item->supervisor->totalFactorItem) : FactorItem::calculateCredit($item->supervisor)),
                ];
            }),
			'meta' =>  [
                'sum_price' => number_format($this->collection->sum('total_price')),
				'total_price' => number_format(FactorItem::filter()->sum('total_price')),
            ]
        ];
    }

    /**
     * @return mixed
     */
    private function setPerDetail()
    {
        return auth()->user()->isAbleTo('file-detail');
    }

    /**
     * @return mixed
     */
    private function setPerEdit()
    {
        return auth()->user()->isAbleTo('file-edit');
    }

    /**
     * @return mixed
     */
    private function setPerShow()
    {
        return auth()->user()->isAbleTo('file-show');
    }
    /**
     * @param FactorItem $item
     * @return string
     */
	private function setType(FactorItem $item)
    {
        $temp = '';
        $has_supervisor = $item->supervisor_id == $item->id ? false : true;
        $total_price = $item->total_price ? number_format($this->setTotalPrice($item)) . 'ریال' : 'ندارد';
        if($has_supervisor)
        {
            $supervisor = $item->supervisor->factor->user->name.' '.$item->supervisor->factor->user->family;
            if ($item->type == '1') {
                $temp = ' اقساط - دارای سرپرست - '.$supervisor.' - '.$total_price;
            } elseif ($item->type == '0') {
                $temp = ' نقد - دارای سرپرست -'.$supervisor.' - '.$total_price;
            } else {
                $temp = 'تعیین نشده'.' - '.$total_price;
            }
        }
        else
        {
            if ($item->type == '1') {
                $temp = 'اقساط - ' . $total_price;
            } elseif ($item->type == '0') {
                $temp = 'نقد - ' . $total_price;
            } else {
                $temp = 'تعیین نشده - ' . $total_price;
            }
        }
		return $temp;
    }
    /**
     * @param FactorItem $item
     * @return string
     */
    private function setItems(FactorItem $item)
    {
        if($item->total_factor_item_status_id)
        {
            return TotalFactorItem::setFactorItemable($item->totalFactorItem);
        }

        $temp = [];
        array_push($temp , isset($item->center) ? $item->factoritemable->title.' - '.$item->center->title : $item->factoritemable->title.' - تعیین نشده');

        return implode(',' , $temp);
    }
    /**
     * @param FactorItem $item
     * @return string
     */
    private function startEndDate(FactorItem $item)
    {
        if(isset($item->factorItemInstallments->first()->created_at))
        {
            $temp = verta($item->approval_date)->format('Y/m/d'). '-' .verta($item->factorItemInstallments->last()->date)->format('Y/m/d');
            return $temp;
        }
    }
     /**
     * @param FactorItem $item
     * @return float
     */
    private function setTotalPrice(FactorItem $item)
    {
        $total_price = $item->serviceTariffs()->exists() ? 0 : $item->total_price;
        $other_price = isset( $item->factor_value[ 'otherPrice' ] ) ? $item->factor_value[ 'otherPrice' ] : 0;
        $total_price = FactorItem::getTotalPrice($item , $total_price , $other_price);

        return $total_price;
    }
}

