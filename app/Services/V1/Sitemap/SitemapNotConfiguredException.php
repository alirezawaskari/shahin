<?php

namespace App\Services\V1\Sitemap;

use RuntimeException;

/**
 * config/sitemap.php بارگذاری نشده است (معمولا به خاطر کش قدیمی کانفیگ).
 * نگاه کنید به SitemapService::ensureConfigured().
 */
class SitemapNotConfiguredException extends RuntimeException
{
}
