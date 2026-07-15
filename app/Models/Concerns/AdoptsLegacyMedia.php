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
