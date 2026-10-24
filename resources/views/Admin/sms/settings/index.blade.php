@extends('Admin.master')

@section('title', 'تنظیمات و مدیریت سرویس پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">تنظیمات و مدیریت سرویس پیامک</h3>
            <p class="text-muted small mb-0">مدیریت پنل‌های ارتباطی، شماره‌های اختصاصی، الگوهای پیام، گروه‌بندی و قراردادها</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.settings.index') }}" class="d-flex flex-wrap gap-2 m-0">
                    <select name="business_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- کلیه کسب‌وکارها (نمای سراسری) --</option>
                        @foreach($all_businesses as $b)
                            <option value="{{ $b->id }}" {{ $business && $business->id === $b->id ? 'selected' : '' }}>
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
            @else
                <span class="badge bg-secondary text-white px-3 py-2">
                    <i class="fa fa-globe me-1"></i> مدیریت سراسری سیستم
                </span>
            @endif
        </div>
    </div>

    @include('Admin.sms.settings.navigation')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if(auth()->user()?->isSuperAdmin())
    <!-- SuperAdmin Executive Governance Center -->
    <div style="background-color: #0f172a !important; color: #ffffff !important; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3 pb-3" style="border-bottom: 1px solid rgba(255,255,255,0.15) !important;">
            <div>
                <h5 class="fw-bold mb-1" style="color: #ffffff !important;">
                    <i class="fa fa-shield" style="color: #fbbf24 !important; margin-left: 8px;"></i> مرکز فرماندهی و اختیارات سوپر ادمین
                </h5>
                <p class="small mb-0" style="color: #cbd5e1 !important;">
                    دسترسی مستقیم و حاکمیتی به قوانین رگولاتوری، ویرایش متون حقوقی و تعهدنامه، مدیریت بالانس و خطوط کلیه کسب‌وکارها
                </p>
            </div>
            <div>
                <a href="{{ route('admin.sms.reports.superadmin.index') }}" class="btn btn-warning btn-sm fw-bold" style="background-color: #f59e0b !important; color: #0f172a !important; border: none; padding: 8px 16px;">
                    <i class="fa fa-globe" style="margin-left: 6px;"></i> گزارش ۳۶۰ درجه کلیه کسب‌وکارها
                </a>
            </div>
        </div>

        <div class="row g-3">
            <!-- Box 1: Merged Legal & Contracts -->
            <div class="col-12 col-md-6 col-xl-3">
                <div style="background-color: #1e293b !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px; padding: 18px; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #ffffff !important;">
                            <i class="fa fa-file-text-o" style="color: #38bdf8 !important; margin-left: 8px;"></i> قراردادها، تعهدنامه و ضوابط
                        </h6>
                        <p class="small mb-3" style="color: #94a3b8 !important; font-size: 13px; line-height: 1.7;">
                            مدیریت الگوی قرارداد و انتساب قراردادها، ویرایش ضوابط مخابراتی و مشاهده وضعیت امضای OTP غرفه‌ها.
                        </p>
                    </div>
                    <a href="{{ route('admin.sms.settings.contracts.index') }}" class="btn btn-sm w-100" style="background-color: rgba(255,255,255,0.08) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                        مدیریت قراردادها و ضوابط <i class="fa fa-arrow-left" style="margin-right: 6px;"></i>
                    </a>
                </div>
            </div>

            <!-- Box 3 -->
            <div class="col-12 col-md-6 col-xl-3">
                <div style="background-color: #1e293b !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px; padding: 18px; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #ffffff !important;">
                            <i class="fa fa-shield" style="color: #c084fc !important; margin-left: 8px;"></i> سطوح دسترسی و کاربران
                        </h6>
                        <p class="small mb-3" style="color: #94a3b8 !important; font-size: 13px; line-height: 1.7;">
                            کنترل سطوح دسترسی (نقره‌ای، طلایی، الماس، تیتانیوم) و ارتقای اختیارات مدیران.
                        </p>
                    </div>
                    <a href="{{ route('admin.sms.settings.access-tiers.index') }}" class="btn btn-sm w-100" style="background-color: rgba(255,255,255,0.08) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                        مدیریت سطوح دسترسی <i class="fa fa-arrow-left" style="margin-right: 6px;"></i>
                    </a>
                </div>
            </div>

            <!-- Box 4: AI & LLM Settings -->
            <div class="col-12 col-md-6 col-xl-3">
                <div style="background-color: #1e293b !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px; padding: 18px; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #ffffff !important;">
                            <i class="fa fa-magic" style="color: #fbbf24 !important; margin-left: 8px;"></i> مدل‌های هوش مصنوعی (LLM)
                        </h6>
                        <p class="small mb-3" style="color: #94a3b8 !important; font-size: 13px; line-height: 1.7;">
                            تنظیم و اتصال زنده به مدل‌های هوش مصنوعی (Gemini, DeepSeek, OpenAI, Ollama) برای استودیو کمپین.
                        </p>
                    </div>
                    <a href="{{ route('admin.sms.settings.ai.index') }}" class="btn btn-sm w-100" style="background-color: rgba(255,255,255,0.08) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                        تنظیمات هوش مصنوعی <i class="fa fa-arrow-left" style="margin-right: 6px;"></i>
                    </a>
                </div>
            </div>

            <!-- Box 5: Financial Balances -->
            <div class="col-12 col-md-6 col-xl-3">
                <div style="background-color: #1e293b !important; border: 1px solid rgba(255,255,255,0.12) !important; border-radius: 10px; padding: 18px; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <h6 class="fw-bold mb-2" style="color: #ffffff !important;">
                            <i class="fa fa-credit-card" style="color: #4ade80 !important; margin-left: 8px;"></i> موجودی و بالانس مالی
                        </h6>
                        <p class="small mb-3" style="color: #94a3b8 !important; font-size: 13px; line-height: 1.7;">
                            کنترل شارژ کیف‌پول پیامک تبلیغاتی و OTP کسب‌وکارها و افزایش اعتبار.
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.sms.financial.advertising.index') }}" class="btn btn-sm flex-fill" style="background-color: rgba(255,255,255,0.08) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                            تبلیغاتی
                        </a>
                        @if(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()))
                        <a href="{{ route('admin.sms.financial.otp.index') }}" class="btn btn-sm flex-fill" style="background-color: rgba(255,255,255,0.08) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.2) !important;">
                            OTP
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        @if(auth()->user()?->isSuperAdmin())
        <!-- Panels (SuperAdmin Only) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #3b82f6 !important; background: #ffffff !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">پنل‌های پیامک</span>
                        <i class="fa fa-server text-primary"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h3 class="fw-bold mb-0 text-dark" style="color: #0f172a !important;">{{ $stats['panels_count'] }}</h3>
                        <span class="badge bg-success small">{{ $stats['active_panels_count'] }} فعال</span>
                    </div>
                    <a href="{{ route('admin.sms.settings.panels.index') }}" class="small text-decoration-none fw-bold text-primary">
                        مدیریت پنل‌ها <i class="fa fa-angle-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @else
        <!-- Contracts & Legal Status (Business User Card) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #3b82f6 !important; background: #ffffff !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">وضعیت تعهدنامه و ضوابط</span>
                        <i class="fa fa-file-text-o text-primary"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        @if($business && $business->sms_contract_accepted)
                            <span class="badge bg-success py-2 px-3">امضا شده (فعال)</span>
                        @else
                            <span class="badge bg-warning text-dark py-2 px-3">در انتظار امضا</span>
                        @endif
                    </div>
                    <a href="{{ route('admin.sms.settings.contracts.index') }}" class="small text-decoration-none fw-bold text-primary">
                        مشاهده قراردادها و ضوابط <i class="fa fa-angle-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Sender Numbers -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #10b981 !important; background: #ffffff !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">خطوط پیامک</span>
                        <i class="fa fa-phone text-success"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h3 class="fw-bold mb-0 text-dark" style="color: #0f172a !important;">{{ $stats['senders_count'] }}</h3>
                        <span class="badge bg-secondary small">{{ $stats['adv_senders_count'] }} تبلیغاتی</span>
                    </div>
                    <a href="{{ route('admin.sms.settings.senders.index') }}" class="small text-decoration-none fw-bold text-success">
                        مشاهده خطوط <i class="fa fa-angle-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Templates -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #f59e0b !important; background: #ffffff !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">الگوهای پیامک</span>
                        <i class="fa fa-file-text-o text-warning"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h3 class="fw-bold mb-0 text-dark" style="color: #0f172a !important;">{{ $stats['templates_count'] }}</h3>
                        <span class="badge bg-success small">{{ $stats['active_templates_count'] }} فعال</span>
                    </div>
                    <a href="{{ route('admin.sms.settings.templates.index') }}" class="small text-decoration-none fw-bold text-warning">
                        مشاهده الگوها <i class="fa fa-angle-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Groups -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="border-top: 4px solid #06b6d4 !important; background: #ffffff !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-bold">گروه‌ها و زیرگروه‌ها</span>
                        <i class="fa fa-folder-open-o text-info"></i>
                    </div>
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h3 class="fw-bold mb-0 text-dark" style="color: #0f172a !important;">{{ $stats['groups_count'] + $stats['subgroups_count'] }}</h3>
                        <span class="badge bg-secondary small">{{ $stats['groups_count'] }} اصلی</span>
                    </div>
                    <a href="{{ route('admin.sms.settings.groups.index') }}" class="small text-decoration-none fw-bold text-info">
                        مدیریت گروه‌ها <i class="fa fa-angle-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Row -->
    <div class="row g-4">
        <!-- OTP Global Status Card -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark" style="color: #0f172a !important;">
                        <i class="fa fa-shield text-primary me-2"></i> وضعیت سرویس احراز هویت (OTP)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">شماره فرستنده OTP سامانه:</span>
                            @if($stats['otp_sender'])
                                <span class="badge bg-success font-monospace px-3 py-2 fs-6" dir="ltr">
                                    {{ $stats['otp_sender']->number }}
                                </span>
                            @else
                                <span class="badge bg-danger">تنظیم نشده</span>
                            @endif
                        </div>
                        @if(auth()->user()?->isSuperAdmin())
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">درگاه ارتباطی OTP:</span>
                            <span class="fw-bold text-dark">{{ $stats['otp_sender']->sms_panel ?? '—' }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">وضعیت خط OTP:</span>
                            <span class="badge {{ ($stats['otp_sender'] && $stats['otp_sender']->status === 'active') ? 'bg-success' : 'bg-secondary' }}">
                                {{ ($stats['otp_sender'] && $stats['otp_sender']->status === 'active') ? 'فعال' : 'غیرفعال' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">قرارداد سرویس OTP:</span>
                            @if($stats['otp_contract'] && $stats['otp_contract']->contract)
                                <span class="badge bg-success">
                                    {{ $stats['otp_contract']->contract->title }}
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">ثبت نشده</span>
                            @endif
                        </div>
                        @if($stats['otp_contract'] && $stats['otp_contract']->contract)
                            <div class="small text-muted mb-2">
                                شماره قرارداد: {{ $stats['otp_contract']->contract_number ?: 'بدون شماره' }} | تاریخ: {{ \App\Helpers\Helper::jalaliDate($stats['otp_contract']->contract_date) ?? '—' }}
                            </div>
                        @endif
                        <div class="text-end mt-2">
                            <a href="{{ route('admin.sms.settings.contracts.index') }}" class="btn btn-sm btn-outline-primary">
                                مشاهده قرارداد و ضوابط
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advertising & Business Scope Card -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark" style="color: #0f172a !important;">
                        <i class="fa fa-bullhorn text-success me-2"></i> سرویس پیامک تبلیغاتی
                    </h5>
                </div>
                <div class="card-body">
                    @if($business)
                        <div class="mb-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <h6 class="fw-bold mb-2 text-dark">اطلاعات کسب‌وکار فعلی ({{ $business->title }})</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">خطوط تخصیص‌یافته از مخزن:</span>
                                <span class="fw-bold text-dark">{{ $stats['senders_count'] }} شماره</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">قرارداد تبلیغاتی:</span>
                                @if($stats['business_adv_contract'] && $stats['business_adv_contract']->contract)
                                    <span class="badge bg-success">
                                        {{ $stats['business_adv_contract']->contract->title }}
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">قرارداد ثبت نشده</span>
                                @endif
                            </div>
                            <div class="text-end mt-3">
                                <a href="{{ route('admin.sms.settings.contracts.index') }}" class="btn btn-sm btn-outline-success">
                                    مشاهده قرارداد پیامک تبلیغاتی
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-3 p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                            <h6 class="fw-bold mb-2 text-dark">مدیریت کل سیستم (سطح سوپرادمین)</h6>
                            <p class="small text-muted mb-2">
                                در این سطح می‌توانید پنل‌های ارتباطی، تمامی خطوط فرستنده، نحوه تخصیص اشتراکی آن‌ها به کسب‌وکارها و قراردادهای هر کسب‌وکار را کنترل کنید.
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">تعداد قراردادهای تبلیغاتی فعال کسب‌وکارها:</span>
                                <span class="badge bg-info text-dark px-3 py-2 fw-bold fs-6">{{ $stats['total_adv_contracts'] }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        @if(auth()->user()?->isSuperAdmin())
                            <a href="{{ route('admin.sms.settings.senders.create') }}" class="btn btn-primary flex-fill">
                                <i class="fa fa-plus me-1"></i> افزودن خط به مخزن
                            </a>
                        @else
                            <a href="{{ route('admin.sms.settings.senders.index') }}" class="btn btn-outline-success flex-fill">
                                <i class="fa fa-phone me-1"></i> مشاهده خطوط مجاز کسب‌وکار
                            </a>
                        @endif
                        <a href="{{ route('admin.sms.settings.templates.create') }}" class="btn btn-outline-primary flex-fill">
                            <i class="fa fa-plus me-1"></i> ایجاد الگوی پیامک
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
