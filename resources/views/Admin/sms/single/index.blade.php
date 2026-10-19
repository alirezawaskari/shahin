@extends('Admin.master')

@section('title', 'ارسال پیامک تکی')

@section('css')
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/sms-forms.css') }}">
<style>
    /* Phone Mockup Frame */
    .phone-mockup-wrapper {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        width: 100%;
        margin-bottom: 1.5rem;
    }
    .phone-mockup {
        width: 310px;
        max-width: 100%;
        height: 600px;
        background: #1e1e2d;
        border-radius: 42px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.35), inset 0 0 0 3px #38384f;
        padding: 12px;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .phone-screen {
        background: #f7f9fc;
        border-radius: 32px;
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .phone-notch {
        width: 130px;
        height: 18px;
        background: #1e1e2d;
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
        z-index: 10;
    }
    .phone-status-bar {
        height: 28px;
        padding: 4px 16px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11px;
        color: #333;
        z-index: 5;
    }
    .phone-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .phone-header .avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #3f51b5;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
    }
    .phone-header .contact-info {
        flex: 1;
        min-width: 0;
    }
    .phone-header .contact-name {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .phone-header .contact-sub {
        font-size: 10px;
        color: #64748b;
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
        max-width: 90%;
        background: #3f51b5;
        color: #ffffff;
        padding: 10px 14px;
        border-radius: 16px 16px 4px 16px;
        align-self: flex-start;
        font-size: 12.5px;
        line-height: 1.6;
        word-break: break-word;
        box-shadow: 0 2px 6px rgba(63, 81, 181, 0.2);
        white-space: pre-wrap;
        direction: rtl;
        text-align: right;
    }
    .sms-bubble-time {
        font-size: 9.5px;
        color: rgba(255, 255, 255, 0.75);
        text-align: left;
        margin-top: 4px;
        display: block;
    }
    .phone-footer {
        background: #ffffff;
        padding: 8px 10px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .phone-footer-input {
        background: #f1f5f9;
        border-radius: 20px;
        padding: 6px 12px;
        font-size: 11px;
        color: #94a3b8;
        flex: 1;
    }
    .placeholder-chip {
        cursor: pointer;
        transition: all 0.2s;
        font-size: 11.5px;
        user-select: none;
    }
    .placeholder-chip:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .contact-dropdown-item {
        cursor: pointer;
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s;
    }
    .contact-dropdown-item:hover {
        background: #eef2ff;
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
</style>
@stop

@section('content')
<div class="container-fluid py-3">

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">ارسال پیامک تکی</h3>
            <p class="text-muted small mb-0">ارسال پیامک تبلیغاتی یا اعتبارسنجی (OTP) به شماره یا کاربر مشخص</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if($business)
                <span class="badge bg-info text-dark px-3 py-2">
                    <i class="fa fa-building-o me-1"></i> کسب‌وکار: {{ $business->title }}
                </span>
            @endif
            <a href="{{ route('admin.sms.bulk.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-users me-1"></i> ارسال انبوه
            </a>
            <a href="{{ route('admin.sms.reports.advertising.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-bar-chart me-1"></i> گزارش پیامک‌ها
            </a>
        </div>
    </div>

    <div class="row">
        {{-- Form Column --}}
        <div class="col-lg-8 col-xl-8">
            <form id="singleSmsForm" method="POST" action="{{ route('admin.sms.single.send') }}">
                @csrf
                <input type="hidden" name="user_id" id="selectedUserId" value="{{ old('user_id') }}">

                {{-- 1. Service & sender --}}
                <div class="card sms-section">
                    <div class="card-header">
                        <h6 class="sms-section-title"><span class="badge bg-primary">۱</span> نوع سرویس و شماره فرستنده</h6>
                        @if($business)
                            <span class="badge bg-light text-dark border">کسب‌وکار: <strong>{{ $business->title }}</strong></span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="sms-field">
                            <span class="sms-label">نوع سرویس پیامک</span>
                            <div class="sms-choice-row">
                                <label class="sms-choice fw-bold" id="advCheckWrapper" for="serviceTypeAdv">
                                    <input class="form-check-input" type="radio" name="service_type" id="serviceTypeAdv" value="advertising" {{ old('service_type', 'advertising') === 'advertising' ? 'checked' : '' }}>
                                    <span><i class="fa fa-bullhorn text-warning me-1"></i> پیامک تبلیغاتی / اطلاع‌رسانی</span>
                                </label>
                                {{-- Hidden (not removed) for lower tiers: the page script expects the element. --}}
                                <label class="sms-choice fw-bold" id="otpCheckWrapper" for="serviceTypeOtp" @if(!($canUseOtp ?? false)) style="display: none;" @endif>
                                    <input class="form-check-input" type="radio" name="service_type" id="serviceTypeOtp" value="otp" {{ old('service_type') === 'otp' ? 'checked' : '' }} @if(!($canUseOtp ?? false)) disabled @endif>
                                    <span><i class="fa fa-key text-success me-1"></i> پیامک اعتبارسنجی (OTP)</span>
                                </label>
                            </div>
                        </div>

                        <div class="sms-field">
                            <label class="sms-label" for="senderNumberSelect">شماره فرستنده</label>

                            {{-- Advertising Sender Select --}}
                            <div id="advertisingSenderContainer">
                                <select name="sender_number_id" id="senderNumberSelect" class="form-select">
                                    @forelse($advertisingNumbers as $sender)
                                        <option value="{{ $sender->id }}" data-balance="{{ $sender->current_balance }}" data-number="{{ $sender->number }}" {{ old('sender_number_id') == $sender->id ? 'selected' : '' }}>
                                            {{ $sender->number }}
                                        </option>
                                    @empty
                                        <option value="" disabled>هیچ شماره تبلیغاتی به این کسب‌وکار تخصیص داده نشده است</option>
                                    @endforelse
                                </select>
                                <div class="sms-hint">تنها خطوط تخصیص یافته به این کسب‌وکار در دسترس هستند.</div>
                            </div>

                            {{-- OTP Auto-selected Box (NO user choice) --}}
                            <div id="otpSenderContainer" style="display: none;">
                                @if($otpSender)
                                    <div class="sms-subbox mb-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <span class="badge bg-success me-2">تخصیص خودکار سامانه</span>
                                            <strong class="font-monospace fs-6">{{ $otpSender->number }}</strong>
                                            @if(auth()->user()?->isSuperAdmin())
                                                <span class="text-muted ms-2">(پنل: {{ $otpSender->sms_panel }})</span>
                                            @endif
                                        </div>
                                        <span class="badge bg-info text-dark">موجودی OTP: {{ number_format($otpBalance) }} ریال</span>
                                    </div>
                                @else
                                    <div class="alert alert-warning py-2 small mb-0">خط OTP در سامانه یافت نشد.</div>
                                @endif
                                <div class="sms-hint text-info">
                                    <i class="fa fa-lock me-1"></i> خط ارسال OTP به صورت خودکار تعیین شده و طبق قوانین سیستم امکان تغییر دستی آن وجود ندارد.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Recipient --}}
                <div class="card sms-section">
                    <div class="card-header">
                        <h6 class="sms-section-title"><span class="badge bg-primary">۲</span> گیرنده</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="sms-label" for="contactSearchInput">انتخاب مخاطب از دفترچه تلفن یا جستجو</label>
                                <div class="position-relative">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                        <input type="text" id="contactSearchInput" class="form-control" placeholder="جستجو بر اساس نام، نام خانوادگی یا شماره تلفن...">
                                        <button type="button" class="btn btn-outline-secondary" id="clearContactBtn" title="پاک کردن انتخاب">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </div>
                                    {{-- Search Results Dropdown --}}
                                    <div id="contactSearchResults" class="border rounded shadow-sm bg-white position-absolute w-100 mt-1" style="display: none; max-height: 220px; overflow-y: auto; z-index: 1000;"></div>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#newContactModal">
                                    <i class="fa fa-user-plus me-1"></i> افزودن مخاطب جدید
                                </button>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="sms-label" for="recipientPhoneInput">شماره گیرنده <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa fa-mobile text-muted"></i></span>
                                    <input type="text" name="recipient_phone" id="recipientPhoneInput" class="form-control text-start font-monospace" placeholder="09xxxxxxxxx" value="{{ old('recipient_phone') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="sms-label" for="selectedContactName">نام مخاطب انتخابی</label>
                                <input type="text" id="selectedContactName" class="form-control bg-light" placeholder="نامشخص" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Message --}}
                <div class="card sms-section">
                    <div class="card-header">
                        <h6 class="sms-section-title"><span class="badge bg-primary">۳</span> متن پیامک</h6>
                    </div>
                    <div class="card-body">
                        {{-- OTP only: fixed system category (احراز هویت / فروش / مالی / حقوقی). Hidden AND disabled (not posted) unless OTP is selected. --}}
                        @if($canUseOtp ?? false)
                        <div class="sms-field" id="otpCategoryBox" @unless(old('service_type') === 'otp') style="display: none;" @endunless>
                            <label class="sms-label" for="otpCategorySelect"><i class="fa fa-tags text-warning me-1"></i> دسته‌بندی سیستمی OTP <span class="text-danger">*</span></label>
                            <select name="otp_category" id="otpCategorySelect" class="form-select" @unless(old('service_type') === 'otp') disabled @endunless>
                                <option value="">-- انتخاب کنید --</option>
                                @foreach(\App\Enums\SmsOtpCategory::options() as $otpCatSlug => $otpCatLabel)
                                    <option value="{{ $otpCatSlug }}" {{ old('otp_category') === $otpCatSlug ? 'selected' : '' }}>{{ $otpCatLabel }}</option>
                                @endforeach
                            </select>
                            <div class="sms-hint">هر پیامک OTP باید دقیقاً یکی از دسته‌بندی‌های ثابت سیستم را داشته باشد.</div>
                        </div>
                        @endif

                        {{-- Message Category / Folders. Classification (عادی/محرمانه) exists for OTP only. --}}
                        @php
                            $classificationVisible = ($canUseOtp ?? false) && old('service_type') === 'otp';
                            // Initial option set (before JS runs): promotional mode shows only promotional groups
                            // and non-OTP templates; non-matching options are rendered hidden + disabled.
                            $otpModeInitial = $classificationVisible;
                        @endphp
                        <div class="sms-subbox">
                            <div class="row g-3">
                                <div class="{{ $classificationVisible ? 'col-md-7' : 'col-12' }}" id="messageGroupCol">
                                    <label class="sms-label" for="messageGroupSelect">
                                        <i class="fa fa-folder text-primary me-1"></i> دسته‌بندی موضوعی / پوشه پیام
                                    </label>
                                    <select name="sms_message_group_id" id="messageGroupSelect" class="form-select">
                                        <option value="" data-slug="" data-confidential="0">-- بدون دسته‌بندی موضوعی (عمومی) --</option>
                                        @if(isset($groups))
                                            @foreach($groups as $grp)
                                                @php $grpPromo = $grp->isPromotional(); $grpHide = !$otpModeInitial && !$grpPromo; @endphp
                                                <optgroup label="📁 {{ $grp->name }}" @if($grpHide) hidden disabled @endif>
                                                    <option value="{{ $grp->id }}" @if($grpHide) hidden disabled @endif data-promotional="{{ $grp->isPromotional() ? '1' : '0' }}" data-slug="{{ $grp->slug }}" data-confidential="{{ $grp->isConfidential() ? '1' : '0' }}" {{ old('sms_message_group_id') == $grp->id ? 'selected' : '' }}>
                                                        {{ $grp->name }} (دسته اصلی)
                                                    </option>
                                                    @foreach($grp->subgroups as $sub)
                                                        <option value="{{ $sub->id }}" @if($grpHide) hidden disabled @endif data-promotional="{{ $grp->isPromotional() ? '1' : '0' }}" data-slug="{{ $sub->slug }}" data-confidential="{{ ($sub->isConfidential() || $grp->isConfidential()) ? '1' : '0' }}" {{ old('sms_message_group_id') == $sub->id ? 'selected' : '' }}>
                                                            {{ $grp->name }} › {{ $sub->name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="sms-hint" id="messageGroupHint"
                                         data-hint-promo="سازمان‌دهی پیام در پوشه‌های موضوعی (مناسبتی و تبریک، فروش، پشتیبانی و زیرگروه‌ها)"
                                         data-hint-otp="پوشه موضوعی اختیاری است؛ پیام‌های مالی و حقوقی به‌طور خودکار محرمانه ثبت می‌شوند.">{{ $otpModeInitial ? 'پوشه موضوعی اختیاری است؛ پیام‌های مالی و حقوقی به‌طور خودکار محرمانه ثبت می‌شوند.' : 'سازمان‌دهی پیام در پوشه‌های موضوعی (مناسبتی و تبریک، فروش، پشتیبانی و زیرگروه‌ها)' }}</div>
                                </div>
                                {{-- OTP only: hidden AND disabled (not submitted) for advertising; toggled by the page script --}}
                                <div class="col-md-5" id="classificationCol" @unless($classificationVisible) style="display: none;" @endunless>
                                    <span class="sms-label"><i class="fa fa-shield text-info me-1"></i> نوع پیام (سطح طبقه‌بندی)</span>
                                    <div class="sms-choice-row flex-nowrap">
                                        <label class="sms-choice small fw-semibold" id="typeRegularWrapper" for="typeRegular" style="flex-basis: 0;">
                                            <input class="form-check-input" type="radio" name="type" id="typeRegular" value="regular" {{ old('type', 'regular') === 'regular' ? 'checked' : '' }} @unless($classificationVisible) disabled @endunless>
                                            <span><i class="fa fa-envelope-o text-secondary me-1"></i> عادی</span>
                                        </label>
                                        <label class="sms-choice small fw-bold text-danger" id="typeClassifiedWrapper" for="typeClassified" style="flex-basis: 0;">
                                            <input class="form-check-input" type="radio" name="type" id="typeClassified" value="classified" {{ old('type') === 'classified' ? 'checked' : '' }} @unless($classificationVisible) disabled @endunless>
                                            <span><i class="fa fa-lock text-danger me-1"></i> محرمانه</span>
                                        </label>
                                    </div>
                                    <div class="sms-hint" id="classificationHint">
                                        <span class="text-muted"><i class="fa fa-info-circle me-1"></i> پیام‌های مالی و حقوقی به‌طور خودکار «محرمانه» تعیین می‌شوند.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Template Selection --}}
                        <div class="sms-field">
                            <label class="sms-label" for="templateSelect">انتخاب از قالب‌های پیامک آماده</label>
                            <select id="templateSelect" name="template_id" class="form-select">
                                <option value="">-- بدون قالب (متن دلخواه) --</option>
                                @foreach($templates as $tpl)
                                    @php
                                        $tplIsOtp = $tpl->service_type === 'otp';
                                        $tplPromo = !$tplIsOtp && (!$tpl->group || $tpl->group->isPromotional());
                                        $tplHide = $otpModeInitial ? !$tplIsOtp : !$tplPromo;
                                    @endphp
                                    <option value="{{ $tpl->id }}" data-promotional="{{ $tplPromo ? '1' : '0' }}" @if($tplHide) hidden disabled @endif data-content="{{ $tpl->content }}" data-type="{{ $tpl->service_type }}" data-otp-category="{{ $tpl->otp_category }}" data-group-id="{{ $tpl->sms_message_group_id }}">
                                        [{{ \App\Support\SmsLabels::serviceType($tpl->service_type) }}] {{ $tpl->title }} {{ $tpl->group ? '('.$tpl->group->name.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Shared editor: textarea + dynamic-parameter chips + live counter --}}
                        @include('Admin.sms.partials.message-editor', [
                            'editorLabel' => 'متن پیامک',
                            'id' => 'messageTextarea',
                            'name' => 'message',
                            'value' => old('message'),
                            'required' => true,
                            'rows' => 6,
                            'costPerPage' => $costPerPage,
                            'advertising' => old('service_type', 'advertising') !== 'otp',
                            'chips' => ['{نام کاربر}', '{نام خانوادگی}', '{تاریخ}', '{ساعت}', '{شناسه فاکتور}', '{لینک پرداخت}', '{شناسه درخواست}'],
                        ])
                    </div>
                </div>

                {{-- 4. Cost & send --}}
                <div class="card sms-section">
                    <div class="card-header">
                        <h6 class="sms-section-title"><span class="badge bg-primary">۴</span> هزینه و ارسال</h6>
                    </div>
                    <div class="card-body">
                        <div class="sms-summary mb-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">هزینه تخمینی ارسال:</span>
                                    <strong class="fs-5 text-danger" id="previewCost">{{ number_format($costPerPage) }}</strong> <span class="small text-muted">ریال</span>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span class="text-muted small d-block">موجودی فعلی حساب:</span>
                                        <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0 px-2 fw-semibold" style="font-size: 11px;" onclick="openSingleQuickPaymentGate()" title="شارژ سریع آنلاین">
                                            <i class="fa fa-bolt me-1 text-warning"></i> شارژ سریع
                                        </button>
                                    </div>
                                    <strong class="fs-5 text-success" id="previewCurrentBalance">0</strong> <span class="small text-muted">ریال</span>
                                </div>
                                <div class="col-md-4">
                                    <span class="text-muted small d-block">موجودی پس از ارسال:</span>
                                    <strong class="fs-5 text-dark" id="previewBalanceAfter">0</strong> <span class="small text-muted">ریال</span>
                                </div>
                            </div>

                            {{-- Insufficient Balance Alert (Hard Block with Quick Payment Gate) --}}
                            <div class="alert alert-danger mt-3 mb-0 p-3 small flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2" id="insufficientBalanceAlert" style="display: none;">
                                <div>
                                    <i class="fa fa-exclamation-triangle me-1"></i>
                                    <strong class="text-danger">موجودی ناکافی:</strong> <span id="insufficientBalanceText">موجودی حساب برای ارسال این پیامک کافی نیست.</span>
                                    <div class="text-muted small mt-1">جهت ارسال فوری و بدون خروج از این صفحه، می‌توانید اعتبار را به صورت آنلاین شارژ فرمایید.</div>
                                </div>
                                <button type="button" class="btn btn-warning text-dark btn-sm fw-bold shadow-sm text-nowrap" onclick="openSingleQuickPaymentGate()">
                                    <i class="fa fa-bolt me-1"></i> شارژ سریع آنلاین (اتصال به درگاه)
                                </button>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <button type="submit" id="submitSendBtn" class="btn btn-success px-4 py-2 fw-bold">
                                <i class="fa fa-paper-plane me-1"></i> تایید و ارسال پیامک
                            </button>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#historyForwardModal">
                                <i class="fa fa-history me-1"></i> ارجاع پیام‌های قبلی به دیگران
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Phone Preview Column --}}
        <div class="col-lg-4 col-xl-4">
            <div class="phone-mockup-wrapper">
                <div class="phone-mockup">
                    <div class="phone-screen">
                        {{-- Top Notch --}}
                        <div class="phone-notch"></div>

                        {{-- Status Bar --}}
                        <div class="phone-status-bar">
                            <span class="phone-clock" id="phoneTime">12:00</span>
                            <div class="d-flex gap-1 align-items-center">
                                <i class="fa fa-signal"></i>
                                <i class="fa fa-wifi"></i>
                                <i class="fa fa-battery-full"></i>
                            </div>
                        </div>

                        {{-- Chat Header --}}
                        <div class="phone-header">
                            <div class="avatar" id="phoneAvatar">?</div>
                            <div class="contact-info">
                                <div class="contact-name" id="phoneRecipientTitle">مخاطب</div>
                                <div class="contact-sub" id="phoneSenderNumber" dir="ltr">
                                    {{ $advertisingNumbers->first()->number ?? ($otpSender->number ?? 'SMS') }}
                                </div>
                            </div>
                        </div>

                        {{-- Chat Body with SMS Bubble --}}
                        <div class="phone-chat-body">
                            <div class="sms-bubble">
                                <span id="phoneBubbleText">متن پیش‌نمایش پیامک...</span>
                                <span class="sms-bubble-time" id="bubbleTime">همین الان</span>
                            </div>
                        </div>

                        {{-- Phone Bottom --}}
                        <div class="phone-footer">
                            <div class="phone-footer-input">پیام متنی (SMS)...</div>
                            <i class="fa fa-arrow-circle-up text-primary fs-5"></i>
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

    {{-- Recent Messages Table (For referral / history) --}}
    @if($recentMessages->isNotEmpty())
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="card-title mb-0 fw-bold text-dark">
                    <i class="fa fa-list me-2 text-primary"></i> آخرین پیامک‌های ارسالی (امکان ارجاع و فوروارد)
                </h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>شناسه</th>
                            <th>گیرنده</th>
                            <th>خط فرستنده</th>
                            <th>نوع</th>
                            <th>بخشی از متن</th>
                            <th>پارت</th>
                            <th>هزینه (ریال)</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentMessages as $msg)
                            <tr>
                                <td>#{{ $msg->id }}</td>
                                <td>
                                    <span dir="ltr"><strong>{{ $msg->recipient_phone }}</strong></span>
                                    @if($msg->user)
                                        <div class="text-muted small">{{ $msg->user->name }} {{ $msg->user->family }}</div>
                                    @endif
                                </td>
                                <td><span dir="ltr">{{ $msg->sender_phone }}</span></td>
                                <td>
                                    <span class="badge {{ $msg->service_type === 'otp' ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ \App\Support\SmsLabels::serviceType($msg->service_type) }}
                                    </span>
                                </td>
                                <td style="max-width: 280px;" class="text-truncate" title="{{ ($msg->content_redacted ?? false) ? 'محرمانه - دسترسی محدود' : $msg->content }}">
                                    @if($msg->content_redacted ?? false)
                                        <span class="text-muted"><i class="fa fa-lock me-1"></i> محرمانه - دسترسی محدود</span>
                                    @else
                                        {{ $msg->content }}
                                    @endif
                                </td>
                                <td>{{ $msg->page_count }}</td>
                                <td>{{ number_format((float) $msg->cost) }}</td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ \App\Support\SmsLabels::status($msg->status) }}</span>
                                </td>
                                <td class="small text-muted">{{ \App\Helpers\Helper::jalaliDate($msg->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary forward-msg-btn"
                                        data-msg-id="{{ $msg->id }}"
                                        data-msg-content="{{ ($msg->content_redacted ?? false) ? '' : e($msg->content) }}"
                                        data-msg-redacted="{{ ($msg->content_redacted ?? false) ? '1' : '0' }}"
                                        data-msg-type="{{ $msg->service_type }}"
                                        data-bs-toggle="modal" data-bs-target="#forwardDirectModal">
                                        <i class="fa fa-share me-1"></i> ارجاع به مخاطب
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- Modal 1: Inline Add New Contact --}}
<div class="modal fade" id="newContactModal" tabindex="-1" aria-labelledby="newContactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="newContactModalLabel"><i class="fa fa-user-plus me-2 text-primary"></i> افزودن مخاطب جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form id="newContactForm">
                <div class="modal-body">
                    <div id="contactModalAlert" class="alert alert-danger py-2 small" style="display: none;"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">نام <span class="text-danger">*</span></label>
                        <input type="text" id="newContactName" class="form-control" required placeholder="مثال: علی">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">نام خانوادگی</label>
                        <input type="text" id="newContactFamily" class="form-control" placeholder="مثال: رضایی">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">شماره موبایل <span class="text-danger">*</span></label>
                        <input type="text" id="newContactPhone" class="form-control font-monospace text-start" required placeholder="09123456789">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" id="saveContactBtn" class="btn btn-primary">ثبت و انتخاب مخاطب</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Forward Message Directly to Another Contact --}}
<div class="modal fade" id="forwardDirectModal" tabindex="-1" aria-labelledby="forwardDirectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="forwardDirectModalLabel"><i class="fa fa-share me-2 text-primary"></i> ارجاع پیامک به مخاطب دیگر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form id="forwardForm" method="POST" action="{{ route('admin.sms.single.forward') }}">
                @csrf
                <input type="hidden" name="original_message_id" id="forwardOriginalMsgId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">متن پیامک اصلی</label>
                        <div id="forwardMsgContent" class="p-2 bg-light border rounded small" style="max-height: 120px; overflow-y: auto;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">شماره گیرنده ارجاع <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_phone" id="forwardRecipientPhone" class="form-control font-monospace text-start" required placeholder="09xxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ارجاع‌دهنده</label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user() ? (auth()->user()->name . ' ' . auth()->user()->family) : 'کاربر جاری' }}" readonly>
                        <input type="hidden" name="referrer_id" value="{{ auth()->id() }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">تاریخ و زمان ارجاع</label>
                        <input type="text" name="referred_at" class="form-control bg-light" value="{{ now()->format('Y-m-d H:i:s') }}" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">تایید و ارجاع پیامک</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick Payment Gate Modal (مودال درگاه پرداخت سریع - ارسال تکی) -->
<div class="modal fade" id="quickPaymentSingleModal" tabindex="-1" aria-labelledby="quickPaymentSingleModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom py-3">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="quickPaymentSingleModalLabel">
                            درگاه پرداخت سریع و افزایش آنی اعتبار پیامک
                        </h5>
                        <small class="text-muted">شارژ مستقیم خط و فعال‌سازی فوری ارسال بدون رفرش صفحه</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Banner -->
                <div class="row g-2 mb-4 p-3 rounded border bg-light">
                    <div class="col-sm-4 border-end">
                        <small class="text-muted d-block">سرویس و شماره خط:</small>
                        <strong class="text-dark fs-6 font-monospace" id="qpSingleModalServiceInfo" dir="ltr">-</strong>
                    </div>
                    <div class="col-sm-4 border-end">
                        <small class="text-muted d-block">موجودی فعلی خط:</small>
                        <strong class="text-dark fs-6" id="qpSingleModalCurrentBalance">۰ ریال</strong>
                    </div>
                    <div class="col-sm-4">
                        <small class="text-muted d-block">کسری برآورد شده:</small>
                        <strong class="text-danger fs-6" id="qpSingleModalDeficitAmount">۰ ریال</strong>
                    </div>
                </div>

                <!-- Step 1: Amount Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark small mb-2">
                        <span class="badge bg-primary me-1">۱</span> تعیین مبلغ شارژ (ریال):
                    </label>

                    <!-- Smart Deficit Preset Button (Shown if deficit > 0) -->
                    <div id="qpSingleSmartDeficitWrapper" class="mb-3" style="display: none;">
                        <button type="button" class="btn btn-outline-danger w-100 p-2 text-start d-flex justify-content-between align-items-center shadow-sm" id="qpSingleBtnSmartDeficit" onclick="selectSingleQuickPreset(this, null)">
                            <span>
                                <i class="fa fa-bolt text-danger me-2"></i>
                                <strong>شارژ دقیق به میزان کسری پیامک:</strong>
                            </span>
                            <span class="badge bg-danger text-white fs-6 font-monospace" id="qpSingleSmartDeficitValue">۰ ریال</span>
                        </button>
                    </div>

                    <!-- Preset Chips -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-single-preset-btn" data-amount="100000" onclick="selectSingleQuickPreset(this, 100000)">۱۰۰,۰۰۰ ریال <small class="text-muted">(۱۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-single-preset-btn" data-amount="500000" onclick="selectSingleQuickPreset(this, 500000)">۵۰۰,۰۰۰ ریال <small class="text-muted">(۵۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-single-preset-btn" data-amount="1000000" onclick="selectSingleQuickPreset(this, 1000000)">۱,۰۰۰,۰۰۰ ریال <small class="text-muted">(۱۰۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-single-preset-btn" data-amount="2000000" onclick="selectSingleQuickPreset(this, 2000000)">۲,۰۰۰,۰۰۰ ریال <small class="text-muted">(۲۰۰ ت)</small></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill qp-single-preset-btn" data-amount="5000000" onclick="selectSingleQuickPreset(this, 5000000)">۵,۰۰۰,۰۰۰ ریال <small class="text-muted">(۵۰۰ ت)</small></button>
                    </div>

                    <!-- Amount Input -->
                    <div class="input-group input-group-lg">
                        <input type="number" id="qpSingleCustomAmountInput" class="form-control font-monospace text-center fw-bold" placeholder="مبلغ شارژ را وارد کنید" min="10000" step="10000">
                        <span class="input-group-text bg-light text-muted fw-bold">ریال</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                        <small class="text-muted" id="qpSingleTomanEquivalent">معادل: ۰ تومان</small>
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
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer active" for="gw_sadad_single">
                                <input type="radio" name="qp_gateway_single" id="gw_sadad_single" value="sadad" class="form-check-input d-none" checked onchange="highlightSingleGateway(this)">
                                <div class="fs-4 text-primary mb-1"><i class="fa fa-university"></i></div>
                                <div class="fw-bold small text-dark">سداد (ملی)</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_mellat_single">
                                <input type="radio" name="qp_gateway_single" id="gw_mellat_single" value="mellat" class="form-check-input d-none" onchange="highlightSingleGateway(this)">
                                <div class="fs-4 text-danger mb-1"><i class="fa fa-credit-card"></i></div>
                                <div class="fw-bold small text-dark">به‌پرداخت ملت</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_sep_single">
                                <input type="radio" name="qp_gateway_single" id="gw_sep_single" value="sep" class="form-check-input d-none" onchange="highlightSingleGateway(this)">
                                <div class="fs-4 text-info mb-1"><i class="fa fa-shield"></i></div>
                                <div class="fw-bold small text-dark">سامان‌کیش (سپ)</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_sim_single">
                                <input type="radio" name="qp_gateway_single" id="gw_sim_single" value="simulator" class="form-check-input d-none" onchange="highlightSingleGateway(this)">
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
                        اتصال به درگاه امن شاپرک تحت پروتکل رمزنگاری SSL انجام شده و پس از تکمیل عملیات پرداخت، موجودی سرویس انتخابی فوراً شارژ خواهد شد.
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-3">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-success fw-bold px-4" id="btnExecuteSingleQuickPay" onclick="executeSingleQuickPayment()">
                    <i class="fa fa-shield me-1"></i> پرداخت و افزایش آنی موجودی
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/dashboard/js/sms-param-chips.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/sms-message-editor.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // State variables
    let currentContact = null;
    const otpBalance = {{ (float) $otpBalance }};
    const otpNumber = "{{ $otpSender ? $otpSender->number : '' }}";

    const textarea = document.getElementById('messageTextarea');
    const serviceTypeAdv = document.getElementById('serviceTypeAdv');
    const serviceTypeOtp = document.getElementById('serviceTypeOtp');
    const senderNumberSelect = document.getElementById('senderNumberSelect');
    const templateSelect = document.getElementById('templateSelect');

    const phoneBubbleText = document.getElementById('phoneBubbleText');
    const phoneRecipientTitle = document.getElementById('phoneRecipientTitle');
    const phoneAvatar = document.getElementById('phoneAvatar');
    const phoneSenderNumber = document.getElementById('phoneSenderNumber');

    const previewCost = document.getElementById('previewCost');
    const previewCurrentBalance = document.getElementById('previewCurrentBalance');
    const previewBalanceAfter = document.getElementById('previewBalanceAfter');
    const insufficientAlert = document.getElementById('insufficientBalanceAlert');
    const submitSendBtn = document.getElementById('submitSendBtn');

    const contactSearchInput = document.getElementById('contactSearchInput');
    const contactSearchResults = document.getElementById('contactSearchResults');
    const recipientPhoneInput = document.getElementById('recipientPhoneInput');
    const selectedContactName = document.getElementById('selectedContactName');
    const selectedUserId = document.getElementById('selectedUserId');
    const clearContactBtn = document.getElementById('clearContactBtn');

    // Clock in phone frame
    const now = new Date();
    document.getElementById('phoneTime').textContent = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);

    function resolvePlaceholders(text) {
        if (!text) return '';
        let resolved = text;
        const firstName = currentContact ? (currentContact.first_name || currentContact.name || '') : '';
        const family = currentContact ? (currentContact.family || '') : '';

        resolved = resolved.replaceAll('{نام کاربر}', firstName);
        resolved = resolved.replaceAll('{نام خانوادگی}', family);

        // Date & Time
        const todayJalali = new Intl.DateTimeFormat('fa-IR').format(new Date());
        const timeNow = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
        resolved = resolved.replaceAll('{تاریخ}', todayJalali);
        resolved = resolved.replaceAll('{ساعت}', timeNow);
        resolved = resolved.replaceAll('{شناسه فاکتور}', '1024');
        resolved = resolved.replaceAll('{لینک پرداخت}', 'lalecard.ir/pf/1024');
        resolved = resolved.replaceAll('{شناسه درخواست}', 'REQ-500');

        return resolved;
    }

    function getCurrentBalance() {
        if (serviceTypeOtp.checked) {
            return otpBalance;
        }
        if (senderNumberSelect && senderNumberSelect.selectedOptions.length > 0) {
            return parseFloat(senderNumberSelect.selectedOptions[0].getAttribute('data-balance') || 0);
        }
        return 0;
    }

    // Shared editor (chips + in-field counter). It owns the counting rules.
    const smsEditor = SmsMessageEditor.get('messageTextarea');
    smsEditor.setResolver(resolvePlaceholders);

    // Update all live elements outside the field (phone preview, cost, balance)
    function updateLivePreview() {
        const isAdv = serviceTypeAdv.checked;
        // "لغو 11" is mandatory and unconditional for advertising messages — no toggle.
        smsEditor.setAdvertising(isAdv);
        const seg = smsEditor.getStats();

        // Update Phone Frame (preview shows the final text as the server will send it)
        phoneBubbleText.textContent = seg.empty ? 'متن پیش‌نمایش پیامک...' : seg.finalText;

        // Cost = counted pages x tariff (config services.sms.cost_per_page). The editor already
        // counts the mandatory «لغو 11» for advertising (empty = 1 page); OTP empty = 0.
        const cost = seg.cost;
        previewCost.textContent = cost.toLocaleString('fa-IR');

        const balance = getCurrentBalance();
        previewCurrentBalance.textContent = balance.toLocaleString('fa-IR');

        const balanceAfter = Math.max(0, balance - cost);
        previewBalanceAfter.textContent = balanceAfter.toLocaleString('fa-IR');

        // Hard Block check
        if (balance < cost) {
            insufficientAlert.style.display = 'flex';
            submitSendBtn.disabled = true;
            submitSendBtn.classList.remove('btn-success');
            submitSendBtn.classList.add('btn-secondary');
            singleLastCalculatedDeficit = Math.max(0, cost - balance);
            const textEl = document.getElementById('insufficientBalanceText');
            if (textEl) {
                textEl.innerText = 'موجودی حساب برای ارسال این پیامک کافی نیست (کسری: ' + Number(singleLastCalculatedDeficit).toLocaleString('fa-IR') + ' ریال).';
            }
        } else {
            insufficientAlert.style.display = 'none';
            submitSendBtn.disabled = false;
            submitSendBtn.classList.add('btn-success');
            submitSendBtn.classList.remove('btn-secondary');
            singleLastCalculatedDeficit = 0;
        }

        // An empty message can never be sent, even though its estimated cost is shown.
        if (seg.empty) {
            submitSendBtn.disabled = true;
            submitSendBtn.classList.remove('btn-success');
            submitSendBtn.classList.add('btn-secondary');
        }
    }

    // Classification (عادی / محرمانه) belongs to OTP only. Shown and submitted only when OTP is
    // selected AND allowed for this user (not disabled); otherwise hidden and disabled (not posted).
    function syncClassificationVisibility() {
        const show = !serviceTypeOtp.disabled && serviceTypeOtp.checked;
        const col = document.getElementById('classificationCol');
        const groupCol = document.getElementById('messageGroupCol');
        const regular = document.getElementById('typeRegular');
        const classified = document.getElementById('typeClassified');
        if (col) col.style.display = show ? '' : 'none';
        if (groupCol) {
            groupCol.classList.toggle('col-md-7', show);
            groupCol.classList.toggle('col-12', !show);
        }
        if (regular) regular.disabled = !show;
        if (classified) classified.disabled = !show;

        // Subject categories + templates: promotional mode shows only promotional groups and non-OTP
        // templates; OTP mode shows the full tree and only OTP templates. Non-matching options are
        // hidden AND disabled; an option that became invalid resets to the placeholder.
        const groupSelect = document.getElementById('messageGroupSelect');
        if (groupSelect) {
            Array.prototype.forEach.call(groupSelect.querySelectorAll('option[data-promotional]'), function (o) {
                const blocked = !show && o.getAttribute('data-promotional') !== '1';
                o.disabled = blocked;
                o.hidden = blocked;
            });
            Array.prototype.forEach.call(groupSelect.querySelectorAll('optgroup'), function (g) {
                const blocked = !show && !g.querySelector('option[data-promotional="1"]');
                g.hidden = blocked;
                g.disabled = blocked;
            });
            if (groupSelect.selectedOptions.length && groupSelect.selectedOptions[0].disabled) {
                groupSelect.value = '';
            }
        }
        const groupHint = document.getElementById('messageGroupHint');
        if (groupHint) groupHint.textContent = groupHint.getAttribute(show ? 'data-hint-otp' : 'data-hint-promo');

        const tplSelect = document.getElementById('templateSelect');
        if (tplSelect) {
            Array.prototype.forEach.call(tplSelect.querySelectorAll('option[data-type]'), function (o) {
                const isOtpTpl = o.getAttribute('data-type') === 'otp';
                const blocked = show ? !isOtpTpl : (isOtpTpl || o.getAttribute('data-promotional') !== '1');
                o.disabled = blocked;
                o.hidden = blocked;
            });
            if (tplSelect.selectedOptions.length && tplSelect.selectedOptions[0].disabled) {
                tplSelect.value = '';
            }
        }

        const catBox = document.getElementById('otpCategoryBox');
        const catSelect = document.getElementById('otpCategorySelect');
        if (catBox) catBox.style.display = show ? '' : 'none';
        if (catSelect) { catSelect.disabled = !show; catSelect.required = show; }
    }

    // Toggle service type (Advertising vs OTP)
    function onServiceTypeChange() {
        syncClassificationVisibility();
        if (serviceTypeOtp.checked) {
            document.getElementById('advertisingSenderContainer').style.display = 'none';
            document.getElementById('otpSenderContainer').style.display = 'block';
            phoneSenderNumber.textContent = otpNumber || 'OTP-System';
        } else {
            document.getElementById('advertisingSenderContainer').style.display = 'block';
            document.getElementById('otpSenderContainer').style.display = 'none';
            if (senderNumberSelect.selectedOptions.length > 0) {
                phoneSenderNumber.textContent = senderNumberSelect.selectedOptions[0].getAttribute('data-number') || 'SMS';
            }
        }
        updateLivePreview();
    }

    serviceTypeAdv.addEventListener('change', onServiceTypeChange);
    serviceTypeOtp.addEventListener('change', onServiceTypeChange);
    senderNumberSelect.addEventListener('change', function () {
        if (senderNumberSelect.selectedOptions.length > 0) {
            phoneSenderNumber.textContent = senderNumberSelect.selectedOptions[0].getAttribute('data-number');
        }
        updateLivePreview();
    });
    // Keystroke live update (the shared editor re-dispatches input/change on the textarea)
    smsEditor.onChange(function () { updateLivePreview(); });
    const paramChipEditor = smsEditor.chips;

    // Message Category (Group) & Classification Type Reaction
    const messageGroupSelect = document.getElementById('messageGroupSelect');
    const typeRegular = document.getElementById('typeRegular');
    const typeClassified = document.getElementById('typeClassified');
    const classificationHint = document.getElementById('classificationHint');

    function updateClassificationFromGroup() {
        if (!messageGroupSelect) return;
        const selected = messageGroupSelect.selectedOptions[0];
        if (!selected || !selected.value) {
            if (classificationHint) {
                classificationHint.innerHTML = '<span class="text-muted"><i class="fa fa-info-circle ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> پیام‌های مالی و حقوقی به‌طور خودکار «محرمانه» تعیین می‌شوند.</span>';
            }
            return;
        }

        const isConfidential = selected.getAttribute('data-confidential') === '1';
        const slug = (selected.getAttribute('data-slug') || '').toLowerCase();

        // hoghoghi / mali are by default classified
        if (isConfidential || slug === 'hoghoghi' || slug === 'mali' || slug.includes('hoghoghi') || slug.includes('mali')) {
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

    if (messageGroupSelect) {
        messageGroupSelect.addEventListener('change', updateClassificationFromGroup);
    }

    // Template selection
    templateSelect.addEventListener('change', function () {
        const selected = this.selectedOptions[0];
        if (selected && selected.value) {
            const content = selected.getAttribute('data-content');
            if (paramChipEditor) {
                paramChipEditor.setValue(content);
            } else {
                textarea.value = content;
            }
            const type = selected.getAttribute('data-type');
            if (type === 'otp') {
                serviceTypeOtp.checked = true;
                onServiceTypeChange();
            } else if (type === 'advertising') {
                serviceTypeAdv.checked = true;
                onServiceTypeChange();
            }

            const tplCategory = selected.getAttribute('data-otp-category');
            const catSelect = document.getElementById('otpCategorySelect');
            if (type === 'otp' && tplCategory && catSelect) catSelect.value = tplCategory;

            const groupId = selected.getAttribute('data-group-id');
            if (groupId && messageGroupSelect) {
                messageGroupSelect.value = groupId;
                updateClassificationFromGroup();
            }

            updateLivePreview();
        }
    });

    // Recipient Phone typing
    recipientPhoneInput.addEventListener('input', function () {
        phoneRecipientTitle.textContent = this.value || 'مخاطب';
    });

    // Contact search
    let searchTimeout = null;
    contactSearchInput.addEventListener('input', function () {
        const query = this.value.trim();
        if (searchTimeout) clearTimeout(searchTimeout);

        if (query.length < 2) {
            contactSearchResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.sms.single.contacts.search') }}?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    contactSearchResults.innerHTML = '';
                    if (data.status === 'success' && data.data && data.data.length > 0) {
                        data.data.forEach(contact => {
                            const item = document.createElement('div');
                            item.className = 'contact-dropdown-item';
                            item.innerHTML = `<strong>${contact.name}</strong> <span class="text-muted font-monospace small">(${contact.phone})</span>`;
                            item.addEventListener('click', () => {
                                selectContact(contact);
                                contactSearchResults.style.display = 'none';
                            });
                            contactSearchResults.appendChild(item);
                        });
                        contactSearchResults.style.display = 'block';
                    } else {
                        contactSearchResults.innerHTML = '<div class="p-2 text-muted small">مخاطبی یافت نشد.</div>';
                        contactSearchResults.style.display = 'block';
                    }
                });
        }, 250);
    });

    function selectContact(contact) {
        currentContact = contact;
        recipientPhoneInput.value = contact.phone;
        selectedContactName.value = contact.name;
        selectedUserId.value = contact.id;
        contactSearchInput.value = `${contact.name} (${contact.phone})`;

        phoneRecipientTitle.textContent = contact.name;
        phoneAvatar.textContent = contact.name ? contact.name.charAt(0) : '?';

        updateLivePreview();
    }

    clearContactBtn.addEventListener('click', function () {
        currentContact = null;
        recipientPhoneInput.value = '';
        selectedContactName.value = '';
        selectedUserId.value = '';
        contactSearchInput.value = '';
        phoneRecipientTitle.textContent = 'مخاطب';
        phoneAvatar.textContent = '?';
        updateLivePreview();
    });

    // Inline Add New Contact Form Submission
    const newContactForm = document.getElementById('newContactForm');
    newContactForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const alertBox = document.getElementById('contactModalAlert');
        alertBox.style.display = 'none';

        const payload = {
            name: document.getElementById('newContactName').value,
            family: document.getElementById('newContactFamily').value,
            phone: document.getElementById('newContactPhone').value,
            _token: '{{ csrf_token() }}'
        };

        fetch(`{{ route('admin.sms.single.contacts.store') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const contactModal = bootstrap.Modal.getInstance(document.getElementById('newContactModal'));
                if (contactModal) contactModal.hide();
                newContactForm.reset();
                selectContact(data.contact);
            } else {
                alertBox.textContent = data.message || 'خطا در ثبت مخاطب.';
                alertBox.style.display = 'block';
            }
        })
        .catch(err => {
            alertBox.textContent = 'خطای ارتباط با سرور.';
            alertBox.style.display = 'block';
        });
    });

    // Forward Message Button click
    document.querySelectorAll('.forward-msg-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-msg-id');
            const isRedacted = this.getAttribute('data-msg-redacted') === '1';
            const content = isRedacted ? 'محرمانه - دسترسی محدود' : this.getAttribute('data-msg-content');
            document.getElementById('forwardOriginalMsgId').value = id;
            document.getElementById('forwardMsgContent').textContent = content;
        });
    });

    // Initial calculation on load
    onServiceTypeChange();
});

// Single Quick Payment Gate Logic
let singleLastCalculatedDeficit = 0;
let currentSingleBusinessId = "{{ $business?->id }}";

function openSingleQuickPaymentGate(suggestedDeficit) {
    const isOtp = document.getElementById('serviceTypeOtp').checked;
    const senderSelect = document.getElementById('senderNumberSelect');
    let senderNumber = '';
    let currentBal = 0;

    if (isOtp) {
        senderNumber = 'خط خدماتی و اعتبارسنجی (OTP)';
        currentBal = parseFloat(document.getElementById('previewCurrentBalance').innerText.replace(/[^0-9]/g, '')) || 0;
    } else {
        if (senderSelect && senderSelect.selectedOptions.length > 0) {
            senderNumber = senderSelect.selectedOptions[0].getAttribute('data-number') || 'شماره تبلیغاتی';
            currentBal = parseFloat(senderSelect.selectedOptions[0].getAttribute('data-balance') || 0);
        }
    }

    const deficit = suggestedDeficit !== undefined && suggestedDeficit !== null ? Number(suggestedDeficit) : singleLastCalculatedDeficit;

    document.getElementById('qpSingleModalServiceInfo').innerText = (isOtp ? 'سرویس OTP: ' : 'خط تبلیغاتی: ') + senderNumber;
    document.getElementById('qpSingleModalCurrentBalance').innerText = Number(currentBal).toLocaleString('fa-IR') + ' ریال';
    document.getElementById('qpSingleModalDeficitAmount').innerText = Number(deficit).toLocaleString('fa-IR') + ' ریال';

    const smartDeficitWrapper = document.getElementById('qpSingleSmartDeficitWrapper');
    if (deficit > 0) {
        smartDeficitWrapper.style.display = 'block';
        document.getElementById('qpSingleSmartDeficitValue').innerText = Number(deficit).toLocaleString('fa-IR') + ' ریال';
        selectSingleQuickPreset(document.getElementById('qpSingleBtnSmartDeficit'), deficit);
    } else {
        smartDeficitWrapper.style.display = 'none';
        const defaultPreset = document.querySelector('.qp-single-preset-btn[data-amount="500000"]');
        if (defaultPreset) selectSingleQuickPreset(defaultPreset, 500000);
    }

    const modalEl = document.getElementById('quickPaymentSingleModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function selectSingleQuickPreset(btn, amount) {
    document.querySelectorAll('.qp-single-preset-btn, #qpSingleBtnSmartDeficit').forEach(b => {
        b.classList.remove('btn-primary', 'text-white', 'border-primary');
        if (b.id !== 'qpSingleBtnSmartDeficit') b.classList.add('btn-outline-secondary');
    });
    if (btn) {
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-primary', 'text-white');
    }
    const val = amount || singleLastCalculatedDeficit || 500000;
    document.getElementById('qpSingleCustomAmountInput').value = val;
    updateSingleTomanDisplay(val);
}

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('qpSingleCustomAmountInput');
    if (input) {
        input.addEventListener('input', function() {
            document.querySelectorAll('.qp-single-preset-btn, #qpSingleBtnSmartDeficit').forEach(b => {
                b.classList.remove('btn-primary', 'text-white');
                if (b.id !== 'qpSingleBtnSmartDeficit') b.classList.add('btn-outline-secondary');
            });
            updateSingleTomanDisplay(this.value);
        });
    }
});

function updateSingleTomanDisplay(amount) {
    const num = parseInt(amount) || 0;
    const toman = Math.floor(num / 10);
    const el = document.getElementById('qpSingleTomanEquivalent');
    if (el) el.innerText = 'معادل: ' + Number(toman).toLocaleString('fa-IR') + ' تومان';
}

function highlightSingleGateway(radio) {
    document.querySelectorAll('#quickPaymentSingleModal .qp-gateway-card').forEach(c => c.classList.remove('active'));
    radio.closest('.qp-gateway-card').classList.add('active');
}

function executeSingleQuickPayment() {
    const amount = parseInt(document.getElementById('qpSingleCustomAmountInput').value) || 0;
    if (amount < 1000) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'مبلغ نامعتبر', text: 'لطفاً مبلغ شارژ را حداقل ۱۰,۰۰۰ ریال وارد فرمایید.' });
        } else {
            alert('لطفاً مبلغ شارژ را حداقل ۱۰,۰۰۰ ریال وارد فرمایید.');
        }
        return;
    }

    const isOtp = document.getElementById('serviceTypeOtp').checked;
    const senderSelect = document.getElementById('senderNumberSelect');
    const senderId = senderSelect?.value || null;
    const selectedGw = document.querySelector('input[name="qp_gateway_single"]:checked')?.value || 'sadad';
    const btn = document.getElementById('btnExecuteSingleQuickPay');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال انتقال به درگاه پرداخت...';

    const targetUrl = isOtp 
        ? "{{ route('admin.sms.financial.otp.topup') }}"
        : "{{ route('admin.sms.financial.advertising.topup') }}";

    const payload = isOtp ? {
        amount: amount,
        business_id: currentSingleBusinessId
    } : {
        sender_number_id: senderId,
        amount: amount,
        business_id: currentSingleBusinessId
    };

    fetch(targetUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json().then(data => ({ status: r.status, data })))
    .then(({ status, data }) => {
        // Payment flow: nothing is credited here; go to the gateway and return to this page after payment.
        if (status === 200 && data.success && data.redirect_url) { window.location.href = data.redirect_url; return; }
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (status === 200 && data.success) {
            const modalEl = document.getElementById('quickPaymentSingleModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            const newBal = data.transaction?.balance_after ?? 0;
            if (isOtp) {
                window.otpBalance = newBal;
            } else if (senderSelect && senderSelect.selectedOptions.length > 0) {
                senderSelect.selectedOptions[0].setAttribute('data-balance', newBal);
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'شارژ آنلاین با موفقیت انجام شد!',
                    html: `موجودی سرویس به میزان <b>${Number(amount).toLocaleString('fa-IR')} ریال</b> افزایش یافت.`,
                    confirmButtonText: 'عالی، ارسال پیامک'
                });
            }

            // Trigger live preview update
            const textarea = document.getElementById('messageTextarea');
            if (textarea) textarea.dispatchEvent(new Event('input'));
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
</script>
@stop
