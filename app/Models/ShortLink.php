<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    use HasFactory;
    protected $guarded=['id'];

    /**
     * @param string $original_link
     * @return ShortLink
     */
    public static function generate(string $original_link)
    {
        $baseUrl = self::getBaseUrl();
        $original_link = self::fixOriginalLink($original_link);
        do {
            $random = Str::random(4);
            $short_url = "{$baseUrl}/l/{$random}";
            $existing = ShortLink::where('short_link', $short_url)->first();
        } while ($existing);

        $shortLink = ShortLink::where('original_link', $original_link)->first();
        if (is_null($shortLink)) {
            $shortLink = new ShortLink;
            $shortLink->original_link = $original_link;
            $shortLink->short_link = $short_url;
            $shortLink->save();
        }
        return $shortLink;
    }
    /**
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\UrlGenerator|\Illuminate\Foundation\Application|string|string[]
     */
        public static function getBaseUrl()
    {
        $url = url('/');
        $baseUrl = preg_replace('#^https?://#i', '', $url);
        return $baseUrl;
    }
    /**
     * remove http:// and https:// from original link
     * @param Request $request
     * @return array|string|string[]|null
     */
    public static function fixOriginalLink(string $original_link)
    {
        $original_link = preg_replace('#^https?://#i', '', $original_link);
        return $original_link;
    }
}
