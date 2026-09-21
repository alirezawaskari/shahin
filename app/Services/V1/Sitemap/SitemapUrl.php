<?php

namespace App\Services\V1\Sitemap;

/**
 * یک تگ <url> در فایل نقشه سایت.
 *
 * $loc همیشه آدرس کامل و از قبل encode شده است؛ escape کردن برای XML کار
 * SitemapWriter است تا در یک جا و یک بار انجام شود.
 */
final readonly class SitemapUrl
{
    public function __construct(
        public string $loc,
        public ?string $lastmod = null,
        public ?string $changefreq = null,
        public ?string $priority = null,
    ) {
    }
}
