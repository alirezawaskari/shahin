<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\V1\Sitemap\SitemapService;
use Illuminate\Http\Request;

/**
 * صفحه‌ی «نقشه سایت» در پنل — همان کاری که php artisan sitemap:generate می‌کند،
 * با یک دکمه. هر دو مسیر از SitemapService::generate() استفاده می‌کنند تا خروجی
 * پنل و کنسول هیچ‌وقت از هم جدا نشوند.
 */
class SitemapController extends Controller
{
    public function __construct(private readonly SitemapService $sitemap)
    {
    }

    public function index()
    {
        $this->authorizeStaff();

        return view('Admin.sitemap.index', [
            'sections' => $this->sitemap->sections(),
            'rows'     => $this->sitemap->status(),
            'base_url' => $this->sitemap->baseUrl(),
            'path'     => rtrim(config('sitemap.path', public_path()), '/\\'),
            'report'   => session('sitemap_report'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeStaff();

        $keys = $this->sitemap->sections()->keys()->all();

        $data = $request->validate([
            'sections'   => ['nullable', 'array'],
            'sections.*' => ['string', 'in:'.implode(',', $keys)],
        ], [
            'sections.*.in' => 'بخش انتخاب‌شده معتبر نیست.',
        ]);

        // هیچ بخشی تیک نخورده یعنی همه — مثل اجرای بدون --section در کنسول.
        $only = array_values(array_unique($data['sections'] ?? []));

        $report = $this->sitemap->generate($only);

        alert()->success('عملیات موفق', sprintf(
            'نقشه سایت به‌روز شد: %d آدرس در %d فایل.',
            $report['total'],
            count($report['index']['files'])
        ));

        return redirect()->route('sitemap.index')->with('sitemap_report', $report);
    }

    /**
     * نقشه سایت مربوط به سایت اصلی است، نه یک کسب‌وکار، و فقط superAdmin آن را
     * در منو می‌بیند (MenuService::SUPER_ADMIN_ONLY). داشبورد برای admin و
     * ghorfeAdmin هم باز است، پس همان محدودیت باید روی خود مسیر هم باشد وگرنه
     * با تایپ کردن آدرس دور زده می‌شود.
     */
    private function authorizeStaff(): void
    {
        abort_unless(auth()->user()?->level === 'superAdmin', 403);
    }
}
