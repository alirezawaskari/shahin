<?php

namespace App\Services\V1\Sitemap;

use App\Models\Article;
use App\Models\News;
use App\Models\Organization;
use App\Models\Representation;
use App\Models\SeoContent;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\SingleWebPage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * ساخت و به‌روزرسانی فایل‌های نقشه سایتِ سایت اصلی (lalecard.ir).
 *
 * خروجی یک فایل ایندکس (sitemap.xml) است که به چند فایل موضوعی ارجاع می‌دهد:
 * صفحه‌های ثابت، خدمات، آیتم‌های خدمت، تک‌صفحه‌ها، مقاله‌ها، سازمان‌ها و
 * نمایندگی‌ها. تنظیمات — دامنه، فعال/غیرفعال بودن بخش‌ها و الگوی آدرس‌ها — در
 * config/sitemap.php است.
 *
 * دو مصرف‌کننده دارد و هر دو دقیقا همین متد generate() را صدا می‌زنند:
 *   - php artisan sitemap:generate
 *   - صفحه‌ی «نقشه سایت» در پنل (Admin\SitemapController)
 *
 * چند نکته که در فیلترها رعایت شده:
 *   - فقط رکوردهایی می‌آیند که کنترلرهای عمومی هم نشان می‌دهند (status / isShow)،
 *     وگرنه نقشه سایت پر از آدرس‌های 404 می‌شود.
 *   - scopeFilter مدل‌ها عمدا استفاده نشده: آن اسکوپ به request و کاربر وارد شده
 *     تکیه دارد و در کنسول اصلا کاربری وجود ندارد. فیلترها اینجا صریح نوشته شده‌اند.
 *   - اگر رکوردی seo_contents داشته باشد، robots=noindex آن را حذف می‌کند و
 *     canonical_url جای آدرس پیش‌فرض را می‌گیرد.
 */
class SitemapService
{
    public function __construct(private readonly SitemapWriter $writer)
    {
    }

    /**
     * فهرست بخش‌ها به ترتیبی که در ایندکس می‌آیند.
     *
     * @return Collection<string, SitemapSection>
     */
    public function sections(): Collection
    {
        return collect([
            new SitemapSection(
                key: 'misc',
                file: 'sitemap-misc.xml',
                label: 'صفحه‌های اصلی سایت',
                urls: fn () => $this->staticUrls(),
                enabled: $this->isEnabled('misc'),
            ),

            new SitemapSection(
                key: 'services',
                file: 'sitemap-services.xml',
                label: 'خدمات',
                urls: fn () => $this->modelUrls(
                    'services',
                    Service::query()->where('status', 1)->where('isShow', true),
                    changefreq: 'weekly',
                    priority: '0.8',
                ),
                enabled: $this->isEnabled('services'),
            ),

            new SitemapSection(
                key: 'service-items',
                file: 'sitemap-service-items.xml',
                label: 'آیتم‌های خدمت',
                urls: fn () => $this->modelUrls(
                    'service-items',
                    ServiceItem::query()->where('status', 1)->where('isShow', true),
                    changefreq: 'weekly',
                    priority: '0.7',
                ),
                enabled: $this->isEnabled('service-items'),
            ),

            new SitemapSection(
                key: 'single-web-pages',
                file: 'sitemap-single-web-pages.xml',
                label: 'تک‌صفحه‌ها',
                urls: fn () => $this->modelUrls(
                    'single-web-pages',
                    SingleWebPage::query()->where('status', true),
                    changefreq: 'monthly',
                    priority: '0.7',
                ),
                enabled: $this->isEnabled('single-web-pages'),
            ),

            new SitemapSection(
                key: 'articles',
                file: 'sitemap-articles.xml',
                label: 'مقاله‌ها',
                urls: fn () => $this->modelUrls(
                    'articles',
                    Article::query()->where('status', 1),
                    changefreq: 'monthly',
                    priority: '0.6',
                ),
                enabled: $this->isEnabled('articles'),
            ),

            new SitemapSection(
                key: 'news',
                file: 'sitemap-news.xml',
                label: 'اخبار',
                urls: fn () => $this->modelUrls(
                    'news',
                    News::query()->where('status', 1),
                    changefreq: 'daily',
                    priority: '0.6',
                ),
                enabled: $this->isEnabled('news'),
                note: 'مدل اخبار فقط در پنل مدیریت است و هنوز هیچ مسیر عمومی‌ای در routes/web.php ندارد؛'
                    .' پس از افزودن صفحه‌ی عمومی، کلید news را در config/sitemap.php فعال کنید.',
            ),

            new SitemapSection(
                key: 'organizations',
                file: 'sitemap-organizations.xml',
                label: 'سازمان‌ها',
                urls: fn () => $this->modelUrls(
                    'organizations',
                    Organization::query()->where('status', true),
                    changefreq: 'monthly',
                    priority: '0.5',
                ),
                enabled: $this->isEnabled('organizations'),
            ),

            new SitemapSection(
                key: 'representations',
                file: 'sitemap-representations.xml',
                label: 'نمایندگی‌ها',
                urls: fn () => $this->modelUrls(
                    'representations',
                    Representation::query()->where('status', true),
                    changefreq: 'monthly',
                    priority: '0.5',
                ),
                enabled: $this->isEnabled('representations'),
            ),
        ])->keyBy(fn (SitemapSection $section) => $section->key);
    }

    /**
     * ساخت/به‌روزرسانی فایل‌ها و برگرداندن گزارش.
     *
     * @param  array<int, string> $only فقط این بخش‌ها ساخته شوند؛ خالی یعنی همه
     * @return array{
     *     base_url: string,
     *     path: string,
     *     index: array{name: string, url: string, files: array<int, string>},
     *     sections: array<int, array{key: string, label: string, status: string, urls: int, files: array<int, string>, note: string|null}>,
     *     total: int,
     *     generated_at: \Illuminate\Support\Carbon
     * }
     *
     * @throws \InvalidArgumentException وقتی کلید ناشناخته‌ای خواسته شود
     */
    public function generate(array $only = []): array
    {
        $this->ensureConfigured();

        $sections = $this->sections();

        if ($only !== []) {
            $unknown = array_diff($only, $sections->keys()->all());

            if ($unknown !== []) {
                throw new \InvalidArgumentException('بخش ناشناخته: '.implode('، ', $unknown));
            }
        }

        $baseUrl = $this->baseUrl();
        $report = [];
        $indexFiles = [];
        $total = 0;

        foreach ($sections as $section) {
            // بخشی که در این اجرا انتخاب نشده دست‌نخورده می‌ماند، ولی فایل‌های
            // موجودش باید در ایندکس بیایند وگرنه با یک اجرای جزئی از ایندکس حذف می‌شوند.
            if ($only !== [] && ! in_array($section->key, $only, true)) {
                $existing = $section->enabled ? $this->writer->existingFiles($section->file) : [];
                $indexFiles = array_merge($indexFiles, $existing);

                $report[] = $this->line($section, 'skipped', 0, $existing);

                continue;
            }

            if (! $section->enabled) {
                $removed = $this->writer->forget($section->file);

                $report[] = $this->line($section, 'disabled', 0, $removed);

                continue;
            }

            $urls = $section->urls();

            if ($urls->isEmpty()) {
                $removed = $this->writer->forget($section->file);

                $report[] = $this->line($section, 'empty', 0, $removed);

                continue;
            }

            $files = $this->writer->writeUrlSet($section->file, $urls, $this->maxUrlsPerFile());
            $indexFiles = array_merge($indexFiles, $files);
            $total += $urls->count();

            $report[] = $this->line($section, 'written', $urls->count(), $files);
        }

        $indexFile = $this->indexFile();
        $this->writer->writeIndex($indexFile, $indexFiles, $baseUrl);

        return [
            'base_url'     => $baseUrl,
            'path'         => rtrim(config('sitemap.path', public_path()), '/\\'),
            'index'        => [
                'name'  => $indexFile,
                'url'   => rtrim($baseUrl, '/').'/'.$indexFile,
                'files' => $indexFiles,
            ],
            'sections'     => $report,
            'total'        => $total,
            'generated_at' => now(),
        ];
    }

    /**
     * وضعیت فعلی فایل‌ها روی دیسک، برای صفحه‌ی پنل.
     *
     * @return array<int, array<string, mixed>>
     */
    public function status(): array
    {
        $baseUrl = $this->baseUrl();

        $rows = [$this->writer->status($this->indexFile(), $baseUrl) + [
            'key'   => 'index',
            'label' => 'فایل ایندکس',
            'note'  => null,
        ]];

        foreach ($this->sections() as $section) {
            $files = $this->writer->existingFiles($section->file);

            // بخشی که به چند فایل شکسته شده، به جای فایل تک‌نسخه‌ای وضعیت
            // اولین قطعه‌اش را نشان می‌دهد تا سطر «ساخته نشده» گمراه‌کننده نباشد.
            $shown = $files[0] ?? $section->file;

            $rows[] = $this->writer->status($shown, $baseUrl) + [
                'key'     => $section->key,
                'label'   => $section->label,
                'enabled' => $section->enabled,
                'note'    => $section->note,
                'parts'   => count($files),
            ];
        }

        return $rows;
    }

    /**
     * اگر config/sitemap.php بارگذاری نشده باشد، ساخت نقشه سایت متوقف می‌شود.
     *
     * رایج‌ترین دلیلش کش کانفیگ است: اگر روی سرور `php artisan config:cache`
     * قبل از اضافه شدن این فایل اجرا شده باشد، لاراول فقط bootstrap/cache/config.php
     * را می‌خواند و config('sitemap') خالی است. بدون این بررسی همه‌ی بخش‌ها «خالی»
     * دیده می‌شدند، فایل‌های موجود پاک می‌شدند و یک sitemap.xml خالی جایشان
     * می‌نشست — یعنی نقشه سایت سالم روی سایت اصلی با یک فایل تهی عوض می‌شد.
     *
     * @throws SitemapNotConfiguredException
     */
    public function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new SitemapNotConfiguredException(
                'تنظیمات نقشه سایت (config/sitemap.php) بارگذاری نشده است. به احتمال زیاد کش کانفیگ'
                .' قدیمی است؛ روی سرور «php artisan config:cache» (یا config:clear) را اجرا کنید و دوباره بسازید.'
            );
        }
    }

    public function isConfigured(): bool
    {
        return is_array(config('sitemap.sections'))
            && is_array(config('sitemap.model_paths'))
            && is_array(config('sitemap.static_pages'));
    }

    public function baseUrl(): string
    {
        return rtrim((string) (config('sitemap.base_url') ?: config('app.url')), '/');
    }

    public function indexFile(): string
    {
        return (string) config('sitemap.index_file', 'sitemap.xml');
    }

    private function maxUrlsPerFile(): int
    {
        return max(1, (int) config('sitemap.max_urls_per_file', 50000));
    }

    private function isEnabled(string $key): bool
    {
        return (bool) config('sitemap.sections.'.$key, true);
    }

    /** @return Collection<int, SitemapUrl> */
    private function staticUrls(): Collection
    {
        return collect(config('sitemap.static_pages', []))
            ->filter(fn ($page) => filled($page['path'] ?? null))
            ->map(fn (array $page) => new SitemapUrl(
                loc: $this->url($page['path']),
                lastmod: null,
                changefreq: $page['changefreq'] ?? null,
                priority: $page['priority'] ?? null,
            ))
            ->values();
    }

    /**
     * آدرس‌های یک مدل.
     *
     * رکوردها با chunkById خوانده می‌شوند تا جدول‌های بزرگ (مقاله‌ها) یک‌جا در
     * حافظه نیایند، و seo_contents هر دسته با یک کوئری گرفته می‌شود نه رکورد به رکورد.
     *
     * @return Collection<int, SitemapUrl>
     */
    private function modelUrls(
        string $sectionKey,
        Builder $query,
        ?string $changefreq = null,
        ?string $priority = null,
    ): Collection {
        $pattern = config('sitemap.model_paths.'.$sectionKey);

        if (blank($pattern)) {
            return collect();
        }

        $urls = collect();

        $query->clone()->chunkById(500, function (Collection $models) use ($urls, $pattern, $changefreq, $priority) {
            $seo = $this->seoOverrides($models);

            foreach ($models as $model) {
                if (blank($model->slug)) {
                    continue;
                }

                $override = $seo[$model->getKey()] ?? null;

                if ($override && $this->isNoIndex($override->robots)) {
                    continue;
                }

                $loc = filled($override?->canonical_url)
                    ? $override->canonical_url
                    : $this->url(str_replace(':slug', $model->slug, $pattern));

                $urls->push(new SitemapUrl($loc, $this->lastModOf($model), $changefreq, $priority));
            }
        });

        return $urls;
    }

    /**
     * ردیف‌های seo_contents یک دسته رکورد، کلیدخورده با شناسه‌ی رکورد.
     *
     * @param  Collection<int, Model> $models
     * @return array<int, SeoContent>
     */
    private function seoOverrides(Collection $models): array
    {
        if ($models->isEmpty()) {
            return [];
        }

        return SeoContent::query()
            ->where('seoable_type', $models->first()->getMorphClass())
            ->whereIn('seoable_id', $models->modelKeys())
            ->get()
            ->keyBy('seoable_id')
            ->all();
    }

    /** مقدار robots یک رشته‌ی آزاد است («noindex, follow» و مانند آن). */
    private function isNoIndex(?string $robots): bool
    {
        return $robots !== null && str_contains(strtolower($robots), 'noindex');
    }

    /**
     * lastmod رکورد. service_items اصلا ستون تاریخ ندارد ($timestamps = false)
     * و null برمی‌گرداند؛ در آن صورت تگ lastmod نوشته نمی‌شود، که مجاز است.
     */
    private function lastModOf(Model $model): ?string
    {
        $date = $model->updated_at ?? $model->created_at;

        return $date?->toAtomString();
    }

    /**
     * ساخت آدرس کامل از یک مسیر.
     *
     * بخش‌های مسیر encode می‌شوند چون اسلاگ بعضی مدل‌ها (سازمان‌ها از fa_name)
     * فارسی است و loc باید آدرس معتبر باشد.
     */
    private function url(string $path): string
    {
        $segments = array_filter(explode('/', trim($path, '/')), 'strlen');

        $encoded = implode('/', array_map('rawurlencode', $segments));

        return $this->baseUrl().'/'.$encoded;
    }

    /**
     * @param  array<int, string> $files
     * @return array<string, mixed>
     */
    private function line(SitemapSection $section, string $status, int $urls, array $files): array
    {
        return [
            'key'    => $section->key,
            'label'  => $section->label,
            'status' => $status,
            'urls'   => $urls,
            'files'  => $files,
            'note'   => $section->note,
        ];
    }
}
