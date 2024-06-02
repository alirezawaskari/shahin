<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PercentageAllocationEmployeeReportCollection extends ResourceCollection
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
                    'type' => $item->resaons()->first() ? $item->resaons()->first()::TYPE[$item->resaons()->first()->type] : 'ثبت نشده',
					'item' => $item->resaons()->first() ? $item->resaons()->first()->percentageAllocationEmployeeable->title : 'ثبت نشده',
                    'price' => number_format($item->price),
                    'description' => $item->description,
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
