<?php

namespace App\Media;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator {
    public function getPath( Media $media ): string {
        return 'upload/' . $media->created_at->format( 'Y/m/d/' ) . '/';
    }

    public function getPathForConversions( Media $media ): string {
        return $this->getPath( $media ) . 'conversions/';
    }

    public function getPathForResponsiveImages( Media $media ): string {
        return $this->getPath( $media ) . 'responsive/';
    }
}
