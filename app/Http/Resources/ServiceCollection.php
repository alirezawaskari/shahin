<?php

namespace App\Http\Resources;

use App\Helpers\Helper;
use App\Models\Category;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceCollection extends ResourceCollection
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
                    'slug' => $item->slug,
                    'title' => $item->title,
                    'image' => $item->image,
                    'category' => Category::findCategory($item->category_id,'title'),
                    'price' => number_format($item->price),
                    'purchase_type' => $item::PURCHASETYPE[$item->purchase_type],
                    'pay_type' => $item::PAYTYPE[$item->pay_type],
                    'show_status' => $item->isShow,
                    'status' => $item->status,
                ];
            })
        ];
    }
}
