@extends('Admin.master')

@section('title', 'وضعیت زنده کمپین پیامکی #' . $campaign->id)

@section('css')
<style>
    .progress-bar-animated-custom {
        transition: width 0.4s ease-in-out;
    }
    .badge-status {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa fa-chart-line text-primary me-2"></i> وضعیت زنده کمپین پیامکی #{{ $campaign->id }}
            </h4>
            <p class="text-muted mb-0 small">
                کسب‌وکار: <strong>{{ $campaign->business?->title ?? 'سیستم' }}</strong> |
                ایجادکننده: <strong>{{ $campaign->creator ? ($campaign->creator->name . ' ' . $campaign->creator->family) : 'کاربر' }}</strong> |
                تاریخ ثبت: {{ $campaign->created_at->format('Y/m/d H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sms.bulk.campaigns') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-list me-1"></i> فهرست کمپین‌ها
            </a>
            <a href="{{ route('admin.sms.bulk.index') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus me-1"></i> ایجاد کمپین جدید
            </a>
        </div>
    </div>
</div>

<!-- Campaign Progress and Overview Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <span class="text-muted small">وضعیت فعلی:</span>
                <span id="campaignStatusBadge" class="badge-status ms-2
                    @if($campaign->status === 'completed') bg-success text-white
                    @elseif($campaign->status === 'sending') bg-primary text-white
                    @elseif($campaign->status === 'scheduled') bg-info text-white
                    @elseif($campaign->status === 'failed') bg-danger text-white
                    @else bg-secondary text-white @endif">
                    @if($campaign->status === 'completed') تکمیل شده
                    @elseif($campaign->status === 'sending') در حال ارسال
                    @elseif($campaign->status === 'scheduled') زمان‌بندی شده
                    @elseif($campaign->status === 'failed') ناموفق
                    @else پیش‌نویس @endif
                </span>
                <span id="livePulseIndicator" class="ms-2 small text-primary" style="{{ in_array($campaign->status, ['completed', 'failed']) ? 'display: none;' : '' }}">
                    <i class="fa fa-spinner fa-spin me-1"></i> در حال به‌روزرسانی زنده...
                </span>
            </div>
            <div class="text-muted small">
                شماره فرستنده: <strong class="text-dark">{{ $campaign->senderNumber?->number ?? 'نامشخص' }}</strong> |
                هزینه تخمینی: <strong class="text-primary">{{ number_format($campaign->cost_estimate) }} ریال</strong>
            </div>
        </div>

        @php
            $total = max(1, $campaign->total_recipients);
            $percentage = round(($campaign->sent_count / $total) * 100, 1);
        @endphp

        <!-- Progress Bar -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-1 small fw-bold">
                <span>پیشرفت ارسال:</span>
                <span id="progressPercentageText">{{ $percentage }}%</span>
            </div>
            <div class="progress" style="height: 18px; border-radius: 9px;">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary progress-bar-animated-custom"
                     role="progressbar"
                     style="width: {{ $percentage }}%;"
                     aria-valuenow="{{ $percentage }}"
                     aria-valuemin="0"
                     aria-valuemax="100">
                </div>
            </div>
        </div>

        <!-- Live Counters -->
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded border">
                    <div class="text-muted small">کل مخاطبان</div>
                    <div class="fs-4 fw-bold text-dark mt-1" id="cntTotal">{{ number_format($campaign->total_recipients) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                    <div class="text-success small fw-bold">ارسال موفق</div>
                    <div class="fs-4 fw-bold text-success mt-1" id="cntSuccess">{{ number_format($campaign->success_count) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-danger bg-opacity-10 rounded border border-danger">
                    <div class="text-danger small fw-bold">ارسال ناموفق</div>
                    <div class="fs-4 fw-bold text-danger mt-1" id="cntFailed">{{ number_format($campaign->failed_count) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-warning bg-opacity-10 rounded border border-warning">
                    <div class="text-warning small fw-bold">در صف ارسال (باقی‌مانده)</div>
                    <div class="fs-4 fw-bold text-warning mt-1" id="cntPending">{{ number_format($campaign->pending_count) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recipients Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa fa-users text-secondary me-1"></i> فهرست مخاطبان و وضعیت ارسال به هر گیرنده
        </h6>
        <span class="text-muted small">نمایش صفحه جاری</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>شماره همراه</th>
                    <th>نام مخاطب</th>
                    <th>وضعیت</th>
                    <th>شناسه رهگیری</th>
                    <th>توضیحات خطا</th>
                    <th>تاریخ ثبت</th>
                </tr>
            </thead>
            <tbody class="small" id="recipientsTableBody">
                @forelse($recipients as $idx => $r)
                    <tr>
                        <td>{{ $recipients->firstItem() + $idx }}</td>
                        <td class="font-monospace fw-bold">{{ $r->phone }}</td>
                        <td>{{ $r->user ? ($r->user->name . ' ' . $r->user->family) : '-' }}</td>
                        <td>
                            @if($r->status === 'sent')
                                <span class="badge bg-success">ارسال شد</span>
                            @elseif($r->status === 'failed')
                                <span class="badge bg-danger">ناموفق</span>
                            @else
                                <span class="badge bg-secondary">در انتظار</span>
                            @endif
                        </td>
                        <td class="font-monospace text-muted">{{ $r->tracking_id ?? '-' }}</td>
                        <td class="text-danger small">{{ $r->error_reason ?? '-' }}</td>
                        <td class="text-muted">{{ $r->created_at->format('Y/m/d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            هیچ رکوردی برای نمایش وجود ندارد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recipients->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $recipients->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const statusApiUrl = "{{ route('admin.sms.bulk.campaigns.status', $campaign) }}";
    let isPolling = @json(!in_array($campaign->status, ['completed', 'failed']));

    function pollStatus() {
        if (!isPolling) return;

        fetch(statusApiUrl, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const c = data.campaign;

                // Update Progress Bar
                document.getElementById('progressBar').style.width = c.progress_percentage + '%';
                document.getElementById('progressPercentageText').innerText = c.progress_percentage + '%';

                // Update Counters
                document.getElementById('cntTotal').innerText = Number(c.total_recipients).toLocaleString('fa-IR');
                document.getElementById('cntSuccess').innerText = Number(c.success_count).toLocaleString('fa-IR');
                document.getElementById('cntFailed').innerText = Number(c.failed_count).toLocaleString('fa-IR');
                document.getElementById('cntPending').innerText = Number(c.pending_count).toLocaleString('fa-IR');

                // Update Status Badge
                const badge = document.getElementById('campaignStatusBadge');
                badge.innerText = c.status_label;
                badge.className = 'badge-status ms-2 ' + (
                    c.status === 'completed' ? 'bg-success text-white' :
                    c.status === 'sending' ? 'bg-primary text-white' :
                    c.status === 'failed' ? 'bg-danger text-white' :
                    c.status === 'scheduled' ? 'bg-info text-white' : 'bg-secondary text-white'
                );

                if (c.is_finished) {
                    isPolling = false;
                    document.getElementById('livePulseIndicator').style.display = 'none';
                    // Reload page once after finishing to reflect updated recipients list
                    setTimeout(() => window.location.reload(), 1500);
                }
            }
        })
        .catch(err => console.error('Status poll error:', err));
    }

    if (isPolling) {
        setInterval(pollStatus, 2500);
    }
</script>
@endsection
