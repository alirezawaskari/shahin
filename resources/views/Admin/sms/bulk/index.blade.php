@extends('Admin.master')

@section('title', 'ارسال پیامک انبوه و کمپین‌ها')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/sms-forms.css') }}">
<style>
    .tier-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
    }
    .tier-silver { background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; }
    .tier-gold { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .tier-diamond { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .tier-titanium { background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe; }
    .tier-superadmin { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .tier-staff { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 12px;
        transition: all 0.2s;
    }
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .stat-value {
        font-size: 20px;
        font-weight: 700;
        margin-top: 4px;
    }
    .stat-card.stat-card-total {
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }
    .stat-card.stat-card-valid {
        background-color: #ecfdf5 !important;
        border: 1px solid #a7f3d0 !important;
    }
    .stat-card.stat-card-valid .stat-label {
        color: #065f46 !important;
        font-weight: 700;
        font-size: 12px;
    }
    .stat-card.stat-card-valid .stat-value {
        color: #059669 !important;
        font-weight: 700;
    }

    .stat-card.stat-card-invalid {
        background-color: #fff1f2 !important;
        border: 1px solid #fecdd3 !important;
    }
    .stat-card.stat-card-invalid .stat-label {
        color: #9f1239 !important;
        font-weight: 700;
        font-size: 12px;
    }
    .stat-card.stat-card-invalid .stat-value {
        color: #e11d48 !important;
        font-weight: 700;
    }

    .stat-card.stat-card-duplicate {
        background-color: #fffbeb !important;
        border: 1px solid #fde68a !important;
    }
    .stat-card.stat-card-duplicate .stat-label {
        color: #92400e !important;
        font-weight: 700;
        font-size: 12px;
    }
    .stat-card.stat-card-duplicate .stat-value {
        color: #d97706 !important;
        font-weight: 700;
    }

    .sender-radio-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        cursor: pointer;
        transition: all 0.2s;
        height: 100%;
    }
    .sender-radio-card:hover {
        border-color: #94a3b8;
    }
    .sender-radio-card.active {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }

    /* Phone Mockup Frame */
    .phone-mockup-wrapper {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        width: 100%;
        margin-bottom: 1.5rem;
    }
    .phone-mockup {
        width: 300px;
        max-width: 100%;
        height: 580px;
        background: #1e1e2d;
        border-radius: 40px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.3), inset 0 0 0 3px #38384f;
        padding: 12px;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .phone-screen {
        background: #f8fafc;
        border-radius: 30px;
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .phone-notch {
        width: 120px;
        height: 16px;
        background: #1e1e2d;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
        z-index: 10;
    }
    .phone-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 22px 12px 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .phone-chat-body {
        flex: 1;
        padding: 14px 10px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        background: #f1f5f9;
    }
    .sms-bubble {
        background: #ffffff;
        color: #1e293b;
        padding: 10px 14px;
        border-radius: 16px 16px 4px 16px;
        max-width: 90%;
        font-size: 13px;
        line-height: 1.6;
        word-break: break-word;
        white-space: pre-wrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        align-self: flex-end;
    }
    .sms-bubble .sms-opt-out {
        color: #94a3b8;
        font-size: 11px;
        border-top: 1px dashed #e2e8f0;
        margin-top: 6px;
        padding-top: 4px;
    }
    .qp-gateway-card {
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
    }
    .qp-gateway-card:hover {
        border-color: #3b82f6 !important;
        background: #f8fafc;
    }
    .qp-gateway-card.active {
        border-color: #3b82f6 !important;
        background: #eff6ff !important;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25);
    }
    .qp-preset-btn {
        transition: all 0.15s ease;
    }
    .quick-charge-sender-btn {
        transition: all 0.15s ease;
        border-radius: 4px;
    }
    .quick-charge-sender-btn:hover {
        background: #f59e0b;
        color: #fff !important;
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa fa-paper-plane text-primary me-2"></i> ارسال پیامک انبوه و مدیریت کمپین‌ها
        </h3>
        <p class="text-muted mb-0 small">
            کسب‌وکار جاری: <strong class="text-dark">{{ $business?->title ?? 'همه کسب‌وکارها' }}</strong>
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @php
            $currentUser = auth()->user();
            $isSuperAdmin = $currentUser?->isSuperAdmin();
            $isStaff = !$isSuperAdmin && ($currentUser?->isAdmin() || in_array($currentUser?->level, ['admin', 'ghorfeAdmin']));

            if ($isSuperAdmin) {
                $badgeClass = 'tier-superadmin';
                $badgeLabel = 'مدیر ارشد (SuperAdmin)';
                $badgeTitle = 'دسترسی کامل مدیریتی به تمام فیلترها و مخاطبان سراسری';
                $badgeIcon = 'fa-star';
            } elseif ($isStaff) {
                $badgeClass = 'tier-staff';
                $badgeLabel = 'پرسنل / مدیر سامانه (Staff)';
                $badgeTitle = 'دسترسی عوامل و پرسنل سامانه به ابزارها و فیلترها';
                $badgeIcon = 'fa-user-secret';
            } else {
                $badgeClass = match($userTier) {
                    'titanium' => 'tier-titanium',
                    'diamond' => 'tier-diamond',
                    'gold' => 'tier-gold',
                    default => 'tier-silver',
                };
                $badgeLabel = 'سطح دسترسی: ' . match($userTier) {
                    'titanium' => 'تیتانیوم (Titanium)',
                    'diamond' => 'الماسی (Diamond)',
                    'gold' => 'طلایی (Gold)',
                    default => 'نقره‌ای (Silver)',
                };
                $badgeTitle = 'سطح دسترسی شما به فیلترها و مخاطبان';
                $badgeIcon = 'fa-shield';
            }
        @endphp
        <div class="tier-badge {{ $badgeClass }}" title="{{ $badgeTitle }}">
            <i class="fa {{ $badgeIcon }}"></i> {{ $badgeLabel }}
        </div>
        <a href="{{ route('admin.sms.bulk.index', ['tab' => 'campaigns']) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-list me-1"></i> تاریخچه کمپین‌ها
        </a>
        <a href="{{ route('admin.sms.bulk.index', ['tab' => 'queue']) }}" class="btn btn-outline-warning btn-sm text-dark">
            <i class="fa fa-clock-o me-1"></i> صف ارسال
            @if(($queuedCount ?? 0) > 0)
                <span class="badge bg-warning text-dark">{{ number_format($queuedCount) }}</span>
            @endif
        </a>
        <a href="{{ route('admin.sms.single.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-envelope me-1"></i> ارسال تکی
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form id="bulkSmsForm" method="POST" action="{{ route('admin.sms.bulk.send') }}">
    @csrf
    {{-- «کاهش تعداد مخاطبین» cap (re-validated against the balance server-side) and the queue entry being resumed --}}
    <input type="hidden" name="max_recipients" id="max_recipients" value="">
    <input type="hidden" name="queue_id" id="queue_id" value="{{ $queuedCampaign?->id }}">
    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <!-- 1. Panel & sender number -->
            <div class="card sms-section">
                <div class="card-header">
                    <h6 class="sms-section-title">
                        <span class="badge bg-primary">۱</span> نوع پنل و سرشماره
                    </h6>
                </div>
                <div class="card-body">
                    <div class="sms-field">
                        <label class="sms-label" for="campaign_name">نام کمپین</label>
                        <input type="text" name="campaign_name" id="campaign_name" class="form-control" maxlength="100" placeholder="مثال: جشنواره پاییزه" value="{{ old('campaign_name') }}">
                        <div class="sms-hint">اختیاری؛ برای پیدا کردن کمپین در تب «کمپین‌ها» و «صف ارسال».</div>
                    </div>

                    <div class="sms-field">
                        <span class="sms-label">نوع پنل پیامک</span>
                        <div class="sms-choice-row">
                            <label class="sms-choice fw-bold" for="serviceTypeAdv">
                                <input class="form-check-input" type="radio" name="service_type" id="serviceTypeAdv" value="advertising" checked>
                                <span><i class="fa fa-bullhorn text-warning me-1"></i> پنل پیامکی تبلیغاتی/اطلاع‌رسانی</span>
                            </label>
                            @if($canUseOtp ?? false)
                                <label class="sms-choice fw-bold" for="serviceTypeOtp">
                                    <input class="form-check-input" type="radio" name="service_type" id="serviceTypeOtp" value="otp">
                                    <span><i class="fa fa-key text-success me-1"></i> پنل پیامکی OTP</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    {{-- Promotional panel: the business' own lines --}}
                    <div id="advertisingSenderContainer">
                    <span class="sms-label">سرشماره‌های تخصیص‌یافته به کسب‌وکار</span>
                    @if($advertisingNumbers->isEmpty())
                        <div class="alert alert-danger mb-3">
                            <i class="fa fa-exclamation-triangle me-1"></i> هیچ سرشماره اختصاصی تبلیغاتی به این کسب‌وکار تخصیص داده نشده است. لطفا با پشتیبانی تماس بگیرید.
                        </div>
                    @else
                        <div class="row g-3 mb-3">
                            @foreach($advertisingNumbers as $index => $sender)
                                <div class="col-md-6">
                                    <div class="sender-radio-card {{ $index === 0 ? 'active' : '' }}" onclick="selectSenderNumber({{ $sender->id }})">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="sender_number_id" id="sender_{{ $sender->id }}" value="{{ $sender->id }}" {{ $index === 0 ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bold" for="sender_{{ $sender->id }}">
                                                    {{ $sender->number }}
                                                </label>
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                @if($sender->isServiceLine())
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle small px-2 py-1" style="font-size: 11px;">
                                                        <i class="fa fa-check-circle me-1"></i> خدماتی
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small px-2 py-1" style="font-size: 11px;">
                                                        عادی
                                                    </span>
                                                @endif
                                                <span class="badge bg-success small">فعال</span>
                                            </div>
                                        </div>
                                        <div class="mt-2 text-muted small d-flex justify-content-between align-items-center">
                                            <span>موجودی پنل تبلیغاتی:</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="text-dark" id="sender_balance_{{ $sender->id }}">{{ number_format($sender->current_balance) }} ریال</strong>
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0 px-2 fw-semibold quick-charge-sender-btn" style="font-size: 11px;" onclick="event.stopPropagation(); openQuickPaymentGate(null, {{ $sender->id }}, '{{ $sender->number }}', {{ $sender->current_balance }});" title="شارژ سریع آنلاین پنل تبلیغاتی">
                                                    <i class="fa fa-bolt me-1 text-warning"></i> شارژ سریع
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    </div>

                    {{-- OTP panel: one fixed system line, no choice --}}
                    @if($canUseOtp ?? false)
                        <div id="otpSenderContainer" style="display: none;">
                            <span class="sms-label">سرشماره OTP</span>
                            @if($otpSender)
                                <div class="sms-subbox mb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <span class="badge bg-success me-2">تخصیص خودکار سامانه</span>
                                        <strong class="font-monospace fs-6">{{ $otpSender->number }}</strong>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info text-dark">موجودی OTP: <span id="otpBalanceDisplay">{{ number_format($otpBalance) }}</span> ریال</span>
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0 px-2 fw-semibold" style="font-size: 11px;" onclick="openQuickPaymentGate(null)" title="شارژ سریع پنل OTP">
                                            <i class="fa fa-bolt me-1 text-warning"></i> شارژ سریع
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 small mb-0">سرشماره OTP در سامانه یافت نشد.</div>
                            @endif
                            <div class="sms-hint text-info"><i class="fa fa-lock me-1"></i> سرشماره OTP به صورت خودکار تعیین می‌شود و قابل تغییر نیست.</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- 2. Audience filter -->
            <div class="card sms-section">
                <div class="card-header">
                    <h6 class="sms-section-title">
                        <span class="badge bg-primary">۲</span> فیلتر مخاطبان
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Audience Filter Section (Requests Filter) -->
                    <div class="sms-subbox">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark small">
                                <i class="fa fa-filter text-primary me-1"></i> فیلتر مخاطبان:
                            </span>
                        </div>

                        <!-- Requests Filter Actions & Status Card -->
                        <div class="card border bg-white mb-3 shadow-none">
                            <div class="card-body p-3">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div>
                                        <div class="fw-bold small text-dark mb-1">
                                            <i class="fa fa-list-alt text-primary ms-1"></i> فیلتر پیشرفته درخواست‌ها
                                        </div>
                                        <div class="text-muted small">
                                            مخاطبان این پیامک بر اساس فیلترهای بخش «درخواست‌ها» تعیین می‌شوند.
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-primary btn-sm px-3 fw-bold" id="open_request_filter">
                                            <i class="fa fa-sliders ms-1"></i> تنظیم و ویرایش فیلترها
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm d-none" id="request_filter_quick_clear">
                                            <i class="fa fa-trash-o ms-1"></i> حذف فیلترها
                                        </button>
                                    </div>
                                </div>

                                <div id="requestFilterBadgeContainer" class="mt-2 pt-2 border-top d-none">
                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                        <span id="requestFilterBadge" class="badge bg-success d-inline-flex align-items-center py-1 px-2 small">
                                            <i class="fa fa-check-circle ms-1"></i> فیلتر درخواست‌ها فعال است (<span data-count>0</span> فیلد)
                                            <button type="button" class="btn-close btn-close-white ms-2" id="request_filter_remove" aria-label="حذف فیلتر درخواست‌ها" style="font-size: .55rem;"></button>
                                        </span>
                                        <div id="requestFilterSummary" class="d-flex flex-wrap gap-1 align-items-center"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="requestFilterInputs"></div>

                        <!-- Manual / Custom Numbers Addition -->
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-dark">شماره‌های همراه اضافی (اختیاری - با خط جدید یا کاما جدا کنید):</label>
                                <textarea name="custom_numbers" id="custom_numbers" rows="2" class="form-control form-control-sm font-monospace" placeholder="09120000000, 09350000000 ..."></textarea>
                                <div class="mt-2 d-flex flex-wrap align-items-center gap-2">
                                    <input type="file" id="excel_numbers_file" accept=".xlsx,.xls,.csv" class="d-none">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="btnExcelNumbers" data-url="{{ route('admin.sms.bulk.excel') }}">
                                        <i class="fa fa-file-excel-o ms-1"></i> ورود شماره‌ها از اکسل
                                    </button>
                                    <span class="small text-muted">فایل xlsx / xls / csv تا ۲ مگابایت؛ شماره‌ها از هر ستونی خوانده می‌شوند.</span>
                                    <span id="excelNumbersResult" class="small"></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="button" id="btnPreviewRecipients" class="btn btn-primary btn-sm px-3">
                                <i class="fa fa-search me-1"></i> فیلتر و شمارش مخاطبین معتبر
                            </button>
                        </div>
                    </div>

                    <!-- Audit / Filter Results Panel -->
                    <div id="filterAuditCard" class="card border-0 bg-white shadow-sm mt-3" style="display: none;">
                        <div class="card-body p-3">
                            <h6 class="fw-bold mb-3 small text-dark">
                                <i class="fa fa-calculator text-primary me-1"></i> نتیجه پالایش و اعتبارسنجی شماره‌ها:
                            </h6>
                            <div class="row g-2 text-center">
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-total p-2">
                                        <div class="text-muted small">کل شماره‌ها</div>
                                        <div class="stat-value text-secondary" id="stat_total">۰</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-valid p-2">
                                        <div class="stat-label">شماره‌های معتبر نهایی</div>
                                        <div class="stat-value" id="stat_valid">۰</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-invalid p-2">
                                        <div class="stat-label">شماره‌های نامعتبر</div>
                                        <div class="stat-value" id="stat_invalid">۰</div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="stat-card stat-card-duplicate p-2">
                                        <div class="stat-label">تکراری (حذف‌شده)</div>
                                        <div class="stat-value" id="stat_duplicate">۰</div>
                                    </div>
                                </div>
                            </div>
                            <div id="invalidNumbersDetails" class="alert alert-light border mt-2 p-2 small mb-0 text-muted" style="display: none;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Message Composition & Template -->
            <div class="card sms-section">
                <div class="card-header">
                    <h6 class="sms-section-title">
                        <span class="badge bg-primary">۳</span> تنظیم متن پیامک و قالب
                    </h6>
                </div>
                <div class="card-body">
                    {{-- OTP panel only: fixed system category + classification. The promotional panel has no folder or category. --}}
                    @if($canUseOtp ?? false)
                        @php
                            $otpVisible = old('service_type') === 'otp';
                        @endphp
                        <div class="sms-subbox" id="otpExtraBox" @unless($otpVisible) style="display: none;" @endunless>
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="sms-label" for="otpCategorySelect"><i class="fa fa-tags text-warning me-1"></i> دسته‌بندی سیستمی OTP <span class="text-danger">*</span></label>
                                    <select name="otp_category" id="otpCategorySelect" class="form-select" @unless($otpVisible) disabled @endunless>
                                        <option value="">-- انتخاب کنید --</option>
                                        @foreach(\App\Enums\SmsOtpCategory::options() as $otpCatSlug => $otpCatLabel)
                                            <option value="{{ $otpCatSlug }}" {{ old('otp_category') === $otpCatSlug ? 'selected' : '' }}>{{ $otpCatLabel }}</option>
                                        @endforeach
                                    </select>
                                    <div class="sms-hint">هر پیامک OTP باید دقیقاً یکی از دسته‌بندی‌های ثابت سیستم را داشته باشد.</div>
                                </div>
                                <div class="col-md-5" id="classificationCol">
                                    <span class="sms-label"><i class="fa fa-shield text-info me-1"></i> نوع پیام (سطح طبقه‌بندی)</span>
                                    <div class="sms-choice-row flex-nowrap">
                                        <label class="sms-choice small fw-semibold" id="typeRegularWrapper" for="typeRegular" style="flex-basis: 0;">
                                            <input class="form-check-input" type="radio" name="type" id="typeRegular" value="regular" {{ old('type', old('classification', 'regular')) === 'regular' ? 'checked' : '' }} @unless($otpVisible) disabled @endunless>
                                            <span><i class="fa fa-envelope-o text-secondary me-1"></i> عادی</span>
                                        </label>
                                        <label class="sms-choice small fw-bold text-danger" id="typeClassifiedWrapper" for="typeClassified" style="flex-basis: 0;">
                                            <input class="form-check-input" type="radio" name="type" id="typeClassified" value="classified" {{ old('type', old('classification')) === 'classified' ? 'checked' : '' }} @unless($otpVisible) disabled @endunless>
                                            <span><i class="fa fa-lock text-danger me-1"></i> محرمانه</span>
                                        </label>
                                    </div>
                                    <div class="sms-hint" id="classificationHint">
                                        <span class="text-muted"><i class="fa fa-info-circle me-1"></i> پیام‌های مالی و حقوقی به‌طور خودکار «محرمانه» تعیین می‌شوند.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Template bar: picking a template puts its text straight into the message box. --}}
                    <div class="sms-field">
                        <label class="sms-label" for="sms_message_template_id">قالب آماده</label>
                        <div class="input-group">
                            <select name="sms_message_template_id" id="sms_message_template_id" class="form-select">
                                <option value="">-- بدون قالب (تایپ متن دلخواه) --</option>
                                @foreach($templates as $tmpl)
                                    @php
                                        $tmplIsOtp = $tmpl->service_type === 'otp';
                                        $tmplPromo = !$tmplIsOtp && (!$tmpl->group || $tmpl->group->isPromotional());
                                    @endphp
                                    @if(!$tmplIsOtp && $tmplPromo)
                                        <option value="{{ $tmpl->id }}" data-content="{{ $tmpl->content }}" data-type="{{ $tmpl->service_type }}" data-promotional="1" data-otp-category="{{ $tmpl->otp_category }}" data-group-id="{{ $tmpl->sms_message_group_id }}">
                                            [{{ \App\Support\SmsLabels::panel($tmpl->service_type) }}] {{ $tmpl->title }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-outline-success" id="saveAsTemplateBtn" data-bs-toggle="modal" data-bs-target="#saveTemplateModal" title="ایجاد قالب پیامکی جدید">
                                <i class="fa fa-plus-circle me-1"></i> قالب جدید
                            </button>
                        </div>
                        <div class="sms-hint">با انتخاب قالب، متن آن مستقیماً در کادر پیام قرار می‌گیرد و قابل ویرایش است.</div>
                    </div>

                    {{-- Shared editor: textarea + dynamic-parameter chips + live counter --}}
                    @include('Admin.sms.partials.message-editor', [
                        'editorLabel' => 'متن پیامک',
                        'id' => 'sms_message',
                        'name' => 'message',
                        'value' => '',
                        'rows' => 6,
                        'placeholder' => 'متن پیامک تبلیغاتی یا اطلاع‌رسانی خود را بنویسید...',
                        'costPerPage' => $costPerPage,
                        'advertising' => true,
                        'chips' => [],
                    ])
                </div>
            </div>

            <!-- 4. Cost check -->
            <div class="card sms-section">
                <div class="card-header">
                    <h6 class="sms-section-title">
                        <span class="badge bg-primary">۴</span> بررسی مالی و هزینه
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Recipients actually sent to: the filter decides the maximum, the sender may only lower it -->
                    <div class="row g-3 mb-3 align-items-start">
                        <div class="col-md-5">
                            <label class="sms-label" for="recipient_limit">تعداد مخاطبین ارسال</label>
                            <input type="number" id="recipient_limit" class="form-control" min="1" step="1" disabled placeholder="—">
                            <div class="sms-hint" id="recipient_limit_hint">پس از محاسبه، تعداد مخاطبین حاصل از فیلتر اینجا نمایش داده می‌شود؛ فقط می‌توانید آن را کم کنید.</div>
                        </div>
                    </div>

                    <!-- Financial Summary & Balance Check Box -->
                    <div id="financialSummaryCard" class="sms-summary">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <div class="text-muted small">هزینه هر پیامک:</div>
                                <div class="fw-bold text-dark fs-6" id="cost_per_sms">۰ ریال</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small">هزینه کل کمپین:</div>
                                <div class="fw-bold text-primary fs-5" id="campaign_total_cost">۰ ریال</div>
                                <div class="text-muted small" style="font-size: 11px;">(مخاطبین معتبر × صفحات × تعرفه هر صفحه)</div>
                            </div>
                            <div class="col-md-4">
                                <div id="balanceSufficiencyStatus">
                                    <span class="badge bg-secondary p-2 d-block">برای محاسبه دکمه بررسی را بزنید</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Payment Gate Callout (Shown when balance is low or deficit exists) -->
                    <div id="quickPaymentCallout" class="card border border-warning shadow-sm mt-3" style="display: none; background: #fffdf5;">
                        <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 42px; height: 42px; min-width: 42px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                    <i class="fa fa-credit-card"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                        <span id="quickPaymentCalloutTitle">موجودی برای ارسال این کمپین ناکافی است</span>
                                        <span class="badge bg-danger text-white small" id="quickPaymentDeficitBadge">کسری: ۰ ریال</span>
                                    </h6>
                                    <p class="text-muted small mb-0" id="quickPaymentCalloutDesc">
                                        جهت ارسال فوری و بدون خروج از این صفحه، اعتبار این خط را به صورت آنلاین و درجا شارژ فرمایید.
                                    </p>
                                </div>
                            </div>
                            <div class="d-flex flex-column gap-2">
                                <button type="button" class="btn btn-warning text-dark fw-bold btn-sm px-3 shadow-sm text-nowrap" id="btnOpenQuickPaymentFromCallout" onclick="openQuickPaymentGate()">
                                    <i class="fa fa-bolt me-1"></i> اتصال به درگاه پرداخت سریع
                                </button>
                                <div id="insufficientBalanceActions" class="d-flex flex-column gap-2" style="display: none !important;">
                                    <button type="button" class="btn btn-outline-primary btn-sm fw-bold text-nowrap" id="btnReduceRecipients" onclick="reduceRecipients()">
                                        <i class="fa fa-users me-1"></i> کاهش تعداد مخاطبین
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm fw-bold text-nowrap" onclick="reopenAudienceFilter()">
                                        <i class="fa fa-filter me-1"></i> فیلتر مجدد
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Scheduling & Confirmation -->
            <div class="card sms-section">
                <div class="card-header">
                    <h6 class="sms-section-title">
                        <span class="badge bg-primary">۵</span> زمان ارسال و تأیید نهایی
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">نوع ارسال:</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="send_type" id="send_immediate" value="immediate" checked onchange="toggleScheduleInput(false)">
                                    <label class="form-check-label" for="send_immediate">ارسال فوری (پس از تایید)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="send_type" id="send_scheduled" value="scheduled" onchange="toggleScheduleInput(true)">
                                    <label class="form-check-label" for="send_scheduled">ارسال زمان‌بندی شده</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="scheduleInputWrapper" style="display: none;">
                            <label class="form-label small fw-bold">تاریخ و ساعت ارسال:</label>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" id="btnLaunchCampaign" class="btn btn-success btn-lg fw-bold" {{ $advertisingNumbers->isEmpty() ? 'disabled' : '' }}>
                            <i class="fa fa-send me-2"></i> تأیید و ثبت کمپین پیامکی
                        </button>
                        <button type="button" id="btnSaveToQueue" class="btn btn-outline-warning text-dark fw-bold mt-2">
                            <i class="fa fa-clock-o me-2"></i> ذخیره در صف ارسال
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phone Preview Side Column -->
        <div class="col-lg-4">
            <div class="phone-mockup-wrapper">
                <div class="phone-mockup">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <div class="phone-header">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 13px;">
                                <i class="fa fa-comments"></i>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div class="fw-bold small text-truncate" id="phonePreviewSender" dir="ltr">
                                    {{ $advertisingNumbers->first()?->number ?? 'شماره تبلیغاتی' }}
                                </div>
                                <div class="text-muted" style="font-size: 10px;">پیش‌نمایش زنده پیامک</div>
                            </div>
                        </div>
                        <div class="phone-chat-body">
                            <div class="sms-bubble">
                                <div id="phoneBubbleText">متن پیامک شما اینجا نمایش داده خواهد شد...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Persistent read-only regulatory rules panel (task.md §2-6) --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white py-2 px-3">
                    <small class="fw-bold text-muted"><i class="fa fa-balance-scale me-1"></i> ضوابط و مقررات ارسال پیامک</small>
                </div>
                <div class="card-body p-3" style="max-height: 160px; overflow-y: auto;">
                    <p class="small text-muted mb-0" style="white-space: pre-wrap;">{{ $regulatoryRulesText }}</p>
                </div>
            </div>
        </div>
    </div>
</form>

@include('Admin.sms.bulk.partials.tabs', [
    'activeTab' => $activeTab,
    'recentMessages' => $recentMessages,
    'queuedList' => $queuedList,
    'campaignList' => $campaignList,
    'queuedCount' => $queuedCount ?? 0,
])
@include('Admin.sms.partials.forward-modal')
@include('Admin.sms.bulk.partials.save-template-modal')

{{-- The requests-filter pop-up lives OUTSIDE #bulkSmsForm: its fields are criteria, not form fields. --}}
@include('Admin.callcenter.partials.filter-modal', ['restrictUsersToBusiness' => $business ?? null, 'previewUrl' => route('admin.sms.bulk.preview')])

<!-- Quick Payment Gate Modal (مودال درگاه پرداخت سریع) -->
<div class="modal fade" id="quickPaymentModal" tabindex="-1" aria-labelledby="quickPaymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="quickPaymentModalLabel">
                            درگاه پرداخت سریع و افزایش آنی اعتبار پیامک
                        </h5>
                        <small class="text-muted">شارژ مستقیم پنل و فعال‌سازی فوری کمپین بدون رفرش صفحه</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Banner -->
                <div class="row g-2 mb-4 p-3 rounded border bg-light">
                    <div class="col-sm-4 border-end">
                        <small class="text-muted d-block">شماره فرستنده انتخابی:</small>
                        <strong class="text-dark fs-6 font-monospace" id="qpModalSenderNumber" dir="ltr">-</strong>
                    </div>
                    <div class="col-sm-4 border-end">
                        <small class="text-muted d-block">موجودی فعلی پنل تبلیغاتی:</small>
                        <strong class="text-dark fs-6" id="qpModalCurrentBalance">۰ ریال</strong>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">کسری برآورد شده کمپین:</small>
                        <strong class="text-danger fs-6" id="qpModalDeficitAmount">۰ ریال</strong>
                    </div>
                </div>

                <!-- Step 1: Amount Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2">
                        <span class="badge bg-primary me-1">۱</span> انتخاب یا تعیین مبلغ شارژ (ریال):
                    </label>

                    <!-- Smart Deficit Preset Button (Shown if deficit > 0) -->
                    <div id="qpSmartDeficitWrapper" class="mb-3" style="display: none;">
                        <button type="button" class="btn btn-outline-danger w-100 p-2 text-start d-flex justify-content-between align-items-center shadow-sm" id="qpBtnSmartDeficit" onclick="selectQuickPreset(this, null)">
                            <span>
                                <i class="fa fa-bolt text-danger me-2"></i>
                                <strong>شارژ دقیق به میزان کسری کمپین:</strong>
                            </span>
                            <span class="badge bg-danger text-white fs-6 font-monospace" id="qpSmartDeficitValue">۰ ریال</span>
                        </button>
                    </div>

                    <!-- Preset Chips -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-preset-btn" data-amount="500000" onclick="selectQuickPreset(this, 500000)">۵۰۰,۰۰۰ ریال <small class="text-muted">(۵۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-preset-btn" data-amount="1000000" onclick="selectQuickPreset(this, 1000000)">۱,۰۰۰,۰۰۰ ریال <small class="text-muted">(۱۰۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-preset-btn" data-amount="2000000" onclick="selectQuickPreset(this, 2000000)">۲,۰۰۰,۰۰۰ ریال <small class="text-muted">(۲۰۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-preset-btn" data-amount="5000000" onclick="selectQuickPreset(this, 5000000)">۵,۰۰۰,۰۰۰ ریال <small class="text-muted">(۵۰۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-preset-btn" data-amount="10000000" onclick="selectQuickPreset(this, 10000000)">۱۰,۰۰۰,۰۰۰ ریال <small class="text-muted">(۱ م ت)</small></button>
                    </div>

                    <!-- Amount Input -->
                    <div class="input-group input-group-lg">
                        <input type="number" id="qpCustomAmountInput" class="form-control font-monospace text-center fw-bold" placeholder="مبلغ شارژ را وارد کنید" min="10000" step="10000">
                        <span class="input-group-text bg-light text-muted fw-bold">ریال</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                        <small class="text-muted" id="qpTomanEquivalent">معادل: ۰ تومان</small>
                        <small class="text-muted">حداقل مبلغ شارژ: ۱۰,۰۰۰ ریال</small>
                    </div>
                </div>

                <!-- Step 2: Payment Gateway Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2">
                        <span class="badge bg-primary me-1">۲</span> انتخاب درگاه پرداخت الکترونیک بانکی (شاپرک):
                    </label>
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer active" for="gw_sadad_bulk">
                                <input type="radio" name="qp_gateway" id="gw_sadad_bulk" value="sadad" class="form-check-input d-none" checked onchange="highlightGateway(this)">
                                <div class="fs-4 text-primary mb-1"><i class="fa fa-university"></i></div>
                                <div class="fw-bold small text-dark">سداد (ملی)</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_mellat_bulk">
                                <input type="radio" name="qp_gateway" id="gw_mellat_bulk" value="mellat" class="form-check-input d-none" onchange="highlightGateway(this)">
                                <div class="fs-4 text-danger mb-1"><i class="fa fa-credit-card"></i></div>
                                <div class="fw-bold small text-dark">به‌پرداخت ملت</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_sep_bulk">
                                <input type="radio" name="qp_gateway" id="gw_sep_bulk" value="sep" class="form-check-input d-none" onchange="highlightGateway(this)">
                                <div class="fs-4 text-info mb-1"><i class="fa fa-shield"></i></div>
                                <div class="fw-bold small text-dark">سامان‌کیش (سپ)</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_sim_bulk">
                                <input type="radio" name="qp_gateway" id="gw_sim_bulk" value="simulator" class="form-check-input d-none" onchange="highlightGateway(this)">
                                <div class="fs-4 text-success mb-1"><i class="fa fa-bolt"></i></div>
                                <div class="fw-bold small text-dark">شارژ درجا و آنی</div>
                                <small class="text-success" style="font-size: 10px;">تست و شارژ مستقیم</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Security Note -->
                <div class="alert alert-light border p-2 small mb-0 text-muted d-flex align-items-center gap-2">
                    <i class="fa fa-lock text-success fs-5"></i>
                    <div>
                        اتصال به درگاه امن شاپرک تحت پروتکل رمزنگاری SSL انجام شده و پس از تکمیل عملیات پرداخت، موجودی خط انتخابی فوراً شارژ خواهد شد.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-outline-primary fw-bold" id="qpBtnReduceRecipients" onclick="reduceRecipients()" style="display: none;">
                    <i class="fa fa-users me-1"></i> کاهش تعداد مخاطبین
                </button>
                <button type="button" class="btn btn-outline-secondary fw-bold" onclick="reopenAudienceFilter()">
                    <i class="fa fa-filter me-1"></i> فیلتر مجدد
                </button>
                <button type="button" class="btn btn-success fw-bold px-4" id="btnExecuteQuickPay" onclick="executeQuickPayment()">
                    <i class="fa fa-shield me-1"></i> پرداخت و افزایش آنی موجودی
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/dashboard/js/layout/productServiceFilterInputs.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/requestFilterPicker.js?v=1.0') }}"></script>
<script src="{{ asset('assets/dashboard/js/excelNumbers.js?v=1.0') }}"></script>
<script src="{{ asset('assets/dashboard/js/sms-param-chips.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/sms-message-editor.js') }}"></script>
<script>
    // Dynamic parameter tokens inside a selected template render as styled,
    // non-text-editable chips instead of raw "{...}" text.
    // Tariff per SMS page, from config('services.sms.cost_per_page') — used by the live estimate.
    const COST_PER_PAGE = @json($costPerPage);

    // Shared editor: owns the chip editor + the in-field counter (chars / remaining / pages / encoding).
    const bulkSmsEditor = SmsMessageEditor.get('sms_message');
    const bulkMessageChipEditor = bulkSmsEditor.chips;

    // ---- Panel (promotional / OTP). The OTP radio only exists for tiers that may use it.
    const serviceTypeOtp = document.getElementById('serviceTypeOtp');
    const OTP_NUMBER = @json($otpSender?->number);
    let otpBalance = @json((float) ($otpBalance ?? 0));

    function isOtpPanel() {
        return !!(serviceTypeOtp && serviceTypeOtp.checked);
    }

    function onServiceTypeChange(skipPreview) {
        const otp = isOtpPanel();
        document.getElementById('advertisingSenderContainer').style.display = otp ? 'none' : '';
        const otpBox = document.getElementById('otpSenderContainer');
        if (otpBox) otpBox.style.display = otp ? '' : 'none';

        // Disabled fields are not submitted: the OTP panel has no chosen line, the promotional one no category.
        document.querySelectorAll('input[name="sender_number_id"]').forEach(i => { i.disabled = otp; });
        const extra = document.getElementById('otpExtraBox');
        if (extra) extra.style.display = otp ? '' : 'none';
        const cat = document.getElementById('otpCategorySelect');
        if (cat) { cat.disabled = !otp; cat.required = otp; }
        document.querySelectorAll('input[name="classification"], input[name="type"]').forEach(i => { i.disabled = !otp; });

        // «لغو 11» belongs to the promotional panel only.
        bulkSmsEditor.setAdvertising(!otp);

        // Quick template create button remains accessible on OTP
        const saveTplBtn = document.getElementById('saveAsTemplateBtn');
        if (saveTplBtn) {
            saveTplBtn.disabled = false;
            saveTplBtn.title = otp ? 'ایجاد قالب پیامکی جدید برای پنل OTP' : 'ایجاد قالب پیامکی جدید';
        }

        // Filter templates: OTP panel shows OTP templates; Advertising panel shows advertising/promotional templates
        rebuildBulkTemplateSelect(otp ? 'otp' : 'advertising');

        const phoneSender = document.getElementById('phonePreviewSender');
        if (otp) {
            phoneSender.innerText = OTP_NUMBER || 'OTP-System';
        } else {
            const checked = document.querySelector('input[name="sender_number_id"]:checked');
            if (checked) phoneSender.innerText = checked.nextElementSibling.innerText.trim();
        }

        updateMessageStatsAndMockup();
        if (skipPreview !== true) triggerPreview();
    }

    const allTemplatesBulk = [
        @foreach($templates as $tmpl)
        @php
            $tmplIsOtp = $tmpl->service_type === 'otp';
            $tmplPromo = !$tmplIsOtp && (!$tmpl->group || $tmpl->group->isPromotional());
        @endphp
        {
            id: "{{ $tmpl->id }}",
            title: @json($tmpl->title),
            panelLabel: @json(\App\Support\SmsLabels::panel($tmpl->service_type)),
            content: @json($tmpl->content),
            type: @json($tmpl->service_type),
            promotional: {{ $tmplPromo ? 'true' : 'false' }},
            otpCategory: @json($tmpl->otp_category)
        },
        @endforeach
    ];

    function rebuildBulkTemplateSelect(serviceType, selectedId) {
        const tplSelect = document.getElementById('sms_message_template_id');
        if (!tplSelect) return;
        const currentVal = selectedId !== undefined ? selectedId : tplSelect.value;
        tplSelect.innerHTML = '<option value="">-- بدون قالب (تایپ متن دلخواه) --</option>';

        allTemplatesBulk.forEach(function (tpl) {
            const matches = serviceType === 'otp' ? (tpl.type === 'otp') : (tpl.type !== 'otp' && tpl.promotional);
            if (matches) {
                const opt = document.createElement('option');
                opt.value = tpl.id;
                opt.textContent = '[' + tpl.panelLabel + '] ' + tpl.title;
                opt.setAttribute('data-content', tpl.content || '');
                opt.setAttribute('data-type', tpl.type);
                opt.setAttribute('data-otp-category', tpl.otpCategory || '');
                if (String(currentVal) === String(tpl.id)) {
                    opt.selected = true;
                }
                tplSelect.appendChild(opt);
            }
        });

        if (tplSelect.value !== currentVal) {
            tplSelect.value = '';
        }

        if (window.jQuery || window.$) {
            const $tpl = (window.jQuery || window.$)(tplSelect);
            if ($tpl.hasClass('select2-hidden-accessible')) {
                $tpl.trigger('change.select2');
            }
        }
    }

    document.getElementById('serviceTypeAdv').addEventListener('change', () => onServiceTypeChange());
    if (serviceTypeOtp) serviceTypeOtp.addEventListener('change', () => onServiceTypeChange());

    function selectSenderNumber(id) {
        document.querySelectorAll('.sender-radio-card').forEach(el => el.classList.remove('active'));
        const input = document.getElementById('sender_' + id);
        if (input) {
            input.checked = true;
            input.closest('.sender-radio-card').classList.add('active');
            const numText = input.nextElementSibling.innerText.trim();
            document.getElementById('phonePreviewSender').innerText = numText;
        }
        triggerPreview();
    }

    function toggleScheduleInput(isScheduled) {
        document.getElementById('scheduleInputWrapper').style.display = isScheduled ? 'block' : 'none';
    }

    // Central function to apply selected bulk template
    function applySelectedBulkTemplate(templateId) {
        const tplSelect = document.getElementById('sms_message_template_id');
        if (!templateId) {
            if (bulkSmsEditor) {
                try { bulkSmsEditor.setValue(''); } catch (e) {}
            }
            const msgEl = document.getElementById('sms_message');
            if (msgEl) msgEl.value = '';
            const editorDiv = document.getElementById('sms_messageEditor') || document.querySelector('.sms-param-editor');
            if (editorDiv) editorDiv.innerHTML = '';
            updateMessageStatsAndMockup();
            triggerPreview();
            return;
        }

        const tpl = allTemplatesBulk.find(t => String(t.id) === String(templateId));
        let content = tpl ? (tpl.content || '') : '';
        if (!content && tplSelect && tplSelect.selectedOptions && tplSelect.selectedOptions.length > 0) {
            content = tplSelect.selectedOptions[0].getAttribute('data-content') || '';
        }

        if (bulkSmsEditor) {
            try {
                bulkSmsEditor.setValue(content);
            } catch (err) {
                console.error('bulkSmsEditor.setValue error:', err);
            }
        }
        const msgEl = document.getElementById('sms_message');
        if (msgEl) {
            msgEl.value = content;
            try {
                msgEl.dispatchEvent(new Event('input', { bubbles: true }));
                msgEl.dispatchEvent(new Event('change', { bubbles: true }));
            } catch (e) {}
        }
        const editorDiv = document.getElementById('sms_messageEditor') || document.querySelector('.sms-param-editor');
        if (editorDiv && (!editorDiv.textContent || editorDiv.textContent.trim() === '') && content) {
            editorDiv.innerText = content;
        }

        if (tpl && tpl.type === 'otp' && tpl.otpCategory) {
            const catSelect = document.getElementById('otpCategorySelect');
            if (catSelect) {
                catSelect.value = tpl.otpCategory;
                updateClassificationFromOtpCategory();
            }
        }

        updateMessageStatsAndMockup();
        triggerPreview();
    }

    // Classification Type Reaction from OTP Category
    const otpCategorySelect = document.getElementById('otpCategorySelect');
    const typeRegular = document.getElementById('typeRegular');
    const typeClassified = document.getElementById('typeClassified');
    const classificationHint = document.getElementById('classificationHint');

    function updateClassificationFromOtpCategory() {
        if (!otpCategorySelect) return;
        const cat = otpCategorySelect.value;
        if (!cat) {
            if (classificationHint) {
                classificationHint.innerHTML = '<span class="text-muted"><i class="fa fa-info-circle ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> پیام‌های مالی و حقوقی به‌طور خودکار «محرمانه» تعیین می‌شوند.</span>';
            }
            return;
        }

        // mali and hoghoghi OTP categories are by default classified
        if (cat === 'financial' || cat === 'legal' || cat === 'finance' || cat === 'mali' || cat === 'hoghoghi') {
            if (typeClassified) typeClassified.checked = true;
            if (classificationHint) {
                classificationHint.innerHTML = '<span class="text-danger fw-bold"><i class="fa fa-lock ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> سطح پیام به صورت خودکار به دلیل ماهیت مالی/حقوقی «محرمانه» تعیین شد.</span>';
            }
        } else {
            if (typeRegular) typeRegular.checked = true;
            if (classificationHint) {
                classificationHint.innerHTML = '<span class="text-success"><i class="fa fa-check-circle ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> سطح پیام به صورت پیش‌فرض «عادی» است.</span>';
            }
        }
    }

    if (otpCategorySelect) {
        otpCategorySelect.addEventListener('change', updateClassificationFromOtpCategory);
        if (otpCategorySelect.value) {
            updateClassificationFromOtpCategory();
        }
    }

    // Auto update message from template selection (supports native, jQuery and Select2)
    const bulkTplSelect = document.getElementById('sms_message_template_id');
    if (bulkTplSelect) {
        bulkTplSelect.addEventListener('change', function () {
            applySelectedBulkTemplate(this.value);
        });
        if (window.jQuery || window.$) {
            const $ = window.jQuery || window.$;
            $(bulkTplSelect).on('change select2:select', function (e) {
                const v = $(this).val() || (e && e.params && e.params.data ? e.params.data.id : null) || bulkTplSelect.value;
                applySelectedBulkTemplate(v);
            });
        }
    }

    // Subject category (دسته‌بندی موضوعی). Bulk messages are always promotional: no classification.
    const messageGroupSelect = document.getElementById('messageGroupSelect');

    // Real-time phone mockup and per-SMS cost; counting itself lives in the shared editor.
    bulkSmsEditor.onChange(function () { updateMessageStatsAndMockup(); });

    function updateMessageStatsAndMockup() {
        const seg = bulkSmsEditor.getStats();
        // Preview shows the final text as it will be sent: the mandatory «لغو 11» is part of it.
        document.getElementById('phoneBubbleText').innerText = seg.empty ? 'متن پیامک شما اینجا نمایش داده خواهد شد...' : seg.finalText;

        const costPerSmsEl = document.getElementById('cost_per_sms');
        if (costPerSmsEl && (!costPerSmsEl.dataset.serverCalculated || costPerSmsEl.dataset.serverCalculated !== '1')) {
            costPerSmsEl.innerText = Number(seg.pageCount * COST_PER_PAGE).toLocaleString('fa-IR') + ' ریال';
        }
    }

    // Preview and calculate button
    document.getElementById('btnPreviewRecipients').addEventListener('click', function () {
        triggerPreview();
    });

    function triggerPreview() {
        const form = document.getElementById('bulkSmsForm');
        const formData = new FormData(form);

        fetch("{{ route('admin.sms.bulk.preview') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 403) {
                Swal.fire({
                    icon: 'error',
                    title: 'سطح دسترسی ناکافی',
                    text: body.message || 'سطح دسترسی شما برای این ترکیب فیلترها کافی نمی‌باشد.',
                    confirmButtonText: 'متوجه شدم'
                });
                return;
            }

            if (body.status === 'success') {
                const rec = body.recipient_summary;
                const msg = body.message_summary;
                const fin = body.financial_summary;
                previewDone = Number(rec.valid_count) > 0;
                lastMaxAffordable = Number(fin.max_affordable_recipients || 0);
                syncRecipientLimit(Number(rec.valid_count), Number(fin.effective_recipient_count));
                const insufficientActions = document.getElementById('insufficientBalanceActions');
                if (insufficientActions) insufficientActions.style.setProperty('display', 'none', 'important');
                const qpReduceBtn = document.getElementById('qpBtnReduceRecipients');
                if (qpReduceBtn) qpReduceBtn.style.display = 'none';

                // Update audit stats
                document.getElementById('filterAuditCard').style.display = 'block';
                document.getElementById('stat_total').innerText = Number(rec.total_candidates).toLocaleString('fa-IR');
                document.getElementById('stat_valid').innerText = Number(rec.valid_count).toLocaleString('fa-IR');
                document.getElementById('stat_invalid').innerText = Number(rec.invalid_count).toLocaleString('fa-IR');
                document.getElementById('stat_duplicate').innerText = Number(rec.duplicate_count).toLocaleString('fa-IR');

                // invalid_sample is absent for users without view-phone-numbers (counts only)
                if (rec.invalid_count > 0 && rec.invalid_sample && rec.invalid_sample.length > 0) {
                    const sampleList = rec.invalid_sample.map(i => `<code>${i.phone}</code>: ${i.reason}`).join(' | ');
                    document.getElementById('invalidNumbersDetails').style.display = 'block';
                    document.getElementById('invalidNumbersDetails').innerHTML = `<strong>نمونه شماره‌های نامعتبر رد شده:</strong> ${sampleList}`;
                } else {
                    document.getElementById('invalidNumbersDetails').style.display = 'none';
                }

                // Update message stats
                // (character/page/encoding counters live inside the editor field — shared SmsMessageEditor)

                // Update finance
                document.getElementById('cost_per_sms').innerText = Number(msg.cost_per_message).toLocaleString('fa-IR') + ' ریال';
                document.getElementById('campaign_total_cost').innerText = Number(fin.estimated_total_cost).toLocaleString('fa-IR') + ' ریال';

                const statusBadge = document.getElementById('balanceSufficiencyStatus');
                const callout = document.getElementById('quickPaymentCallout');
                const btnLaunch = document.getElementById('btnLaunchCampaign');
                lastCalculatedDeficit = Number(fin.balance_deficit || 0);

                if (Number(fin.estimated_total_cost) === 0 || Number(rec.valid_count) === 0) {
                    statusBadge.innerHTML = `<span class="badge bg-secondary p-2 d-block"><i class="fa fa-info-circle me-1"></i> ابتدا مخاطبین و متن پیامک را تعیین فرمایید</span>`;
                    btnLaunch.disabled = true;
                    if (callout) callout.style.display = 'none';
                } else if (fin.has_sufficient_balance) {
                    statusBadge.innerHTML = `<span class="badge bg-success p-2 d-block"><i class="fa fa-check-circle me-1"></i> موجودی کافی است (باقی‌مانده: ${Number(fin.balance_after).toLocaleString('fa-IR')} ریال)</span>`;
                    if (Number(fin.effective_recipient_count) < Number(rec.valid_count)) {
                        statusBadge.innerHTML += `<div class="small text-primary mt-1"><i class="fa fa-users me-1"></i> ارسال به ${Number(fin.effective_recipient_count).toLocaleString('fa-IR')} نفر از ${Number(rec.valid_count).toLocaleString('fa-IR')} مخاطب (کاهش تعداد مخاطبین فعال است)</div>`;
                    }
                    btnLaunch.disabled = false;
                    if (fin.is_low_balance) {
                        if (callout) {
                            callout.style.display = 'block';
                            document.getElementById('quickPaymentCalloutTitle').innerText = 'موجودی خط برای کمپین‌های بعدی رو به اتمام است';
                            document.getElementById('quickPaymentDeficitBadge').innerText = 'اعتبار اندک';
                            document.getElementById('quickPaymentDeficitBadge').className = 'badge bg-warning text-dark small';
                            document.getElementById('quickPaymentCalloutDesc').innerText = 'می‌توانید پیش از اتمام موجودی، از طریق درگاه پرداخت سریع اعتبار این خط را افزایش دهید.';
                        }
                    } else {
                        if (callout) callout.style.display = 'none';
                    }
                } else {
                    statusBadge.innerHTML = `
                        <span class="badge bg-danger p-2 d-block mb-2">
                            <i class="fa fa-times-circle me-1"></i> موجودی ناکافی است (کسری: ${Number(fin.balance_deficit).toLocaleString('fa-IR')} ریال)
                        </span>
                        <button type="button" class="btn btn-warning text-dark btn-sm w-100 fw-bold shadow-sm" onclick="openQuickPaymentGate(${fin.balance_deficit})">
                            <i class="fa fa-bolt me-1"></i> شارژ سریع کسری (${Number(fin.balance_deficit).toLocaleString('fa-IR')} ریال)
                        </button>
                    `;
                    btnLaunch.disabled = true;
                    // Alternatives to topping up: shrink the audience to what the balance affords, or re-filter.
                    if (insufficientActions) insufficientActions.style.setProperty('display', 'flex', 'important');
                    const reduceLabel = lastMaxAffordable > 0
                        ? `<i class="fa fa-users me-1"></i> کاهش تعداد مخاطبین (به ${lastMaxAffordable.toLocaleString('fa-IR')} نفر)`
                        : '<i class="fa fa-users me-1"></i> کاهش تعداد مخاطبین';
                    ['btnReduceRecipients', 'qpBtnReduceRecipients'].forEach(id => {
                        const el = document.getElementById(id);
                        if (!el) return;
                        el.innerHTML = reduceLabel;
                        el.disabled = lastMaxAffordable < 1;
                        if (id === 'qpBtnReduceRecipients') el.style.display = 'inline-block';
                    });
                    if (callout) {
                        callout.style.display = 'block';
                        document.getElementById('quickPaymentCalloutTitle').innerText = 'موجودی خط برای ارسال این کمپین ناکافی است';
                        document.getElementById('quickPaymentDeficitBadge').innerText = 'کسری: ' + Number(fin.balance_deficit).toLocaleString('fa-IR') + ' ریال';
                        document.getElementById('quickPaymentDeficitBadge').className = 'badge bg-danger text-white small';
                        document.getElementById('quickPaymentCalloutDesc').innerText = 'جهت ارسال فوری و بدون خروج از این صفحه، اعتبار این خط را به صورت آنلاین و درجا شارژ فرمایید.';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
        });
    }

    // ---- Recipients actually sent to: the filter sets the maximum, the sender may only lower it.
    const recipientLimitEl = document.getElementById('recipient_limit');
    const recipientCapEl = document.getElementById('max_recipients');
    let recipientLimitMax = 0;
    let recipientLimitTimer = null;

    function syncRecipientLimit(validCount, effectiveCount) {
        recipientLimitMax = validCount;
        recipientLimitEl.disabled = validCount < 1;
        recipientLimitEl.max = validCount;
        recipientLimitEl.value = validCount < 1 ? '' : Math.min(effectiveCount || validCount, validCount);
        document.getElementById('recipient_limit_hint').innerText = validCount < 1
            ? 'مخاطب معتبری پیدا نشد؛ فیلتر را تغییر دهید.'
            : 'حداکثر ' + validCount.toLocaleString('fa-IR') + ' نفر (حاصل فیلتر)؛ فقط می‌توانید این عدد را کم کنید.';
    }

    recipientLimitEl.addEventListener('input', function () {
        let v = parseInt(this.value, 10);
        if (isNaN(v)) return;
        // Never above what the filter produced, never below one.
        if (v > recipientLimitMax) { v = recipientLimitMax; this.value = v; }
        if (v < 1) { v = 1; this.value = v; }
        recipientCapEl.value = v < recipientLimitMax ? v : '';
        clearTimeout(recipientLimitTimer);
        recipientLimitTimer = setTimeout(triggerPreview, 500);
    });

    // Quick Payment Gate Logic
    let lastCalculatedDeficit = 0;
    let currentQuickSenderId = null;
    let currentQuickSenderNumber = '';
    let currentQuickBusinessId = "{{ $business?->id }}";

    function openQuickPaymentGate(suggestedDeficit, senderId, senderNumber, currentBal) {
        if (!senderId && isOtpPanel()) {
            senderId = 'otp';
            senderNumber = 'پنل OTP' + (OTP_NUMBER ? ' (' + OTP_NUMBER + ')' : '');
            currentBal = otpBalance;
        }
        if (!senderId) {
            const checkedSender = document.querySelector('input[name="sender_number_id"]:checked');
            if (checkedSender) {
                senderId = checkedSender.value;
                const card = checkedSender.closest('.sender-radio-card');
                senderNumber = card ? card.querySelector('label').innerText.trim() : '';
                const balEl = document.getElementById('sender_balance_' + senderId);
                currentBal = balEl ? parseInt(balEl.innerText.replace(/[^0-9]/g, '')) || 0 : 0;
            }
        }

        currentQuickSenderId = senderId;
        currentQuickSenderNumber = senderNumber;
        const deficit = suggestedDeficit !== undefined && suggestedDeficit !== null ? Number(suggestedDeficit) : lastCalculatedDeficit;

        document.getElementById('qpModalSenderNumber').innerText = senderNumber || 'خط انتخابی';
        document.getElementById('qpModalCurrentBalance').innerText = Number(currentBal || 0).toLocaleString('fa-IR') + ' ریال';
        document.getElementById('qpModalDeficitAmount').innerText = Number(deficit).toLocaleString('fa-IR') + ' ریال';

        const smartDeficitWrapper = document.getElementById('qpSmartDeficitWrapper');
        if (deficit > 0) {
            smartDeficitWrapper.style.display = 'block';
            document.getElementById('qpSmartDeficitValue').innerText = Number(deficit).toLocaleString('fa-IR') + ' ریال';
            selectQuickPreset(document.getElementById('qpBtnSmartDeficit'), deficit);
        } else {
            smartDeficitWrapper.style.display = 'none';
            const defaultPreset = document.querySelector('.qp-preset-btn[data-amount="1000000"]');
            if (defaultPreset) selectQuickPreset(defaultPreset, 1000000);
        }

        const modalEl = document.getElementById('quickPaymentModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    function selectQuickPreset(btn, amount) {
        document.querySelectorAll('.qp-preset-btn, #qpBtnSmartDeficit').forEach(b => {
            b.classList.remove('btn-primary', 'text-white', 'border-primary');
            if (b.id !== 'qpBtnSmartDeficit') b.classList.add('btn-outline-secondary');
        });
        if (btn) {
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary', 'text-white');
        }
        const val = amount || lastCalculatedDeficit || 1000000;
        document.getElementById('qpCustomAmountInput').value = val;
        updateTomanDisplay(val);
    }

    document.getElementById('qpCustomAmountInput').addEventListener('input', function() {
        document.querySelectorAll('.qp-preset-btn, #qpBtnSmartDeficit').forEach(b => {
            b.classList.remove('btn-primary', 'text-white');
            if (b.id !== 'qpBtnSmartDeficit') b.classList.add('btn-outline-secondary');
        });
        updateTomanDisplay(this.value);
    });

    function updateTomanDisplay(amount) {
        const num = parseInt(amount) || 0;
        const toman = Math.floor(num / 10);
        document.getElementById('qpTomanEquivalent').innerText = 'معادل: ' + Number(toman).toLocaleString('fa-IR') + ' تومان';
    }

    function highlightGateway(radio) {
        document.querySelectorAll('.qp-gateway-card').forEach(c => c.classList.remove('active'));
        radio.closest('.qp-gateway-card').classList.add('active');
    }

    function executeQuickPayment() {
        const amount = parseInt(document.getElementById('qpCustomAmountInput').value) || 0;
        if (amount < 1000) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'مبلغ نامعتبر', text: 'لطفاً مبلغ شارژ را حداقل ۱۰,۰۰۰ ریال وارد فرمایید.' });
            } else {
                alert('لطفاً مبلغ شارژ را حداقل ۱۰,۰۰۰ ریال وارد فرمایید.');
            }
            return;
        }

        const selectedGw = document.querySelector('input[name="qp_gateway"]:checked')?.value || 'sadad';
        const btn = document.getElementById('btnExecuteQuickPay');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال انتقال به درگاه پرداخت...';

        const quickPayIsOtp = currentQuickSenderId === 'otp';
        fetch(quickPayIsOtp ? "{{ route('admin.sms.financial.otp.topup') }}" : "{{ route('admin.sms.financial.advertising.topup') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(quickPayIsOtp ? {
                amount: amount,
                business_id: currentQuickBusinessId
            } : {
                sender_number_id: currentQuickSenderId,
                amount: amount,
                business_id: currentQuickBusinessId
            })
        })
        .then(r => r.json().then(data => ({ status: r.status, data })))
        .then(({ status, data }) => {
            // Payment flow: nothing is credited here; go to the gateway and return to this page after payment.
            if (status === 200 && data.success && data.redirect_url) { window.location.href = data.redirect_url; return; }
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (status === 200 && data.success) {
                // Close modal
                const modalEl = document.getElementById('quickPaymentModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Update balance on sender cards (shared panel balance)
                const newBal = data.transaction?.balance_after ?? 0;
                if (quickPayIsOtp) {
                    otpBalance = Number(newBal);
                    const otpEl = document.getElementById('otpBalanceDisplay');
                    if (otpEl) otpEl.innerText = Number(newBal).toLocaleString('fa-IR');
                } else {
                    document.querySelectorAll('[id^="sender_balance_"]').forEach(el => {
                        el.innerText = Number(newBal).toLocaleString('fa-IR') + ' ریال';
                    });
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'شارژ آنلاین با موفقیت انجام شد!',
                        html: `موجودی پنل تبلیغاتی کسب‌وکار به میزان <b>${Number(amount).toLocaleString('fa-IR')} ریال</b> افزایش یافت.`,
                        confirmButtonText: 'عالی، بازگشت به کمپین'
                    });
                }

                // Recalculate preview immediately
                triggerPreview();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'خطا در افزایش موجودی', text: data.message || 'خطایی در ثبت شارژ رخ داد.' });
                } else {
                    alert(data.message || 'خطایی رخ داد.');
                }
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'خطای ارتباط با سرور', text: 'امکان اتصال به درگاه پرداخت فراهم نشد.' });
            } else {
                alert('خطای ارتباط با سرور');
            }
        });
    }

    // ---- Insufficient balance: «کاهش تعداد مخاطبین» / «فیلتر مجدد» ----
    let lastMaxAffordable = 0;

    function reduceRecipients() {
        if (lastMaxAffordable < 1) return;
        // The server re-caps this to what the balance really affords; the client number is only a request.
        document.getElementById('max_recipients').value = lastMaxAffordable;
        const modalEl = document.getElementById('quickPaymentModal');
        const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
        if (modal) modal.hide();
        triggerPreview();
    }

    function clearRecipientCap() {
        document.getElementById('max_recipients').value = '';
    }

    function reopenAudienceFilter() {
        clearRecipientCap();
        const modalEl = document.getElementById('quickPaymentModal');
        const modal = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
        if (modal) {
            modalEl.addEventListener('hidden.bs.modal', function openFilter() {
                modalEl.removeEventListener('hidden.bs.modal', openFilter);
                document.getElementById('open_request_filter').click();
            });
            modal.hide();
        } else {
            document.getElementById('open_request_filter').click();
        }
    }

    // A new audience filter invalidates any earlier recipient cap (capture: runs before the picker's own handler).
    ['request_filter_apply', 'request_filter_clear', 'request_filter_remove', 'request_filter_quick_clear'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('click', clearRecipientCap, true);
    });

    // ---- Outgoing queue («صف ارسال»): unsent content is parked, never debited or sent ----
    const QUEUE_SAVE_URL = "{{ route('admin.sms.bulk.queue.save') }}";
    let previewDone = false;
    let formSubmitting = false;
    let lastQueuedSignature = null;

    function queuePayload() {
        const fd = new FormData(document.getElementById('bulkSmsForm'));
        fd.delete('max_recipients');
        fd.delete('type');
        if (document.querySelector('input[name="send_type"]:checked')?.value !== 'scheduled') {
            fd.delete('scheduled_at');
        }
        return fd;
    }

    function queueSignature(fd) {
        const parts = [];
        fd.forEach((v, k) => { if (k !== '_token' && k !== 'queue_id') parts.push(k + '=' + v); });
        return parts.join('&');
    }

    function hasUnsentContent() {
        return previewDone && !formSubmitting && document.getElementById('sms_message').value.trim() !== '';
    }

    function persistQueueOnLeave() {
        if (!hasUnsentContent()) return;
        const fd = queuePayload();
        const sig = queueSignature(fd);
        if (sig === lastQueuedSignature) return;
        lastQueuedSignature = sig;
        // sendBeacon survives page unload; the response is ignored (the server de-duplicates by content).
        if (navigator.sendBeacon) {
            navigator.sendBeacon(QUEUE_SAVE_URL, fd);
        } else {
            fetch(QUEUE_SAVE_URL, { method: 'POST', body: fd, keepalive: true, headers: { 'Accept': 'application/json' } });
        }
    }

    document.getElementById('bulkSmsForm').addEventListener('submit', function () { formSubmitting = true; });
    window.addEventListener('pagehide', persistQueueOnLeave);
    window.addEventListener('beforeunload', persistQueueOnLeave);

    document.getElementById('btnSaveToQueue').addEventListener('click', function () {
        const fd = queuePayload();
        fetch(QUEUE_SAVE_URL, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: fd
        })
        .then(r => r.json().then(data => ({ status: r.status, data })))
        .then(({ status, data }) => {
            if (status === 200 && data.status === 'success') {
                document.getElementById('queue_id').value = data.queue_id;
                lastQueuedSignature = queueSignature(fd);
                Swal.fire({
                    icon: 'success',
                    title: 'ذخیره شد',
                    html: `${data.message}<br><a href="${data.queue_url}">مشاهده صف ارسال</a>`,
                    confirmButtonText: 'متوجه شدم'
                });
            } else {
                Swal.fire({ icon: 'error', title: 'ذخیره در صف ارسال ناموفق بود', text: data.message || 'خطایی رخ داد.', confirmButtonText: 'متوجه شدم' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'خطای ارتباط با سرور', confirmButtonText: 'متوجه شدم' }));
    });

    // ---- Resume an outgoing-queue entry: put the saved content back into the compose page ----
    const QUEUED_INPUT = @json($queuedInput);

    function flattenFilters(prefix, value, out) {
        if (value !== null && typeof value === 'object') {
            Object.keys(value).forEach(k => flattenFilters(prefix + '[' + k + ']', value[k], out));
        } else if (value !== null && value !== undefined && value !== '') {
            out.push([prefix, value]);
        }
    }

    function restoreQueuedInput(q) {
        if (q.campaign_name) document.getElementById('campaign_name').value = q.campaign_name;
        const panelRadio = document.querySelector('input[name="service_type"][value="' + (q.service_type || 'advertising') + '"]');
        if (panelRadio) panelRadio.checked = true;
        onServiceTypeChange(true);
        if (q.otp_category) {
            const catEl = document.getElementById('otpCategorySelect');
            if (catEl) catEl.value = q.otp_category;
        }
        const classEl = document.querySelector('input[name="classification"][value="' + (q.classification || 'regular') + '"]');
        if (classEl) classEl.checked = true;

        if (q.message) {
            if (bulkMessageChipEditor) bulkMessageChipEditor.setValue(q.message);
            else document.getElementById('sms_message').value = q.message;
        }
        if (q.sms_message_template_id) {
            document.getElementById('sms_message_template_id').value = q.sms_message_template_id;
        }
        if (q.sms_message_group_id && messageGroupSelect) {
            messageGroupSelect.value = q.sms_message_group_id;
        }

        const filters = q.filters || {};
        if (filters.custom_numbers) {
            document.getElementById('custom_numbers').value = filters.custom_numbers;
        }
        const pairs = [];
        Object.keys(filters).forEach(k => { if (k !== 'custom_numbers') flattenFilters('filters[' + k + ']', filters[k], pairs); });
        const host = document.getElementById('requestFilterInputs');
        pairs.forEach(([name, value]) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            host.appendChild(input);
        });
        if (filters.request_filter) {
            const container = document.getElementById('requestFilterBadgeContainer');
            const countEl = document.querySelector('#requestFilterBadge [data-count]');
            if (container) container.classList.remove('d-none');
            if (countEl) countEl.textContent = Object.keys(filters.request_filter).length.toLocaleString('fa-IR');
            const quickClear = document.getElementById('request_filter_quick_clear');
            if (quickClear) quickClear.classList.remove('d-none');
        }

        if (q.scheduled_at) {
            document.getElementById('send_scheduled').checked = true;
            toggleScheduleInput(true);
            document.getElementById('scheduled_at').value = String(q.scheduled_at).replace(' ', 'T').slice(0, 16);
        }

        if (q.service_type !== 'otp' && q.sender_number_id && document.getElementById('sender_' + q.sender_number_id)) {
            selectSenderNumber(q.sender_number_id);
        } else {
            triggerPreview();
        }
    }

    // ---- Save the current text as a template
    (function () {
        const modalEl = document.getElementById('saveTemplateModal');
        const formEl = document.getElementById('saveTemplateForm');
        if (!modalEl || !formEl) return;
        modalEl.addEventListener('show.bs.modal', function () {
            document.getElementById('saveTemplateContent').value = document.getElementById('sms_message').value;
            document.getElementById('saveTemplateAlert').style.display = 'none';
        });
        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            const alertBox = document.getElementById('saveTemplateAlert');
            alertBox.style.display = 'none';
            const isOtp = serviceTypeOtp && serviceTypeOtp.checked;
            const otpCatEl = document.getElementById('otpCategorySelect');
            const currentOtpCat = (isOtp && otpCatEl) ? otpCatEl.value : null;

            fetch("{{ route('admin.sms.single.templates.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                body: JSON.stringify({
                    title: document.getElementById('saveTemplateTitle').value.trim(),
                    content: document.getElementById('saveTemplateContent').value,
                    service_type: isOtp ? 'otp' : 'advertising',
                    otp_category: isOtp ? currentOtpCat : null
                })
            })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (ok && data.status === 'success') {
                    const isTplOtp = data.template.service_type === 'otp';
                    allTemplatesBulk.push({
                        id: String(data.template.id),
                        title: data.template.title,
                        panelLabel: isTplOtp ? 'پنل پیامکی OTP' : 'پنل پیامکی تبلیغاتی/اطلاع‌رسانی',
                        content: data.template.content,
                        type: data.template.service_type || (isTplOtp ? 'otp' : 'advertising'),
                        promotional: !isTplOtp,
                        otpCategory: data.template.otp_category
                    });
                    rebuildBulkTemplateSelect(isOtp ? 'otp' : 'advertising', data.template.id);
                    applySelectedBulkTemplate(data.template.id);
                    bootstrap.Modal.getInstance(modalEl).hide();
                    formEl.reset();
                } else {
                    const firstError = data.errors ? Object.values(data.errors)[0][0] : null;
                    alertBox.textContent = firstError || data.message || 'خطا در ذخیره قالب.';
                    alertBox.style.display = 'block';
                }
            })
            .catch(() => {
                alertBox.textContent = 'خطای ارتباط با سرور.';
                alertBox.style.display = 'block';
            });
        });
    })();

    // Trigger initial preview on page load
    document.addEventListener('DOMContentLoaded', function () {
        onServiceTypeChange(true);
        updateMessageStatsAndMockup();
        if (QUEUED_INPUT) {
            restoreQueuedInput(QUEUED_INPUT);
            updateMessageStatsAndMockup();
        }
        const tplSel = document.getElementById('sms_message_template_id');
        if (tplSel && tplSel.value) {
            tplSel.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
