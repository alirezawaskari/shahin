<?php

namespace App\Helpers;

use App\Models\Media;
use App\Models\MediaLibrary;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class MediaLibraryHelper
{
   
    /**
     * Move a temporary ajax upload (a MediaLibrary id from the hidden *_path
     * field) into a model's own Spatie media collection.
     *
     * The temp file lives on the private `local` disk and the model's media on
     * `public`, so this is a cross-disk copy — which works on both Linux and
     * Windows (same-disk copies fail on Windows). The temp record is removed
     * afterwards.
     *
     * @param \Spatie\MediaLibrary\HasMedia $model
     * @param string|null $tempId          MediaLibrary id
     * @param string $collection           target media collection on $model
     * @param array $customProperties      optional media custom properties (e.g. title, index)
     * @return \App\Models\Media|null       the created media, or null when nothing was attached
     */
    public static function attachTo(\Spatie\MediaLibrary\HasMedia $model, ?string $tempId, string $collection, array $customProperties = []): ?Media
    {
        if (empty($tempId)) {
            return null;
        }

        // $tempId is the Spatie Media row id (the "media_id" the ajax upload returns).
        $tempMedia = Media::find($tempId);
        if (!$tempMedia) {
            return null;
        }

        // The temp holder that owns this media (needed for temp cleanup). Guard that
        // it's a genuine ajax-temp upload (MediaLibrary/'images') so a caller can never
        // smuggle a model's permanent media id in here and have it moved/deleted.
        $temp = $tempMedia->model;
        if (!($temp instanceof MediaLibrary) || $tempMedia->collection_name !== 'images') {
            return null;
        }

        $attached = null;
        $absolutePath = $tempMedia->getPath();

        if ($absolutePath && is_file($absolutePath)) {
            // Optimize (cap the longest side to 2560px + recompress) so the stored file
            // stays under the size limit, keeping the original format and any transparency.
            $extension = pathinfo($tempMedia->file_name, PATHINFO_EXTENSION);
            $bytes = self::optimizeToUnderLimit($absolutePath, $extension);
            if ($bytes === false || $bytes === '') {
                \Illuminate\Support\Facades\Log::warning('MediaLibraryHelper::attachTo could not read temp file: ' . $absolutePath);
                return null; // keep the temp so nothing is lost
            }

            // Physically delete the file(s) a new upload replaces, so nothing is orphaned
            // on disk even when the user didn't press the remove button first. Native delete
            // because Spatie's own delete() deep-lists the media dir (fails on Windows/Apache).
            //   - single-file collection  -> clear the whole collection (image, icon, …)
            //   - slot-based collection    -> clear only the media in this slot (has an 'index')
            //   - append collection        -> keep everything (portfolio: genuinely additive)
            if (self::isSingleFileCollection($model, $collection)) {
                foreach ($model->getMedia($collection) as $old) {
                    self::deleteMedia($old);
                }
                $model->unsetRelation('media');
            } elseif (array_key_exists('index', $customProperties)) {
                $slotIndex = (int) $customProperties['index'];
                foreach ($model->getMedia($collection) as $old) {
                    if ((int) $old->getCustomProperty('index') === $slotIndex) {
                        self::deleteMedia($old);
                    }
                }
                $model->unsetRelation('media');
            }

            try {
                // Plain PHP read + addMediaFromString: avoids Flysystem's disk-to-disk
                // streaming which fails under Apache/Windows.
                $media = $model->addMediaFromString($bytes)
                    ->usingFileName($tempMedia->file_name)
                    ->withCustomProperties($customProperties)
                    ->toMediaCollection($collection);

                // Guarantee the file physically exists: Spatie's Flysystem write can
                // silently no-op on Apache/Windows (media row created, file missing),
                // so write it with native PHP ops as a fallback.
                $target = $media->getPath();
                if (!is_file($target)) {
                    \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($target));
                    \Illuminate\Support\Facades\File::put($target, $bytes);
                }
                $attached = $media;
            } catch (\Throwable $e) {
                // Keep the temp upload so the image is NOT lost — the user can retry.
                \Illuminate\Support\Facades\Log::warning('MediaLibraryHelper::attachTo attach failed: ' . $e->getMessage());
                return null;
            }
        }

        // Clean up the temp with native file/DB ops (Spatie's delete() deep-lists the
        // media directory via RecursiveDirectoryIterator, which fails on Windows/Apache).
        self::forceDeleteTemp($temp, $absolutePath);

        return $attached;
    }

    /**
     * Remove a temporary MediaLibrary + its media file without triggering Spatie's
     * filesystem cleanup (which does a recursive directory listing that breaks on Windows).
     */
    private static function forceDeleteTemp(MediaLibrary $temp, ?string $filePath = null): void
    {
        try {
            if ($filePath && is_file($filePath)) {
                @unlink($filePath);
            }
            Media::where('model_type', MediaLibrary::class)->where('model_id', $temp->id)->delete();
            MediaLibrary::where('id', $temp->id)->delete();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MediaLibraryHelper::forceDeleteTemp failed: ' . $e->getMessage());
        }
    }

    /** Longest side (px) an optimized image is allowed to keep. */
    public const MAX_IMAGE_DIMENSION = 2560;

    /** Target ceiling for a stored image file. */
    public const MAX_IMAGE_BYTES = 2 * 1024 * 1024; // 2 MB

    /**
     * Delete a single media (file + DB row) with native ops, cross-platform safe.
     * Spatie's $media->delete() deep-lists the media directory (RecursiveDirectoryIterator)
     * which fails under Windows/Apache, so we unlink the file and drop the row directly.
     */
    public static function deleteMedia($media): void
    {
        if (!$media) {
            return;
        }
        try {
            $path = $media->getPath();
            if ($path && is_file($path)) {
                @unlink($path);
            }
        } catch (\Throwable $e) {
            // ignore path resolution issues; still drop the row below
        }
        Media::where('id', $media->id)->delete();
    }

    /**
     * URL of the first media in $collection, but ONLY if its file physically exists
     * on disk — otherwise ''. Lets image accessors ($model->image, …) stay falsy for
     * a media row whose file is missing, so `@if($model->image)` in views is a real
     * "show the image" check (mirrors the old File::exists(public_path().$col) guards)
     * without every view having to stat the disk itself.
     */
    public static function urlIfFileExists(\Spatie\MediaLibrary\HasMedia $model, string $collection): string
    {
        $media = $model->getFirstMedia($collection);
        if (! $media) {
            return '';
        }
        try {
            $path = $media->getPath();
        } catch (\Throwable $e) {
            return '';
        }
        return ($path && is_file($path)) ? $media->getUrl() : '';
    }

    /**
     * The media in $collection whose files physically exist on disk (a media row
     * with a missing file is dropped). Use for galleries / multi-image collections
     * so a broken file never renders. Returns a re-indexed Spatie media collection,
     * so callers can ->map(fn($m) => $m->getUrl()) or iterate and call getUrl().
     */
    public static function existingMedia(\Spatie\MediaLibrary\HasMedia $model, string $collection)
    {
        return $model->getMedia($collection)->filter(function ($media) {
            try {
                $path = $media->getPath();
            } catch (\Throwable $e) {
                return false;
            }
            return $path && is_file($path);
        })->values();
    }

    /**
     * Optimize an uploaded image and attach it straight to $model's $collection via
     * Spatie (with the native File::put fallback for Windows/Apache). For DIRECT
     * uploads — e.g. CKEditor content images owned by the Ckeditor model — that don't
     * go through the temp (uploadFilesTemporarily) flow. Runs the same optimizer as
     * attachTo (resize + recompress, transparency-safe; svg/gif untouched).
     *
     * @return \App\Models\Media|null  the created media, or null on failure
     */
    public static function attachUpload(\Spatie\MediaLibrary\HasMedia $model, UploadedFile $file, string $collection, array $customProperties = [])
    {
        $extension = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $bytes = self::optimizeToUnderLimit($file->getRealPath(), $extension);
        if ($bytes === false || $bytes === '') {
            $bytes = @file_get_contents($file->getRealPath());
        }
        if ($bytes === false) {
            return null;
        }

        $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $extension;
        try {
            $media = $model->addMediaFromString($bytes)
                ->usingFileName($filename)
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collection);

            // guarantee the file exists (Spatie's Flysystem write can no-op on Apache/Windows)
            $target = $media->getPath();
            if (!is_file($target)) {
                File::ensureDirectoryExists(dirname($target));
                File::put($target, $bytes);
            }
            return $media;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MediaLibraryHelper::attachUpload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Adopt an EXISTING legacy image (referenced by a stored path/URL) into $model's
     * Spatie $collection — the "touch-to-migrate" primitive: on a plain save (no
     * re-upload) the old file is copied into Spatie so future reads use the new system.
     *
     * NON-DESTRUCTIVE: the legacy file on disk and the legacy column/row are left
     * untouched, so the accessor fallback keeps working even if this copy fails. Returns
     * null (a no-op) whenever the legacy path can't be resolved to a real file, so a
     * broken/dead legacy path can never break the save that triggered it.
     *
     * @param string|null $legacyPath value stored in the legacy column / Gallery.image
     *                    (e.g. "/upload/2026/5/x.jpg", "uploads/gallery/x.jpg", or a full
     *                    "http://host/upload/..." URL that asset() had produced)
     * @return \App\Models\Media|null  the created media, or null on no-op/failure
     */
    public static function adoptLegacyFile(\Spatie\MediaLibrary\HasMedia $model, string $collection, ?string $legacyPath, array $customProperties = [])
    {
        $abs = self::resolveLegacyPath($legacyPath);
        if ($abs === null || !is_file($abs)) {
            return null;
        }

        $bytes = @file_get_contents($abs);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $extension = strtolower(pathinfo($abs, PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $extension;

        // keep a pointer back to the legacy file this media was adopted from, so an
        // adopted row is always traceable to (and reconcilable with) its old source.
        $customProperties['old_file_path'] = $legacyPath;

        try {
            $media = $model->addMediaFromString($bytes)
                ->usingFileName($filename)
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collection);

            // guarantee the file exists (Spatie's Flysystem write can no-op on Apache/Windows)
            $target = $media->getPath();
            if (!is_file($target)) {
                File::ensureDirectoryExists(dirname($target));
                File::put($target, $bytes);
            }
            return $media;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MediaLibraryHelper::adoptLegacyFile failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Adopt an EXISTING Spatie media row into $model's $collection by copying its bytes.
     *
     * Counterpart to adoptLegacyFile() for legacy values that reference a media row rather
     * than a path under public/ — e.g. the ghorfe *_id MediaLibrary UUID FKs, whose files
     * live on the private 'local' disk and so cannot be resolved to a public path.
     *
     * The source media is left untouched: the legacy reference stays valid and re-running
     * this is harmless (adoptLegacyMedia() skips a collection that already has media).
     *
     * @return \App\Models\Media|null
     */
    public static function adoptMediaFile(\Spatie\MediaLibrary\HasMedia $model, string $collection, $sourceMedia, array $customProperties = [])
    {
        if (! $sourceMedia) {
            return null;
        }

        try {
            $abs = $sourceMedia->getPath();
        } catch (\Throwable $e) {
            return null;
        }
        if (! $abs || ! is_file($abs)) {
            return null;
        }

        $bytes = @file_get_contents($abs);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $extension = strtolower(pathinfo($sourceMedia->file_name, PATHINFO_EXTENSION)) ?: 'jpg';
        $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . $extension;

        // traceability, mirroring adoptLegacyFile()'s old_file_path
        $customProperties['old_media_id'] = $sourceMedia->id;

        try {
            $media = $model->addMediaFromString($bytes)
                ->usingFileName($filename)
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collection);

            // guarantee the file exists (Spatie's Flysystem write can no-op on Apache/Windows)
            $target = $media->getPath();
            if (!is_file($target)) {
                File::ensureDirectoryExists(dirname($target));
                File::put($target, $bytes);
            }
            return $media;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MediaLibraryHelper::adoptMediaFile failed: ' . $e->getMessage());
            return null;
        }
    }

    /** Resolve a stored legacy path/URL to an absolute file path under public/, or null. */
    private static function resolveLegacyPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }
        // a full URL may have been stored (e.g. asset()-wrapped) — keep only the path part
        if (preg_match('~^https?://~i', $path)) {
            $p = parse_url($path, PHP_URL_PATH);
            if (empty($p)) {
                return null;
            }
            $path = $p;
        }
        $rel = ltrim(rawurldecode($path), '/');
        if ($rel === '') {
            return null;
        }
        return public_path($rel);
    }

    /** Whether $collection is declared singleFile() on the model. */
    private static function isSingleFileCollection(\Spatie\MediaLibrary\HasMedia $model, string $collection): bool
    {
        foreach ($model->getRegisteredMediaCollections() as $registered) {
            if ($registered->name === $collection) {
                return (bool) $registered->singleFile;
            }
        }
        return false;
    }

    /**
     * Return the bytes to store for an uploaded image, resized/recompressed so the file
     * fits within MAX_IMAGE_BYTES and no side exceeds MAX_IMAGE_DIMENSION. The original
     * format is preserved (jpg/png/webp) and transparency is kept (GD alpha handling).
     * Vector (svg) and animated (gif) files are returned untouched.
     *
     * @return string|false  optimized bytes, original bytes when nothing to do, or false on read failure
     */
    private static function optimizeToUnderLimit(string $sourcePath, string $extension)
    {
        $ext = strtolower($extension);
        $original = @file_get_contents($sourcePath);
        if ($original === false) {
            return false;
        }

        // Only raster formats GD can safely re-encode; keep svg (vector) and gif (animation) as-is.
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $original;
        }

        $size = @getimagesize($sourcePath);
        $w = $size[0] ?? 0;
        $h = $size[1] ?? 0;
        $overSize = strlen($original) > self::MAX_IMAGE_BYTES;
        $overDim  = $w > self::MAX_IMAGE_DIMENSION || $h > self::MAX_IMAGE_DIMENSION;

        // already within both limits -> keep original bytes untouched (no quality loss)
        if (!$overSize && !$overDim) {
            return $original;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'opt_');
        @unlink($tmp);
        $tmp .= '.' . $ext;

        $isLossy  = in_array($ext, ['jpg', 'jpeg', 'webp'], true);
        $quality  = $isLossy ? 82 : 90;
        $dimSteps = [self::MAX_IMAGE_DIMENSION, 2200, 1900, 1600, 1300, 1100, 900];

        $best = null;
        try {
            foreach ($dimSteps as $maxSide) {
                $img = \Spatie\Image\Image::load($sourcePath);
                // cap the longest side, preserving aspect ratio (downscale only)
                if ($img->getWidth() >= $img->getHeight()) {
                    if ($img->getWidth() > $maxSide) {
                        $img->width($maxSide);
                    }
                } elseif ($img->getHeight() > $maxSide) {
                    $img->height($maxSide);
                }
                $img->quality($quality)->save($tmp);

                $bytes = @file_get_contents($tmp);
                if ($bytes !== false && $bytes !== '') {
                    $best = $bytes; // steps only get smaller, so keep the latest as best-so-far
                    if (strlen($bytes) <= self::MAX_IMAGE_BYTES) {
                        break; // largest variant that still fits under the ceiling
                    }
                }
                if ($isLossy && $quality > 55) {
                    $quality -= 7;
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('MediaLibraryHelper image optimize failed: ' . $e->getMessage());
        } finally {
            @unlink($tmp);
        }

        return ($best !== null && $best !== '') ? $best : $original;
    }

    /**
     * @param UploadedFile $image
     * @param int $userId
     * @return MediaLibrary
     * @throws \RuntimeException
     */
    public static function uploadImage(UploadedFile $image, ?int $userId = null): MediaLibrary
    {
        // include a unique suffix so rapid multi-uploads in the same second don't collide
        $filename = now()->format('Ymd_His') . '_' . uniqid() . '.' . strtolower($image->getClientOriginalExtension());

        $mediaLibrary = MediaLibrary::create([
            'title'   => $filename,
            'alt'     => $filename,
            'user_id' => $userId,
        ]);

        try {
            $mediaLibrary->addMedia($image)
                ->usingFileName($filename)
                ->preservingOriginal()
                ->toMediaCollection('images');
        } catch (\Exception $e) {
            $mediaLibrary->delete();
            throw new \RuntimeException('Failed to upload image: ' . $e->getMessage());
        }

        return $mediaLibrary;
    }
    // /**
    // * Summary of moveImage
    // * @param string $fileUrl
    // * @param int $userId
    // * @throws \RuntimeException
    // * @return MediaLibrary
    // */
    // public static function moveImage( string $fileUrl, int $userId ): MediaLibrary {
    //     $filename = pathinfo( parse_url( $fileUrl, PHP_URL_PATH ), PATHINFO_BASENAME );

    //     $mediaLibrary = MediaLibrary::create( [
    //         'title'   => $filename,
    //         'alt'     => $filename,
    //         'user_id' => $userId,
    //     ] );

    //     try {
    //         $mediaLibrary->addMedia( $fileUrl )
    //         ->usingFileName( $filename )
    //         ->toMediaCollectionOnDisk( 'images', 'file_lalecard', now()->format( 'Y/m/d' ) );
    //     } catch ( \Exception $e ) {
    //         $mediaLibrary->delete();
    //         throw new \RuntimeException( 'Failed to move image: ' . $e->getMessage() );
    //     }

    //     return $mediaLibrary;
    // }
}
