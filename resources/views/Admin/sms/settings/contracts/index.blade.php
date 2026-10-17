@extends('Admin.master')

@section('title', 'مدیریت قراردادها و تعهدنامه‌های پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">قراردادها و تعهدنامه‌های پیامک</h3>
            <p class="text-muted small mb-0">اتصال به سیستم قراردادهای لاله (بانک قراردادها، پوشه‌ها و کتابخانه رسانه)</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.settings.contracts.index') }}" class="d-flex flex-wrap gap-2 m-0">
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
                    <i class="fa fa-building-o ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> کسب‌وکار: {{ $business->title }}
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

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Section 0: Client SMS Service Agreement & Electronic Signature (Sign once, view-only once signed) --}}
    @if($business)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-file-text-o text-primary fs-5" style="margin-left: 6px !important; margin-right: 0 !important;"></i>
                    <h5 class="fw-bold mb-0">تعهدنامه و قرارداد استفاده از سامانه پیامک (کسب‌وکار: {{ $business->title }})</h5>
                </div>
                <div>
                    @if($isContractSigned)
                        <span class="badge bg-success px-3 py-2">
                            <i class="fa fa-check-circle ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> امضا و تایید شده (صرفاً قابل مشاهده)
                        </span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2">
                            <i class="fa fa-clock-o ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> در انتظار امضا و تایید
                        </span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                @if($isContractSigned)
                    {{-- SIGNED STATE: Once signed, they ONLY can view it --}}
                    <div class="alert alert-success d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 p-3 mb-4 rounded-3 border-0 bg-success bg-opacity-10">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                <i class="fa fa-check fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-success mb-1">این تعهدنامه به صورت الکترونیکی امضا شده و وضعیت آن نهایی است</h6>
                                <p class="text-muted small mb-0">
                                    امضای الکترونیکی ثبت گردیده است. بر اساس ضوابط سامانه، این قرارداد پس از امضا <strong>صرفاً قابل مشاهده (Read-Only)</strong> بوده و غیرقابل ویرایش مجدد می‌باشد.
                                </p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-success btn-sm fw-bold" data-bs-toggle="collapse" data-bs-target="#signedContractTextCollapse">
                                <i class="fa fa-eye ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> مشاهده متن تعهدنامه
                            </button>
                            <a href="{{ route('admin.sms.contract.sign') }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <i class="fa fa-print ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> نسخه رسمی و چاپ
                            </a>
                        </div>
                    </div>

                    {{-- Metadata Certificate Box --}}
                    <div class="row g-3 p-3 bg-light rounded-3 mb-3 border">
                        <div class="col-12 col-md-4">
                            <span class="text-muted small d-block">نام و نام خانوادگی امضاکننده:</span>
                            <strong class="text-dark">{{ $contractSigner ?: 'ثبت شده' }}</strong>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="text-muted small d-block">تاریخ و زمان ثبت امضا:</span>
                            <strong class="text-dark">{{ $contractSignedAt ? verta($contractSignedAt)->format('Y/m/d - H:i:s') : '—' }}</strong>
                        </div>
                        <div class="col-12 col-md-4">
                            <span class="text-muted small d-block">شناسه یکتای ثبت قرارداد:</span>
                            <strong class="font-monospace text-primary">SMS-AGR-{{ $business->id }}-{{ $contractSignedAt ? \Carbon\Carbon::parse($contractSignedAt)->timestamp : '0' }}</strong>
                        </div>
                    </div>

                    {{-- Read-only collapsible contract text --}}
                    <div class="collapse show" id="signedContractTextCollapse">
                        <div class="row g-3 mt-1">
                            <div class="col-12 col-lg-8">
                                <div class="p-3 bg-white border rounded-3">
                                    <h6 class="fw-bold text-dark mb-2 border-bottom pb-2">
                                        <i class="fa fa-file-text-o text-muted ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> متن تعهدنامه امضاشده
                                    </h6>
                                    <div style="max-height: 280px; overflow-y: auto; line-height: 1.8; font-size: 0.9rem;" class="text-secondary">
                                        <p style="white-space: pre-wrap;" class="mb-0">{{ $contractText }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="p-3 bg-white border rounded-3">
                                    <h6 class="fw-bold text-dark mb-2 border-bottom pb-2">
                                        <i class="fa fa-balance-scale text-muted ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ضوابط و مقررات مخابراتی
                                    </h6>
                                    <div style="max-height: 280px; overflow-y: auto; line-height: 1.8; font-size: 0.85rem;" class="text-muted">
                                        <p style="white-space: pre-wrap;" class="mb-0">{{ $regulatoryRulesText }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    {{-- UNSIGNED STATE: Client can see and sign the contract once --}}
                    <div class="alert alert-warning p-3 mb-4 rounded-3 border-0 bg-warning bg-opacity-10">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; min-width: 44px;">
                                <i class="fa fa-exclamation-triangle fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">تعهدنامه استفاده از سرویس پیامک هنوز امضا نشده است</h6>
                                <p class="text-muted small mb-0">
                                    جهت فعال‌سازی و ارسال هرگونه پیامک، مطالعه و امضای الکترونیکی تعهدنامه و ضوابط رگولاتوری الزامی است. لطفاً متن زیر را مطالعه کرده و فرم انتهای آن را امضا فرمایید.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-lg-8">
                            <div class="p-3 bg-light border rounded-3">
                                <h6 class="fw-bold text-dark mb-2 border-bottom pb-2">
                                    <i class="fa fa-file-text-o text-muted ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> متن تعهدنامه
                                </h6>
                                <div style="max-height: 280px; overflow-y: auto; line-height: 1.8; font-size: 0.9rem;" class="text-secondary bg-white p-3 rounded border">
                                    <p style="white-space: pre-wrap;" class="mb-0">{{ $contractText }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-4">
                            <div class="p-3 bg-light border rounded-3">
                                <h6 class="fw-bold text-dark mb-2 border-bottom pb-2">
                                    <i class="fa fa-balance-scale text-muted ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ضوابط و مقررات مخابراتی
                                </h6>
                                <div style="max-height: 280px; overflow-y: auto; line-height: 1.8; font-size: 0.85rem;" class="text-muted bg-white p-3 rounded border">
                                    <p style="white-space: pre-wrap;" class="mb-0">{{ $regulatoryRulesText }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action to 2-Step OTP Digital Signing --}}
                    <div class="p-3 bg-light border rounded-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1">
                                <i class="fa fa-shield text-primary ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> فرآیند دو مرحله‌ای امضای الکترونیک (تایید پیامکی OTP)
                            </h6>
                            <p class="text-muted small mb-0">
                                امضای رسمی تعهدنامه مطابق با استاندارد سامانه، با ارسال کد تایید ۶ رقمی به شماره موبایل شما انجام می‌پذیرد.
                            </p>
                        </div>
                        <a href="{{ route('admin.sms.contract.sign') }}" class="btn btn-success fw-bold px-4 py-2 flex-shrink-0">
                            <i class="fa fa-pencil ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ورود به فرم امضای دیجیتال با کد پیامکی (OTP)
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Section 1: Global OTP Contract -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="fw-bold mb-0">
                        <i class="fa fa-shield text-warning ms-2" style="margin-left: 8px !important; margin-right: 0 !important;"></i> قرارداد عمومی پیامک خدماتی (OTP)
                    </h5>
                    <span class="badge bg-warning text-dark">سراسری سامانه</span>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">
                        این تعهدنامه برای تمامی ارسال‌های احراز هویت و کدهای تایید در سراسر سامانه الزامی و معتبر است.
                    </p>

                    @if($otpContractUsage && $otpContractUsage->contract)
                        @php $contract = $otpContractUsage->contract; @endphp
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-primary mb-0">{{ $contract->title }}</h6>
                                <span class="badge bg-success">فعال</span>
                            </div>
                            <div class="small text-muted mb-2">
                                <div>شماره قرارداد: <strong>{{ $otpContractUsage->contract_number ?: 'ثبت نشده' }}</strong></div>
                                <div>تاریخ قرارداد: <strong>{{ $otpContractUsage->contract_date ? verta($otpContractUsage->contract_date)->format('Y/m/d') : '—' }}</strong></div>
                                <div>پوشه: <strong>{{ $contract->folder?->title ?: 'دسته‌بندی نشده' }}</strong></div>
                            </div>

                            @if($contract->content)
                                <div class="p-2 bg-white rounded border small text-muted mb-2" style="max-height: 120px; overflow-y: auto; line-height: 1.6;">
                                    {{ Str::limit($contract->content, 250) }}
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <div>
                                    @if($contract->hasOriginalFile())
                                        <a href="{{ $contract->file }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-download ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> دانلود فایل ضمیمه قرارداد
                                        </a>
                                    @else
                                        <span class="text-muted small">بدون فایل ضمیمه</span>
                                    @endif
                                </div>
                                @if(auth()->user()?->isSuperAdmin())
                                <form action="{{ route('admin.sms.settings.contracts.destroy', $otpContractUsage) }}" method="POST" onsubmit="return confirm('آیا از غیرفعال‌سازی این قرارداد اطمینان دارید؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-times ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> لغو انتساب
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning py-3 mb-3">
                            <i class="fa fa-exclamation-circle ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> در حال حاضر هیچ قرارداد سراسری برای خطوط OTP فعال نشده است.
                        </div>
                    @endif

                    @if(auth()->user()?->isSuperAdmin())
                    <!-- Attach/Create OTP Contract Form (SuperAdmin Only) -->
                    <div class="accordion" id="otpAccordion">
                        <div class="accordion-item border-0 bg-light rounded">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-light text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#otpFormCollapse">
                                    <i class="fa fa-plus-circle ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> {{ $otpContractUsage ? 'تغییر یا بارگذاری مجدد قرارداد OTP' : 'تنظیم و ایجاد قرارداد OTP' }}
                                </button>
                            </h2>
                            <div id="otpFormCollapse" class="accordion-collapse collapse" data-bs-parent="#otpAccordion">
                                <div class="accordion-body bg-white border-top">
                                    <form action="{{ route('admin.sms.settings.contracts.store') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="service_type" value="otp">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">نحوه انتخاب قرارداد</label>
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="mode" id="otpModeNew" value="new" checked>
                                                    <label class="form-check-label small" for="otpModeNew">ایجاد قرارداد جدید</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="mode" id="otpModeExist" value="existing">
                                                    <label class="form-check-label small" for="otpModeExist">انتخاب از بانک قراردادها</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="otpExistingGroup" style="display: none;" class="mb-3">
                                            <label class="form-label fw-bold small">انتخاب از بانک قراردادها</label>
                                            <select name="contract_id" class="form-select">
                                                <option value="">— انتخاب کنید —</option>
                                                @foreach($bankContracts as $bc)
                                                    <option value="{{ $bc->id }}">{{ $bc->title }} (شناسه {{ $bc->id }})</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="otpNewGroup">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">عنوان قرارداد <span class="text-danger">*</span></label>
                                                <input type="text" name="title" class="form-control" value="تعهدنامه و قرارداد عمومی پیامک خدماتی OTP" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">پوشه قرارداد</label>
                                                <select name="contract_folder_id" class="form-select">
                                                    @foreach($folders as $fid => $ftitle)
                                                        <option value="{{ $fid }}">{{ $ftitle }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">متن قرارداد / تعهدنامه</label>
                                                <textarea name="content" class="form-control" rows="4" placeholder="متن کامل قرارداد..."></textarea>
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label fw-bold small">شماره قرارداد</label>
                                                <input type="text" name="contract_number" class="form-control" placeholder="مثال: OTP-1405-01">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-bold small">تاریخ قرارداد</label>
                                                <input type="date" name="contract_date" class="form-control" value="{{ now()->toDateString() }}">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">فایل ضمیمه قرارداد (PDF یا Word یا تصویر)</label>
                                            <input type="file" name="file" class="form-control">
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                                <i class="fa fa-check ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ثبت و فعال‌سازی قرارداد OTP
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-light border small text-muted mt-3 mb-0 d-flex align-items-center gap-2" style="background: #f8fafc;">
                        <i class="fa fa-lock text-secondary fs-5 ms-1" style="margin-left: 8px !important; margin-right: 0 !important;"></i>
                        <span>🔒 تنظیم، تغییر و فعال‌سازی تعهدنامه‌های سراسری منحصراً توسط مدیریت ارشد سامانه انجام می‌شود.</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 2: Advertising Contracts (Per Business) -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="fw-bold mb-0">
                        <i class="fa fa-bullhorn text-success ms-2" style="margin-left: 8px !important; margin-right: 0 !important;"></i> قراردادهای پیامک تبلیغاتی کسب‌وکارها
                    </h5>
                    <span class="badge bg-success">تفکیک هر کسب‌وکار</span>
                </div>
                <div class="card-body">
                    @if($business)
                        <!-- Single Business View -->
                        <div class="mb-3">
                            <h6 class="fw-bold">وضعیت قرارداد کسب‌وکار: {{ $business->title }}</h6>
                            @if($businessAdvUsage && $businessAdvUsage->contract)
                                @php $bContract = $businessAdvUsage->contract; @endphp
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold text-success mb-0">{{ $bContract->title }}</h6>
                                        <span class="badge bg-success">تایید و فعال</span>
                                    </div>
                                    <div class="small text-muted mb-2">
                                        <div>شماره قرارداد: <strong>{{ $businessAdvUsage->contract_number ?: 'ثبت نشده' }}</strong></div>
                                        <div>تاریخ: <strong>{{ $businessAdvUsage->contract_date ? verta($businessAdvUsage->contract_date)->format('Y/m/d') : '—' }}</strong></div>
                                    </div>
                                    @if($bContract->hasOriginalFile())
                                        <div class="mt-2">
                                            <a href="{{ $bContract->file }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="fa fa-download me-1"></i> دانلود فایل قرارداد
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-warning py-3">
                                    برای این کسب‌وکار هنوز قرارداد تبلیغاتی ثبت نشده است.
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- SuperAdmin Multi-Business View -->
                        <p class="small text-muted mb-3">
                            ارسال پیامک‌های تبلیغاتی توسط کسب‌وکارها منوط به عقد قرارداد و بارگذاری مدارک مربوطه است.
                        </p>

                        @if($advertisingContracts)
                            <div class="table-responsive mb-3" style="max-height: 340px; overflow-y: auto;">
                                <table class="table table-hover align-middle table-sm small mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>کسب‌وکار</th>
                                            <th>تعهدنامه پیامک</th>
                                            <th>قرارداد تبلیغاتی</th>
                                            <th>شماره قرارداد</th>
                                            <th class="text-end">عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($advertisingContracts as $biz)
                                            <tr>
                                                <td class="fw-bold">{{ $biz->title }}</td>
                                                <td>
                                                    @if($biz->sms_contract_accepted)
                                                        <span class="badge bg-success" title="{{ $biz->sms_contract_signature_name }}">
                                                            <i class="fa fa-check ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> امضا شده
                                                        </span>
                                                        <div class="small text-muted" style="font-size: 0.75rem;">{{ $biz->sms_contract_signature_name }}</div>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fa fa-clock-o ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> در انتظار امضا
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($biz->advertising_contract_usage && $biz->advertising_contract_usage->contract)
                                                        <span class="badge bg-success">دارد</span>
                                                    @else
                                                        <span class="badge bg-secondary">ندارد</span>
                                                    @endif
                                                </td>
                                                <td class="font-monospace">
                                                    {{ $biz->advertising_contract_usage?->contract_number ?: '—' }}
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 open-adv-modal" 
                                                            data-business-id="{{ $biz->id }}" 
                                                            data-business-title="{{ $biz->title }}">
                                                        {{ $biz->advertising_contract_usage ? 'تغییر' : 'ثبت قرارداد' }}
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @endif

                    <!-- Advertising Contract Actions -->
                    @if(auth()->user()?->isSuperAdmin())
                        @if($business || !$advertisingContracts)
                            <div class="p-3 bg-light rounded text-center">
                                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#advContractModal">
                                    <i class="fa fa-plus ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ثبت قرارداد تبلیغاتی برای این کسب‌وکار
                                </button>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-light border small text-muted mt-3 mb-0 d-flex align-items-center gap-2" style="background: #f8fafc;">
                            <i class="fa fa-lock text-secondary fs-5 ms-1" style="margin-left: 8px !important; margin-right: 0 !important;"></i>
                            <span>🔒 ثبت، تنظیم و تایید قرارداد پیامک تبلیغاتی هر کسب‌وکار منحصراً توسط مدیریت ارشد سامانه انجام می‌شود.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()?->isSuperAdmin())
<!-- Modal for assigning Advertising Contract (SuperAdmin Only) -->
<div class="modal fade" id="advContractModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.sms.settings.contracts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="service_type" value="advertising">
                <input type="hidden" name="business_id" id="modalBusinessId" value="{{ $business?->id }}">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="advModalTitle">
                        ثبت قرارداد پیامک تبلیغاتی
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">نحوه انتخاب قرارداد</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="advModeNew" value="new" checked>
                                <label class="form-check-label small" for="advModeNew">ایجاد قرارداد جدید</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="advModeExist" value="existing">
                                <label class="form-check-label small" for="advModeExist">انتخاب از بانک قراردادها</label>
                            </div>
                        </div>
                    </div>

                    <div id="advExistingGroup" style="display: none;" class="mb-3">
                        <label class="form-label fw-bold small">انتخاب از بانک قراردادها</label>
                        <select name="contract_id" class="form-select">
                            <option value="">— انتخاب کنید —</option>
                            @foreach($bankContracts as $bc)
                                <option value="{{ $bc->id }}">{{ $bc->title }} (شناسه {{ $bc->id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="advNewGroup">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">عنوان قرارداد <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="تعهدنامه ارسال پیامک تبلیغاتی" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">پوشه قرارداد</label>
                            <select name="contract_folder_id" class="form-select">
                                @foreach($folders as $fid => $ftitle)
                                    <option value="{{ $fid }}">{{ $ftitle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">متن قرارداد / تعهدنامه</label>
                            <textarea name="content" class="form-control" rows="4" placeholder="متن کامل تعهدنامه تبلیغاتی..."></textarea>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">شماره قرارداد</label>
                            <input type="text" name="contract_number" class="form-control" placeholder="مثال: ADV-1405-01">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">تاریخ قرارداد</label>
                            <input type="date" name="contract_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">فایل ضمیمه قرارداد (PDF، تصویر یا سند اسکن شده)</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ثبت و ذخیره قرارداد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mode toggle for OTP
        const otpModeNew = document.getElementById('otpModeNew');
        const otpModeExist = document.getElementById('otpModeExist');
        const otpNewGroup = document.getElementById('otpNewGroup');
        const otpExistingGroup = document.getElementById('otpExistingGroup');

        if (otpModeNew && otpModeExist) {
            otpModeNew.addEventListener('change', () => {
                otpNewGroup.style.display = 'block';
                otpExistingGroup.style.display = 'none';
            });
            otpModeExist.addEventListener('change', () => {
                otpNewGroup.style.display = 'none';
                otpExistingGroup.style.display = 'block';
            });
        }

        // Mode toggle for Advertising
        const advModeNew = document.getElementById('advModeNew');
        const advModeExist = document.getElementById('advModeExist');
        const advNewGroup = document.getElementById('advNewGroup');
        const advExistingGroup = document.getElementById('advExistingGroup');

        if (advModeNew && advModeExist) {
            advModeNew.addEventListener('change', () => {
                advNewGroup.style.display = 'block';
                advExistingGroup.style.display = 'none';
            });
            advModeExist.addEventListener('change', () => {
                advNewGroup.style.display = 'none';
                advExistingGroup.style.display = 'block';
            });
        }

        // Open modal for specific business
        const openModalButtons = document.querySelectorAll('.open-adv-modal');
        const modalBusinessId = document.getElementById('modalBusinessId');
        const advModalTitle = document.getElementById('advModalTitle');
        const advModalEl = document.getElementById('advContractModal');
        const advModal = advModalEl ? new bootstrap.Modal(advModalEl) : null;

        openModalButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                const bId = this.getAttribute('data-business-id');
                const bTitle = this.getAttribute('data-business-title');
                if (modalBusinessId) modalBusinessId.value = bId;
                if (advModalTitle) advModalTitle.textContent = 'ثبت قرارداد پیامک تبلیغاتی: ' + bTitle;
                if (advModal) advModal.show();
            });
        });
    });
</script>
@endif
@endsection
