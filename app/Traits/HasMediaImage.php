<?php

namespace App\Traits;

use App\Models\MediaLibrary;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasMediaImage
{
    /**
     * Get the URL of a media image by its column name
     */
    public function getMediaUrl(string $foreignKeyColumn): ?string
    {
        $media = $this->getMediaFromRelation($foreignKeyColumn);

        if (!$media) {
            return null;
        }

        $url = $media->getFirstMediaUrl('images');
        if ($url) {
            return $url;
        }

        $datePath = $media->created_at->format('Y/m/d');
        $filePath = 'upload/' . $datePath . '/' . $media->title;

        if (Storage::disk('public')->exists($filePath)) {
            return asset('storage/' . $filePath);
        }

        return null;
    }

    /**
     * Load the MediaLibrary record via relationship or direct query
     */
    private function getMediaFromRelation(string $foreignKeyColumn): ?MediaLibrary
    {
        $relationMethod = $this->getMediaRelationMethod($foreignKeyColumn);

        if (method_exists($this, $relationMethod)) {
            return $this->{$relationMethod};
        }

        return MediaLibrary::find($this->{$foreignKeyColumn});
    }

    private function getMediaRelationMethod(string $foreignKey): string
    {
        $parts = explode('_id', $foreignKey)[0];
        return Str::camel($parts);
    }
}
