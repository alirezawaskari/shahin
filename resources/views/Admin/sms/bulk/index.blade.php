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
        padding: 16px;
        transition: all 0.2s;
    }
    .stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .stat-value {
        font-size: 22px;
        font-weight: 700;
        margin-top: 6px;
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
        position: sticky;
        top: 20px;
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
                $tierClass = match($userTier) {
                    'titanium' => 'tier-titanium',
                    'diamond' => 'tier-diamond',
                    'gold' => 'tier-gold',
                    default => 'tier-silver',
                };
                $tierLabel = match($userTier) {
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
                                    <div class="stat-card p-2 bg-light">
                                        <div class="text-muted small">کل شماره‌ها</div>
                                        <div class="stat-value text-secondary" id="stat_total">۰</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-card p-2 bg-success bg-opacity-10 border-success">
                                        <div class="text-success small fw-bold">شماره‌های معتبر نهایی</div>
                                        <div class="stat-value text-success" id="stat_valid">۰</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-card p-2 bg-danger bg-opacity-10 border-danger">
                                        <div class="text-danger small">شماره‌های نامعتبر</div>
                                        <div class="stat-value text-danger" id="stat_invalid">۰</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="stat-card p-2 bg-warning bg-opacity-10 border-warning">
                                        <div class="text-warning small">تکراری (حذف‌شده)</div>
                                        <div class="stat-value text-warning" id="stat_duplicate">۰</div>
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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">انتخاب از قالب‌های ذخیره‌شده:</label>
                            <select name="sms_message_template_id" id="sms_message_template_id" class="form-select form-select-sm">
                                <option value="">-- بدون قالب (تایپ متن دلخواه) --</option>
                                @foreach($templates as $tmpl)
                                    <option value="{{ $tmpl->id }}" data-content="{{ $tmpl->content }}">
                                        {{ $tmpl->title }} ({{ $tmpl->group?->name ?? 'عمومی' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" name="auto_append_cancel" id="auto_append_cancel" value="1" checked>
                                <label class="form-check-label small" for="auto_append_cancel">
                                    درج خودکار عبارت الزامی مخابراتی «لغو ۱۱» در انتهای پیامک
                                </label>
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
                                        <div class="mt-2 text-muted small d-flex justify-content-between">
                                            <span>موجودی شماره:</span>
                                            <strong class="text-dark">{{ number_format($sender->current_balance) }} ریال</strong>
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
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
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
            document.getElementById('sms_message').value = content;
            updatePhoneMockup();
            triggerPreview();
        }
    });

    // Real-time phone mockup text update
    document.getElementById('sms_message').addEventListener('input', function () {
        updatePhoneMockup();
    });

    document.getElementById('auto_append_cancel').addEventListener('change', function () {
        updatePhoneMockup();
        triggerPreview();
    });

    function updatePhoneMockup() {
        const text = document.getElementById('sms_message').value;
        const autoCancel = document.getElementById('auto_append_cancel').checked;
        document.getElementById('phoneBubbleText').innerText = text.trim() ? text : 'متن پیامک شما اینجا نمایش داده خواهد شد...';
        document.getElementById('phoneBubbleOptOut').style.display = autoCancel ? 'block' : 'none';
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
                if (fin.has_sufficient_balance) {
                    statusBadge.innerHTML = `<span class="badge bg-success p-2 d-block"><i class="fa fa-check-circle me-1"></i> موجودی کافی است (باقی‌مانده: ${Number(fin.balance_after).toLocaleString('fa-IR')} ریال)</span>`;
                    document.getElementById('btnLaunchCampaign').disabled = false;
                } else {
                    statusBadge.innerHTML = `<span class="badge bg-danger p-2 d-block"><i class="fa fa-times-circle me-1"></i> موجودی ناکافی است (کسری: ${Number(fin.balance_deficit).toLocaleString('fa-IR')} ریال)</span>`;
                    document.getElementById('btnLaunchCampaign').disabled = true;
                }
            }
        })
        .catch(error => {
            console.error('Preview error:', error);
        });
    }

    // Trigger initial preview on page load
    document.addEventListener('DOMContentLoaded', function () {
        updatePhoneMockup();
    });
</script>
@endsection
