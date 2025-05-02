<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;


class SmsTemplateCollection extends ResourceCollection
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
                    'name' => $item->name,
                    'content' => $item->content,
                    'status' => $item->status,
                    'user' => $item->user ? $item->user->family : 'ثبت نشده',
                    'created_at' => verta($item->created_at)->format('Y/m/d'),
                ];
            }),
        ];
    }

}

