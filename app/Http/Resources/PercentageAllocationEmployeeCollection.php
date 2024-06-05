<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PercentageAllocationEmployeeCollection extends ResourceCollection
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
                    'name' =>  $item->user->name,
                    'family' =>  $item->user->family,
                    'phone' => $item->user->phone,
                    'type' => $item::TYPE[$item->type],
                    'factor_sender' => $item::FactorSender[$item->factor_sender],
					'item' => $item->percentageAllocationEmployeeable->title,
                    'start_date' => verta($item->start_allocation_date)->format('Y/m/d'),
                    'end_date' => verta($item->end_allocation_date)->format('Y/m/d'),
                    'percent' =>  $item->percent,
                    'agent' =>  $item->agent->family,
                    'date' => verta($item->created_at)->format('Y/m/d'),
                    'per_delete' => $this->setPerDelete(),
                ];
            })
        ];
    }

    /**
     * @return mixed
     */
    private function setPerDelete()
    {
        return auth()->user()->isAbleTo('percentageAllocationEmployee_delete');
    }
}
