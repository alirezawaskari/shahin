<?php

namespace App\Http\Resources;

use App\Models\Agency;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryArticleCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($item) {
                return [
                    'sort_by' => $item->created_at,
                    'id' => $item->id,
                    'title' => $item->title,
                    'similarGroupsCategories' => $item->similarGroupsCategories,
                    'similarGroupsServices' => $item->similarGroupsServices
                ];
            })
        ];
    }
}
