<?php

namespace App\Console\Commands;

use App\Services\V1\Sitemap\SitemapService;
use Illuminate\Console\Command;

/**
 * ساخت/به‌روزرسانی فایل‌های نقشه سایت از خط فرمان.
 *
 * همان کاری را می‌کند که دکمه‌ی صفحه‌ی «نقشه سایت» در پنل انجام می‌دهد؛ هر دو
 * SitemapService::generate() را صدا می‌زنند. برای اجرای زمان‌بندی‌شده کافی است
 * در App\Console\Kernel::schedule() یک ->command('sitemap:generate') اضافه شود.
 *
 *   php artisan sitemap:generate
 *   php artisan sitemap:generate --section=articles --section=services
 *   php artisan sitemap:generate --list
 */
class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate
                            {--section=* : فقط این بخش‌ها ساخته شوند (کلید بخش)}
                            {--list : فقط فهرست بخش‌ها و وضعیتشان را نشان بده}';

    protected $description = 'ساخت و به‌روزرسانی فایل‌های نقشه سایت در ریشه public';

    public function handle(SitemapService $sitemap): int
    {
        if ($this->option('list')) {
            return $this->showSections($sitemap);
        }

        try {
            $report = $sitemap->generate($this->option('section'));
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());
            $this->line('بخش‌های موجود: '.$sitemap->sections()->keys()->implode('، '));

            return self::FAILURE;
        }

        $this->table(
            ['بخش', 'کلید', 'وضعیت', 'آدرس‌ها', 'فایل‌ها'],
            collect($report['sections'])->map(fn (array $row) => [
                $row['label'],
                $row['key'],
                $this->statusLabel($row['status']),
                $row['urls'],
                implode(', ', $row['files']) ?: '—',
            ])->all()
        );

        foreach ($report['sections'] as $row) {
            if ($row['note'] && $row['status'] !== 'written') {
                $this->warn($row['label'].': '.$row['note']);
            }
        }

        $this->info(sprintf(
            'نقشه سایت ساخته شد: %d آدرس در %d فایل. ایندکس: %s',
            $report['total'],
            count($report['index']['files']),
            $report['index']['url'],
        ));

        $this->line('مسیر فایل‌ها: '.$report['path']);

        return self::SUCCESS;
    }

    private function showSections(SitemapService $sitemap): int
    {
        $this->table(
            ['کلید', 'بخش', 'فایل', 'فعال', 'ساخته شده', 'آخرین ساخت'],
            collect($sitemap->status())->map(fn (array $row) => [
                $row['key'],
                $row['label'],
                $row['name'],
                ($row['enabled'] ?? true) ? 'بله' : 'خیر',
                $row['exists'] ? 'بله' : 'خیر',
                $row['modified']?->format('Y-m-d H:i') ?? '—',
            ])->all()
        );

        return self::SUCCESS;
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'written'  => 'ساخته شد',
            'empty'    => 'رکوردی نداشت',
            'disabled' => 'غیرفعال',
            'skipped'  => 'انتخاب نشد',
            default    => $status,
        };
    }
}
