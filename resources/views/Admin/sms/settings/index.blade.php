@extends('Admin.master')

@section('title', 'تنظیمات و مدیریت سرویس پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">تنظیمات و مدیریت سرویس پیامک</h3>
            <p class="text-muted small mb-0">مدیریت پنل‌های ارتباطی، شماره‌های اختصاصی، الگوهای پیام، گروه‌بندی و قراردادها</p>
        </div>
        @if($business)
            <span class="badge bg-info text-dark px-3 py-2">
                <i class="fa fa-building-o me-1"></i> کسب‌وکار: {{ $business->title }}
            </span>
        @endif
    </div>

    @include('Admin.sms.settings.navigation')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    <!-- Summary Stats -->
    <div class="row g-3 mb-4">
        <!-- Panels -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fa fa-server fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">پنل‌های پیامک</div>
                        <h4 class="fw-bold mb-0">{{ $stats['panels_count'] }}</h4>
                        <small class="text-success">{{ $stats['active_panels_count'] }} فعال</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.sms.settings.panels.index') }}" class="small text-decoration-none">
                        مدیریت پنل‌ها <i class="fa fa-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Sender Numbers -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success me-3">
                        <i class="fa fa-phone fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">خطوط اختصاصی</div>
                        <h4 class="fw-bold mb-0">{{ $stats['senders_count'] }}</h4>
                        <small class="text-muted">{{ $stats['adv_senders_count'] }} تبلیغاتی</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.sms.settings.senders.index') }}" class="small text-decoration-none">
                        مدیریت خطوط <i class="fa fa-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Templates -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fa fa-file-text-o fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">الگوهای پیامک</div>
                        <h4 class="fw-bold mb-0">{{ $stats['templates_count'] }}</h4>
                        <small class="text-success">{{ $stats['active_templates_count'] }} الگو فعال</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.sms.settings.templates.index') }}" class="small text-decoration-none">
                        مشاهده الگوها <i class="fa fa-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Groups & Contracts -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info me-3">
                        <i class="fa fa-folder-open-o fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">گروه‌ها و زیرگروه‌ها</div>
                        <h4 class="fw-bold mb-0">{{ $stats['groups_count'] + $stats['subgroups_count'] }}</h4>
                        <small class="text-muted">{{ $stats['groups_count'] }} گروه اصلی</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="{{ route('admin.sms.settings.groups.index') }}" class="small text-decoration-none">
                        مدیریت گروه‌ها <i class="fa fa-arrow-left ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Row -->
    <div class="row g-4">
        <!-- OTP Global Status Card -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa fa-shield text-primary me-2"></i> وضعیت سرویس احراز هویت (OTP)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 p-3 rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">شماره فرستنده OTP سراسری:</span>
                            @if($stats['otp_sender'])
                                <span class="badge bg-success font-monospace px-3 py-2 fs-6">
                                    {{ $stats['otp_sender']->number }}
                                </span>
                            @else
                                <span class="badge bg-danger">تنظیم نشده</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">پنل ارتباطی OTP:</span>
                            <span class="fw-bold">{{ $stats['otp_sender']->sms_panel ?? '—' }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">وضعیت خط OTP:</span>
                            <span class="badge {{ ($stats['otp_sender'] && $stats['otp_sender']->status === 'active') ? 'bg-success' : 'bg-secondary' }}">
                                {{ $stats['otp_sender']->status ?? 'نامشخص' }}
                            </span>
                        </div>
                    </div>

                    <div class="p-3 rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">قرارداد عمومی OTP:</span>
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
                                شماره قرارداد: {{ $stats['otp_contract']->contract_number ?: 'بدون شماره' }} | تاریخ: {{ $stats['otp_contract']->contract_date ?: '—' }}
                            </div>
                        @endif
                        <div class="text-end mt-2">
                            <a href="{{ route('admin.sms.settings.contracts.index') }}" class="btn btn-sm btn-outline-primary">
                                مشاهده و تغییر قرارداد
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advertising & Business Scope Card -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa fa-bullhorn text-success me-2"></i> سرویس پیامک تبلیغاتی
                    </h5>
                </div>
                <div class="card-body">
                    @if($business)
                        <div class="mb-3 p-3 rounded bg-light">
                            <h6 class="fw-bold mb-2">اطلاعات کسب‌وکار فعلی ({{ $business->title }})</h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">خطوط اختصاصی تخصیص داده شده:</span>
                                <span class="fw-bold">{{ $stats['senders_count'] }} شماره</span>
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
                                    تنظیم قرارداد پیامک تبلیغاتی
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="mb-3 p-3 rounded bg-light">
                            <h6 class="fw-bold mb-2">مدیریت کل سیستم (سطح سوپرادمین)</h6>
                            <p class="small text-muted mb-2">
                                در این سطح می‌توانید پنل‌های ارتباطی، تمامی خطوط فرستنده، نحوه تخصیص آن‌ها به کسب‌وکارها و قراردادهای هر کسب‌وکار را کنترل کنید.
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">تعداد قراردادهای تبلیغاتی فعال کسب‌وکارها:</span>
                                <span class="badge bg-primary fs-6">{{ $stats['total_adv_contracts'] }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.sms.settings.senders.create') }}" class="btn btn-primary flex-fill">
                            <i class="fa fa-plus me-1"></i> افزودن خط اختصاصی
                        </a>
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
