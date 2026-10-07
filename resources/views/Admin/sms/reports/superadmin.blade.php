@extends('Admin.master')

@section('title', 'گزارش جامع بین‌سازمانی پیامک (مدیریت کل)')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h3 class="fw-bold mb-1">گزارش جامع بین‌سازمانی پیامک</h3>
                <span class="badge bg-danger text-white px-2 py-1">مدیریت ارشد (SuperAdmin)</span>
            </div>
            <p class="text-muted small mb-0">نظارت بر کلیه تراکنش‌ها، خطوط، پنل‌ها و پیامک‌های ارسالی در سرتاسر کسب‌وکارهای متصل</p>
        </div>
        <div>
            <a href="{{ route('admin.sms.reports.superadmin.export', request()->all()) }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel-o me-1"></i> خروجی جامع اکسل
            </a>
        </div>
    </div>

    @include('Admin.sms.financial.navigation')

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Messages -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fa fa-envelope fa-2x"></i>
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
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger me-3">
                        <i class="fa fa-money fa-2x"></i>
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
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success me-3">
                        <i class="fa fa-check-circle fa-2x"></i>
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
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info me-3">
                        <i class="fa fa-building fa-2x"></i>
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
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1">پیامک‌های OTP</span>
                        <h5 class="fw-bold mb-0">{{ number_format($otpData?->count ?? 0) }} پیامک</h5>
                        <small class="text-muted">هزینه: {{ number_format((float) ($otpData?->cost ?? 0)) }} ریال</small>
                    </div>
                    <i class="fa fa-shield fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-info bg-opacity-10 text-info mb-1">پیامک‌های تبلیغاتی</span>
                        <h5 class="fw-bold mb-0">{{ number_format($advData?->count ?? 0) }} پیامک</h5>
                        <small class="text-muted">هزینه: {{ number_format((float) ($advData?->cost ?? 0)) }} ریال</small>
                    </div>
                    <i class="fa fa-bullhorn fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary mb-1">پیامک‌های عادی / سیستمی</span>
                        <h5 class="fw-bold mb-0">{{ number_format($normData?->count ?? 0) }} پیامک</h5>
                        <small class="text-muted">هزینه: {{ number_format((float) ($normData?->cost ?? 0)) }} ریال</small>
                    </div>
                    <i class="fa fa-commenting-o fa-2x text-secondary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Super-Admin Filter Bar -->
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

    <!-- Messages Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fa fa-list me-1"></i> پیامک‌های ثبت‌شده در سامانه</h6>
            <span class="badge bg-light text-dark">{{ $messages->total() }} پیامک</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>کسب‌وکار</th>
                        <th>نوع</th>
                        <th>خط فرستنده</th>
                        <th>گیرنده</th>
                        <th>متن پیامک</th>
                        <th>صفحه</th>
                        <th>هزینه (ریال)</th>
                        <th>وضعیت</th>
                        <th>شناسه پیگیری</th>
                        <th>تاریخ ثبت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        <tr>
                            <td>{{ $msg->id }}</td>
                            <td class="fw-bold text-primary">{{ $msg->business?->title ?? '-' }}</td>
                            <td>
                                @if($msg->service_type === 'otp')
                                    <span class="badge bg-primary bg-opacity-10 text-primary">OTP</span>
                                @elseif($msg->service_type === 'advertising')
                                    <span class="badge bg-info bg-opacity-10 text-info">تبلیغاتی</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">عادی</span>
                                @endif
                            </td>
                            <td>{{ $msg->senderNumber?->number ?? $msg->sender_phone ?? '-' }}</td>
                            <td class="fw-bold">{{ $msg->recipient_phone }}</td>
                            <td class="text-start" style="max-width: 240px;">
                                <div class="text-truncate" title="{{ $msg->content }}">{{ $msg->content }}</div>
                            </td>
                            <td>{{ $msg->page_count }}</td>
                            <td class="fw-bold text-danger">{{ $msg->cost ? number_format($msg->cost) : '0' }}</td>
                            <td>
                                @if(in_array($msg->status, ['delivered', 'sent']))
                                    <span class="badge bg-success bg-opacity-10 text-success">موفق</span>
                                @elseif($msg->status === 'queued')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">در صف</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">ناموفق</span>
                                @endif
                            </td>
                            <td><code>{{ $msg->tracking_id ?? '-' }}</code></td>
                            <td>{{ $msg->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-muted py-4">پیامکی منطبق با فیلترها یافت نشد.</td>
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
@endsection
