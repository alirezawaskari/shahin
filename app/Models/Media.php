<?php

namespace App\Models;

use App\Helpers\MediaLibraryHelper;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Media extends SpatieMedia
{
    use HasVersion4Uuids;

    /**
     * Delete this media row and its file using native, cross-platform ops.
     *
     * Spatie's default deletion lists the media directory via
     * RecursiveDirectoryIterator (fails on Windows/Apache and on our shared
     * per-date folders). Routing every Media deletion through
     * MediaLibraryHelper::deleteMedia() (unlink + query-builder row delete)
     * keeps deletes working on every platform, while Spatie's own delete
     * cascade still decides WHEN media is purged — it preserves media on a
     * soft delete and only fires on a hard/force delete. This also fixes the
     * single-file collection auto-clear and temp cleanup on Windows.
     */
    public function delete()
    {
        MediaLibraryHelper::deleteMedia($this);

        return true;
    }
}
