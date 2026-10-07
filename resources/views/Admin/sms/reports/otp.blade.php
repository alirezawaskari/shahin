@extends('Admin.master')

@section('title', 'گزارش پیامک‌های اعتبارسنجی (OTP)')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">گزارش پیامک‌های اعتبارسنجی (OTP)</h3>
            <p class="text-muted small mb-0">مشاهده آمار ارسال، وضعیت تحویل، هزینه‌های مصرفی و فیلتر پیام‌های ورود و کد تایید</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.sms.reports.otp.export', request()->all()) }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel-o me-1"></i> خروجی اکسل
            </a>
            @if($business)
                <span class="badge bg-info text-dark px-3 py-2">
                    <i class="fa fa-building-o me-1"></i> کسب‌وکار: {{ $business->title }}
                </span>
            @endif
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
                        <i class="fa fa-paper-plane fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">کل پیامک‌های OTP</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_messages']) }}</h4>
                        <small class="text-muted">در بازه انتخابی</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger me-3">
                        <i class="fa fa-money fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">مجموع هزینه مصرفی</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($summary['total_cost']) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <small class="text-muted">هزینه محاسبه شده</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivered & Sent -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success me-3">
                        <i class="fa fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">ارسال و تحویل موفق</div>
                        <h4 class="fw-bold mb-0 text-success">{{ number_format($summary['delivered_count']) }}</h4>
                        <small class="text-success">نرخ تحویل: {{ $summary['delivery_rate'] }}%</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Failed & Queued -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fa fa-clock-o fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">ناموفق / در صف</div>
                        <h4 class="fw-bold mb-0 text-warning">{{ number_format($summary['failed_count'] + $summary['queued_count']) }}</h4>
                        <small class="text-danger">{{ number_format($summary['failed_count']) }} ناموفق</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sms.reports.otp.index') }}" class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold">جستجو (شماره، شناسه یا متن)</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="0912... یا کد رهگیری">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">وضعیت پیامک</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>تحویل شده</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>ارسال شده</option>
                        <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>در صف</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ناموفق</option>
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">الگوی پیامک</label>
                    <select name="sms_message_template_id" class="form-select form-select-sm">
                        <option value="">همه الگوها</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->id }}" {{ request('sms_message_template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->title }}
                            </option>
                        @endforeach
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
                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fa fa-filter me-1"></i> فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fa fa-envelope-o me-1"></i> لیست پیامک‌های ارسالی OTP</h6>
            <span class="badge bg-light text-dark">{{ $messages->total() }} پیامک</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>گیرنده</th>
                        <th>متن پیامک</th>
                        <th>کاراکتر / صفحه</th>
                        <th>هزینه (ریال)</th>
                        <th>وضعیت</th>
                        <th>شناسه پیگیری</th>
                        <th>الگو</th>
                        <th>تاریخ ارسال</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        <tr>
                            <td>{{ $msg->id }}</td>
                            <td class="fw-bold">{{ $msg->recipient_phone }}</td>
                            <td class="text-start" style="max-width: 280px;">
                                <div class="text-truncate" title="{{ $msg->content }}">{{ $msg->content }}</div>
                            </td>
                            <td>{{ $msg->character_count }} / {{ $msg->page_count }}</td>
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
                            <td>{{ $msg->template?->title ?? '-' }}</td>
                            <td>{{ $msg->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-muted py-4">پیامکی منطبق با فیلترهای انتخابی یافت نشد.</td>
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
