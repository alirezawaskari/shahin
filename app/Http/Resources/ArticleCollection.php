<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ArticleCollection extends ResourceCollection
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
                    'title' => $item->title,
                    'status' => $item->status,
                    'image' => $item->image,
                    'user' => $item->user,
                    'category_article' => $item->category_article,
                    'created_at' => $item->created_at
                ];
            })
        ];
    }
}

