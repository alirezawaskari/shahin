@extends('Admin.master')

@section('title', 'مدیریت مالی و موجودی خطوط تبلیغاتی')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">مدیریت مالی و اعتبار خطوط پیامک تبلیغاتی</h3>
            <p class="text-muted small mb-0">مشاهده موجودی بر اساس هر خط فرستنده، شارژ مجزا و کشف خطوط جایگزین دارای اعتبار کافی</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.financial.advertising.index') }}" class="d-flex gap-2">
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

    <!-- Sender Selection Bar -->
    @if($assigned_senders->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
                <span class="fw-bold small me-2"><i class="fa fa-phone me-1"></i> انتخاب خط فرستنده:</span>
                @foreach($assigned_senders as $s)
                    <a href="{{ route('admin.sms.financial.advertising.index', ['sender_number_id' => $s->id]) }}" 
                       class="btn btn-sm {{ $active_sender && $active_sender->id === $s->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $s->number }}
                        @php
                            $bal = $senders_summary->firstWhere('sender.id', $s->id)['balance'] ?? 0;
                        @endphp
                        <span class="badge bg-white text-dark ms-1">{{ number_format($bal) }} ریال</span>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="alert alert-warning shadow-sm border-0 mb-4" role="alert">
            <i class="fa fa-exclamation-triangle me-1"></i> هیچ خط پیامک تبلیغاتی به این کسب‌وکار تخصیص داده نشده است.
        </div>
    @endif

    @if($sender_details)
        @php
            $selectedSender = $sender_details['sender'];
            $selectedBal = $sender_details['balance'];
        @endphp

        <!-- Insufficient Balance & Alternative Sender Banner -->
        @if($selectedBal <= 0)
            @php
                $alternatives = $senders_summary->filter(function($item) use ($selectedSender) {
                    return $item['sender']->id !== $selectedSender->id && $item['balance'] > 0;
                });
            @endphp
            <div class="alert alert-warning shadow-sm border-0 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-4">
                <div>
                    <h5 class="fw-bold text-dark mb-1">
                        <i class="fa fa-info-circle text-warning me-1"></i> موجودی خط {{ $selectedSender->number }} صفر ریال می‌باشد
                    </h5>
                    @if($alternatives->isNotEmpty())
                        <p class="mb-0 text-muted small">
                            خطوط تبلیغاتی جایگزین زیر در همین کسب‌وکار دارای موجودی فعال هستند و می‌توانید از آن‌ها برای ارسال استفاده فرمایید:
                        </p>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            @foreach($alternatives as $alt)
                                <a href="{{ route('admin.sms.financial.advertising.index', ['sender_number_id' => $alt['sender']->id]) }}" class="badge bg-success text-white text-decoration-none px-2 py-1">
                                    خط {{ $alt['sender']->number }} (موجودی: {{ number_format($alt['balance']) }} ریال)
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-0 text-muted small">هیچ خط تبلیغاتی جایگزین دارای اعتباری در این کسب‌وکار موجود نیست. لطفا حساب را شارژ نمایید.</p>
                    @endif
                </div>
                <div class="mt-3 mt-md-0">
                    <button type="button" class="btn btn-warning text-dark btn-sm px-3" data-bs-toggle="modal" data-bs-target="#advTopUpModal">
                        <i class="fa fa-plus-circle me-1"></i> شارژ همین خط
                    </button>
                </div>
            </div>
        @endif

        <!-- Financial KPI Cards for Active Sender -->
        <div class="row g-3 mb-4">
            <!-- Balance -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="p-3 rounded-circle {{ $selectedBal > 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} me-3">
                            <i class="fa fa-credit-card fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">موجودی خط {{ $selectedSender->number }}</div>
                            <h4 class="fw-bold mb-0 {{ $selectedBal <= 0 ? 'text-danger' : '' }}">{{ number_format($selectedBal) }} <small class="fs-6 text-muted">ریال</small></h4>
                            <small class="{{ $selectedBal > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $selectedBal > 0 ? 'دارای اعتبار ارسال' : 'فاقد موجودی' }}
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pt-0 text-end">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#advTopUpModal">
                            <i class="fa fa-plus me-1"></i> شارژ این خط
                        </button>
                    </div>
                </div>
            </div>

            <!-- Total Charged -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary me-3">
                            <i class="fa fa-arrow-down fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">مجموع کل شارژ شده</div>
                            <h4 class="fw-bold mb-0 text-primary">{{ number_format($sender_details['total_charged']) }} <small class="fs-6 text-muted">ریال</small></h4>
                            <small class="text-muted">شارژ اختصاصی این خط</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Consumed -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger me-3">
                            <i class="fa fa-arrow-up fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">مجموع هزینه مصرفی</div>
                            <h4 class="fw-bold mb-0 text-danger">{{ number_format($sender_details['total_consumed']) }} <small class="fs-6 text-muted">ریال</small></h4>
                            <small class="text-muted">هزینه پیامک‌های ارسالی</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Count -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info me-3">
                            <i class="fa fa-envelope-o fa-2x"></i>
                        </div>
                        <div>
                            <div class="text-muted small">تعداد پیامک‌های ارسالی</div>
                            <h4 class="fw-bold mb-0">{{ number_format($sender_details['message_count']) }}</h4>
                            <small class="text-muted">پیام‌های ارسال شده از این خط</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- History & Transactions Table -->
        <div class="row g-4 mb-4">
            <!-- Daily Aggregate History -->
            @if($sender_details['history']->isNotEmpty())
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
                                    @foreach($sender_details['history'] as $item)
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
            <div class="col-12 {{ $sender_details['history']->isNotEmpty() ? 'col-lg-8' : 'col-12' }}">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0"><i class="fa fa-history me-1"></i> ریز تراکنش‌های اعتباری خط {{ $selectedSender->number }}</h6>
                        <span class="badge bg-light text-dark">{{ $sender_details['transactions']->total() }} تراکنش</span>
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
                                    <th>پیامک / کمپین</th>
                                    <th>تاریخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sender_details['transactions'] as $tx)
                                    <tr>
                                        <td>{{ $tx->id }}</td>
                                        <td>
                                            @if($tx->charge_amount > 0)
                                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1">
                                                    <i class="fa fa-plus-circle me-1"></i> شارژ خط
                                                </span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">
                                                    <i class="fa fa-minus-circle me-1"></i> کسر ارسال
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
                                            @if($tx->campaign)
                                                <span class="text-primary">کمپین #{{ $tx->campaign->id }}</span>
                                            @elseif($tx->message)
                                                <span>پیامک به {{ $tx->message->recipient_phone }}</span>
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
                                        <td colspan="8" class="text-muted py-4">هیچ تراکنشی برای این خط یافت نشد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($sender_details['transactions']->hasPages())
                        <div class="card-footer bg-white border-0 py-3">
                            {{ $sender_details['transactions']->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal: Top Up Advertising -->
        <div class="modal fade" id="advTopUpModal" tabindex="-1" aria-labelledby="advTopUpModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('admin.sms.financial.advertising.topup') }}" method="POST">
                        @csrf
                        <input type="hidden" name="sender_number_id" value="{{ $selectedSender->id }}">
                        @if(auth()->user()?->isSuperAdmin())
                            <input type="hidden" name="business_id" value="{{ $business->id }}">
                        @endif
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold" id="advTopUpModalLabel">
                                <i class="fa fa-bullhorn me-1 text-primary"></i> شارژ موجودی خط تبلیغاتی {{ $selectedSender->number }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small">
                                این مبلغ منحصراً به موجودی خط <strong>{{ $selectedSender->number }}</strong> در کسب‌وکار <strong>{{ $business->title }}</strong> افزوده خواهد شد و با سایر خطوط یا موجودی OTP به اشتراک گذاشته نمی‌شود.
                            </p>
                            <div class="mb-3">
                                <label class="form-label fw-bold">مبلغ شارژ (ریال) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" placeholder="مثال: 1000000" min="1000" step="1000" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">شناسه فیش / رهگیری بانکی (اختیاری)</label>
                                <input type="text" name="tracking_id" class="form-control" placeholder="مثال: BANK-ADV-1234">
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
    @endif

    <!-- All Assigned Senders Overview Table -->
    @if($senders_summary->isNotEmpty())
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fa fa-list me-1"></i> وضعیت کلی کلیه خطوط تبلیغاتی تخصیص‌یافته</h6>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 text-center small">
                    <thead class="table-light">
                        <tr>
                            <th>شماره خط</th>
                            <th>پنل</th>
                            <th>موجودی فعلی (ریال)</th>
                            <th>مجموع شارژ شده (ریال)</th>
                            <th>مجموع مصرفی (ریال)</th>
                            <th>تعداد پیامک</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($senders_summary as $item)
                            <tr>
                                <td class="fw-bold">{{ $item['sender']->number }}</td>
                                <td>{{ $item['sender']->sms_panel ?? '-' }}</td>
                                <td class="fw-bold {{ $item['balance'] > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($item['balance']) }}
                                </td>
                                <td>{{ number_format($item['total_charged']) }}</td>
                                <td>{{ number_format($item['total_consumed']) }}</td>
                                <td>{{ number_format($item['message_count']) }}</td>
                                <td>
                                    <a href="{{ route('admin.sms.financial.advertising.index', ['sender_number_id' => $item['sender']->id]) }}" 
                                       class="btn btn-sm btn-outline-primary py-0">
                                        مشاهده ریز تراکنش‌ها
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
