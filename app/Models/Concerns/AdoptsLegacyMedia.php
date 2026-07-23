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
     * Single-image collections fed by legacy polymorphic `files` rows rather than by a
     * column: ['spatie_collection' => 'legacy_File_title'] (Check/Promissory store their
     * front/back photos as File(path, title) rows on the files() morphMany).
     *
     * Same contract as legacyMediaMap(): the File rows are READ-ONLY — never written,
     * never deleted — so the accessor fallback keeps serving them if adoption fails.
     */
    protected function legacyFileMap(): array
    {
        return [];
    }

    /** The legacy File row backing $collection, or null. READ-ONLY. */
    public function legacyFileRow(string $collection)
    {
        $title = $this->legacyFileMap()[$collection] ?? null;
        if (! $title || ! method_exists($this, 'files')) {
            return null;
        }

        return $this->files()
            ->where('title', $title)
            ->whereNotNull('path')
            ->where('path', '!=', '')
            ->first();
    }

    /** The legacy (pre-Spatie) url for a File-row-backed collection, or ''. */
    public function legacyFileUrl(string $collection): string
    {
        $row = $this->legacyFileRow($collection);

        return $row ? asset($row->path) : '';
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
     * Multi-image collections to adopt from the legacy `work_samples` morphMany (Service
     * portfolio). Counterpart to legacyGalleryMigrations() for the workSamples() relation —
     * unlimited, no type filter, no slot index. Each entry: ['collection' => '<name>'].
     * Default none.
     *
     * @return array<int, array{collection:string}>
     */
    protected function legacyWorkSampleMigrations(): array
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
     * @param string|null $field the form-field name this value sits under, stamped onto the
     *                    adopted media as a 'field' custom property. These columns are keyed
     *                    by form name (factor_values['Doctor's order'] => '/upload/x.jpg'),
     *                    which is the ONLY thing tying a file to its field — so an adopted
     *                    file must carry it, or documentUrls($field) could never find it.
     *                    A string key names the field; a list index inherits its parent's
     *                    (multi_file: ['other_image' => ['/a.jpg', '/b.jpg']]).
     */
    private function adoptLegacyPathsIn($value, string $collection, array &$adopted, ?string $field = null): void
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $this->adoptLegacyPathsIn($item, $collection, $adopted, is_string($key) ? $key : $field);
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

        $properties = $field !== null ? ['field' => $field] : [];

        if (MediaLibraryHelper::adoptLegacyFile($this, $collection, $value, $properties)) {
            $adopted[] = $value;
        }
    }

    /**
     * Urls for a form field's uploaded file(s): the Spatie media tagged with this field
     * name, PLUS any legacy path in the JSON column not already represented by one.
     *
     * A UNION, not a fallback, because multi_file fields are additive: with "media if any,
     * else legacy", the first new upload would win the media branch and silently hide every
     * previously saved image. A legacy path is dropped only once an adopted media carries it
     * as old_file_path — i.e. only when showing it would duplicate the same file.
     *
     * Media come first, so documentUrl() (which takes [0]) prefers the new system.
     * Always absolute urls — render {{ $url }} directly, never asset() (the old blades were
     * split between asset() and raw; that inconsistency dies here). asset() passes a value
     * that is already a full url through untouched.
     *
     * @param string $column the legacy JSON column to read (factor_values, contract_values)
     * @return array<string>
     */
    protected function mediaUrlsForField(string $collection, string $column, string $field): array
    {
        return array_column($this->mediaItemsForField($collection, $column, $field), 'url');
    }

    /**
     * Same union as mediaUrlsForField(), but each entry keeps its provenance:
     *   ['url' => string, 'legacy' => bool, 'media_id' => string|null]
     *
     * 'legacy' => true means the file is still only a path in the JSON column and has not
     * been adopted yet — the view shows the "با ذخیره به سیستم جدید منتقل می‌شود" hint and
     * no ✕ (there is no media id to delete by, and the next save would re-adopt it anyway).
     *
     * @return array<array{url:string,legacy:bool,media_id:?string}>
     */
    protected function mediaItemsForField(string $collection, string $column, string $field): array
    {
        $media = MediaLibraryHelper::existingMedia($this, $collection)
            ->filter(fn ($m) => $m->getCustomProperty('field') === $field);

        $items = $media->map(fn ($m) => [
            'url'      => $m->getUrl(),
            'legacy'   => false,
            'media_id' => $m->id,
        ])->values()->all();

        // legacy paths already adopted into this field's media — showing them again
        // would render the same file twice
        $adopted = $media->map(fn ($m) => $m->getCustomProperty('old_file_path'))
            ->filter()
            ->values()
            ->all();

        $legacy = $this->getAttribute($column)[$field] ?? null;
        foreach ((is_array($legacy) ? $legacy : [$legacy]) as $path) {
            if (! is_string($path) || $path === '' || in_array($path, $adopted, true)) {
                continue;
            }
            $items[] = ['url' => asset($path), 'legacy' => true, 'media_id' => null];
        }

        // same file can't appear twice (e.g. one path repeated in the column)
        $seen = [];
        return array_values(array_filter($items, function ($i) use (&$seen) {
            if (in_array($i['url'], $seen, true)) {
                return false;
            }
            $seen[] = $i['url'];

            return true;
        }));
    }

    /**
     * Flat-list counterpart to mediaUrlsForField(): the display urls for a legacyArrayMediaMap()
     * column that is a plain array of paths (not field-keyed) — e.g. PurchaseInvoice::factor_image.
     *
     * Returns the UNION of this collection's Spatie media urls (adopted or newly uploaded, served
     * from /storage) and any legacy path in the column not yet represented by an adopted media.
     * Mirrors the single-image accessors ("Spatie first, legacy fallback"): once a save adopts the
     * legacy files, the gallery shows the migrated /storage copies instead of the old /upload paths
     * — otherwise the column is never rewritten and the view would keep serving the legacy files.
     *
     * Media come first, so a caller taking [0] prefers the new system. De-duplicated by url.
     *
     * @return array<string>
     */
    public function arrayMediaUrls(string $collection, string $column): array
    {
        $media = MediaLibraryHelper::existingMedia($this, $collection);

        $urls = $media->map(fn ($m) => $m->getUrl())->values()->all();

        // legacy paths already adopted into this collection — showing them again would
        // render the same file twice (served once from /storage, once from /upload).
        $adopted = $media->map(fn ($m) => $m->getCustomProperty('old_file_path'))
            ->filter()
            ->values()
            ->all();

        $walk = function ($value) use (&$walk, &$urls, $adopted) {
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
            $urls[] = asset($value);
        };
        $walk($this->getAttribute($column));

        $seen = [];
        return array_values(array_filter($urls, function ($u) use (&$seen) {
            if (in_array($u, $seen, true)) {
                return false;
            }
            $seen[] = $u;

            return true;
        }));
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
        return $this->legacyMediaUrl($collection) === ''
            && $this->legacyFileUrl($collection) === '';
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
     * work_samples counterpart to hasLegacyGalleryRows(): TRUE when a legacyWorkSampleMigrations()
     * collection still has legacy `work_samples` rows to adopt (used to show the migrate hint on
     * the portfolio tab). Read-only.
     */
    public function hasLegacyWorkSampleRows(string $collection): bool
    {
        if (! method_exists($this, 'workSamples')) {
            return false;
        }

        foreach ($this->legacyWorkSampleMigrations() as $migration) {
            if (($migration['collection'] ?? null) !== $collection) {
                continue;
            }

            return $this->workSamples()->whereNotNull('image')->where('image', '!=', '')->exists();
        }

        return false;
    }

    /**
     * Display rows (title + absolute url) for the not-yet-adopted legacy work samples of a
     * collection, so the portfolio view can show the legacy images with a migrate notice
     * before a save adopts them. Empty once there is nothing left to migrate.
     *
     * @return array<int, array{title:?string, url:string}>
     */
    public function legacyWorkSampleItems(string $collection): array
    {
        if (! $this->hasLegacyWorkSampleRows($collection)) {
            return [];
        }

        return $this->workSamples()
            ->whereNotNull('image')->where('image', '!=', '')
            ->orderBy('id')->get()
            ->map(fn ($row) => ['title' => $row->title, 'url' => asset($row->image)])
            ->values()
            ->all();
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

    /**
     * This model's media in $collection, read FRESH from the database.
     *
     * Every idempotency check below ("do I already have this?") must not trust the cached
     * `media` relation. Two things stale it:
     *   - one request can fire saved() many times (Admin\FactorItemController::formData()
     *     updates once per beforeform — 10 saves in a row), and
     *   - adopting a file inside this very method does not refresh the relation.
     * With a stale relation every pass sees the pre-adoption state and adopts the same
     * legacy file again — 10 saves produced 10 copies of every image.
     */
    private function freshMedia(string $collection)
    {
        $this->unsetRelation('media');

        return $this->getMedia($collection);
    }

    /** Copy any not-yet-migrated legacy images into Spatie. Safe to call repeatedly. */
    public function adoptLegacyMedia(): void
    {
        // single images: legacy column -> single-file collection
        foreach ($this->legacyMediaMap() as $collection => $column) {
            if ($this->freshMedia($collection)->isNotEmpty()) {
                continue;
            }
            $legacy = $this->getRawOriginal($column);
            if (empty($legacy) || in_array($legacy, $this->legacyMediaPlaceholders(), true)) {
                continue;
            }
            MediaLibraryHelper::adoptLegacyFile($this, $collection, $legacy);
        }

        // single images: legacy polymorphic `files` row -> single-file collection
        foreach (array_keys($this->legacyFileMap()) as $collection) {
            if ($this->freshMedia($collection)->isNotEmpty()) {
                continue;
            }
            $row = $this->legacyFileRow($collection);
            if (! $row) {
                continue;
            }
            MediaLibraryHelper::adoptLegacyFile($this, $collection, $row->path);
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
            // again and again — so it MUST be read fresh (see freshMedia()).
            $adopted = $this->freshMedia($collection)
                ->map(fn ($m) => $m->getCustomProperty('old_file_path'))
                ->filter()
                ->values()
                ->all();

            $this->adoptLegacyPathsIn($value, $collection, $adopted);
        }

        // galleries: legacy `galleries` rows -> multi/slot collection
        if (method_exists($this, 'galleries')) {
            foreach ($this->legacyGalleryMigrations() as $migration) {
                $collection = $migration['collection'] ?? null;
                if (! $collection || $this->freshMedia($collection)->isNotEmpty()) {
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

        // work samples: legacy `work_samples` rows -> unlimited multi-image collection
        // (Service portfolio). Same shape as galleries but the workSamples() morphMany,
        // no type filter and no slot index.
        if (method_exists($this, 'workSamples')) {
            foreach ($this->legacyWorkSampleMigrations() as $migration) {
                $collection = $migration['collection'] ?? null;
                if (! $collection || $this->freshMedia($collection)->isNotEmpty()) {
                    continue;
                }
                foreach ($this->workSamples()->orderBy('id')->get() as $row) {
                    if (empty($row->image)) {
                        continue;
                    }
                    MediaLibraryHelper::adoptLegacyFile($this, $collection, $row->image, ['title' => $row->title]);
                }
            }
        }
    }
}
