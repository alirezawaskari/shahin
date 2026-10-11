@extends('Admin.master')

@section('title', 'وضعیت زنده کمپین هوش مصنوعی #' . $campaign->id)

@section('css')
<style>
    .badge-status {
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa fa-android text-primary me-2"></i> وضعیت زنده کمپین هوش مصنوعی #{{ $campaign->id }}
        </h3>
        <p class="text-muted mb-0 small">
            کسب‌وکار: <strong>{{ $campaign->business?->title ?? 'سیستم' }}</strong> |
            ایجادکننده: <strong>{{ $campaign->creator ? ($campaign->creator->name . ' ' . $campaign->creator->family) : 'کاربر' }}</strong> |
            تاریخ ثبت: {{ $campaign->created_at->format('Y/m/d H:i') }}
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.sms.ai.campaigns') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-list me-1"></i> فهرست کمپین‌های AI
        </a>
        <a href="{{ route('admin.sms.ai.index') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> کمپین جدید
        </a>
    </div>
</div>

<!-- Overview Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <span class="text-muted small">وضعیت فعلی:</span>
                <span id="campaignStatusBadge" class="badge-status ms-2
                    @if($campaign->status === 'completed') bg-success text-white
                    @elseif($campaign->status === 'completed_with_errors') bg-warning text-dark
                    @elseif($campaign->status === 'sending') bg-primary text-white
                    @elseif($campaign->status === 'scheduled') bg-info text-white
                    @elseif($campaign->status === 'failed') bg-danger text-white
                    @else bg-secondary text-white @endif">
                    @if($campaign->status === 'completed') تکمیل شده
                    @elseif($campaign->status === 'completed_with_errors') تکمیل ناقص (موجودی)
                    @elseif($campaign->status === 'sending') در حال ارسال
                    @elseif($campaign->status === 'scheduled') زمان‌بندی شده
                    @elseif($campaign->status === 'failed') ناموفق
                    @else پیش‌نویس @endif
                </span>
            </div>
            <div>
                <span class="text-muted small">شماره فرستنده:</span>
                <code class="ms-1">{{ $campaign->senderNumber?->number ?? 'سیستمی' }}</code>
            </div>
        </div>

        @if($campaign->ai_goal_prompt)
            <div class="p-3 bg-light rounded border mb-3">
                <div class="small fw-bold text-muted mb-1"><i class="fa fa-lightbulb text-warning me-1"></i> هدف کمپین (AI Prompt):</div>
                <p class="mb-0 text-dark small">{{ $campaign->ai_goal_prompt }}</p>
            </div>
        @endif

        @php
            $total = max(1, $campaign->total_recipients);
            $pct = round(($campaign->sent_count / $total) * 100, 1);
        @endphp

        <!-- Progress bar -->
        <div class="mb-3">
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>پیشرفت ارسال:</span>
                <span id="progressText">{{ min(100, $pct) }}% ({{ $campaign->sent_count }} از {{ $campaign->total_recipients }})</span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 6px;">
                <div id="campaignProgressBar"
                    class="progress-bar @if($campaign->status === 'completed') bg-success @elseif($campaign->status === 'completed_with_errors') bg-warning @elseif($campaign->status === 'failed') bg-danger @else bg-primary progress-bar-striped progress-bar-animated @endif"
                    role="progressbar"
                    style="width: {{ min(100, $pct) }}%;">
                </div>
            </div>
        </div>

        <!-- 4 Stats Cards -->
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded border">
                    <div class="text-muted small">کل مخاطبان</div>
                    <div class="fs-5 fw-bold text-dark mt-1" id="statTotal">{{ number_format($campaign->total_recipients) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded border">
                    <div class="text-muted small">موفق</div>
                    <div class="fs-5 fw-bold text-success mt-1" id="statSuccess">{{ number_format($campaign->success_count) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded border">
                    <div class="text-muted small">ناموفق</div>
                    <div class="fs-5 fw-bold text-danger mt-1" id="statFailed">{{ number_format($campaign->failed_count) }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 bg-light rounded border">
                    <div class="text-muted small">در صف ارسال</div>
                    <div class="fs-5 fw-bold text-warning mt-1" id="statPending">{{ number_format($campaign->pending_count) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recipients List Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="fa fa-users me-1 text-primary"></i> فهرست مخاطبان کمپین
        </h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th style="width: 50px;">ردیف</th>
                    <th>نام مخاطب</th>
                    <th>شماره موبایل</th>
                    <th>وضعیت ارسال</th>
                    <th>کد رهگیری</th>
                    <th>خطا</th>
                </tr>
            </thead>
            <tbody class="small" id="recipientsTableBody">
                @forelse($recipients as $idx => $rec)
                    <tr>
                        <td>{{ $recipients->firstItem() + $idx }}</td>
                        <td>{{ $rec->user ? ($rec->user->name . ' ' . $rec->user->family) : 'مخاطب' }}</td>
                        <td><code>{{ $rec->phone }}</code></td>
                        <td>
                            @if($rec->status === 'sent')
                                <span class="badge bg-success">ارسال شده</span>
                            @elseif($rec->status === 'failed')
                                <span class="badge bg-danger">خطا</span>
                            @else
                                <span class="badge bg-warning text-dark">در انتظار</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $rec->tracking_id ?? '-' }}</small></td>
                        <td><small class="text-danger">{{ $rec->error_reason ?? '-' }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            هیچ مخاطبی یافت نشد.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recipients->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $recipients->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Live status polling. Same JSON endpoint as bulk campaigns (shared sms_campaigns table).
    const statusApiUrl = "{{ route('admin.sms.ai.campaigns.status', $campaign) }}";
    let isPolling = @json(!in_array($campaign->status, ['completed', 'completed_with_errors', 'failed']));

    function pollStatus() {
        if (!isPolling) return;

        fetch(statusApiUrl, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const c = data.campaign;
                const fmt = (n) => Number(n).toLocaleString('fa-IR');

                // Progress
                const bar = document.getElementById('campaignProgressBar');
                bar.style.width = c.progress_percentage + '%';
                document.getElementById('progressText').innerText =
                    c.progress_percentage + '% (' + fmt(c.sent_count) + ' از ' + fmt(c.total_recipients) + ')';

                // Counters
                document.getElementById('statTotal').innerText = fmt(c.total_recipients);
                document.getElementById('statSuccess').innerText = fmt(c.success_count);
                document.getElementById('statFailed').innerText = fmt(c.failed_count);
                document.getElementById('statPending').innerText = fmt(c.pending_count);

                // Status badge
                const badge = document.getElementById('campaignStatusBadge');
                badge.innerText = c.status_label;
                badge.className = 'badge-status ms-2 ' + (
                    c.status === 'completed' ? 'bg-success text-white' :
                    c.status === 'completed_with_errors' ? 'bg-warning text-dark' :
                    c.status === 'sending' ? 'bg-primary text-white' :
                    c.status === 'failed' ? 'bg-danger text-white' :
                    c.status === 'scheduled' ? 'bg-info text-white' : 'bg-secondary text-white'
                );

                if (c.is_finished) {
                    isPolling = false;
                    bar.classList.remove('progress-bar-striped', 'progress-bar-animated', 'bg-primary');
                    bar.classList.add(
                        c.status === 'completed' ? 'bg-success' :
                        c.status === 'completed_with_errors' ? 'bg-warning' : 'bg-danger'
                    );
                    // Reload once so the recipients table reflects the final state
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
