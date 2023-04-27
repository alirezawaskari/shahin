<?php

namespace App\Http\Resources;


use App\Models\User;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Cache;

class DebtorCollection extends ResourceCollection
{
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
                    'name' => $item->name,
                    'family' => $item->family,
                    'phone' => $item->phone,
                    'province' => $item->province ? $item->province->name : 'ندارد',
                    'city' => $item->city ? $item->city->name : 'ندارد',
                    'melicode' => $item->melicode,
					'credit' => number_format($item->credit).' ریال ',
                    'token' => $item->token,
                    'representation' => $item->representation ? $item->representation->fa_name : 'م تهران',
                    'organization' => $item->organization ? $item->organization->fa_name : 'ندارد',
                    'date' => verta($item->created_at)->format('Y/m/d'),
                    'per_delete' => $this->setDeletePermission(),
                     'debt'=>User::fetchDebtItemCount($item),
                    'total_debts'=>User::fetchTotalDebts($item)
                ];
            })
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \int[][]
     */
    public function with($request)
    {
        return [
            'meta' => [
                'all_users_debt_except_penalty'=>Cache::has('total_debt_except_penalty')?cache('total_debt_except_penalty'):0
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function setDeletePermission()
    {
        return auth()->user()->isAbleTo('user-delete');
    }
}

