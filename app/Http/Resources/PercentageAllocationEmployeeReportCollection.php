<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PercentageAllocationEmployeeReportCollection extends ResourceCollection
{

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public $percentage_allocation_employee_reports;

    /**
     * @param $percentage_allocation_employee_reports
     * @param $collection
     */
    public function __construct($percentage_allocation_employee_reports , $collection)
    {
        parent::__construct($collection);
        $this->percentage_allocation_employee_reports = $percentage_allocation_employee_reports;
    }
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
                    'price' => $item->walletTransaction ? number_format($item->walletTransaction->price) : $item->price,
                    'description' => $item->description,
                    'date' => verta($item->created_at)->format('Y/m/d'),
                    'per_delete' => $this->setPerDelete(),
                ];
            }),
            'meta' =>  [
                'sum_price' => number_format($this->collection->sum(function ($item) {
                    return $item->walletTransaction->price;
                })),
                'total_price' => number_format($this->percentage_allocation_employee_reports->sum(function ($item) {
                    return $item->walletTransaction->price;
                })),
            ]
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
