<?php

namespace App\Services;

use App\Models\Gallery;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GalleryService
{
    public function handle($request, $modelClass, $modelId, $categoryId, $model = null)
    {
        $manager = new ImageManager(new Driver());

        $folder = public_path('uploads/gallery');

        if (!file_exists($folder)) {
            mkdir($folder, 0777, true);
        }

        $process = function ($images, $type, $typeValue) use (
            $manager,
            $folder,
            $modelClass,
            $modelId,
            $categoryId,
            $model
        ) {
            foreach ($images ?? [] as $index => $item) {

                if (empty($item['file'])) continue;

                $image = $manager->read($item['file']);

                if ($type === 'desk') {
                    $image = $index === 4
                        ? $image->cover(900, 375)
                        : $image->cover(300, 176);
                } else {
                    $image = $index === 4
                        ? $image->cover(175, 420)
                        : $image->cover(115, 190);
                }

                $filename = now()->timestamp . '_' . $type . '_' . $index . '_' . uniqid() . '.jpg';
                $path = $folder . '/' . $filename;

                $image->toJpeg(80)->save($path);

                $key = $type . '_' . $index;

                // DB Gallery
                $gallery = Gallery::updateOrCreate(
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

                // optional relation model
                if ($model && method_exists($model, 'galleries')) {
                    $model->galleries()->updateOrCreate(
                        [
                            'type' => $typeValue,
                            'position_index' => $key,
                        ],
                        [
                            'title' => $item['file_title'] ?? '',
                            'image' => 'uploads/gallery/' . $filename,
                        ]
                    );
                }
            }
        };

        $process($request->deskImage ?? [], 'desk', 1);
        $process($request->mobileImage ?? [], 'mobile', 0);
    }
}