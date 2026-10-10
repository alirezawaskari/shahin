@extends('Admin.master')

@section('title', 'تاریخچه کمپین‌های پیامک انبوه')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa fa-history text-primary me-2"></i> تاریخچه کمپین‌های پیامک انبوه
            </h4>
            <p class="text-muted mb-0 small">
                کسب‌وکار: <strong>{{ $business?->title ?? 'همه' }}</strong>
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sms.bulk.index') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus me-1"></i> ایجاد کمپین جدید
            </a>
            <a href="{{ route('admin.sms.single.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-envelope me-1"></i> ارسال تکی
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <form method="GET" action="{{ route('admin.sms.bulk.campaigns') }}" class="row g-2 align-items-center">
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
                    <th>شماره فرستنده</th>
                    <th>قالب / پیش‌نمایش</th>
                    <th>سطح مورد نیاز</th>
                    <th>کل مخاطبان</th>
                    <th>موفق / ناموفق</th>
                    <th>وضعیت</th>
                    <th>هزینه تخمینی</th>
                    <th>تاریخ ثبت</th>
                    <th style="width: 100px;">عملیات</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($campaigns as $camp)
                    @php
                        $tot = max(1, $camp->total_recipients);
                        $perc = round(($camp->sent_count / $tot) * 100, 1);
                    @endphp
                    <tr>
                        <td class="fw-bold">#{{ $camp->id }}</td>
                        <td class="font-monospace">{{ $camp->senderNumber?->number ?? '-' }}</td>
                        <td>
                            @if($camp->template)
                                <span class="badge bg-light text-dark border">{{ $camp->template->title }}</span>
                            @else
                                <span class="text-muted">{{ Str::limit($camp->filters['message_preview'] ?? 'متن آزاد', 40) }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $camp->access_tier_required ?? 'نقره‌ای' }}</span>
                        </td>
                        <td class="fw-bold">{{ number_format($camp->total_recipients) }}</td>
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
                            <div class="progress mt-1" style="height: 4px; width: 80px;">
                                <div class="progress-bar bg-success" style="width: {{ $perc }}%;"></div>
                            </div>
                        </td>
                        <td class="text-primary">{{ number_format($camp->cost_estimate) }} ریال</td>
                        <td class="text-muted">{{ $camp->created_at->format('Y/m/d H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.sms.bulk.campaigns.show', $camp) }}" class="btn btn-outline-primary btn-sm px-2" title="مشاهده وضعیت زنده">
                                <i class="fa fa-eye"></i> مشاهده
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            هیچ کمپینی ثبت نشده است.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($campaigns->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>
@endsection
