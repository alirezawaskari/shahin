<?php

namespace App\Services\V1\Admin;

use App\Models\GhorfeOnlineListProduct;

class ProductService
{
    public function createGhorfeProduct($data) : void
    {
        GhorfeOnlineListProduct::create([
            'user_id' => auth()->user()->id,
            'product_id' => $data['product_id'],
            'ghorfe_online_list_id' => ghorfe()->id,
            'purchase_type' => $data['purchase_type'],
            'pay_type' => $data['pay_type'],
            'tariff_id' => null,
            'guarantee' => $data['guarantee'],
        ]);
    }
    public function saveImageProduct($request,$product) : void
    {
        $images = $request->input('image_name_good');
        $currentImageCount = $product->getMedia('images')->count();
        if ($images  != null && $currentImageCount + count($images) > 4) {
            $firstImage = $product->getMedia('images')->first();

            if (!empty($firstImage)) {
                $firstImage->delete();
            }
        }
        if ($images)
        {
            foreach ($images as $image) {
                $imagePath = public_path($image['name']);
                $product->addMedia($imagePath)
                    ->toMediaCollection('images');
            }
        }
    }
}
