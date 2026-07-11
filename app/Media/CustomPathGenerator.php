<?php

namespace App\Media;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator {
    public function getPath( Media $media ): string {
        return $this->basePath( $media );
    }

    public function getPathForConversions( Media $media ): string {
        return $this->basePath( $media ) . 'conversions/';
    }

    public function getPathForResponsiveImages( Media $media ): string {
        return $this->basePath( $media ) . 'responsive/';
    }

    /**
     * Store each owning model's media under its own directory, e.g.
     * service/2026/07/09/, category/2026/07/09/, user/2026/07/09/.
     */
    protected function basePath( Media $media ): string {
        $model = $media->model_type
            ? Str::snake( class_basename( $media->model_type ) )
            : 'misc';

        return $model . '/' . ( $media->created_at ?? now() )->format( 'Y/m/d' ) . '/';
    }
}
