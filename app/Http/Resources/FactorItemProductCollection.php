<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\FactorItemProduct;
use App\Models\Product;

class FactorItemProductCollection extends ResourceCollection
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
                    'id' => $item->id,
                    'image' => $item->productAttribute->product->image,
                    'name' => $item->productAttribute->product->name,
                    'product_code' => $item->productAttribute->product->id,
                    'diversity_code' => $item->productAttribute->id,
                    'product_size' => Product::productSize($item->productAttribute->product),
                    'seller_code' => $item->productAttribute->seller_code,
                    'order_count' => $item->count,
                    'stock' => $item->productAttribute->stock,
                    'created_at' => verta($item->created_at)->format('Y/m/d H:i:s'),
                    'sender_date' => verta($item->created_at)->format('Y/m/d H:i:s'),
                ];
            }),
			'meta' =>  [
                'sum_price' => number_format($this->collection->sum('price')),
				'total_price' => number_format(FactorItemProduct::filter()->sum('price')),
            ]
        ];
    }
}

