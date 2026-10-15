@extends('Admin.master')

@section('title', 'دستیار هوشمند کمپین‌های پیامکی (AI Campaign Studio)')

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

    .step-card {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 6px rgba(0,0,0,0.03);
        margin-bottom: 24px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .step-card.locked {
        opacity: 0.55;
        pointer-events: none;
        background: #f8fafc;
    }
    .step-header {
        padding: 16px 20px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .step-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #fff;
        background: #4f46e5;
        margin-left: 10px;
    }
    .step-badge.approved {
        background: #10b981;
    }

    .ai-bubble {
        background: #f5f3ff;
        border: 1px solid #ddd6fe;
        border-radius: 12px;
        padding: 16px;
        position: relative;
    }
    .ai-bubble::before {
        content: "";
        position: absolute;
        top: -8px;
        right: 24px;
        width: 14px;
        height: 14px;
        background: #f5f3ff;
        border-top: 1px solid #ddd6fe;
        border-right: 1px solid #ddd6fe;
        transform: rotate(-45deg);
    }

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
        height: 560px;
        background: #1e1e2d;
        border-radius: 38px;
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25), inset 0 0 0 3px #38384f;
        padding: 12px;
        position: relative;
        display: flex;
        flex-direction: column;
    }
    .phone-screen {
        background: #f8fafc;
        border-radius: 28px;
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .phone-notch {
        width: 110px;
        height: 14px;
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

    .stat-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        text-align: center;
    }
</style>
@endsection

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa fa-android text-primary me-2"></i> دستیار هوشمند کمپین‌های پیامکی (AI Studio)
        </h3>
        <p class="text-muted mb-0 small">
            کسب‌وکار جاری: <strong class="text-dark">{{ $business?->title ?? 'سیستم' }}</strong> |
            مدل هوش مصنوعی: <code class="text-indigo">{{ $aiModel }}</code>
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
            <form method="GET" action="{{ route('admin.sms.ai.index') }}" class="d-flex flex-wrap gap-2 m-0">
                <select name="business_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($all_businesses as $b)
                        <option value="{{ $b->id }}" {{ $business->id === $b->id ? 'selected' : '' }}>
                            {{ $b->title }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
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
        <div class="tier-badge {{ $tierClass }}">
            <i class="fa fa-shield"></i> سطح دسترسی: {{ $tierLabel }}
        </div>
        <a href="{{ route('admin.sms.ai.campaigns', ['business_id' => $business?->id]) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-history me-1"></i> تاریخچه کمپین‌های AI
        </a>
        <a href="{{ route('admin.sms.bulk.index', ['business_id' => $business?->id]) }}" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-bullhorn me-1"></i> ارسال انبوه دستی
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

<div class="row">
    <!-- Left Column: Step-by-Step AI Campaign Flow -->
    <div class="col-lg-8">

        <!-- STEP 1 & 2: Goal Input & AI Audience Proposal -->
        <div class="step-card" id="step1Card">
            <div class="step-header">
                <div class="d-flex align-items-center">
                    <span class="step-badge" id="step1Badge">۱</span>
                    <h6 class="fw-bold mb-0">گام اول: هدف کمپین را به زبان طبیعی وارد کنید</h6>
                </div>
                <span class="badge bg-light text-dark border">ورودی طبیعی</span>
            </div>
            <div class="p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small text-muted">هدف بازاریابی یا اطلاع‌رسانی شما چیست؟</label>
                    <textarea id="campaignGoalInput" class="form-control" rows="3"
                        placeholder="مثال: می‌خواهم به مناسبت روز مادر به همه خانم‌های تهرانی پیامک تخفیف ۳۰ درصدی برای خدمات ویژه ارسال کنم.">تخفیف ویژه جشنواره پاییزی برای بانوان در شهر تهران</textarea>
                    <div class="form-text small">
                        هوش مصنوعی هدف شما را تحلیل کرده و مخاطبان منطبق در دیتابیس را بر اساس سن، جنسیت، شهر، و نقش استخراج می‌کند.
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">نوع جامعه مخاطب:</label>
                        <select id="recipientTypeSelect" class="form-select form-select-sm">
                            <option value="normal">مشتریان و اعضای کسب‌وکار (عادی)</option>
                            <option value="advertising">بانک شماره‌های تبلیغاتی (نیازمند الماس)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">شماره فرستنده تبلیغاتی:</label>
                        <select id="senderNumberSelect" class="form-select form-select-sm">
                            @forelse($advertisingNumbers as $snd)
                                <option value="{{ $snd->id }}" data-balance="{{ $snd->current_balance }}">
                                    {{ $snd->number }}
                                </option>
                            @empty
                                <option value="">هیچ شماره فرستنده‌ای یافت نشد</option>
                            @endforelse
                        </select>
                    </div>
                </div>

                <button type="button" id="btnProposeAudience" class="btn btn-primary btn-sm">
                    <i class="fa fa-magic me-1"></i> تحلیل هدف و پیشنهاد مخاطبان توسط هوش مصنوعی
                </button>

                <!-- Step 2 AI Proposal Display Box -->
                <div id="aiAudienceProposalBox" class="mt-4" style="display: none;">
                    <div class="ai-bubble">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa fa-magic text-primary"></i>
                            <strong class="text-primary small">پیشنهاد هوش مصنوعی برای جامعه هدف:</strong>
                        </div>
                        <p id="aiAudienceRationale" class="mb-2 text-dark small"></p>

                        <div class="bg-white rounded p-3 border mb-3">
                            <div class="row g-2 text-center">
                                <div class="col-md-4">
                                    <div class="stat-pill">
                                        <div class="text-muted small">تعداد مخاطبان واجد شرایط</div>
                                        <div id="statRecipientCount" class="fs-5 fw-bold text-primary">۰</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-pill">
                                        <div class="text-muted small">سطح دسترسی مورد نیاز</div>
                                        <div id="statRequiredTier" class="fs-6 fw-bold text-dark">-</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-pill">
                                        <div class="text-muted small">وضعیت مجوز دسترسی</div>
                                        <div id="statTierStatus" class="fs-6 fw-bold text-success">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-muted small" id="activeFilterTags"></div>
                        </div>

                        <!-- STEP 3: Explicit Human Approval Gate -->
                        <div class="border-top pt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div class="text-muted small">
                                <i class="fa fa-exclamation-circle text-warning me-1"></i>
                                <strong>تایید انسانی الزامی:</strong> قبل از تدوین متن پیامک، باید جامعه مخاطبان را رسماً تایید نمایید.
                            </div>
                            <button type="button" id="btnApproveAudience" class="btn btn-success btn-sm">
                                <i class="fa fa-check-circle me-1"></i> تایید جامعه مخاطبان پیشنهادی و ادامه به گام بعد
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4 & 5: Message Drafting & Editing -->
        <div class="step-card locked" id="step2Card">
            <div class="step-header">
                <div class="d-flex align-items-center">
                    <span class="step-badge" id="step2Badge">۲</span>
                    <h6 class="fw-bold mb-0">گام دوم: تدوین متن پیامک با هوش مصنوعی و ویرایش کاربر</h6>
                </div>
                <span class="badge bg-light text-dark border">سایزبندی متناسب با بودجه</span>
            </div>
            <div class="p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">سقف بودجه صفحات پیامک (Page Budget):</label>
                        <select id="pageBudgetSelect" class="form-select form-select-sm">
                            <option value="1" selected>۱ صفحه (حداکثر ۷۰ کاراکتر فارسی - اقتصادی‌ترین حالت)</option>
                            <option value="2">۲ صفحه (حداکثر ۱۳۴ کاراکتر فارسی - متعادل)</option>
                            <option value="3">۳ صفحه (حداکثر ۲۰۱ کاراکتر فارسی - تشریحی)</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="button" id="btnDraftMessage" class="btn btn-primary btn-sm w-100">
                            <i class="fa fa-pencil me-1"></i> نگارش متن بهینه با هوش مصنوعی
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-bold text-muted mb-0">متن پیامک (قابل ویرایش توسط کاربر):</label>
                        <span class="small text-muted">
                            کاراکتر: <strong id="charCounter">۰</strong> |
                            صفحات: <strong id="pageCounter">۱</strong> |
                            نوع: <strong id="encodingLabel">Unicode</strong>
                        </span>
                    </div>
                    <textarea id="messageTextInput" class="form-control" rows="4"
                        placeholder="متن نگارش شده توسط هوش مصنوعی اینجا قرار می‌گیرد و شما می‌توانید آن را ویرایش نمایید..."></textarea>
                    <div class="small text-muted mt-2">
                        <i class="fa fa-info-circle"></i>
                        عبارت قانونی «لغو ۱۱» همواره و بدون امکان غیرفعال‌سازی به انتهای پیامک تبلیغاتی افزوده می‌شود.
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" id="btnProceedToCost" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-calculator me-1"></i> محاسبه نهایی هزینه و رفتن به گام تایید
                    </button>
                </div>
            </div>
        </div>

        <!-- STEP 6, 7 & 8: Final Cost, Human Confirmation Gate & Launch -->
        <div class="step-card locked" id="step3Card">
            <div class="step-header">
                <div class="d-flex align-items-center">
                    <span class="step-badge" id="step3Badge">۳</span>
                    <h6 class="fw-bold mb-0">گام سوم: محاسبه نهایی هزینه، تایید انسانی قطعی و ارسال</h6>
                </div>
                <span class="badge bg-light text-dark border">درگاه کنترل انسانی</span>
            </div>
            <div class="p-4">
                <!-- Financial Breakdown -->
                <div class="bg-light rounded p-3 border mb-3">
                    <div class="row g-2 text-center">
                        <div class="col-md-3">
                            <div class="stat-pill">
                                <div class="text-muted small">تعداد مخاطبان</div>
                                <div id="finRecipients" class="fw-bold text-dark">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-pill">
                                <div class="text-muted small">صفحات هر پیامک</div>
                                <div id="finPages" class="fw-bold text-dark">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-pill">
                                <div class="text-muted small">هزینه هر پیامک</div>
                                <div id="finCostPerSms" class="fw-bold text-dark">-</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-pill">
                                <div class="text-muted small">کل هزینه تخمینی</div>
                                <div id="finTotalCost" class="fw-bold text-danger">-</div>
                            </div>
                        </div>
                    </div>

                    <div id="balanceAlertBox" class="mt-3"></div>
                </div>

                <!-- Hard Human Confirmation Gate -->
                <div class="alert alert-warning border-warning d-flex align-items-start gap-2 mb-3">
                    <i class="fa fa-shield text-warning mt-1 fs-5"></i>
                    <div>
                        <strong class="d-block mb-1">گیت کنترل انسانی (Hard Code-Level Human Confirmation Gate):</strong>
                        <p class="mb-0 small text-dark">
                            هوش مصنوعی به تنهایی فاقد هرگونه دسترسی برای صدور دستور ارسال یا کسر اعتبار است.
                            ارسال پیامک‌ها تنها و منحصراً پس از تایید آگاهانه و صریح شما انجام خواهد شد.
                        </p>
                    </div>
                </div>

                <div class="form-check mb-4 p-3 bg-white border rounded">
                    <input class="form-check-input ms-2" type="checkbox" id="humanConfirmationCheckbox">
                    <label class="form-check-label fw-bold text-dark small" for="humanConfirmationCheckbox">
                        اینجانب متن نهایی، فهرست مخاطبان، و برآورد مالی فوق را بررسی نموده و بدین‌وسیله دستور قطعی ثبت و ارسال کمپین هوش مصنوعی را صادر می‌نمایم.
                    </label>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="text-muted small" id="launchStatusText">
                        برای صدور دستور ارسال، ابتدا تایید انسانی را علامت بزنید.
                    </div>
                    <button type="button" id="btnLaunchAiCampaign" class="btn btn-danger" disabled>
                        <i class="fa fa-paper-plane me-1"></i> تایید نهایی و صدور دستور ارسال کمپین
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Column: Interactive Phone Mockup Preview -->
    <div class="col-lg-4">
        <div class="phone-mockup-wrapper">
            <div class="phone-mockup">
                <div class="phone-screen">
                    <div class="phone-notch"></div>
                    <div class="phone-header">
                        <div style="width: 28px; height: 28px; border-radius: 50%; background: #4f46e5; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                            <i class="fa fa-comment"></i>
                        </div>
                        <div style="flex: 1; overflow: hidden;">
                            <div id="phoneSenderNumber" dir="ltr" style="font-size: 12px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                فرستنده پیامک
                            </div>
                            <div style="font-size: 10px; color: #94a3b8;">کمپین هوش مصنوعی</div>
                        </div>
                    </div>
                    <div class="phone-chat-body">
                        <div class="sms-bubble">
                            <span id="phoneBubbleText">متن پیامک پس از نگارش هوش مصنوعی یا ویرایش شما در این قسمت شبیه‌سازی خواهد شد...</span>
                            <div class="sms-opt-out" id="phoneBubbleOptOut">لغو 11</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    const currentBusinessId = {{ $business?->id ? (int)$business->id : 'null' }};

    // State management
    const state = {
        goal: '',
        proposedFilters: {},
        rationale: '',
        recipientCount: 0,
        approvedAudience: null,
        draftText: '',
        pageBudget: 1,
        finalCost: 0,
        hasSufficientBalance: false
    };

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    // Live Phone Mockup update
    function updatePhoneMockup() {
        const text = document.getElementById('messageTextInput').value;
        const senderSelect = document.getElementById('senderNumberSelect');
        const selectedOption = senderSelect.options[senderSelect.selectedIndex];

        document.getElementById('phoneSenderNumber').innerText = selectedOption ? selectedOption.text.split(' ')[0] : 'فرستنده پیامک';
        document.getElementById('phoneBubbleText').innerText = text.trim() ? text : 'متن پیامک پس از نگارش هوش مصنوعی یا ویرایش شما در این قسمت شبیه‌سازی خواهد شد...';
        // "لغو 11" is mandatory and unconditional for advertising messages — always shown and counted.
        document.getElementById('phoneBubbleOptOut').style.display = 'block';

        // Calculate live characters and pages accurately
        const typedLen = [...text].length;
        const totalLen = typedLen > 0 ? (typedLen + 7) : 0; // +7 for '\nلغو 11'
        document.getElementById('charCounter').innerText = typedLen;
        let pages = 1;
        const countForPages = totalLen > 0 ? totalLen : 1;
        if (countForPages <= 70) pages = 1;
        else if (countForPages <= 134) pages = 2;
        else pages = 2 + Math.ceil((countForPages - 134) / 67);
        document.getElementById('pageCounter').innerText = pages;
    }

    document.getElementById('messageTextInput').addEventListener('input', updatePhoneMockup);
    document.getElementById('senderNumberSelect').addEventListener('change', updatePhoneMockup);

    // STEP 1 & 2: Propose Audience
    document.getElementById('btnProposeAudience').addEventListener('click', function () {
        const goal = document.getElementById('campaignGoalInput').value.trim();
        const recipientType = document.getElementById('recipientTypeSelect').value;

        if (!goal) {
            Swal.fire({ icon: 'warning', title: 'خطا', text: 'لطفاً ابتدا هدف کمپین را وارد کنید.' });
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال تحلیل و استخراج مخاطبان...';

        fetch("{{ route('admin.sms.ai.proposeAudience') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ goal: goal, recipient_type: recipientType, business_id: currentBusinessId })
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-magic me-1"></i> تحلیل هدف و پیشنهاد مخاطبان توسط هوش مصنوعی';

            if (res.status !== 'success') {
                Swal.fire({ icon: 'error', title: 'خطا', text: res.message || 'خطا در تحلیل هوش مصنوعی' });
                return;
            }

            const data = res.data;
            state.goal = data.goal;
            state.proposedFilters = data.filters;
            state.rationale = data.rationale;
            state.recipientCount = data.recipient_count;

            document.getElementById('aiAudienceRationale').innerText = data.rationale;
            document.getElementById('statRecipientCount').innerText = Number(data.recipient_count).toLocaleString('fa-IR');
            document.getElementById('statRequiredTier').innerText = data.required_tier.toUpperCase();

            const tierStatusEl = document.getElementById('statTierStatus');
            if (data.is_tier_allowed) {
                tierStatusEl.innerHTML = '<span class="text-success"><i class="fa fa-check"></i> مجاز</span>';
                document.getElementById('btnApproveAudience').disabled = false;
            } else {
                tierStatusEl.innerHTML = `<span class="text-danger"><i class="fa fa-times"></i> ${data.tier_message}</span>`;
                document.getElementById('btnApproveAudience').disabled = true;
            }

            // Tag list
            let tagsHtml = '<strong>فیلترهای اعمال‌شده:</strong> ';
            const keys = Object.keys(data.filters);
            if (keys.length === 0) {
                tagsHtml += '<span class="badge bg-secondary">همه مخاطبان فعال کسب‌وکار</span>';
            } else {
                tagsHtml += keys.map(k => `<span class="badge bg-info text-dark me-1">${k}: ${data.filters[k]}</span>`).join(' ');
            }
            document.getElementById('activeFilterTags').innerHTML = tagsHtml;

            document.getElementById('aiAudienceProposalBox').style.display = 'block';
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-magic me-1"></i> تحلیل هدف و پیشنهاد مخاطبان توسط هوش مصنوعی';
            console.error(err);
            Swal.fire({ icon: 'error', title: 'خطا در ارتباط با سرور' });
        });
    });

    // STEP 3: Explicit Human Approval Gate
    document.getElementById('btnApproveAudience').addEventListener('click', function () {
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال ثبت تایید...';

        fetch("{{ route('admin.sms.ai.approveAudience') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                goal: state.goal,
                filters: state.proposedFilters,
                rationale: state.rationale,
                recipient_count: state.recipientCount,
                approved: true,
                business_id: currentBusinessId
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                state.approvedAudience = res.approved_audience;

                btn.className = 'btn btn-secondary btn-sm';
                btn.innerHTML = '<i class="fa fa-check-circle me-1"></i> جامعه مخاطبان تایید شد';
                document.getElementById('step1Badge').className = 'step-badge approved';

                // Unlock Step 2
                document.getElementById('step2Card').classList.remove('locked');

                Swal.fire({
                    icon: 'success',
                    title: 'تایید انجام شد',
                    text: 'جامعه مخاطبان تایید گردید. اکنون می‌توانید متن پیامک را تدوین نمایید.',
                    timer: 2000,
                    showConfirmButton: false
                });

                // Auto-trigger message drafting
                document.getElementById('btnDraftMessage').click();
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-check-circle me-1"></i> تایید جامعه مخاطبان پیشنهادی و ادامه به گام بعد';
                Swal.fire({ icon: 'error', title: 'خطا', text: res.message });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check-circle me-1"></i> تایید جامعه مخاطبان پیشنهادی و ادامه به گام بعد';
            console.error(err);
        });
    });

    // STEP 4: Draft Message with AI
    document.getElementById('btnDraftMessage').addEventListener('click', function () {
        if (!state.approvedAudience) {
            Swal.fire({ icon: 'warning', title: 'تایید مخاطبان الزامی است', text: 'ابتدا باید جامعه مخاطبان را تایید کنید.' });
            return;
        }

        const pageBudget = document.getElementById('pageBudgetSelect').value;
        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال نگارش متن متناسب با بودجه...';

        fetch("{{ route('admin.sms.ai.draftMessage') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                goal: state.goal,
                approved_audience: state.approvedAudience,
                page_budget: pageBudget,
                business_id: currentBusinessId
            })
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-pencil me-1"></i> نگارش متن بهینه با هوش مصنوعی';

            if (res.status === 'success') {
                const data = res.data;
                document.getElementById('messageTextInput').value = data.draft_text;
                updatePhoneMockup();
            } else {
                Swal.fire({ icon: 'error', title: 'خطا', text: res.message });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-pencil me-1"></i> نگارش متن بهینه با هوش مصنوعی';
            console.error(err);
        });
    });

    // STEP 6: Final Cost Calculation
    document.getElementById('btnProceedToCost').addEventListener('click', function () {
        calculateFinalCost();
    });

    function calculateFinalCost() {
        const text = document.getElementById('messageTextInput').value.trim();
        if (!text) {
            Swal.fire({ icon: 'warning', title: 'متن خالی است', text: 'لطفاً متن پیامک را وارد نمایید.' });
            return;
        }

        const senderId = document.getElementById('senderNumberSelect').value;
        const recipientType = document.getElementById('recipientTypeSelect').value;

        fetch("{{ route('admin.sms.ai.calculateCost') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                message: text,
                filters: state.approvedAudience ? state.approvedAudience.filters : state.proposedFilters,
                sender_number_id: senderId,
                recipient_type: recipientType,
                business_id: currentBusinessId
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                const rec = res.data.recipient_summary;
                const msg = res.data.message_summary;
                const fin = res.data.financial_summary;

                state.finalCost = fin.estimated_total_cost;
                state.hasSufficientBalance = fin.has_sufficient_balance;

                document.getElementById('finRecipients').innerText = Number(rec.valid_count).toLocaleString('fa-IR');
                document.getElementById('finPages').innerText = msg.page_count;
                document.getElementById('finCostPerSms').innerText = Number(msg.cost_per_message).toLocaleString('fa-IR') + ' ریال';
                document.getElementById('finTotalCost').innerText = Number(fin.estimated_total_cost).toLocaleString('fa-IR') + ' ریال';

                const alertBox = document.getElementById('balanceAlertBox');
                if (fin.has_sufficient_balance) {
                    alertBox.innerHTML = `<div class="badge bg-success p-2 w-100 text-center fs-6">
                        <i class="fa fa-check-circle me-1"></i> موجودی کافی است (موجودی فعلی: ${Number(fin.current_balance).toLocaleString('fa-IR')} ریال - باقی‌مانده پس از ارسال: ${Number(fin.balance_after).toLocaleString('fa-IR')} ریال)
                    </div>`;
                } else {
                    alertBox.innerHTML = `<div class="badge bg-danger p-2 w-100 text-center fs-6">
                        <i class="fa fa-times-circle me-1"></i> موجودی ناکافی است (موجودی فعلی: ${Number(fin.current_balance).toLocaleString('fa-IR')} ریال - کسری: ${Number(fin.balance_deficit).toLocaleString('fa-IR')} ریال)
                    </div>`;
                }

                // Unlock Step 3
                document.getElementById('step3Card').classList.remove('locked');
                document.getElementById('step2Badge').className = 'step-badge approved';
                syncLaunchButton();
            } else {
                Swal.fire({ icon: 'error', title: 'خطا در محاسبه هزینه', text: res.message });
            }
        })
        .catch(err => {
            console.error(err);
        });
    }

    // STEP 7 & 8: Human Confirmation Gate Check
    const confirmCheckbox = document.getElementById('humanConfirmationCheckbox');
    const launchBtn = document.getElementById('btnLaunchAiCampaign');
    const launchStatusText = document.getElementById('launchStatusText');

    function syncLaunchButton() {
        const isConfirmed = confirmCheckbox.checked;
        const canLaunch = isConfirmed && state.hasSufficientBalance;

        launchBtn.disabled = !canLaunch;

        if (!isConfirmed) {
            launchStatusText.innerHTML = '<span class="text-warning"><i class="fa fa-info-circle me-1"></i> تایید آگاهانه کاربر انسانی برای صدور دستور ارسال الزامی است.</span>';
        } else if (!state.hasSufficientBalance) {
            launchStatusText.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle me-1"></i> موجودی شماره فرستنده برای این کمپین کافی نمی‌باشد.</span>';
        } else {
            launchStatusText.innerHTML = '<span class="text-success"><i class="fa fa-check-circle me-1"></i> آماده ارسال — دستور کاربر دریافت شد.</span>';
        }
    }

    confirmCheckbox.addEventListener('change', syncLaunchButton);

    // Launch Campaign Action
    launchBtn.addEventListener('click', function () {
        if (!confirmCheckbox.checked) {
            Swal.fire({ icon: 'warning', title: 'تایید انسانی الزامی است', text: 'لطفاً چک‌باکس تایید انسانی را علامت بزنید.' });
            return;
        }

        Swal.fire({
            title: 'تایید قطعی ارسال کمپین پیامکی',
            text: 'آیا از صدور دستور ارسال نهایی برای این کمپین اطمینان دارید؟',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'بله، ارسال شود',
            cancelButtonText: 'انصراف'
        }).then((result) => {
            if (result.isConfirmed) {
                executeLaunch();
            }
        });
    });

    function executeLaunch() {
        const text = document.getElementById('messageTextInput').value.trim();
        const senderId = document.getElementById('senderNumberSelect').value;
        const recipientType = document.getElementById('recipientTypeSelect').value;

        launchBtn.disabled = true;
        launchBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال صدور دستور ارسال و ثبت کمپین...';

        fetch("{{ route('admin.sms.ai.confirmAndLaunch') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken(),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                human_confirmed: true,
                goal: state.goal,
                message: text,
                filters: state.approvedAudience ? state.approvedAudience.filters : state.proposedFilters,
                sender_number_id: senderId,
                recipient_type: recipientType,
                business_id: currentBusinessId
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'کمپین با موفقیت ثبت شد',
                    text: res.message,
                    confirmButtonText: 'مشاهده وضعیت زنده کمپین'
                }).then(() => {
                    window.location.href = res.status_url;
                });
            } else {
                launchBtn.disabled = false;
                launchBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> تایید نهایی و صدور دستور ارسال کمپین';
                Swal.fire({ icon: 'error', title: 'خطا در ثبت کمپین', text: res.message });
            }
        })
        .catch(err => {
            launchBtn.disabled = false;
            launchBtn.innerHTML = '<i class="fa fa-paper-plane me-1"></i> تایید نهایی و صدور دستور ارسال کمپین';
            console.error(err);
            Swal.fire({ icon: 'error', title: 'خطا در ارتباط با سرور' });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        updatePhoneMockup();
    });
</script>
@endsection
