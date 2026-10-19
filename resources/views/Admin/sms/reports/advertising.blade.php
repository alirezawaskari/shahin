@extends('Admin.master')

@section('title', 'گزارش پیامک‌ها و کمپین‌های تبلیغاتی')

@section('css')
<style>
    .badge-soft-primary {
        background-color: rgba(63, 81, 181, 0.12) !important;
        color: #3f51b5 !important;
        border: none;
    }
    .badge-soft-success {
        background-color: rgba(10, 207, 151, 0.12) !important;
        color: #0acf97 !important;
        border: none;
    }
    .badge-soft-danger {
        background-color: rgba(242, 18, 94, 0.12) !important;
        color: #f2125e !important;
        border: none;
    }
    .badge-soft-warning {
        background-color: rgba(255, 131, 0, 0.12) !important;
        color: #ff8300 !important;
        border: none;
    }
    .badge-soft-info {
        background-color: rgba(0, 188, 212, 0.12) !important;
        color: #0097a7 !important;
        border: none;
    }
    .badge-soft-secondary {
        background-color: rgba(108, 117, 125, 0.12) !important;
        color: #495057 !important;
        border: none;
    }

    .kpi-icon-badge {
        width: 56px;
        height: 56px;
        min-width: 56px;
        min-height: 56px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .kpi-icon-badge i {
        font-size: 1.5rem;
        line-height: 1;
    }

    .table td, .table th {
        line-height: 1.8 !important;
        padding: 12px 10px !important;
        vertical-align: middle;
    }
    .sms-content-cell {
        max-width: 320px;
        line-height: 1.8 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
        padding-bottom: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">گزارش پیامک‌ها و کمپین‌های تبلیغاتی</h3>
            <p class="text-muted small mb-0">تحلیل عملکرد بر اساس هر خط فرستنده، کمپین‌های انبوه و تکی و نرخ موفقیت ارسال‌ها</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.reports.advertising.index') }}" class="d-flex flex-wrap gap-2 m-0">
                    <select name="business_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($all_businesses as $b)
                            <option value="{{ $b->id }}" {{ $business->id === $b->id ? 'selected' : '' }}>
                                {{ $b->title }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('admin.sms.reports.advertising.export', request()->all()) }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel-o me-1"></i> خروجی اکسل
            </a>
            @if($business)
                <span class="badge bg-info text-dark px-3 py-2">
                    <i class="fa fa-building-o me-1"></i> کسب‌وکار: {{ $business->title }}
                </span>
            @endif
        </div>
    </div>

    @include('Admin.sms.reports.navigation')

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Campaigns -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-primary">
                        <i class="fa fa-bullhorn"></i>
                    </div>
                    <div>
                        <div class="text-muted small">تعداد کمپین‌های تبلیغاتی</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_campaigns']) }}</h4>
                        <small class="text-muted">مخاطبان هدف: {{ number_format($summary['total_recipients']) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Messages -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-info">
                        <i class="fa fa-paper-plane"></i>
                    </div>
                    <div>
                        <div class="text-muted small">کل پیامک‌های ارسالی</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_messages']) }}</h4>
                        <small class="text-muted">پیام‌های تبلیغاتی</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-danger">
                        <i class="fa fa-money"></i>
                    </div>
                    <div>
                        <div class="text-muted small">مجموع هزینه تبلیغاتی</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($summary['total_cost']) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <small class="text-muted">مصرف شده از خطوط</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Rate -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-success">
                        <i class="fa fa-check-circle"></i>
                    </div>
                    <div>
                        <div class="text-muted small">نرخ تحویل و موفقیت</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $summary['delivery_rate'] }}%</h4>
                        <small class="text-muted">{{ number_format($summary['delivered_count']) }} موفق / {{ number_format($summary['failed_count']) }} ناموفق</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('Admin.sms.reports.partials.direction-tiles')
    <div class="text-muted small mb-3">
        <i class="fa fa-info-circle ms-1"></i>
        این گزارش پیامک‌های تبلیغاتی و عادی (از جمله پاسخ‌های خودکار و پیام‌های تکی عادی) را نشان می‌دهد؛ آمار هزینه و تحویل شامل پیامک‌های ارسالی از هر دو نوع است. پیامک‌های اعتبارسنجی (OTP) فقط در گزارش OTP می‌آیند.
    </div>

    <!-- Per-Sender Breakdown -->
    @if($sender_breakdown->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fa fa-list-alt me-1"></i> تفکیک عملکرد بر اساس خطوط فرستنده <small class="text-muted">(تبلیغاتی و عادی؛ هزینه و نرخ موفقیت فقط ارسالی‌ها)</small></h6>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 text-center small">
                    <thead class="table-light">
                        <tr>
                            <th>شماره خط</th>
                            @if(auth()->user()?->isSuperAdmin())
                                <th>درگاه</th>
                            @endif
                            <th>پیامک‌های ارسالی</th>
                            <th>پیامک‌های دریافتی</th>
                            <th>تعداد کمپین‌ها</th>
                            <th>مجموع هزینه (ریال)</th>
                            <th>نرخ موفقیت</th>
                            <th>فیلتر اختصاصی</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sender_breakdown as $sb)
                            <tr>
                                <td class="fw-bold">{{ $sb['sender']->number }}</td>
                                @if(auth()->user()?->isSuperAdmin())
                                    <td>{{ $sb['sender']->sms_panel ?? '-' }}</td>
                                @endif
                                <td>{{ number_format($sb['messages_count']) }}</td>
                                <td>{{ number_format($sb['inbound_count']) }}</td>
                                <td>{{ number_format($sb['campaigns_count']) }}</td>
                                <td class="text-danger fw-bold">{{ number_format($sb['cost']) }}</td>
                                <td>
                                    <span class="badge {{ $sb['success_rate'] >= 80 ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $sb['success_rate'] }}%
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.sms.reports.advertising.index', array_filter(array_merge(request()->except(['sender_number_id', 'messages_page', 'campaigns_page']), ['sender_number_id' => $sb['sender']->id]))) }}" 
                                       class="btn btn-sm btn-outline-primary py-0">
                                        مشاهده گزارش این خط
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sms.reports.advertising.index') }}" class="row g-3">
                @if(auth()->user()?->isSuperAdmin() && isset($business) && $business)
                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                @endif
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold">خط فرستنده</label>
                    <select name="sender_number_id" class="form-select form-select-sm">
                        <option value="">همه خطوط تبلیغاتی</option>
                        @foreach($assigned_senders as $s)
                            <option value="{{ $s->id }}" {{ request('sender_number_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">وضعیت</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>تحویل شده</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>ارسال شده</option>
                        <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>در صف</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ناموفق</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold">جستجو (شماره یا کد رهگیری)</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="شماره گیرنده یا متن">
                </div>
@include('Admin.sms.reports.partials.direction-filter')
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">نوع پیام</label>
                    <select name="service_type" class="form-select form-select-sm">
                        <option value="">همه</option>
                        <option value="advertising" {{ request('service_type') === 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                        <option value="normal" {{ request('service_type') === 'normal' ? 'selected' : '' }}>عادی</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">از تاریخ</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">تا تاریخ</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-12 text-end">
                    <a href="{{ route('admin.sms.reports.advertising.index', array_filter(['business_id' => request('business_id')])) }}" class="btn btn-light btn-sm me-2">پاکسازی فیلترها</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-filter me-1"></i> اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns List -->
    @if($campaigns->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fa fa-bullhorn me-1"></i> کمپین‌های تبلیغاتی ثبت‌شده</h6>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 text-center small">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>خط فرستنده</th>
                            <th>نوع کمپین</th>
                            <th>کل مخاطبان</th>
                            <th>ارسال شده</th>
                            <th>موفق</th>
                            <th>ناموفق</th>
                            <th>هزینه برآوردی</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $camp)
                            <tr>
                                <td>{{ $camp->id }}</td>
                                <td>{{ $camp->senderNumber?->number ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $camp->type }}</span>
                                </td>
                                <td class="fw-bold">{{ number_format($camp->total_recipients) }}</td>
                                <td>{{ number_format($camp->sent_count) }}</td>
                                <td class="text-success fw-bold">{{ number_format($camp->success_count) }}</td>
                                <td class="text-danger">{{ number_format($camp->failed_count) }}</td>
                                <td>{{ $camp->cost_estimate ? number_format($camp->cost_estimate) : '-' }} ریال</td>
                                <td>
                                    <span class="badge {{ $camp->status === 'completed' ? 'bg-success' : ($camp->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ \App\Support\SmsLabels::status($camp->status) }}
                                    </span>
                                </td>
                                <td>{{ \App\Helpers\Helper::jalaliDate($camp->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($campaigns->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Messages Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold mb-0"><i class="fa fa-envelope-o me-1"></i> پیامک‌های تبلیغاتی و عادی (ارسالی و دریافتی)</h6>
            <span class="badge bg-light text-dark">{{ $messages->total() }} پیامک</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>جهت</th>
                        <th>نوع پیام</th>
                        <th>خط فرستنده</th>
                        <th>گیرنده</th>
                        <th>متن پیامک</th>
                        <th>درخواست</th>
                        <th>کاراکتر / صفحه</th>
                        <th>هزینه (ریال)</th>
                        <th>وضعیت</th>
                        <th>شناسه پیگیری</th>
                        <th>گروه</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        <tr>
                            <td>{{ $msg->id }}</td>
                            <td>@include('Admin.sms.reports.partials.direction-badge')</td>
                            <td><span class="badge {{ $msg->service_type === 'advertising' ? 'badge-soft-primary' : 'badge-soft-secondary' }}">{{ \App\Support\SmsLabels::serviceType($msg->service_type ?: 'normal') }}</span></td>
                            <td>{{ $msg->senderNumber?->number ?? $msg->sender_phone ?? '-' }}</td>
                            <td class="fw-bold">{{ $msg->recipient_phone }}</td>
                            <td class="text-start" style="max-width: 320px;">
                                @if($msg->content_redacted ?? false)
                                    <span class="text-muted"><i class="fa fa-lock me-1"></i> محرمانه - دسترسی محدود</span>
                                @else
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="sms-content-cell" title="{{ $msg->content }}">{{ $msg->content }}</span>@if(method_exists($msg, 'isConfidential') && $msg->isConfidential())<span class="badge bg-danger ms-1">محرمانه</span>@endif
                                        <button type="button" class="btn btn-sm btn-link p-0 text-primary view-details-btn" 
                                                title="مشاهده متن کامل"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#smsDetailsModal"
                                                data-toggle="modal"
                                                data-target="#smsDetailsModal"
                                                data-content="{{ $msg->content }}"
                                                data-id="{{ $msg->id }}"
                                                data-recipient="{{ $msg->recipient_phone }}"
                                                data-sender="{{ $msg->senderNumber?->number ?? $msg->sender_phone ?? '-' }}"
                                                data-status="{{ in_array($msg->status, ['delivered', 'sent']) ? 'تحویل شد' : ($msg->status === 'queued' ? 'در صف' : 'ناموفق') }}"
                                                data-cost="{{ number_format($msg->cost ?? 0) }}"
                                                data-pages="{{ $msg->page_count }}"
                                                data-chars="{{ $msg->character_count }}"
                                                data-tracking="{{ $msg->tracking_id ?: '-' }}"
                                                data-date="{{ \App\Helpers\Helper::jalaliDate($msg->created_at, 'Y/m/d H:i') ?? '-' }}">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                            <td class="small">{{ $msg->request_breadcrumb ?: '-' }}</td>
                            <td>{{ $msg->character_count }} / {{ $msg->page_count }}</td>
                            @if(($msg->direction ?? '') === 'inbound')
                                <td class="text-muted">-</td>
                            @else
                                <td class="fw-bold text-danger">{{ $msg->cost ? number_format($msg->cost) : '0' }}</td>
                            @endif
                            <td>
                                @if(($msg->direction ?? '') === 'inbound')
                                    <span class="badge badge-soft-info">دریافت شد</span>
                                @elseif(in_array($msg->status, ['delivered', 'sent']))
                                    <span class="badge badge-soft-success">موفق</span>
                                @elseif($msg->status === 'queued')
                                    <span class="badge badge-soft-warning">در صف</span>
                                @else
                                    <span class="badge badge-soft-danger">ناموفق</span>
                                @endif
                            </td>
                            <td><code>{{ $msg->tracking_id ?? '-' }}</code></td>
                            <td>{{ $msg->group?->name ?? '-' }}</td>
                            <td>{{ \App\Helpers\Helper::jalaliDate($msg->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-muted py-4">پیامک تبلیغاتی منطبق با فیلترها یافت نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>

<!-- SMS Details Modal -->
<div class="modal fade" id="smsDetailsModal" tabindex="-1" role="dialog" aria-labelledby="smsDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="smsDetailsModalLabel"><i class="fa fa-info-circle me-1 text-primary"></i> جزئیات کامل پیامک</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3"><strong>فرستنده:</strong> <span id="mSender" dir="ltr" class="d-inline-block"></span></div>
                    <div class="col-6 col-md-3"><strong>گیرنده:</strong> <span id="mRecipient" dir="ltr" class="d-inline-block fw-bold"></span></div>
                    <div class="col-6 col-md-3"><strong>وضعیت:</strong> <span id="mStatus"></span></div>
                    <div class="col-6 col-md-3"><strong>شناسه رهگیری:</strong> <span id="mTracking" dir="ltr" class="font-monospace"></span></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3"><strong>تعداد صفحات:</strong> <span id="mPages"></span></div>
                    <div class="col-6 col-md-3"><strong>تعداد کاراکتر:</strong> <span id="mChars"></span></div>
                    <div class="col-6 col-md-3"><strong>هزینه:</strong> <span id="mCost" class="text-danger fw-bold"></span> ریال</div>
                    <div class="col-6 col-md-3"><strong>تاریخ ارسال:</strong> <span id="mDate"></span></div>
                </div>
                <div class="mb-0">
                    <label class="fw-bold mb-1">متن پیامک:</label>
                    <div class="p-3 bg-light rounded border text-start" style="white-space: pre-wrap; line-height: 1.8; max-height: 200px; overflow-y: auto;" id="mContent"></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="copyMsgBtn">کپی متن</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    $(document).on('click', '#copyMsgBtn', function() {
        var t = $('#mContent').text();
        if (navigator.clipboard) { navigator.clipboard.writeText(t); }
        $(this).text('کپی شد');
    });
    $(document).on('click', '.view-details-btn', function() {
        var btn = $(this);
        $('#mSender').text(btn.data('sender') || '-');
        $('#mRecipient').text(btn.data('recipient') || '-');
        $('#mStatus').text(btn.data('status') || '-');
        $('#mTracking').text(btn.data('tracking') || '-');
        $('#mPages').text(btn.data('pages') || '1');
        $('#mChars').text(btn.data('chars') || '0');
        $('#mCost').text(btn.data('cost') || '0');
        $('#mDate').text(btn.data('date') || '-');
        $('#mContent').text(btn.data('content') || '');
        $('#copyMsgBtn').text('کپی متن');

        var modalEl = document.getElementById('smsDetailsModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        } else if (typeof $ !== 'undefined' && $.fn.modal) {
            $('#smsDetailsModal').modal('show');
        }
    });
});
</script>
@endsection
