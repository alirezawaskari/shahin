<?php

namespace App\Models\Concerns;

use App\Helpers\MediaLibraryHelper;

/**
 * "Touch-to-migrate": on every save, any legacy image this model still references only
 * in a legacy column / legacy `galleries` row — and does NOT yet have in its Spatie
 * collection — is copied into Spatie. So simply opening a record and saving it (with no
 * re-upload) progressively moves its images onto the new system, one record at a time.
 *
 * Idempotent (skips a collection that already has media), NON-DESTRUCTIVE (legacy files
 * and columns are left in place, so the accessor/read fallbacks keep working), and fully
 * guarded (a broken legacy path or any failure is swallowed — it must never break a save).
 *
 * A consuming model opts in by overriding either hook:
 *   - legacyMediaMap(): ['spatie_collection' => 'legacy_column', ...]   (single images)
 *   - legacyGalleryMigrations(): [['collection'=>..,'type'=>?int,'slotted'=>bool,'max'=>?int], ...]
 */
trait AdoptsLegacyMedia
{
    public static function bootAdoptsLegacyMedia(): void
    {
        static::saved(function ($model) {
            try {
                $model->adoptLegacyMedia();
            } catch (\Throwable $e) {
                // never let progressive migration break the save that triggered it
                \Illuminate\Support\Facades\Log::warning(
                    'AdoptsLegacyMedia failed for ' . get_class($model) . '#' . $model->getKey() . ': ' . $e->getMessage()
                );
            }
        });
    }

    /** Single-image collections to adopt: ['spatie_collection' => 'legacy_column']. */
    protected function legacyMediaMap(): array
    {
        return [];
    }

    /**
     * Exact legacy column values that are NOT a real upload and must never be adopted:
     * shared placeholder/default images the model writes into the column itself (see
     * User::boot() stamping avatar = '/img/user.png' on every create). Adopting one
     * would copy the single shared default into every record's own collection and
     * permanently shadow the accessor's live default.
     */
    protected function legacyMediaPlaceholders(): array
    {
        return [];
    }

    /**
     * Array/JSON columns whose embedded file paths are adopted into a multi-file
     * collection: ['legacy_array_column' => 'spatie_collection'].
     *
     * Keyed by COLUMN (not collection) because several columns may feed one collection
     * (FactorItem: factor_values + factor_image -> factor_documents).
     *
     * The column itself is left UNTOUCHED — legacy paths stay exactly as stored and keep
     * serving from public/. This only copies the referenced files into Spatie so the media
     * also lives in the new system. The structure may be nested (factor_values mixes image
     * arrays with plain text and ids); a value is adopted ONLY if it resolves to a real
     * file under public/, so non-file values can never match.
     */
    protected function legacyArrayMediaMap(): array
    {
        return [];
    }

    /**
     * Gallery collections to adopt from the legacy `galleries` morphMany. Each entry:
     *   collection: Spatie collection name
     *   type:       legacy Gallery.type to pull (null = all rows)
     *   slotted:    true to write an 'index' custom property (0..) for slot editors
     *   max:        cap on rows to adopt (null = no cap)
     */
    protected function legacyGalleryMigrations(): array
    {
        return [];
    }

    /**
     * Walk a (possibly nested) array-column value and adopt every string that resolves to
     * a real legacy file under public/. Non-file values (text, ids, null) resolve to
     * nothing and are skipped, so no structural knowledge of the column is needed.
     *
     * @param array $adopted legacy paths already in the collection; appended to as we go,
     *                       so the same path repeated in one column is only adopted once.
     */
    private function adoptLegacyPathsIn($value, string $collection, array &$adopted): void
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->adoptLegacyPathsIn($item, $collection, $adopted);
            }
            return;
        }

        if (! is_string($value) || $value === '') {
            return;
        }

        // already a new-system (public disk) url, or already adopted from this legacy path
        if (str_contains($value, '/storage/') || in_array($value, $adopted, true)) {
            return;
        }

        if (MediaLibraryHelper::adoptLegacyFile($this, $collection, $value)) {
            $adopted[] = $value;
        }
    }

    /**
     * The legacy (pre-Spatie) url for a single-image collection, or '' if there isn't one.
     *
     * For admin previews: lets a view show the not-yet-adopted image without knowing each
     * model's accessor name (image_url on Category, image on Service, logo on Gateway...).
     * Reads the same legacyMediaMap()/placeholder rules adoption uses, so a preview can never
     * disagree with what a save would actually migrate.
     */
    public function legacyMediaUrl(string $collection): string
    {
        $column = $this->legacyMediaMap()[$collection] ?? null;
        if (! $column) {
            return '';
        }

        $legacy = $this->getRawOriginal($column);
        if (empty($legacy) || in_array($legacy, $this->legacyMediaPlaceholders(), true)) {
            return '';
        }

        return asset($legacy);
    }

    /**
     * Is this collection still being served from the LEGACY column rather than Spatie?
     *
     * True exactly when the read accessors fall back to the legacy file — i.e. there is no
     * usable Spatie media (none at all, or its file is missing on disk) but the legacy column
     * still holds a path. Lets a view show the "با ذخیره به سیستم جدید منتقل می‌شود" hint on
     * precisely the images the next save would migrate, and drop it once they are adopted.
     */
    public function mediaIsLegacy(string $collection): bool
    {
        return MediaLibraryHelper::urlIfFileExists($this, $collection) === ''
            && $this->legacyMediaUrl($collection) !== '';
    }

    /**
     * legacyFileMap() counterpart to mediaIsLegacy(): TRUE when a collection is still served
     * from a legacy polymorphic `files` row rather than Spatie — i.e. a save would adopt it.
     * mediaIsLegacy() reads legacyMediaMap() (columns); this reads legacyFileMap() (File rows),
     * used by Check/Promissory front/back. Read-only.
     */
    public function fileMediaIsLegacy(string $collection): bool
    {
        return MediaLibraryHelper::urlIfFileExists($this, $collection) === ''
            && $this->legacyFileUrl($collection) !== '';
    }

    /**
     * Array/JSON counterpart to mediaIsLegacy(): TRUE when a legacyArrayMediaMap() column
     * still holds at least one file path not yet adopted into its collection — i.e. a save
     * would migrate it. mediaIsLegacy() covers only single-file legacyMediaMap() fields, so
     * views over array columns (contact files, ticket attachments, invoice images) use this
     * to show the same "با ذخیره به سیستم جدید منتقل می‌شود" hint. Read-only.
     *
     * Mirrors adoptLegacyMedia()'s array branch: a path already adopted (its old_file_path is
     * stamped on a media row) or already a /storage/ url is NOT pending; anything else is.
     */
    public function arrayMediaHasLegacy(string $column): bool
    {
        $collection = $this->legacyArrayMediaMap()[$column] ?? null;
        if ($collection === null) {
            return false;
        }

        $adopted = $this->freshMedia($collection)
            ->map(fn ($m) => $m->getCustomProperty('old_file_path'))
            ->filter()
            ->all();

        $pending = false;
        $walk = function ($value) use (&$walk, &$pending, $adopted) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $walk($item);
                }
                return;
            }
            if (! is_string($value) || $value === '') {
                return;
            }
            if (str_contains($value, '/storage/') || in_array($value, $adopted, true)) {
                return; // already migrated, or already adopted from this exact legacy path
            }
            $pending = true;
        };
        $walk($this->getAttribute($column));

        return $pending;
    }

    /**
     * May the admin delete this collection's media outright (i.e. would a ✕ tell the truth)?
     *
     * FALSE while the legacy column still holds a path, because a delete cannot stick:
     *   1. the read accessors are "Spatie if any, else legacy", so the legacy file is served
     *      again the moment the media row is gone; and
     *   2. adoptLegacyMedia() below skips a collection only while it HAS media — delete it and
     *      the next save re-adopts the very same legacy file.
     * The ✕ would appear to work and then silently undo itself, which is worse than no ✕.
     * Replacing still works: these are singleFile() collections, so a new upload supersedes.
     *
     * TRUE for anything with nothing to fall back to — collections with no legacy column at
     * all (uploaded fresh on the new system), an empty column, or a placeholder value. Once a
     * record's legacy column is eventually cleared, the ✕ appears here automatically.
     */
    public function mediaIsRemovable(string $collection): bool
    {
        return $this->legacyMediaUrl($collection) === '';
    }

    /**
     * Does the legacy `galleries` table still hold row(s) that would feed this collection?
     * READ-ONLY — the galleries rows are never modified or deleted, only counted.
     */
    public function hasLegacyGalleryRows(string $collection): bool
    {
        if (! method_exists($this, 'galleries')) {
            return false;
        }

        foreach ($this->legacyGalleryMigrations() as $migration) {
            if (($migration['collection'] ?? null) !== $collection) {
                continue;
            }
            $query = $this->galleries();
            // mirror adoptLegacyMedia()'s filter exactly, so this can never disagree
            // with what a save would actually re-adopt
            if (array_key_exists('type', $migration) && $migration['type'] !== null) {
                $query->where('type', $migration['type']);
            }
            return $query->whereNotNull('image')->where('image', '!=', '')->exists();
        }

        return false;
    }

    /**
     * May the admin delete THIS media from a gallery/multi-file collection?
     *
     * Deleting one of several is fine: adoptLegacyMedia() skips a collection that still has
     * media, so the remaining images hold the door shut. Deleting the LAST one empties the
     * collection, and the next save re-adopts every legacy `galleries` row again — the delete
     * would silently undo itself. So the ✕ is hidden on the final image while legacy rows
     * still exist. Uploading more images restores it (count > 1).
     */
    public function galleryMediaIsRemovable(string $collection): bool
    {
        if ($this->getMedia($collection)->count() > 1) {
            return true;
        }

        return ! $this->hasLegacyGalleryRows($collection);
    }

    /** Copy any not-yet-migrated legacy images into Spatie. Safe to call repeatedly. */
    public function adoptLegacyMedia(): void
    {
        // single images: legacy column -> single-file collection
        foreach ($this->legacyMediaMap() as $collection => $column) {
            if ($this->getMedia($collection)->isNotEmpty()) {
                continue;
            }
            $legacy = $this->getRawOriginal($column);
            if (empty($legacy) || in_array($legacy, $this->legacyMediaPlaceholders(), true)) {
                continue;
            }
            MediaLibraryHelper::adoptLegacyFile($this, $collection, $legacy);
        }

        // array/JSON columns: adopt every legacy file they reference into the collection,
        // leaving the column itself untouched.
        foreach ($this->legacyArrayMediaMap() as $column => $collection) {
            $value = $this->getAttribute($column);
            if (! is_array($value) || ! $value) {
                continue;
            }
            // Legacy paths already adopted into this collection. Because the column is
            // never rewritten, this is what keeps re-saving from adopting the same file
            // again and again.
            $adopted = $this->getMedia($collection)
                ->map(fn ($m) => $m->getCustomProperty('old_file_path'))
                ->filter()
                ->values()
                ->all();

            $this->adoptLegacyPathsIn($value, $collection, $adopted);
        }

        // galleries: legacy `galleries` rows -> multi/slot collection
        if (! method_exists($this, 'galleries')) {
            return;
        }
        foreach ($this->legacyGalleryMigrations() as $migration) {
            $collection = $migration['collection'] ?? null;
            if (! $collection || $this->getMedia($collection)->isNotEmpty()) {
                continue;
            }

            $query = $this->galleries();
            if (array_key_exists('type', $migration) && $migration['type'] !== null) {
                $query->where('type', $migration['type']);
            }
            $rows = $query->orderBy('id')->get();
            if (! empty($migration['max'])) {
                $rows = $rows->take($migration['max']);
            }

            $index = 0;
            foreach ($rows as $row) {
                if (empty($row->image)) {
                    continue;
                }
                $props = ['title' => $row->title];
                if (! empty($migration['slotted'])) {
                    $props['index'] = $index;
                }
                MediaLibraryHelper::adoptLegacyFile($this, $collection, $row->image, $props);
                $index++;
            }
        }
    }
}
