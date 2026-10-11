@extends('Admin.master')

@section('title', 'مدیریت مالی و موجودی پیامک اعتبارسنجی (OTP)')

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
</style>
@endsection

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">مدیریت مالی و اعتبار پیامک اعتبارسنجی (OTP)</h3>
            <p class="text-muted small mb-0">مشاهده موجودی، شارژ حساب، کنترل سقف هشدار و تاریخچه تراکنش‌های کدهای تایید</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.financial.otp.index') }}" class="d-flex flex-wrap gap-2 m-0">
                    <select name="business_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($all_businesses as $b)
                            <option value="{{ $b->id }}" {{ $business->id === $b->id ? 'selected' : '' }}>
                                {{ $b->title }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif
            @if($business)
                <span class="badge bg-info text-dark px-3 py-2">
                    <i class="fa fa-building-o me-1"></i> کسب‌وکار: {{ $business->title }}
                </span>
            @endif
        </div>
    </div>

    @include('Admin.sms.financial.navigation')

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    <!-- Low-Balance or Hard-Stop Alert Banners -->
    @if($guard['balance'] <= 0)
        <div class="alert alert-danger shadow-sm border-0 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-danger text-white rounded-circle me-3">
                    <i class="fa fa-ban fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">توقف اضطراری ارسال پیامک اعتبارسنجی (Hard-Stop)</h5>
                    <p class="mb-0 small">موجودی اعتبار OTP کسب‌وکار صفر یا منفی می‌باشد. تا زمان شارژ مجدد، ارسال کدهای ورود و پیام‌های اعتبارسنجی متوقف شده است.</p>
                </div>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#otpTopUpModal">
                    <i class="fa fa-plus-circle me-1"></i> شارژ فوری حساب
                </button>
            </div>
        </div>
    @elseif($guard['is_low_balance'])
        <div class="alert alert-warning shadow-sm border-0 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <div class="p-2 bg-warning text-dark rounded-circle me-3">
                    <i class="fa fa-exclamation-triangle fa-2x"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">هشدار کسری اعتبار OTP</h5>
                    <p class="mb-0 small">{{ $guard['warning'] }}</p>
                </div>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" class="btn btn-warning text-dark btn-sm px-3" data-bs-toggle="modal" data-bs-target="#otpTopUpModal">
                    <i class="fa fa-plus-circle me-1"></i> افزایش موجودی
                </button>
            </div>
        </div>
    @endif

    <!-- Financial KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Balance -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon-badge {{ $guard['balance'] > $guard['low_balance_threshold'] ? 'badge-soft-success' : ($guard['balance'] > 0 ? 'badge-soft-warning' : 'badge-soft-danger') }}">
                            <i class="fa fa-credit-card"></i>
                        </div>
                        <div>
                            <div class="text-muted small">موجودی فعلی OTP</div>
                            <h4 class="fw-bold mb-0 {{ $guard['balance'] <= 0 ? 'text-danger' : '' }}">{{ number_format($balance) }} <small class="fs-6 text-muted">ریال</small></h4>
                            <small class="{{ $guard['balance'] > $guard['low_balance_threshold'] ? 'text-success' : 'text-danger' }}">
                                {{ $guard['balance'] > $guard['low_balance_threshold'] ? 'وضعیت نرمال' : ($guard['balance'] > 0 ? 'کمتر از حد آستانه' : 'متوقف شده') }}
                            </small>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top text-end">
                        <button type="button" class="btn btn-primary btn-sm w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#otpTopUpModal">
                            <i class="fa fa-plus me-1"></i> افزایش موجودی
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Threshold -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-secondary">
                        <i class="fa fa-bell-o"></i>
                    </div>
                    <div>
                        <div class="text-muted small">آستانه هشدار کسری اعتبار</div>
                        <h4 class="fw-bold mb-0">{{ number_format($low_balance_threshold) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <small class="text-muted">ارسال اخطار زیر ۱۰۰,۰۰۰ تومان (۱,۰۰۰,۰۰۰ ریال)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Charged -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-primary">
                        <i class="fa fa-arrow-down"></i>
                    </div>
                    <div>
                        <div class="text-muted small">مجموع کل شارژ شده</div>
                        <h4 class="fw-bold mb-0 text-primary">{{ number_format($total_charged) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <small class="text-muted">مجموع پرداختی‌ها و شارژها</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Consumed & Message Count -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge badge-soft-danger">
                        <i class="fa fa-paper-plane-o"></i>
                    </div>
                    <div>
                        <div class="text-muted small">کل مصرف شده (تعداد: {{ number_format($message_count) }})</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($total_consumed) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <small class="text-muted">هزینه پیامک‌های ارسال شده</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History & Transactions Table -->
    <div class="row g-4">
        <!-- Daily Aggregate History -->
        @if($history->isNotEmpty())
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="fw-bold mb-0"><i class="fa fa-calendar me-1"></i> خلاصه گردش روزانه</h6>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover align-middle mb-0 text-center small">
                            <thead class="table-light">
                                <tr>
                                    <th>تاریخ</th>
                                    <th>شارژ (ریال)</th>
                                    <th>مصرف (ریال)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $item)
                                    <tr>
                                        <td>{{ $item->date }}</td>
                                        <td class="text-success fw-bold">{{ $item->total_charged > 0 ? '+' . number_format($item->total_charged) : '-' }}</td>
                                        <td class="text-danger fw-bold">{{ $item->total_consumed > 0 ? '-' . number_format($item->total_consumed) : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Transactions List -->
        <div class="col-12 {{ $history->isNotEmpty() ? 'col-lg-8' : 'col-12' }}">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold mb-0"><i class="fa fa-history me-1"></i> ریز تراکنش‌های اعتباری OTP</h6>
                    <span class="badge bg-light text-dark">{{ $transactions->total() }} تراکنش</span>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center small">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>نوع عملیات</th>
                                <th>مبلغ (ریال)</th>
                                <th>مانده قبل</th>
                                <th>مانده بعد</th>
                                <th>شناسه پیگیری</th>
                                <th>ثبت‌کننده / پیامک</th>
                                <th>تاریخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $tx)
                                <tr>
                                    <td>{{ $tx->id }}</td>
                                    <td>
                                        @if($tx->charge_amount > 0)
                                            <span class="badge badge-soft-success px-2 py-1">
                                                <i class="fa fa-plus-circle me-1"></i> شارژ حساب
                                            </span>
                                        @else
                                            <span class="badge badge-soft-danger px-2 py-1">
                                                <i class="fa fa-minus-circle me-1"></i> کسر هزینه پیامک
                                            </span>
                                        @endif
                                    </td>
                                    <td class="fw-bold {{ $tx->charge_amount > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $tx->charge_amount > 0 ? '+' . number_format($tx->charge_amount) : '-' . number_format($tx->consume_amount) }}
                                    </td>
                                    <td>{{ number_format($tx->balance_before) }}</td>
                                    <td class="fw-bold">{{ number_format($tx->balance_after) }}</td>
                                    <td><code>{{ $tx->tracking_id ?? '-' }}</code></td>
                                    <td>
                                        @if($tx->message)
                                            <span class="text-muted" title="{{ $tx->message->recipient_phone }}">
                                                پیام به {{ $tx->message->recipient_phone }}
                                            </span>
                                        @elseif($tx->creator)
                                            {{ $tx->creator->name }} {{ $tx->creator->family }}
                                        @else
                                            سیستم
                                        @endif
                                    </td>
                                    <td>{{ $tx->created_at->format('Y/m/d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-muted py-4">هیچ تراکنشی برای این کسب‌وکار یافت نشد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal: Top Up OTP -->
<div class="modal fade" id="otpTopUpModal" tabindex="-1" aria-labelledby="otpTopUpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.sms.financial.otp.topup') }}" method="POST">
                @csrf
                @if(auth()->user()?->isSuperAdmin())
                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="otpTopUpModalLabel">
                        <i class="fa fa-credit-card me-1 text-primary"></i> شارژ اعتبار پیامک اعتبارسنجی (OTP)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        این مبلغ منحصراً به موجودی OTP کسب‌وکار <strong>{{ $business->title }}</strong> افزوده خواهد شد و با موجودی خطوط تبلیغاتی تداخلی ندارد.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">مبلغ شارژ (ریال) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" placeholder="مثال: 500000" min="1000" step="1000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">شناسه فیش / رهگیری بانکی (اختیاری)</label>
                        <input type="text" name="tracking_id" class="form-control" placeholder="مثال: BANK-12345678">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-check me-1"></i> تایید و افزایش موجودی
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
