@extends('Admin.master')

@section('title')
    نقشه سایت
@endsection

@section('content')

    <div class="page-header">
        <div>
            <h3>نقشه سایت</h3>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item active">نقشه سایت</li>
                </ol>
            </nav>
        </div>
    </div>

    @unless($configured)
        <div class="alert alert-danger" role="alert">
            تنظیمات نقشه سایت (<code>config/sitemap.php</code>) بارگذاری نشده است، پس ساخت نقشه سایت غیرفعال است.
            به احتمال زیاد کش کانفیگ سرور قدیمی است؛ روی سرور
            <code>php artisan config:cache</code> (یا <code>config:clear</code>) را اجرا کنید و صفحه را دوباره باز کنید.
        </div>
    @endunless

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">ساخت و به‌روزرسانی</h4>

            <p class="mb-2">
                فایل‌ها در <code>{{ $path }}</code> نوشته می‌شوند و روی
                <a href="{{ rtrim($base_url, '/') }}" target="_blank" rel="noopener">{{ rtrim($base_url, '/') }}</a>
                سرو می‌شوند. آدرسی که باید در سرچ کنسول ثبت شود فایل ایندکس است.
            </p>

            <p class="text-muted">
                همین کار از خط فرمان هم انجام می‌شود:
                <code>php artisan sitemap:generate</code>
            </p>

            <form method="post" action="{{ route('sitemap.store') }}">
                @csrf

                <div class="row">
                    @foreach($sections as $section)
                        <div class="col-12 col-md-4 col-lg-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="sections[]"
                                       value="{{ $section->key }}"
                                       id="section_{{ $section->key }}"
                                    {{ $section->enabled ? '' : 'disabled' }}>
                                <label class="form-check-label" for="section_{{ $section->key }}">
                                    {{ $section->label }}
                                    @unless($section->enabled)
                                        <span class="badge bg-secondary">غیرفعال</span>
                                    @endunless
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-muted mt-2">
                    اگر هیچ بخشی تیک نخورد، همه‌ی بخش‌های فعال ساخته می‌شوند.
                    بخش‌های غیرفعال در <code>config/sitemap.php</code> خاموش شده‌اند.
                </p>

                <button type="submit" class="btn btn-primary" {{ $configured ? '' : 'disabled' }}>
                    ساخت / به‌روزرسانی نقشه سایت
                </button>
            </form>
        </div>
    </div>

    @if($report)
        <div class="card">
            <div class="card-body table-responsive">
                <h4 class="card-title">
                    نتیجه‌ی آخرین اجرا
                    <small class="text-muted">({{ $report['generated_at']->format('Y-m-d H:i:s') }})</small>
                </h4>

                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>بخش</th>
                        <th>وضعیت</th>
                        <th>تعداد آدرس</th>
                        <th>فایل‌ها</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($report['sections'] as $row)
                        <tr>
                            <td>
                                {{ $row['label'] }}
                                @if($row['note'] && $row['status'] !== 'written')
                                    <div class="text-muted small">{{ $row['note'] }}</div>
                                @endif
                            </td>
                            <td>
                                @switch($row['status'])
                                    @case('written')
                                        <span class="badge bg-success">ساخته شد</span>
                                        @break
                                    @case('empty')
                                        <span class="badge bg-warning text-dark">رکوردی نداشت</span>
                                        @break
                                    @case('disabled')
                                        <span class="badge bg-secondary">غیرفعال</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">انتخاب نشد</span>
                                @endswitch
                            </td>
                            <td>{{ number_format($row['urls']) }}</td>
                            <td>{{ implode('، ', $row['files']) ?: '—' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>مجموع</th>
                        <th></th>
                        <th>{{ number_format($report['total']) }}</th>
                        <th>{{ count($report['index']['files']) }} فایل در ایندکس</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body table-responsive">
            <h4 class="card-title">فایل‌های موجود</h4>

            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>بخش</th>
                    <th>فایل</th>
                    <th>وضعیت</th>
                    <th>حجم</th>
                    <th>آخرین ساخت</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>
                            {{ $row['label'] }}
                            @if(isset($row['enabled']) && ! $row['enabled'])
                                <span class="badge bg-secondary">غیرفعال</span>
                            @endif
                            @if($row['note'])
                                <div class="text-muted small">{{ $row['note'] }}</div>
                            @endif
                        </td>
                        <td>
                            @if($row['exists'])
                                <a href="{{ $row['url'] }}" target="_blank" rel="noopener">{{ $row['name'] }}</a>
                                @if(($row['parts'] ?? 0) > 1)
                                    <span class="badge bg-info text-dark">{{ $row['parts'] }} قسمت</span>
                                @endif
                            @else
                                {{ $row['name'] }}
                            @endif
                        </td>
                        <td>
                            @if($row['exists'])
                                <span class="badge bg-success">ساخته شده</span>
                            @else
                                <span class="badge bg-light text-dark">ساخته نشده</span>
                            @endif
                        </td>
                        <td>{{ $row['size'] !== null ? number_format($row['size'] / 1024, 1).' KB' : '—' }}</td>
                        <td>{{ $row['modified']?->format('Y-m-d H:i') ?? '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection
