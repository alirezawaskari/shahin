<?php

namespace App\Http\Resources;

use App\Models\TotalFactorItem;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TotalFactorItemCollection extends ResourceCollection
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
                    'total_factor_item_id' => $item->id,
                    'name' => $item->factor ? $item->factor->user->name. ' ' . $item->factor->user->family: null,
                    'phone' => $item->factor ? $item->factor->user->phone: null,
                    'total_price' => number_format($item->total_price),
                    'credit' => number_format($item->credit),
                    'prepayment_price' => $item->property()->exists()? number_format($item->property->prepayment_price):null,
                    'installments_num' => $item->installments_num,
                    'installments_num_paid' => $item->totalFactorItemInstallments ? count($item->totalFactorItemInstallments->where('status' , 1)) : 0,
                    'installments_price' => $item->totalFactorItemInstallments->first() ? number_format($item->totalFactorItemInstallments->first()->price) : 0,
                    'extra_pay' => TotalFactorItem::getExtraPay($item),
                    'resnumber' => $item->resnumber,
                    'user' => $item->factor->callcenter ? $item->factor->callcenter->family : 'ندارد',
                    'representation' => $item->factor->representation ? $item->factor->representation->fa_name : 'م تهران',
                    'organization' => $item->factor->organization ? $item->factor->organization->fa_name : 'ندارد',
                    'confirm' => $item->confirm,
                    'date' => verta($item->created_at)->format('Y/m/d H:i:s'),
                    'date_time' => $item->date ? $item->date : 'ندارد',
                    'start_end_date' => $this->startEndDate($item),
                    'supporter' => ($item->supporter ? $item->supporter->family.' - ': 'ندارد - ').($item->file_number ? $item->file_number : 'ندارد'),
                    'type' => $this->setType($item),
                    'status' => TotalFactorItem::statusFactor($item->total_factor_item_status_id),
                    'status_mali' => TotalFactorItem::statusMali($item->status_mali),
                    'items' => $this->setItems($item),
                    'per_detail' => $this->setPerDetail(),
                    'per_edit' => $this->setPerEdit(),
                    'per_show' => $this->setPerShow(),
                    'has_payment' => TotalFactorItem::checkPayment($item),
                ];
            }),
			'meta' =>  [
                'sum_price' => number_format($this->collection->sum('total_price')),
				'total_price' => number_format(TotalFactorItem::filter()->sum('total_price')),
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
     * @param TotalFactorItem $item
     * @return string
     */
	private function setType(TotalFactorItem $item)
    {
        $temp = '';

        if ($item->type == '1') {
            $temp = 'اقساط - ' . ($item->total_price ? number_format($item->total_price) . 'ریال' : 'ندارد');
        } elseif ($item->type == '0') {
            $temp = 'نقد - ' . ($item->total_price ? number_format($item->total_price) . 'ریال ' : 'ندارد');
        } else {
            $temp = 'تعیین نشده - ' . ($item->total_price ? number_format($item->total_price) . 'ریال ' : 'ندارد');
        }

		return $temp;
    }
    /**
     * @param TotalFactorItem $item
     * @return string
     */
    private function setItems(TotalFactorItem $item)
    {
        return TotalFactorItem::setFactorItemable($item);
    }
    /**
     * @param TotalFactorItem $item
     * @return string
     */
    private function startEndDate(TotalFactorItem $item)
    {
        if(isset($item->totalFactorItemInstallments->first()->created_at))
        {
            $temp = verta($item->approval_date)->format('Y/m/d'). '-' .verta($item->totalFactorItemInstallments->last()->date)->format('Y/m/d');
            return $temp;
        }
    }
}
