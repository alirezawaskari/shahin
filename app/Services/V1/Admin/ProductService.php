<?php

namespace App\Services\V1\Admin;

use App\Helpers\MediaLibraryHelper;
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
        // $image['name'] is the temp media id (media_id) from the ajax uploader.
        // The item flagged as main (its radio submits an 'index') goes to the
        // single-file 'image' collection; the rest go to the multi 'gallery'.
        $images = $request->input('image_name_good');
        if (! $images) {
            return;
        }
        foreach ($images as $image) {
            if (empty($image['name'])) {
                continue;
            }
            if (isset($image['index'])) {
                MediaLibraryHelper::attachTo($product, $image['name'], 'image');
            } else {
                MediaLibraryHelper::attachTo($product, $image['name'], 'gallery', [
                    'title' => $request->input('good.name'),
                ]);
            }
        }
    }
}
