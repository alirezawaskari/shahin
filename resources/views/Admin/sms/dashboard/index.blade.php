@extends('Admin.master')

@section('title', 'پیش‌خوان سرویس پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">پیش‌خوان سرویس پیامک</h3>
            <p class="text-muted small mb-0">
                کسب‌وکار: <strong>{{ $business->title }}</strong> — نمای کلی اعتبار، حجم ارسال/دریافت و دسترسی سریع
                به بخش‌های سرویس پیامک.
            </p>
        </div>
        <form method="GET" class="d-flex gap-2">
            <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>۷ روز گذشته</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>۳۰ روز گذشته</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>۹۰ روز گذشته</option>
            </select>
        </form>
    </div>

    {{-- Balance cards: same card/widget pattern used on the main admin dashboard (Admin.index) --}}
    <div class="row page-header">
        <div class="col-lg-3 col-md-6">
            <div class="data-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">موجودی OTP</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10 font-size-22">
                        <p>{{ number_format($otpBalance) }} ریال</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="data-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">ارسال‌شده ({{ $days }} روز اخیر)</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10 font-size-22">
                        <p>{{ number_format($volume['sent']) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="data-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">دریافت‌شده ({{ $days }} روز اخیر)</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10 font-size-22">
                        <p>{{ number_format($volume['received']) }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="data-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">هزینه ({{ $days }} روز اخیر)</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10 font-size-22">
                        <p>{{ number_format($volume['total_cost']) }} ریال</p>
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
                                    <td class="font-monospace">{{ $sender->number }}</td>
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
                                        <td class="small">{{ $msg->direction === 'inbound' ? 'دریافتی' : 'ارسالی' }}</td>
                                        <td class="small">{{ $msg->group?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border small">{{ $msg->status }}</span>
                                        </td>
                                        <td class="small text-muted">{{ $msg->created_at?->format('Y/m/d H:i') }}</td>
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

        {{-- Quick links --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0"><i class="fa fa-bolt me-1"></i> دسترسی سریع</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('sms.single') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fa fa-paper-plane d-block fs-4 mb-1"></i> ارسال پیامک تکی
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('groupsms.create') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fa fa-users d-block fs-4 mb-1"></i> ارسال پیامک گروهی
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.sms.settings.templates.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fa fa-file-text-o d-block fs-4 mb-1"></i> قالب‌های پیامک
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('admin.sms.logs.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fa fa-list d-block fs-4 mb-1"></i> گزارش‌ها و فهرست پیامک‌ها
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
