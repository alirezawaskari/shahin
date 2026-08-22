<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SeoContent extends Model
{
    protected $table = 'seo_contents';
     protected $fillable = [
        'seoable_id',
        'seoable_type',
        'seo_title',
        'meta_description',
        'robots',
        'canonical_url',
        'redirect_from',
        'redirect_to',
        'redirect_enabled',
        'og_title',
        'og_description',
        'og_site_name',
    ];
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * تبدیل نوع فیلدها
     */
    protected function casts(): array
    {
        return [
            'redirect_enabled' => 'boolean',
        ];
    }
}
