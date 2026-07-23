<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignCollaboration extends Model
{
    protected $fillable = [
        'campaign_id',
        'name',
        'logo',
        'url',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * The Spatie media this row's logo was cut from. The logo itself is attached to the
     * PARENT campaign (`company_logos`), and `logo` only stores the resulting url — so
     * the media is found by matching that url back against the collection.
     */
    public function logoMedia()
    {
        if (! $this->logo || ! $this->campaign) {
            return null;
        }

        return $this->campaign->getMedia('company_logos')
            ->first(fn ($media) => $media->getUrl() === $this->logo);
    }

    /**
     * Logo url, but ONLY if the file physically exists — otherwise ''. `logo` is a
     * denormalized copy of the media url, so a deleted or moved file left it pointing
     * at nothing and the view rendered a broken image. Mirrors
     * MediaLibraryHelper::urlIfFileExists() so `@if($collab->logoUrl())` is a real check.
     */
    public function logoUrl(): string
    {
        $media = $this->logoMedia();

        if ($media) {
            try {
                $path = $media->getPath();
            } catch (\Throwable $e) {
                return '';
            }

            return ($path && is_file($path)) ? $media->getUrl() : '';
        }

        // No matching media (a row saved before the logo moved into the collection):
        // fall back to a plain disk check on the stored url.
        if (! $this->logo) {
            return '';
        }

        $relative = ltrim((string) parse_url($this->logo, PHP_URL_PATH), '/');

        return ($relative && is_file(public_path($relative))) ? $this->logo : '';
    }
}
