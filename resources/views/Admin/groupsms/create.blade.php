@extends('Admin.master')

@section('title', 'ارسال پیامک انبوه و کمپین‌ها')

@section('css')
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
        margin-bottom: 10px;
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
<div class="row mb-4">
    <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="fa fa-paper-plane text-primary me-2"></i> ارسال پیامک انبوه و مدیریت کمپین‌ها
            </h4>
            <p class="text-muted mb-0 small">
                کسب‌وکار جاری: <strong class="text-dark">{{ $business?->title ?? 'همه کسب‌وکارها' }}</strong>
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $currentTier = $userTier ?? (auth()->user()?->isSuperAdmin() ? 'titanium' : (auth()->user()?->access_tier ?: 'silver'));
                $tierClass = match($currentTier) {
                    'titanium' => 'tier-titanium',
                    'diamond' => 'tier-diamond',
                    'gold' => 'tier-gold',
                    default => 'tier-silver',
                };
                $tierLabel = match($currentTier) {
                    'titanium' => 'تیتانیومی (Titanium)',
                    'diamond' => 'الماسی (Diamond)',
                    'gold' => 'طلایی (Gold)',
                    default => 'نقره‌ای (Silver)',
                };
            @endphp
            <div class="tier-badge {{ $tierClass }}" title="سطح دسترسی شما به فیلترها و مخاطبان">
                <i class="fa fa-shield"></i> سطح دسترسی: {{ $tierLabel }}
            </div>
            <a href="{{ route('admin.sms.bulk.campaigns') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-list me-1"></i> تاریخچه کمپین‌ها
            </a>
            <a href="{{ route('admin.sms.single.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fa fa-envelope me-1"></i> ارسال تکی
            </a>
        </div>
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
    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <!-- 1. Recipient Type & Target Audience -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <span class="badge bg-primary me-2">۱</span> انتخاب نوع مخاطبان و فیلترهای هدفمند
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Recipient Type Toggle -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">نوع مخاطبان:</label>
                        <div class="d-flex gap-3">
                            <div class="form-check form-check-inline border rounded p-2 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="recipient_type" id="type_normal" value="normal" checked>
                                <label class="form-check-label fw-semibold" for="type_normal">
                                    <i class="fa fa-users text-primary me-1"></i> مخاطبان عادی / مشتریان
                                    <div class="text-muted small fw-normal">مشتریان ثبت‌شده و مرتبط با کسب‌وکار (کلیه سطوح)</div>
                                </label>
                            </div>
                            <div class="form-check form-check-inline border rounded p-2 px-3 flex-fill">
                                <input class="form-check-input" type="radio" name="recipient_type" id="type_advertising" value="advertising" {{ in_array($userTier, ['diamond', 'titanium']) ? '' : 'disabled' }}>
                                <label class="form-check-label fw-semibold {{ in_array($userTier, ['diamond', 'titanium']) ? '' : 'text-muted' }}" for="type_advertising">
                                    <i class="fa fa-bullhorn text-warning me-1"></i> مخاطبان تبلیغاتی / انبوه
                                    <div class="text-muted small fw-normal">بانک شماره‌های تبلیغاتی (نیازمند سطح الماس یا تیتانیوم)</div>
                                </label>
                            </div>
                        </div>
                        @if(!in_array($userTier, ['diamond', 'titanium']))
                            <div class="alert alert-warning py-1 px-2 mt-2 small mb-0">
                                <i class="fa fa-lock me-1"></i> سطح دسترسی شما <strong>{{ $tierLabel }}</strong> است. دسترسی به ارسال پیامک انبوه تبلیغاتی نیازمند سطح <strong>الماس (Diamond)</strong> یا <strong>تیتانیوم (Titanium)</strong> می‌باشد.
                            </div>
                        @endif
                    </div>

                    <!-- Multi-field Filters Grid -->
                    <div class="p-3 bg-light rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-dark small">
                                <i class="fa fa-filter text-secondary me-1"></i> فیلترهای مخاطبان:
                            </span>
                            <span class="badge bg-secondary small" id="tierGateHint">سطح مورد نیاز: نقره‌ای</span>
                        </div>

                        <div class="row g-3">
                            <!-- Role Filter (Gold+) -->
                            <div class="col-md-4">
                                <label class="form-label small">نقش سازمانی:</label>
                                <select name="filters[role]" class="form-select form-select-sm" id="filter_role" {{ in_array($userTier, ['gold', 'diamond', 'titanium']) ? '' : 'disabled' }}>
                                    <option value="all">همه نقش‌ها</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->display_name ?? $role->name }}</option>
                                    @endforeach
                                </select>
                                @if(!in_array($userTier, ['gold', 'diamond', 'titanium']))
                                    <small class="text-muted d-block mt-1"><i class="fa fa-lock"></i> نیازمند سطح طلایی</small>
                                @endif
                            </div>

                            <!-- Business Filter (Titanium for cross-business) -->
                            <div class="col-md-4">
                                <label class="form-label small">کسب‌وکار (غرفه):</label>
                                <select name="filters[business]" class="form-select form-select-sm" id="filter_business">
                                    @if($userTier === 'titanium')
                                        <option value="all">کل کسب‌وکارها (همه)</option>
                                    @endif
                                    @if($business)
                                        <option value="{{ $business->id }}" selected>{{ $business->title }} (کسب‌وکار شما)</option>
                                    @endif
                                    @if($userTier === 'titanium')
                                        @foreach($businesses as $b)
                                            @if(!$business || $b->id != $business->id)
                                                <option value="{{ $b->id }}">{{ $b->title }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                                @if($userTier !== 'titanium')
                                    <small class="text-muted d-block mt-1">محدود به کسب‌وکار جاری (تیتانیوم برای همه)</small>
                                @endif
                            </div>

                            <!-- Gender Filter (Silver+) -->
                            <div class="col-md-4">
                                <label class="form-label small">جنسیت:</label>
                                <select name="filters[gender]" class="form-select form-select-sm" id="filter_gender">
                                    <option value="all">همه (زن و مرد)</option>
                                    <option value="0">آقایان</option>
                                    <option value="1">بانوان</option>
                                </select>
                            </div>

                            <!-- Date Range (Silver+) -->
                            <div class="col-md-6">
                                <label class="form-label small">از تاریخ عضویت:</label>
                                <input type="date" name="filters[start_date]" class="form-control form-control-sm" id="filter_start_date">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">تا تاریخ عضویت:</label>
                                <input type="date" name="filters[end_date]" class="form-control form-control-sm" id="filter_end_date">
                            </div>

                            <!-- Birth Date / Year (Gold+) -->
                            <div class="col-md-4">
                                <label class="form-label small">سال تولد:</label>
                                <input type="number" name="filters[birth_year]" placeholder="مثال: 1370" class="form-control form-control-sm" id="filter_birth_year" {{ in_array($userTier, ['gold', 'diamond', 'titanium']) ? '' : 'disabled' }}>
                                @if(!in_array($userTier, ['gold', 'diamond', 'titanium']))
                                    <small class="text-muted d-block mt-1"><i class="fa fa-lock"></i> نیازمند سطح طلایی</small>
                                @endif
                            </div>

                            <!-- Service Category (Diamond+) -->
                            <div class="col-md-4">
                                <label class="form-label small">دسته‌بندی خدمات:</label>
                                <select name="filters[service_category_id]" class="form-select form-select-sm" id="filter_service_cat" {{ in_array($userTier, ['diamond', 'titanium']) ? '' : 'disabled' }}>
                                    <option value="all">همه دسته‌ها</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->title }}</option>
                                    @endforeach
                                </select>
                                @if(!in_array($userTier, ['diamond', 'titanium']))
                                    <small class="text-muted d-block mt-1"><i class="fa fa-lock"></i> نیازمند سطح الماس</small>
                                @endif
                            </div>

                            <!-- Province (Silver+) -->
                            <div class="col-md-4">
                                <label class="form-label small">استان:</label>
                                <select name="filters[province_id]" class="form-select form-select-sm" id="filter_province">
                                    <option value="all">همه استان‌ها</option>
                                    @foreach($provinces as $prov)
                                        <option value="{{ $prov->id }}">{{ $prov->name ?? $prov->title }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Manual / Custom Numbers Addition -->
                            <div class="col-12">
                                <label class="form-label small">شماره‌های همراه اضافی (اختیاری - با خط جدید یا کاما جدا کنید):</label>
                                <textarea name="custom_numbers" id="custom_numbers" rows="2" class="form-control form-control-sm font-monospace" placeholder="09120000000, 09350000000 ..."></textarea>
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
                                <div class="col-3">
                                    <div class="stat-card stat-card-total p-2">
                                        <div class="text-muted small">کل شماره‌ها</div>
                                        <div class="stat-value text-secondary" id="stat_total">۰</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-card stat-card-valid p-2">
                                        <div class="stat-label">شماره‌های معتبر نهایی</div>
                                        <div class="stat-value" id="stat_valid">۰</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-card stat-card-invalid p-2">
                                        <div class="stat-label">شماره‌های نامعتبر</div>
                                        <div class="stat-value" id="stat_invalid">۰</div>
                                    </div>
                                </div>
                                <div class="col-3">
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

            <!-- 2. Message Composition & Template -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <span class="badge bg-primary me-2">۲</span> تنظیم متن پیامک و قالب
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Message Category / Folders & Type (Classified / Regular) --}}
                    <div class="row mb-3 p-3 bg-light rounded border g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="fa fa-folder text-primary ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> دسته‌بندی موضوعی / پوشه پیام:
                            </label>
                            <select name="sms_message_group_id" id="messageGroupSelect" class="form-select form-select-sm">
                                <option value="" data-slug="" data-confidential="0">-- بدون دسته‌بندی موضوعی (عمومی) --</option>
                                @if(isset($groups))
                                    @foreach($groups as $grp)
                                        <optgroup label="📁 {{ $grp->name }}">
                                            <option value="{{ $grp->id }}" data-slug="{{ $grp->slug }}" data-confidential="{{ $grp->isConfidential() ? '1' : '0' }}" {{ old('sms_message_group_id') == $grp->id ? 'selected' : '' }}>
                                                {{ $grp->name }} (دسته اصلی)
                                            </option>
                                            @foreach($grp->subgroups as $sub)
                                                <option value="{{ $sub->id }}" data-slug="{{ $sub->slug }}" data-confidential="{{ ($sub->isConfidential() || $grp->isConfidential()) ? '1' : '0' }}" {{ old('sms_message_group_id') == $sub->id ? 'selected' : '' }}>
                                                    {{ $grp->name }} › {{ $sub->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text small text-muted">
                                سازمان‌دهی پیام در پوشه‌های موضوعی (اجرایی، پشتیبانی، حقوقی، مالی، فروش و زیرگروه‌ها)
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="fa fa-shield text-info ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> نوع پیام (سطح طبقه‌بندی):
                            </label>
                            <div class="d-flex gap-2 p-1 border rounded bg-white mt-1">
                                <div class="form-check flex-fill text-center p-2 mb-0 border rounded" id="typeRegularWrapper">
                                    <input class="form-check-input" type="radio" name="type" id="typeRegular" value="regular" {{ old('type', 'regular') === 'regular' ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-semibold cursor-pointer" for="typeRegular">
                                        <i class="fa fa-envelope-o text-secondary ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> عادی
                                    </label>
                                </div>
                                <div class="form-check flex-fill text-center p-2 mb-0 border rounded" id="typeClassifiedWrapper">
                                    <input class="form-check-input" type="radio" name="type" id="typeClassified" value="classified" {{ old('type') === 'classified' ? 'checked' : '' }}>
                                    <label class="form-check-label small fw-bold cursor-pointer text-danger" for="typeClassified">
                                        <i class="fa fa-lock text-danger ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> محرمانه
                                    </label>
                                </div>
                            </div>
                            <div class="form-text small mt-1" id="classificationHint">
                                <span class="text-muted"><i class="fa fa-info-circle ms-1" style="margin-left: 4px !important; margin-right: 0 !important;"></i> پیام‌های مالی و حقوقی به‌طور خودکار «محرمانه» تعیین می‌شوند.</span>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">انتخاب از قالب‌های ذخیره‌شده:</label>
                            <select name="sms_message_template_id" id="sms_message_template_id" class="form-select form-select-sm">
                                <option value="">-- بدون قالب (تایپ متن دلخواه) --</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" data-content="{{ $tmpl->content }}" data-group-id="{{ $tmpl->sms_message_group_id }}">
                                        {{ $tmpl->title }} ({{ $tmpl->group?->name ?? 'عمومی' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="small text-muted mb-1">
                                <i class="fa fa-info-circle"></i>
                                عبارت الزامی مخابراتی «لغو ۱۱» همواره و بدون امکان غیرفعال‌سازی به انتهای پیامک افزوده می‌شود.
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">متن پیامک:</label>
                        <textarea name="message" id="sms_message" rows="4" class="form-control" placeholder="متن پیامک تبلیغاتی یا اطلاع‌رسانی خود را بنویسید..."></textarea>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-2 rounded small">
                        <div>
                            کاراکترها: <strong id="char_count" class="text-primary">۰</strong> |
                            تعداد بخش (صفحه): <strong id="page_count" class="text-primary">۱</strong> |
                            نوع رمزگذاری: <span id="encoding_type" class="badge bg-secondary">Unicode (فارسی)</span>
                        </div>
                        <div>
                            تعرفه هر صفحه: <strong class="text-dark">۱,۲۰۰ ریال</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Advertising Sender Number & Cost Estimate -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <span class="badge bg-primary me-2">۳</span> انتخاب شماره فرستنده تبلیغاتی و بررسی مالی
                    </h6>
                </div>
                <div class="card-body">
                    <label class="form-label small fw-bold mb-2">شماره‌های تبلیغاتی تخصیص‌یافته به کسب‌وکار:</label>
                    @if($advertisingNumbers->isEmpty())
                        <div class="alert alert-danger mb-3">
                            <i class="fa fa-exclamation-triangle me-1"></i> هیچ شماره اختصاصی تبلیغاتی به این کسب‌وکار تخصیص داده نشده است. لطفا با پشتیبانی تماس بگیرید.
                        </div>
                    @else
                        <div class="row g-2 mb-3">
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
                                            <span class="badge bg-success small">فعال</span>
                                        </div>
                                        <div class="mt-2 text-muted small d-flex justify-content-between align-items-center">
                                            <span>موجودی شماره:</span>
                                            <div class="d-flex align-items-center gap-2">
                                                <strong class="text-dark" id="sender_balance_{{ $sender->id }}">{{ number_format($sender->current_balance) }} ریال</strong>
                                                <button type="button" class="btn btn-sm btn-outline-warning text-dark py-0 px-2 fw-semibold quick-charge-sender-btn" style="font-size: 11px;" onclick="event.stopPropagation(); openQuickPaymentGate(null, {{ $sender->id }}, '{{ $sender->number }}', {{ $sender->current_balance }});" title="شارژ سریع آنلاین این خط">
                                                    <i class="fa fa-bolt me-1 text-warning"></i> شارژ سریع
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Financial Summary & Balance Check Box -->
                    <div id="financialSummaryCard" class="p-3 rounded border bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-4 border-end">
                                <div class="text-muted small">هزینه هر پیامک:</div>
                                <div class="fw-bold text-dark fs-6" id="cost_per_sms">۰ ریال</div>
                            </div>
                            <div class="col-md-4 border-end">
                                <div class="text-muted small">هزینه تخمینی کل کمپین:</div>
                                <div class="fw-bold text-primary fs-5" id="campaign_total_cost">۰ ریال</div>
                                <div class="text-muted small" style="font-size: 11px;">(مخاطبین معتبر × صفحات × ۱,۲۰۰ ریال)</div>
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
                            <div>
                                <button type="button" class="btn btn-warning text-dark fw-bold btn-sm px-3 shadow-sm text-nowrap" id="btnOpenQuickPaymentFromCallout" onclick="openQuickPaymentGate()">
                                    <i class="fa fa-bolt me-1"></i> اتصال به درگاه پرداخت سریع
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Scheduling & Confirmation -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <span class="badge bg-primary me-2">۴</span> زمان ارسال و تأیید نهایی
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
                                <div class="fw-bold small text-truncate" id="phonePreviewSender">
                                    {{ $advertisingNumbers->first()?->number ?? 'شماره تبلیغاتی' }}
                                </div>
                                <div class="text-muted" style="font-size: 10px;">پیش‌نمایش زنده پیامک</div>
                            </div>
                        </div>
                        <div class="phone-chat-body">
                            <div class="sms-bubble">
                                <div id="phoneBubbleText">متن پیامک شما اینجا نمایش داده خواهد شد...</div>
                                <div class="sms-opt-out" id="phoneBubbleOptOut">لغو 11</div>
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
                        <small class="text-muted">شارژ مستقیم خط و فعال‌سازی فوری کمپین بدون رفرش صفحه</small>
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
                        <small class="text-muted d-block">موجودی فعلی خط:</small>
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
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer active" for="gw_sadad">
                                <input type="radio" name="qp_gateway" id="gw_sadad" value="sadad" class="form-check-input d-none" checked onchange="highlightGateway(this)">
                                <div class="fs-4 text-primary mb-1"><i class="fa fa-university"></i></div>
                                <div class="fw-bold small text-dark">سداد (ملی)</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_mellat">
                                <input type="radio" name="qp_gateway" id="gw_mellat" value="mellat" class="form-check-input d-none" onchange="highlightGateway(this)">
                                <div class="fs-4 text-danger mb-1"><i class="fa fa-credit-card"></i></div>
                                <div class="fw-bold small text-dark">به‌پرداخت ملت</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_sep">
                                <input type="radio" name="qp_gateway" id="gw_sep" value="sep" class="form-check-input d-none" onchange="highlightGateway(this)">
                                <div class="fs-4 text-info mb-1"><i class="fa fa-shield"></i></div>
                                <div class="fw-bold small text-dark">سامان‌کیش (سپ)</div>
                                <small class="text-muted" style="font-size: 10px;">شاپرک آنلاین</small>
                            </label>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="qp-gateway-card p-3 border rounded text-center d-block cursor-pointer" for="gw_sim">
                                <input type="radio" name="qp_gateway" id="gw_sim" value="simulator" class="form-check-input d-none" onchange="highlightGateway(this)">
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
                <button type="button" class="btn btn-success fw-bold px-4" id="btnExecuteQuickPay" onclick="executeQuickPayment()">
                    <i class="fa fa-shield me-1"></i> پرداخت و افزایش آنی موجودی
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/dashboard/js/sms-param-chips.js') }}"></script>
<script>
    // Dynamic parameter tokens inside a selected template render as styled,
    // non-text-editable chips instead of raw "{...}" text.
    const bulkMessageChipEditor = SmsParamChips.attach(document.getElementById('sms_message'), []);

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

    // Auto update message from template selection
    document.getElementById('sms_message_template_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const content = selected.getAttribute('data-content') || '';
        if (content) {
            if (bulkMessageChipEditor) {
                bulkMessageChipEditor.setValue(content);
            } else {
                document.getElementById('sms_message').value = content;
            }
            updatePhoneMockup();
            triggerPreview();
        }

        const groupId = selected.getAttribute('data-group-id');
        if (groupId && messageGroupSelect) {
            messageGroupSelect.value = groupId;
            updateClassificationFromGroup();
        }
    });

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

    // Real-time phone mockup text update
    document.getElementById('sms_message').addEventListener('input', function () {
        updatePhoneMockup();
    });

    function updatePhoneMockup() {
        const text = document.getElementById('sms_message').value;
        document.getElementById('phoneBubbleText').innerText = text.trim() ? text : 'متن پیامک شما اینجا نمایش داده خواهد شد...';
        // "لغو 11" is mandatory and unconditional for advertising messages — always shown.
        document.getElementById('phoneBubbleOptOut').style.display = 'block';
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

                // Update audit stats
                document.getElementById('filterAuditCard').style.display = 'block';
                document.getElementById('stat_total').innerText = Number(rec.total_candidates).toLocaleString('fa-IR');
                document.getElementById('stat_valid').innerText = Number(rec.valid_count).toLocaleString('fa-IR');
                document.getElementById('stat_invalid').innerText = Number(rec.invalid_count).toLocaleString('fa-IR');
                document.getElementById('stat_duplicate').innerText = Number(rec.duplicate_count).toLocaleString('fa-IR');

                if (rec.invalid_count > 0 && rec.invalid_sample.length > 0) {
                    const sampleList = rec.invalid_sample.map(i => `<code>${i.phone}</code>: ${i.reason}`).join(' | ');
                    document.getElementById('invalidNumbersDetails').style.display = 'block';
                    document.getElementById('invalidNumbersDetails').innerHTML = `<strong>نمونه شماره‌های نامعتبر رد شده:</strong> ${sampleList}`;
                } else {
                    document.getElementById('invalidNumbersDetails').style.display = 'none';
                }

                // Update message stats
                document.getElementById('char_count').innerText = msg.character_count;
                document.getElementById('page_count').innerText = msg.page_count;
                document.getElementById('encoding_type').innerText = msg.is_unicode ? 'Unicode (فارسی)' : 'GSM-7 (انگلیسی)';

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

    // Quick Payment Gate Logic
    let lastCalculatedDeficit = 0;
    let currentQuickSenderId = null;
    let currentQuickSenderNumber = '';
    let currentQuickBusinessId = "{{ $business?->id }}";

    function openQuickPaymentGate(suggestedDeficit, senderId, senderNumber, currentBal) {
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
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال اتصال به درگاه شاپرک و شارژ آنی...';

        const trackingId = 'QP-' + selectedGw.toUpperCase() + '-' + Date.now().toString().slice(-8);

        fetch("{{ route('admin.sms.financial.advertising.topup') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                sender_number_id: currentQuickSenderId,
                amount: amount,
                business_id: currentQuickBusinessId,
                tracking_id: trackingId
            })
        })
        .then(r => r.json().then(data => ({ status: r.status, data })))
        .then(({ status, data }) => {
            btn.disabled = false;
            btn.innerHTML = originalText;

            if (status === 200 && data.success) {
                // Close modal
                const modalEl = document.getElementById('quickPaymentModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Update balance on sender card
                const newBal = data.transaction?.balance_after ?? 0;
                const balEl = document.getElementById('sender_balance_' + currentQuickSenderId);
                if (balEl) {
                    balEl.innerText = Number(newBal).toLocaleString('fa-IR') + ' ریال';
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'شارژ آنلاین با موفقیت انجام شد!',
                        html: `موجودی خط <b>${currentQuickSenderNumber}</b> به میزان <b>${Number(amount).toLocaleString('fa-IR')} ریال</b> افزایش یافت.<br><small class="text-muted">شناسه رهگیری بانکی: ${trackingId}</small>`,
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

    // Trigger initial preview on page load
    document.addEventListener('DOMContentLoaded', function () {
        updatePhoneMockup();
    });
</script>
@endsection
