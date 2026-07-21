<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The terms/rules PDF for a role category (Role::CATEGORIES key). Replaces the old
 * Context key/value storage; the file now lives in a Spatie single-file collection,
 * with the previous Context path kept in `file_path` as the legacy fallback.
 */
class EmployeeTerm extends Model implements HasMedia
{
    use InteractsWithMedia, \App\Models\Concerns\AdoptsLegacyMedia;

    protected $guarded = ['id'];

    /** Old Context key prefix these files used to live under (employee_terms_<category>). */
    const LEGACY_CONTEXT_PREFIX = 'employee_terms_';

    /** Touch-to-migrate: legacy path column -> Spatie single-file collection. */
    protected function legacyMediaMap(): array
    {
        return ['terms_file' => 'file_path'];
    }

    /**
     * The terms file for every Role::CATEGORIES key, shaped for the admin list AND the
     * master "please accept the terms" banner. Spatie url if adopted, else the legacy
     * file_path, else the value still sitting in the old Context store (untouched rows).
     *
     * @return array<string,array{role_category_title:string,role_category_fa_title:string,role_category_terms_file_path:?string,is_legacy:bool}>
     */
    public static function rolesWithTerms(): array
    {
        $terms = static::all()->keyBy('role_category');

        $out = [];
        foreach (\App\Models\Role::CATEGORIES as $key => $fa) {
            $term = $terms->get($key);

            if ($term) {
                $path   = $term->terms_file ?: null;
                $legacy = $term->mediaIsLegacy('terms_file');
            } else {
                // never migrated onto the model — still in the Context key/value store
                $context = \App\Models\Context::fetchByKey(self::LEGACY_CONTEXT_PREFIX . $key);
                $path    = $context->value ?? null;
                $legacy  = ! empty($path);
            }

            $out[$key] = [
                'role_category_title'           => $key,
                'role_category_fa_title'        => $fa,
                'role_category_terms_file_path' => $path,
                'is_legacy'                     => $legacy,
            ];
        }

        return $out;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('terms_file')->useDisk('public')->singleFile();
    }

    /** Terms file url: Spatie media if present, else the legacy `file_path` value. */
    public function getTermsFileAttribute(): string
    {
        return \App\Helpers\MediaLibraryHelper::urlIfFileExists($this, 'terms_file')
            ?: ($this->getRawOriginal('file_path') ? asset($this->getRawOriginal('file_path')) : '');
    }
}
