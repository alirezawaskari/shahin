<?php

namespace App\Services\V1\Admin;

use App\Models\SeoContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SeoContentService
{
    /**
     * @param Model $model
     * @param array $data
     * @return SeoContent
     */
    public function save(Model $model, array $data): SeoContent
    {
        $seoData = Arr::only($data, [
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
        ]);

        if (empty($seoData['redirect_enabled'])) {
            $seoData['redirect_from'] = null;
            $seoData['redirect_to'] = null;
        }

        $seoData['redirect_enabled'] = filter_var(
            $seoData['redirect_enabled'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        $seoData['robots'] = $seoData['robots'] ?? 'index, follow';

        return $model->seoContent()->updateOrCreate([], $seoData);
    }

    public function delete(Model $model): ?bool
    {
        return $model->seoContent()?->delete();
    }
}
