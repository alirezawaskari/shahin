<?php

namespace App\Services\V1\Admin;

use App\Models\Gallery;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GalleryService
{
    public function handle($request, $modelClass, $modelId, $categoryId, $model = null)
    {
        $manager = new ImageManager(new Driver());

        $process = function ($images, $type, $typeValue) use (
            $manager,
            $modelClass,
            $modelId,
            $categoryId,
            $model
        ) {

            foreach ($images ?? [] as $index => $item) {

                if (empty($item['file'])) continue;

                $file = $item['file'];

                /*
                |--------------------------------
                | 🟡 LEGACY IMAGE (Gallery DB)
                |--------------------------------
                */

                $image = $manager->read($file);

                $image = $type === 'desk'
                    ? ($index === 4 ? $image->cover(900, 375) : $image->cover(300, 176))
                    : ($index === 4 ? $image->cover(175, 420) : $image->cover(115, 190));

                $filename = now()->timestamp . '_' . $type . '_' . $index . '_' . uniqid() . '.jpg';

                $folder = public_path('uploads/gallery');

                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                $image->toJpeg(80)->save($folder . '/' . $filename);

                $key = $type . '_' . $index;

                Gallery::updateOrCreate(
                    [
                        'galleryable_id' => $modelId,
                        'galleryable_type' => $modelClass,
                        'type' => $typeValue,
                        'position_index' => $key,
                    ],
                    [
                        'title' => $item['file_title'] ?? '',
                        'image' => 'uploads/gallery/' . $filename,
                        'category_id' => $categoryId,
                    ]
                );

                /*
                |--------------------------------
                | 🟢 MEDIA LIBRARY (NEW SYSTEM)
                |--------------------------------
                */

                if ($model && method_exists($model, 'addMedia')) {

                    $model->addMedia($file)
                        ->withCustomProperties([
                            'title' => $item['file_title'] ?? null,
                            'category_id' => $categoryId,
                            'type' => $type,
                            'index' => $index,
                            'legacy_gallery_key' => $key,
                        ])
                        ->toMediaCollection($type === 'desk'
                            ? 'gallery_desk'
                            : 'gallery_mobile'
                        );
                }
            }
        };

        $process($request->deskImage ?? [], 'desk', 1);
        $process($request->mobileImage ?? [], 'mobile', 0);
    }
}