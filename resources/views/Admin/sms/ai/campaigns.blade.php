@extends('Admin.master')

@section('title', 'تاریخچه کمپین‌های پیامکی هوش مصنوعی (AI)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa fa-android text-primary me-2"></i> تاریخچه کمپین‌های پیامکی هوش مصنوعی
        </h3>
        <p class="text-muted mb-0 small">
            کسب‌وکار: <strong>{{ $business?->title ?? 'همه' }}</strong>
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.sms.ai.index') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> ایجاد کمپین هوش مصنوعی جدید
        </a>
        <a href="{{ route('admin.sms.bulk.campaigns') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-bullhorn me-1"></i> کمپین‌های انبوه دستی
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" action="{{ route('admin.sms.ai.campaigns') }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <label class="col-form-label col-form-label-sm small fw-bold">فیلتر وضعیت:</label>
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">همه وضعیت‌ها</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>تکمیل شده</option>
                    <option value="completed_with_errors" {{ request('status') === 'completed_with_errors' ? 'selected' : '' }}>تکمیل ناقص (موجودی)</option>
                    <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>در حال ارسال</option>
                    <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>زمان‌بندی شده</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ناموفق</option>
                </select>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th style="width: 60px;">شناسه</th>
                    <th>هدف کمپین (AI Prompt)</th>
                    <th>شماره فرستنده</th>
                    <th>پیش‌نمایش پیامک</th>
                    <th>کل مخاطبان</th>
                    <th>موفق / ناموفق</th>
                    <th>وضعیت</th>
                    <th>هزینه</th>
                    <th>تاریخ ثبت</th>
                    <th style="width: 100px;">عملیات</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($campaigns as $camp)
                    <tr>
                        <td class="fw-bold">#{{ $camp->id }}</td>
                        <td>
                            <span class="badge bg-purple text-dark border mb-1">
                                <i class="fa fa-android me-1"></i> AI
                            </span>
                            <div class="text-truncate text-dark fw-bold" style="max-width: 200px;" title="{{ $camp->ai_goal_prompt }}">
                                {{ $camp->ai_goal_prompt ?: '(بدون پرامپت)' }}
                            </div>
                        </td>
                        <td><code>{{ $camp->senderNumber?->number ?? 'سیستمی' }}</code></td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 180px;" title="{{ $camp->filters['message_preview'] ?? '' }}">
                                {{ $camp->filters['message_preview'] ?? '-' }}
                            </span>
                        </td>
                        <td><span class="fw-bold">{{ number_format($camp->total_recipients) }}</span></td>
                        <td>
                            <span class="text-success fw-bold">{{ number_format($camp->success_count) }}</span> /
                            <span class="text-danger fw-bold">{{ number_format($camp->failed_count) }}</span>
                        </td>
                        <td>
                            @if($camp->status === 'completed')
                                <span class="badge bg-success">تکمیل شده</span>
                            @elseif($camp->status === 'completed_with_errors')
                                <span class="badge bg-warning text-dark">تکمیل ناقص (موجودی)</span>
                            @elseif($camp->status === 'sending')
                                <span class="badge bg-primary">در حال ارسال</span>
                            @elseif($camp->status === 'scheduled')
                                <span class="badge bg-info">زمان‌بندی شده</span>
                            @elseif($camp->status === 'failed')
                                <span class="badge bg-danger">ناموفق</span>
                            @else
                                <span class="badge bg-secondary">پیش‌نویس</span>
                            @endif
                        </td>
                        <td>{{ number_format((float) $camp->cost_estimate) }} ریال</td>
                        <td>{{ \App\Helpers\Helper::jalaliDate($camp->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.sms.ai.campaigns.show', $camp) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="مشاهده وضعیت">
                                <i class="fa fa-eye"></i> جزئیات
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                            هنوز هیچ کمپین پیامکی با هوش مصنوعی ثبت نشده است.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campaigns->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>
@endsection
