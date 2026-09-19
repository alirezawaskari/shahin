<?php

namespace App\Services\V1\Sitemap;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

/**
 * نوشتن و پاک کردن فایل‌های XML نقشه سایت.
 *
 * تنها جایی است که با دیسک کار می‌کند و تنها فایل‌هایی را دست می‌زند که خودش
 * ساخته است: «<نام فایل>» و نسخه‌های شماره‌دارش «<نام فایل بدون پسوند>-N.xml».
 * هیچ مسیر دیگری از public پاک نمی‌شود.
 */
class SitemapWriter
{
    /**
     * نوشتن آدرس‌های یک بخش.
     *
     * اگر تعداد آدرس‌ها از سقف بگذرد، به فایل‌های شماره‌دار شکسته می‌شود و فایل
     * تک‌نسخه‌ای قبلی پاک می‌گردد (و برعکس)، تا هیچ‌وقت دو نسخه از یک بخش
     * هم‌زمان روی دیسک نماند.
     *
     * @param  Collection<int, SitemapUrl> $urls
     * @return array<int, string> نام فایل‌هایی که نوشته شد
     */
    public function writeUrlSet(string $file, Collection $urls, int $maxPerFile): array
    {
        $chunks = $urls->chunk(max($maxPerFile, 1))->values();

        if ($chunks->count() <= 1) {
            $this->put($file, $this->renderUrlSet($chunks->first() ?? collect()));
            $this->deleteParts($file, keep: []);

            return [$file];
        }

        $written = [];

        foreach ($chunks as $index => $chunk) {
            $part = $this->partName($file, $index + 1);
            $this->put($part, $this->renderUrlSet($chunk));
            $written[] = $part;
        }

        $this->delete($file);
        $this->deleteParts($file, keep: $written);

        return $written;
    }

    /**
     * نوشتن فایل ایندکس.
     *
     * @param array<int, string> $files نام فایل‌های بخش‌ها
     */
    public function writeIndex(string $indexFile, array $files, string $baseUrl): void
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($files as $file) {
            $lines[] = '    <sitemap>';
            $lines[] = '        <loc>'.$this->escape(rtrim($baseUrl, '/').'/'.$file).'</loc>';
            $lines[] = '        <lastmod>'.$this->escape($this->lastModOf($file)).'</lastmod>';
            $lines[] = '    </sitemap>';
        }

        $lines[] = '</sitemapindex>';

        $this->put($indexFile, implode(PHP_EOL, $lines).PHP_EOL);
    }

    /**
     * پاک کردن فایل یک بخش و همه‌ی نسخه‌های شماره‌دارش — برای بخشی که غیرفعال
     * شده یا دیگر هیچ آدرسی ندارد، تا لینک‌های مرده در دسترس نمانند.
     *
     * @return array<int, string> نام فایل‌هایی که پاک شد
     */
    public function forget(string $file): array
    {
        $removed = $this->delete($file) ? [$file] : [];

        return array_merge($removed, $this->deleteParts($file, keep: []));
    }

    /**
     * وضعیت فعلی یک فایل روی دیسک، برای نمایش در پنل.
     *
     * @return array{name: string, exists: bool, size: int|null, modified: \Illuminate\Support\Carbon|null, url: string}
     */
    public function status(string $file, string $baseUrl): array
    {
        $path = $this->pathTo($file);
        $exists = File::exists($path);

        return [
            'name'     => $file,
            'exists'   => $exists,
            'size'     => $exists ? File::size($path) : null,
            'modified' => $exists ? $this->timestamp(File::lastModified($path)) : null,
            'url'      => rtrim($baseUrl, '/').'/'.$file,
        ];
    }

    /** همه‌ی فایل‌های موجود یک بخش (تک‌نسخه‌ای یا شماره‌دار). */
    public function existingFiles(string $file): array
    {
        $files = File::exists($this->pathTo($file)) ? [$file] : [];

        return array_merge($files, $this->partFiles($file));
    }

    /** @param Collection<int, SitemapUrl> $urls */
    private function renderUrlSet(Collection $urls): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
        ];

        foreach ($urls as $url) {
            $lines[] = '    <url>';
            $lines[] = '        <loc>'.$this->escape($url->loc).'</loc>';

            if ($url->lastmod) {
                $lines[] = '        <lastmod>'.$this->escape($url->lastmod).'</lastmod>';
            }

            if ($url->changefreq) {
                $lines[] = '        <changefreq>'.$this->escape($url->changefreq).'</changefreq>';
            }

            if ($url->priority) {
                $lines[] = '        <priority>'.$this->escape($url->priority).'</priority>';
            }

            $lines[] = '    </url>';
        }

        $lines[] = '</urlset>';

        return implode(PHP_EOL, $lines).PHP_EOL;
    }

    /**
     * نوشتن اتمیک: اول کنار فایل مقصد یک فایل موقت نوشته می‌شود و بعد جابه‌جا
     * می‌گردد، تا اگر خزنده دقیقا وسط ساخته‌شدن سراغ فایل آمد، نصفه‌ی یک XML را
     * تحویل نگیرد.
     */
    private function put(string $file, string $contents): void
    {
        $path = $this->pathTo($file);

        File::ensureDirectoryExists(dirname($path));

        $temp = $path.'.tmp';
        File::put($temp, $contents);

        // rename روی ویندوز اگر مقصد موجود باشد خطا می‌دهد، پس اول پاکش می‌کنیم.
        if (File::exists($path)) {
            File::delete($path);
        }

        File::move($temp, $path);
    }

    private function delete(string $file): bool
    {
        $path = $this->pathTo($file);

        return File::exists($path) && File::delete($path);
    }

    /**
     * پاک کردن نسخه‌های شماره‌دار یک بخش به جز آن‌هایی که همین حالا نوشته شدند —
     * تا اگر تعداد رکوردها کم شد، فایل‌های اضافه‌ی اجرای قبلی جا نمانند.
     *
     * @param  array<int, string> $keep
     * @return array<int, string>
     */
    private function deleteParts(string $file, array $keep): array
    {
        $removed = [];

        foreach ($this->partFiles($file) as $part) {
            if (in_array($part, $keep, true)) {
                continue;
            }

            if ($this->delete($part)) {
                $removed[] = $part;
            }
        }

        return $removed;
    }

    /** @return array<int, string> */
    private function partFiles(string $file): array
    {
        $base = pathinfo($file, PATHINFO_FILENAME);
        $paths = File::glob($this->pathTo($base.'-*.xml')) ?: [];

        return array_values(array_filter(
            array_map('basename', $paths),
            // فقط پسوند عددی؛ «sitemap-articles-1.xml» بله، «sitemap-articles-old.xml» نه.
            fn (string $name) => (bool) preg_match('/^'.preg_quote($base, '/').'-\d+\.xml$/', $name)
        ));
    }

    private function partName(string $file, int $index): string
    {
        return pathinfo($file, PATHINFO_FILENAME).'-'.$index.'.xml';
    }

    private function lastModOf(string $file): string
    {
        $path = $this->pathTo($file);

        return File::exists($path)
            ? $this->timestamp(File::lastModified($path))->toAtomString()
            : now()->toAtomString();
    }

    /** createFromTimestamp پیش‌فرض UTC است؛ منطقه‌ی زمانی برنامه را می‌دهیم تا
     *  lastmod ایندکس با lastmod رکوردها هم‌شکل بماند. */
    private function timestamp(int $unixTime): Carbon
    {
        return Carbon::createFromTimestamp($unixTime, config('app.timezone'));
    }

    private function pathTo(string $file): string
    {
        return rtrim(config('sitemap.path', public_path()), '/\\').DIRECTORY_SEPARATOR.$file;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
