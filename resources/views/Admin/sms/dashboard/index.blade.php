@extends('Admin.master')

@section('title', 'پیشخوان پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">پیشخوان پیامک</h3>
            <p class="text-muted small mb-0">
                کسب‌وکار: <strong>{{ $business ? $business->title : 'کلیه کسب‌وکارها (نمای سراسری)' }}</strong> — نمای کلی اعتبار، حجم ارسال/دریافت و دسترسی سریع
                به بخش‌های سرویس پیامک.
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.dashboard.index') }}" class="d-flex flex-wrap gap-2 m-0">
                    <input type="hidden" name="days" value="{{ $days }}">
                    <select name="business_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ !$business ? 'selected' : '' }}>-- کلیه کسب‌وکارها (نمای سراسری) --</option>
                        @foreach($all_businesses as $b)
                            <option value="{{ $b->id }}" {{ $business && $business->id === $b->id ? 'selected' : '' }}>
                                {{ $b->title }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('admin.sms.reports.superadmin.index') }}" class="btn btn-outline-info btn-sm">
                    <i class="fa fa-globe me-1"></i> گزارش کلان کسب‌وکارها
                </a>
            @endif
            <form method="GET" class="d-flex flex-wrap gap-2 m-0">
                @if(request()->filled('business_id'))
                    <input type="hidden" name="business_id" value="{{ request('business_id') }}">
                @endif
                <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>۷ روز گذشته</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>۳۰ روز گذشته</option>
                    <option value="90" {{ $days == 90 ? 'selected' : '' }}>۹۰ روز گذشته</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0"><i class="fa fa-bolt me-1"></i> دسترسی سریع</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-6 col-md">
                    <a href="{{ route('admin.sms.single.index') }}" class="btn btn-primary w-100 py-3 shadow-sm text-white">
                        <i class="fa fa-paper-plane d-block fs-4 mb-1"></i>
                        <span class="fw-bold">ارسال پیامک تکی</span>
                    </a>
                </div>
                <div class="col-6 col-md">
                    <a href="{{ route('admin.sms.bulk.index') }}" class="btn btn-primary w-100 py-3 shadow-sm text-white">
                        <i class="fa fa-users d-block fs-4 mb-1"></i>
                        <span class="fw-bold">ارسال پیامک گروهی</span>
                    </a>
                </div>
                <div class="col-6 col-md">
                    <a href="{{ route('admin.sms.logs.index', ['direction' => 'inbound']) }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                        <i class="fa fa-inbox d-block fs-4 mb-1"></i>
                        <span class="fw-bold">صندوق دریافت</span>
                    </a>
                </div>
                <div class="col-6 col-md">
                    <a href="{{ route('admin.sms.settings.templates.index') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                        <i class="fa fa-file-text-o d-block fs-4 mb-1"></i>
                        <span class="fw-bold">قالب‌های پیامک</span>
                    </a>
                </div>
                <div class="col-12 col-md">
                    <a href="{{ route('admin.sms.reports.advertising.index') }}" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                        <i class="fa fa-bar-chart d-block fs-4 mb-1"></i>
                        <span class="fw-bold">گزارش پیامک‌ها</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Balance & KPI cards --}}
    <div class="row g-3 mb-4">
        @if(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()))
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #10b981 !important; border-radius: 12px;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">موجودی اعتبار OTP</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #ecfdf5; color: #10b981;">
                            <i class="fa fa-shield"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: #0f172a;">{{ number_format($otpBalance) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <div class="text-success small fw-semibold">
                            <i class="fa fa-check-circle me-1"></i> وضعیت نرمال
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #3b82f6 !important; border-radius: 12px;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">ارسال‌شده ({{ $days }} روز اخیر)</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #eff6ff; color: #3b82f6;">
                            <i class="fa fa-paper-plane"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: #0f172a;">{{ number_format($volume['sent']) }}</h4>
                        <div class="text-muted small">
                            موفق: <span class="text-success fw-bold">{{ number_format($volume['success']) }}</span> | ناموفق: <span class="text-danger fw-bold">{{ number_format($volume['failed']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #8b5cf6 !important; border-radius: 12px;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">دریافت‌شده ({{ $days }} روز اخیر)</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #f5f3ff; color: #8b5cf6;">
                            <i class="fa fa-inbox"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: #0f172a;">{{ number_format($volume['received']) }}</h4>
                        <div class="text-muted small">
                            پیامک‌های دریافتی از مخاطبان
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #f59e0b !important; border-radius: 12px;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">هزینه ({{ $days }} روز اخیر)</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #fffbeb; color: #f59e0b;">
                            <i class="fa fa-credit-card"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: #0f172a;">{{ number_format($volume['total_cost']) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <div class="text-muted small">
                            مجموع هزینه مصرف‌شده
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #ef4444 !important; border-radius: 12px;">
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-bold">پیام‌های تکرار شده (۳۰ روز اخیر)</span>
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #fef2f2; color: #ef4444;" title="پیام‌هایی با متن و گیرنده دقیقاً یکسان">
                            <i class="fa fa-clone"></i>
                        </div>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1" style="color: #0f172a;">{{ number_format($repeats['duplicates']) }} <small class="fs-6 text-muted">پیام</small></h4>
                        <div class="text-muted small">
                            تکرار متن یکسان به همان شماره: <span class="text-danger fw-bold">{{ number_format($repeats['unique_pairs']) }}</span> مورد
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Advertising balances per sender number --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="fa fa-phone me-1"></i> اعتبار تبلیغاتی به تفکیک سرشماره</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>سرشماره</th>
                                <th>وضعیت</th>
                                <th>موجودی (ریال)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($advertisingBalances as $sender)
                                <tr>
                                    <td class="font-monospace"><span dir="ltr">{{ $sender->number }}</span></td>
                                    <td>
                                        <span class="badge {{ $sender->status === 'active' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $sender->status === 'active' ? 'فعال' : 'غیرفعال' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold">{{ number_format($sender->current_balance) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        هیچ سرشماره تبلیغاتی به این کسب‌وکار تخصیص داده نشده است.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Success / fail breakdown --}}
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="fa fa-bar-chart me-1"></i> وضعیت ارسال ({{ $days }} روز اخیر)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">موفق</div>
                                <div class="fs-4 fw-bold text-success">{{ number_format($volume['success']) }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small">ناموفق</div>
                                <div class="fs-4 fw-bold text-danger">{{ number_format($volume['failed']) }}</div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold small text-muted mb-2">آخرین پیامک‌ها</h6>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @forelse($recentMessages as $msg)
                                    <tr>
                                        <td class="small">#{{ $msg->id }}</td>
                                        <td class="small">{{ \App\Support\SmsLabels::direction($msg->direction) }}</td>
                                        <td class="small">{{ $msg->group?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border small">{{ \App\Support\SmsLabels::status($msg->status) }}</span>
                                        </td>
                                        <td class="small text-muted">{{ \App\Helpers\Helper::jalaliDate($msg->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center text-muted py-3">پیامکی در این بازه ثبت نشده است.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
