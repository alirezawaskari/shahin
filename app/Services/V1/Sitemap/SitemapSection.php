<?php

namespace App\Services\V1\Sitemap;

use Closure;
use Illuminate\Support\Collection;

/**
 * تعریف یک فایل نقشه سایت: کلید، نام فایل، عنوان فارسی و روشی که آدرس‌هایش را
 * می‌سازد. فهرست بخش‌ها در SitemapService::sections() ساخته می‌شود.
 */
final readonly class SitemapSection
{
    /**
     * @param string  $key   کلید بخش؛ همان کلیدی که در config/sitemap.php فعال/غیرفعال می‌شود
     * @param string  $file  نام فایل خروجی، بدون مسیر
     * @param string  $label عنوانی که در پنل و خروجی کنسول نشان داده می‌شود
     * @param Closure $urls  fn(): Collection<int, SitemapUrl>
     * @param bool    $enabled
     * @param string|null $note توضیحی که کنار بخش نمایش داده می‌شود (مثلا دلیل غیرفعال بودن)
     */
    public function __construct(
        public string $key,
        public string $file,
        public string $label,
        public Closure $urls,
        public bool $enabled = true,
        public ?string $note = null,
    ) {
    }

    /** @return Collection<int, SitemapUrl> */
    public function urls(): Collection
    {
        return ($this->urls)();
    }
}
