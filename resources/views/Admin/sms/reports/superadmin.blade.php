@extends('Admin.master')

@section('title', 'گزارش جامع بین‌سازمانی پیامک (مدیریت کل)')

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

    .breakdown-icon-badge {
        width: 48px;
        height: 48px;
        min-width: 48px;
        min-height: 48px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .breakdown-icon-badge i {
        font-size: 1.35rem;
        line-height: 1;
    }

    .table td, .table th {
        line-height: 1.8 !important;
        padding: 12px 10px !important;
        vertical-align: middle;
    }
    .sms-content-cell {
        max-width: 250px;
        line-height: 1.8 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding-bottom: 2px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h3 class="fw-bold mb-1">گزارش جامع بین‌سازمانی پیامک</h3>
                <span class="badge bg-danger text-white px-2 py-1">مدیریت ارشد (SuperAdmin)</span>
            </div>
            <p class="text-muted small mb-0">نظارت بر کلیه تراکنش‌ها، خطوط، پنل‌ها و پیامک‌های ارسالی در سرتاسر کسب‌وکارهای متصل</p>
        </div>
        <div class="w-100 w-md-auto d-flex justify-content-start justify-content-md-end">
            <a href="{{ route('admin.sms.reports.superadmin.export', request()->all()) }}" class="btn btn-success btn-sm text-nowrap">
                <i class="fa fa-file-excel-o me-1"></i> خروجی جامع اکسل
            </a>
        </div>
    </div>

    @include('Admin.sms.reports.navigation')

    <!-- Advanced Super-Admin Filter Bar -->
@include('Admin.sms.reports.partials.direction-tiles')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold mb-0"><i class="fa fa-sliders me-1"></i> فیلترهای پیشرفته بین‌سازمانی</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sms.reports.superadmin.index') }}" class="row g-3">
                <!-- Business -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold">کسب‌وکار</label>
                    <select name="business" class="form-select form-select-sm">
                        <option value="">همه کسب‌وکارها</option>
                        @foreach($businesses as $b)
                            <option value="{{ $b->id }}" {{ request('business') == $b->id ? 'selected' : '' }}>
                                {{ $b->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sender -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold">خط فرستنده</label>
                    <select name="sender" class="form-select form-select-sm">
                        <option value="">همه خطوط</option>
                        @foreach($senders as $s)
                            <option value="{{ $s->id }}" {{ request('sender') == $s->id ? 'selected' : '' }}>
                                {{ $s->number }} ({{ $s->type }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Panel Type -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold">نوع پنل</label>
                    <select name="panel_type" class="form-select form-select-sm">
                        <option value="">همه پنل‌ها</option>
                        @foreach($panel_types as $ptKey => $ptTitle)
                            <option value="{{ $ptKey }}" {{ request('panel_type') === $ptKey ? 'selected' : '' }}>
                                {{ $ptTitle }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Message Type -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold">نوع پیامک</label>
                    <select name="message_type" class="form-select form-select-sm">
                        <option value="">همه انواع</option>
                        <option value="otp" {{ request('message_type') === 'otp' ? 'selected' : '' }}>اعتبارسنجی (OTP)</option>
                        <option value="advertising" {{ request('message_type') === 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                        <option value="normal" {{ request('message_type') === 'normal' ? 'selected' : '' }}>عادی / اطلاع‌رسانی</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold">وضعیت</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>تحویل شده</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>ارسال شده</option>
                        <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>در صف</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ناموفق</option>
                    </select>
                </div>

                <!-- Group -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold">گروه پیامک</label>
                    <select name="group" class="form-select form-select-sm">
                        <option value="">همه گروه‌ها</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->id }}" {{ request('group') == $grp->id ? 'selected' : '' }}>
                                {{ $grp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-bold">جستجوی متن یا شماره</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="شماره، شناسه یا متن">
                </div>

@include('Admin.sms.reports.partials.direction-filter', ['colClass' => 'col-12 col-sm-6 col-md-2'])

                <!-- Date Range -->
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold">از تاریخ</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-bold">تا تاریخ</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>

                <!-- Buttons -->
                <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                    <a href="{{ route('admin.sms.reports.superadmin.index') }}" class="btn btn-light btn-sm flex-fill">پاکسازی</a>
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fa fa-filter me-1"></i> فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- The numbers below describe exactly the rows the filters above select, not the whole module. --}}
    @php
        $activeFilterCount = collect(request()->except(['page', 'business_id']))->filter(fn ($v) => $v !== null && $v !== '' && $v !== 'all')->count();
    @endphp
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <h6 class="fw-bold mb-0"><i class="fa fa-filter text-primary me-1"></i> خلاصه نتیجه فیلتر</h6>
        @if($activeFilterCount > 0)
            <span class="badge bg-primary">{{ $activeFilterCount }} فیلتر فعال</span>
            <a href="{{ url()->current() }}{{ request('business_id') ? '?business_id=' . request('business_id') : '' }}" class="small text-decoration-none">حذف فیلترها</a>
        @else
            <span class="badge bg-light text-muted border">بدون فیلتر؛ کل داده‌های این گزارش</span>
        @endif
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Messages -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-primary">
                        <i class="fa fa-envelope"></i>
                    </div>
                    <div>
                        <div class="text-muted small">کل پیامک‌های شبکه</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_messages']) }}</h4>
                        <small class="text-muted">در {{ number_format($summary['businesses_count']) }} کسب‌وکار</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Network Cost -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-danger">
                        <i class="fa fa-money"></i>
                    </div>
                    <div>
                        <div class="text-muted small">مجموع کل هزینه مصرفی</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($summary['total_cost']) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <small class="text-muted">کل درآمد/مصرف پیامک</small>
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
                        <div class="text-muted small">نرخ موفقیت شبکه</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $summary['delivery_rate'] }}%</h4>
                        <small class="text-muted">{{ number_format($summary['delivered_count']) }} موفق / {{ number_format($summary['failed_count']) }} ناموفق</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Businesses Active -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-info">
                        <i class="fa fa-building"></i>
                    </div>
                    <div>
                        <div class="text-muted small">کسب‌وکارهای فعال</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['businesses_count']) }}</h4>
                        <small class="text-muted">دارای پیامک در بازه فیلتر</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Type Breakdown (OTP vs Advertising vs Normal) -->
    <div class="row g-3 mb-4">
        @php
            $otpData = $message_type_breakdown->get('otp');
            $advData = $message_type_breakdown->get('advertising');
            $normData = $message_type_breakdown->get('normal');
        @endphp
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <span class="badge badge-soft-primary mb-2 px-2 py-1">پیامک‌های OTP</span>
                        <h5 class="fw-bold mb-0">{{ number_format($otpData?->count ?? 0) }} پیامک</h5>
                        <small class="text-muted">هزینه: {{ number_format((float) ($otpData?->cost ?? 0)) }} ریال</small>
                    </div>
                    <div class="breakdown-icon-badge badge-soft-primary">
                        <i class="fa fa-shield"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <span class="badge badge-soft-info mb-2 px-2 py-1">پیامک‌های تبلیغاتی</span>
                        <h5 class="fw-bold mb-0">{{ number_format($advData?->count ?? 0) }} پیامک</h5>
                        <small class="text-muted">هزینه: {{ number_format((float) ($advData?->cost ?? 0)) }} ریال</small>
                    </div>
                    <div class="breakdown-icon-badge badge-soft-info">
                        <i class="fa fa-bullhorn"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <span class="badge badge-soft-secondary mb-2 px-2 py-1">پیامک‌های عادی / سیستمی</span>
                        <h5 class="fw-bold mb-0">{{ number_format($normData?->count ?? 0) }} پیامک</h5>
                        <small class="text-muted">هزینه: {{ number_format((float) ($normData?->cost ?? 0)) }} ریال</small>
                    </div>
                    <div class="breakdown-icon-badge badge-soft-secondary">
                        <i class="fa fa-commenting-o"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold mb-0"><i class="fa fa-list me-1"></i> پیامک‌های ثبت‌شده در سامانه</h6>
            <span class="badge bg-light text-dark">{{ $messages->total() }} پیامک</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>جهت</th>
                        <th>کسب‌وکار</th>
                        <th>نوع</th>
                        <th>خط فرستنده</th>
                        <th>گیرنده</th>
                        <th>متن پیامک</th>
                        <th>درخواست</th>
                        <th>صفحه</th>
                        <th>هزینه (ریال)</th>
                        <th>وضعیت</th>
                        <th>شناسه پیگیری</th>
                        <th>تاریخ ثبت</th>
                        <th style="width: 75px;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        <tr>
                            <td>{{ $msg->id }}</td>
                            <td>@include('Admin.sms.reports.partials.direction-badge')</td>
                            <td class="fw-bold text-primary">{{ $msg->business?->title ?? '-' }}</td>
                            <td>
                                @if($msg->service_type === 'otp')
                                    <span class="badge badge-soft-primary">OTP</span>
                                @elseif($msg->service_type === 'advertising')
                                    <span class="badge badge-soft-info">تبلیغاتی</span>
                                @else
                                    <span class="badge badge-soft-secondary">عادی</span>
                                @endif
                            </td>
                            <td>{{ $msg->senderNumber?->number ?? $msg->sender_phone ?? '-' }}</td>
                            <td class="fw-bold">{{ $msg->recipient_phone }}</td>
                            <td class="text-start" style="max-width: 250px;">
                                @if($msg->content_redacted ?? false)
                                    <span class="text-muted"><i class="fa fa-lock me-1"></i> محرمانه - دسترسی محدود</span>
                                @else
                                    <div class="sms-content-cell" title="{{ $msg->content }}">{{ $msg->content }}</div>@if(method_exists($msg, 'isConfidential') && $msg->isConfidential())<span class="badge bg-danger ms-1">محرمانه</span>@endif
                                @endif
                            </td>
                            <td class="small">{{ $msg->request_breadcrumb ?: '-' }}</td>
                            <td>{{ $msg->page_count }}</td>
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
                            <td>{{ \App\Helpers\Helper::jalaliDate($msg->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                            <td>
                                <button type="button" class="btn btn-outline-info btn-sm view-details-btn" 
                                        data-id="{{ $msg->id }}"
                                        data-business="{{ $msg->business?->title ?? '-' }}"
                                        data-content="{{ ($msg->content_redacted ?? false) ? 'محرمانه - دسترسی محدود' : e($msg->content) }}"
                                        data-sender="{{ $msg->senderNumber?->number ?? $msg->sender_phone ?? '-' }}"
                                        data-recipient="{{ $msg->recipient_phone }}"
                                        data-service="{{ $msg->service_type === 'otp' ? 'اعتبارسنجی (OTP)' : ($msg->service_type === 'advertising' ? 'تبلیغاتی' : 'عادی') }}"
                                        data-status="{{ in_array($msg->status, ['delivered', 'sent']) ? 'تحویل شد' : ($msg->status === 'queued' ? 'در صف' : 'ناموفق') }}"
                                        data-delivery="{{ \App\Support\SmsLabels::status($msg->delivery_status) }}"
                                        data-cost="{{ number_format($msg->cost ?? 0) }}"
                                        data-pages="{{ $msg->page_count }}"
                                        data-chars="{{ $msg->character_count }}"
                                        data-tracking="{{ $msg->tracking_id ?: '-' }}"
                                        data-date="{{ verta($msg->created_at)->format('Y/m/d H:i:s') }}"
                                        data-delivered-at="{{ $msg->delivered_at ? verta($msg->delivered_at)->format('Y/m/d H:i:s') : '-' }}"
                                        data-error="{{ e($msg->error_reason ?: '') }}"
                                        data-request-id="{{ $msg->callcenter_id ?: $msg->request_id ?: '' }}"
                                            data-request-label="{{ $msg->request_breadcrumb }}"
                                        data-bs-toggle="modal" data-bs-target="#smsDetailsModal"
                                        data-toggle="modal" data-target="#smsDetailsModal"
                                        title="مشاهده جزئیات">
                                    <i class="fa fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-muted py-4">پیامکی منطبق با فیلترها یافت نشد.</td>
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
                    <div class="col-6 col-md-3"><strong>کسب‌وکار:</strong> <span id="mBusiness" class="text-primary fw-bold"></span></div>
                    <div class="col-6 col-md-3"><strong>نوع:</strong> <span id="mService"></span></div>
                    <div class="col-6 col-md-3"><strong>وضعیت کلی:</strong> <span id="mStatus"></span></div>
                    <div class="col-6 col-md-3"><strong>وضعیت تحویل:</strong> <span id="mDelivery"></span></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3"><strong>فرستنده:</strong> <span id="mSender" dir="ltr" class="d-inline-block"></span></div>
                    <div class="col-6 col-md-3"><strong>گیرنده:</strong> <span id="mRecipient" dir="ltr" class="d-inline-block fw-bold"></span></div>
                    <div class="col-6 col-md-3"><strong>تعداد صفحات:</strong> <span id="mPages"></span></div>
                    <div class="col-6 col-md-3"><strong>تعداد کاراکتر:</strong> <span id="mChars"></span></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3"><strong>هزینه:</strong> <span id="mCost" class="text-danger fw-bold"></span> ریال</div>
                    <div class="col-6 col-md-3"><strong>شناسه رهگیری:</strong> <span id="mTracking" dir="ltr" class="font-monospace"></span></div>
                    <div class="col-6 col-md-3"><strong>تاریخ ثبت:</strong> <span id="mDate"></span></div>
                    <div class="col-6 col-md-3"><strong>زمان تحویل:</strong> <span id="mDeliveredAt"></span></div>
                </div>
                <div class="mb-3" id="mRequestRow" style="display: none;">
                    <strong>درخواست مرتبط:</strong> <span id="mRequestId"></span>
                </div>
                <div class="mb-3" id="mErrorRow" style="display: none;">
                    <div class="alert alert-danger mb-0">
                        <strong>علت خطا:</strong> <span id="mError"></span>
                    </div>
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
        $('#mBusiness').text(btn.data('business') || '-');
        $('#mService').text(btn.data('service') || '-');
        $('#mStatus').text(btn.data('status') || '-');
        $('#mDelivery').text(btn.data('delivery') || '-');
        $('#mSender').text(btn.data('sender') || '-');
        $('#mRecipient').text(btn.data('recipient') || '-');
        $('#mPages').text(btn.data('pages') || '1');
        $('#mChars').text(btn.data('chars') || '0');
        $('#mCost').text(btn.data('cost') || '0');
        $('#mTracking').text(btn.data('tracking') || '-');
        $('#mDate').text(btn.data('date') || '-');
        $('#mDeliveredAt').text(btn.data('delivered-at') || '-');
        $('#mContent').text(btn.data('content') || '');
        $('#copyMsgBtn').text('کپی متن');

        var reqId = btn.data('request-id');
        if (reqId) {
            $('#mRequestId').text(btn.data('request-label') || ('#' + reqId));
            $('#mRequestRow').show();
        } else {
            $('#mRequestRow').hide();
        }

        var err = btn.data('error');
        if (err) {
            $('#mError').text(err);
            $('#mErrorRow').show();
        } else {
            $('#mErrorRow').hide();
        }

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
