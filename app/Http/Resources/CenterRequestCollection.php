<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CenterRequestCollection extends ResourceCollection
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
                    'subject' => $item->subject,
                    'name' => $item->name,
                    'phone' => $item->phone,
                    'province' => $item->province ? $item->province->name : 'ندارد',
                    'category' => $item->center_category ? $item->center_category->title : 'ندارد',
                    'type' => $item::TYPE[$item->type],
                    'user' => $item->user ? $item->user->family : 'ندارد',
                    'representation' => $item->representation ? $item->representation->fa_name : 'نمایندگی کل',
                    'created_at' => verta($item->created_at)->format('Y/m/d H:m:s'),
                    'status' => $item::status($item->status),
                ];
            })
        ];
    }
}


