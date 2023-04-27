<?php

namespace App\Http\Resources;

use App\Models\Agency;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AgencyCollection extends ResourceCollection
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
                    'sort_by' => "updated_at",
                    'id' => $item->id,
                    'name' =>  $item->name,
                    'phone' => $item->phone,
                    'province' => $item->province ? $item->province : 'ثبت نشده',
                    'city' => $item->city ? $item->city : 'ثبت نشده',
                    'agency_type' => isset($item->agencyType) ? $item->agencyType->title : 'ندارد',
					'status' => Agency::status($item->status),
                    'created_at' => verta($item->created_at)->format('%d %B ، %Y - H:i:s'),
                    'updated_at' => verta($item->updated_at)->format('%d %B ، %Y - H:i:s'),
                ];
            })
        ];
    }

}
