@extends('Admin.master')

@section('title', 'قراردادها، تعهدنامه و ضوابط حقوقی پیامک')

@section('content')
<div class="container-fluid py-3">
    <!-- Header & Top Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">قراردادها، تعهدنامه و ضوابط حقوقی پیامک</h3>
            <p class="text-muted small mb-0">
                @if(auth()->user()?->isSuperAdmin())
                    مدیریت انتساب قراردادها، الگوهای قرارداد، متون حقوقی و وضعیت تایید دیجیتال (OTP) به تفکیک پنل خدماتی و تبلیغاتی
                @else
                    مشاهده قراردادهای فعال کسب‌وکار، ضوابط و مقررات مخابراتی و تایید الکترونیکی تعهدنامه استفاده از سرویس پیامک
                @endif
            </p>
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
            @if(auth()->user()?->isSuperAdmin())
                <a href="{{ url('/dashboard/contract') }}" class="btn btn-outline-secondary btn-sm" title="مرکز اصلی مدیریت قراردادها و الگوهای سامانه">
                    <i class="fa fa-folder-open me-1"></i> مرکز اصلی قراردادها
                </a>
                <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#legalContentModal">
                    <i class="fa fa-balance-scale me-1"></i> ویرایش متون حقوقی و ضوابط
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#templatesModal">
                    <i class="fa fa-files-o me-1"></i> الگوها و قراردادهای عمومی
                </button>
                <button type="button" class="btn btn-primary btn-sm open-assign-modal"
                        data-service-type="otp"
                        data-business-id="{{ $business?->id }}">
                    <i class="fa fa-plus me-1"></i> انتساب قرارداد جدید
                </button>
            @endif
        </div>
    </div>

    <!-- Navigation -->
    @include('Admin.sms.settings.navigation')

    @if(session('success') || session('contract_usage') === 'success')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') ?: 'قرارداد با موفقیت ثبت گردید.' }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if(session('contract_usage') === 'detached')
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            انتساب قرارداد با موفقیت لغو گردید.
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

    @if(auth()->user()?->isSuperAdmin())
    <!-- Active General Templates & Legal Rules Status Bar (SuperAdmin Quick Overview + Popup Triggers) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #f59e0b !important;">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">الگوی قرارداد عمومی پنل خدماتی (OTP)</span>
                        @if($otpContractUsage && $otpContractUsage->contract)
                            <strong class="text-dark">{{ $otpContractUsage->contract->title }}</strong>
                            @if($otpContractUsage->contract_number)
                                <span class="badge bg-light text-dark border font-monospace ms-1">{{ $otpContractUsage->contract_number }}</span>
                            @endif
                        @else
                            <span class="text-warning small fw-bold">تعریف نشده</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        @if($otpContractUsage && $otpContractUsage->contract)
                            <button type="button" class="btn btn-sm btn-light border view-contract-btn"
                                    data-title="{{ $otpContractUsage->contract->title }}"
                                    data-panel="پنل خدماتی (OTP) — الگوی عمومی"
                                    data-number="{{ $otpContractUsage->contract_number ?: '—' }}"
                                    data-date="{{ $otpContractUsage->contract_date ? verta($otpContractUsage->contract_date)->format('Y/m/d') : '—' }}"
                                    data-content="{{ $otpContractUsage->contract->content ?: 'بدون متن (فایل پیوست)' }}"
                                    data-signer="الگوی پایه سامانه">
                                <i class="fa fa-eye"></i> مشاهده
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#templatesModal">
                            مدیریت الگو
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #10b981 !important;">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">الگوی قرارداد عمومی پنل عادی و تبلیغاتی</span>
                        @if($advContractUsage && $advContractUsage->contract)
                            <strong class="text-dark">{{ $advContractUsage->contract->title }}</strong>
                            @if($advContractUsage->contract_number)
                                <span class="badge bg-light text-dark border font-monospace ms-1">{{ $advContractUsage->contract_number }}</span>
                            @endif
                        @else
                            <span class="text-warning small fw-bold">تعریف نشده</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        @if($advContractUsage && $advContractUsage->contract)
                            <button type="button" class="btn btn-sm btn-light border view-contract-btn"
                                    data-title="{{ $advContractUsage->contract->title }}"
                                    data-panel="پنل عادی و تبلیغاتی — الگوی عمومی"
                                    data-number="{{ $advContractUsage->contract_number ?: '—' }}"
                                    data-date="{{ $advContractUsage->contract_date ? verta($advContractUsage->contract_date)->format('Y/m/d') : '—' }}"
                                    data-content="{{ $advContractUsage->contract->content ?: 'بدون متن (فایل پیوست)' }}"
                                    data-signer="الگوی پایه سامانه">
                                <i class="fa fa-eye"></i> مشاهده
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#templatesModal">
                            مدیریت الگو
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #3b82f6 !important;">
                <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">ضوابط مخابراتی و تعهدنامه استفاده</span>
                        <strong class="text-dark">متون حقوقی و مقررات ارسال</strong>
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#legalContentModal">
                            <i class="fa fa-pencil me-1"></i> مشاهده و ویرایش
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search / Filter Bar -->
    @if(!$business)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="{{ route('admin.sms.settings.contracts.index') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-12 col-md-10">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="جستجوی کسب‌وکار بر اساس نام یا شناسه..." value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-md-2 text-end">
                        <button type="submit" class="btn btn-secondary btn-sm w-100">
                            <i class="fa fa-filter me-1"></i> فیلتر
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- =========================================================================
         MAIN SECTION: CONTRACT MANAGEMENT (مدیریت قراردادها)
         Single full-width scalable card with panel tabs (OTP Panel / Advertising Panel)
    ========================================================================= -->
    @php
        $rows = $business ? collect([$business]) : ($advertisingContracts ?? collect());
    @endphp
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom pt-3 pb-0 px-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <ul class="nav nav-tabs border-bottom-0" id="contractPanelTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="tab-otp-btn" data-bs-toggle="tab" data-bs-target="#pane-otp" type="button" role="tab">
                        <i class="fa fa-shield text-warning me-1"></i> قراردادهای پنل خدماتی (OTP)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="tab-adv-btn" data-bs-toggle="tab" data-bs-target="#pane-adv" type="button" role="tab">
                        <i class="fa fa-bullhorn text-success me-1"></i> قراردادهای پنل عادی و تبلیغاتی
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content">
                <!-- TAB 1: OTP PANEL CONTRACTS -->
                <div class="tab-pane fade show active" id="pane-otp" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>کسب‌وکار (طرف قرارداد)</th>
                                    <th>قرارداد فعال پنل OTP</th>
                                    <th>نوع انتساب</th>
                                    <th>شماره قرارداد</th>
                                    <th>تاریخ قرارداد</th>
                                    <th>وضعیت امضای دیجیتال (OTP)</th>
                                    <th class="text-end pe-3">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $biz)
                                    @php
                                        $specOtp = $business ? $businessOtpUsage : $biz->otp_contract_usage;
                                        $effOtp = $specOtp ?: $otpContractUsage;
                                    @endphp
                                    @php
                                        $otpAttachments = collect();
                                        if ($effOtp) {
                                            foreach ($effOtp->getMedia('staff_attachments') as $m) {
                                                $otpAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
                                            }
                                            foreach ($effOtp->getMedia('client_attachments') as $m) {
                                                $otpAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
                                            }
                                        }
                                        if ($biz) {
                                            foreach ($biz->getMedia('sms_contract_attachments_staff') as $m) {
                                                if (!$otpAttachments->contains('url', $m->getUrl())) {
                                                    $otpAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
                                                }
                                            }
                                            foreach ($biz->getMedia('sms_contract_attachments_client') as $m) {
                                                if (!$otpAttachments->contains('url', $m->getUrl())) {
                                                    $otpAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
                                                }
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="ps-3">{{ $biz->id }}</td>
                                        <td class="fw-bold">{{ $biz->title }}</td>
                                        <td>
                                            @if($effOtp && $effOtp->contract)
                                                <span class="fw-bold text-dark">{{ $effOtp->contract->title }}</span>
                                            @else
                                                <span class="text-muted small">بدون قرارداد</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($specOtp && $specOtp->contract)
                                                <span class="badge bg-primary">قرارداد اختصاصی</span>
                                            @elseif($otpContractUsage && $otpContractUsage->contract)
                                                <span class="badge bg-light text-dark border">الگوی عمومی پنل</span>
                                            @else
                                                <span class="badge bg-secondary">فاقد قرارداد</span>
                                            @endif
                                        </td>
                                        <td class="font-monospace small">{{ $effOtp?->contract_number ?: '—' }}</td>
                                        <td class="small">{{ $effOtp?->contract_date ? verta($effOtp->contract_date)->format('Y/m/d') : '—' }}</td>
                                        <td>
                                            @if($biz->sms_contract_accepted)
                                                <span class="badge bg-success">امضا شده</span>
                                                <div class="small text-muted">{{ $biz->sms_contract_signature_name }}</div>
                                            @else
                                                <span class="badge bg-warning text-dark">در انتظار امضا</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                @if($effOtp && $effOtp->contract)
                                                    <button type="button"
                                                            class="btn btn-outline-secondary view-contract-btn"
                                                            title="مشاهده متن قرارداد (غیرقابل ویرایش)"
                                                            data-title="{{ $effOtp->contract->title }}"
                                                            data-panel="پنل خدماتی (OTP) — {{ $biz->title }}"
                                                            data-number="{{ $effOtp->contract_number ?: '—' }}"
                                                            data-date="{{ $effOtp->contract_date ? verta($effOtp->contract_date)->format('Y/m/d') : '—' }}"
                                                            data-content="{{ $effOtp->contract->content ?: 'بدون متن (فایل پیوست)' }}"
                                                            data-signer="{{ $biz->sms_contract_accepted ? ($biz->sms_contract_signature_name . ' (تایید شده با OTP)') : 'در انتظار امضا' }}"
                                                            data-attachments="{{ json_encode($otpAttachments) }}">
                                                        <i class="fa fa-eye"></i> مشاهده
                                                    </button>
                                                @endif
                                                @if(auth()->user()?->isSuperAdmin())
                                                    <button type="button"
                                                            class="btn btn-outline-primary open-assign-modal"
                                                            data-service-type="otp"
                                                            data-business-id="{{ $biz->id }}"
                                                            data-business-title="{{ $biz->title }}">
                                                        <i class="fa fa-link"></i> انتساب قرارداد جدید
                                                    </button>
                                                    @if($specOtp)
                                                        <form action="{{ route('admin.sms.settings.contracts.destroy', $specOtp) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از لغو انتساب قرارداد اختصاصی این کسب‌وکار اطمینان دارید؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="لغو انتساب اختصاصی">
                                                                <i class="fa fa-unlink"></i> لغو انتساب
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">هیچ کسب‌وکاری یافت نشد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: ADVERTISING PANEL CONTRACTS -->
                <div class="tab-pane fade" id="pane-adv" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>کسب‌وکار (طرف قرارداد)</th>
                                    <th>قرارداد فعال پنل تبلیغاتی</th>
                                    <th>نوع انتساب</th>
                                    <th>شماره قرارداد</th>
                                    <th>تاریخ قرارداد</th>
                                    <th>وضعیت امضای دیجیتال (OTP)</th>
                                    <th class="text-end pe-3">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $biz)
                                    @php
                                        $specAdv = $business ? $businessAdvUsage : $biz->advertising_contract_usage;
                                        $effAdv = $specAdv ?: $advContractUsage;
                                        $advAttachments = collect();
                                        if ($effAdv) {
                                            foreach ($effAdv->getMedia('staff_attachments') as $m) {
                                                $advAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
                                            }
                                            foreach ($effAdv->getMedia('client_attachments') as $m) {
                                                $advAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
                                            }
                                        }
                                        if ($biz) {
                                            foreach ($biz->getMedia('sms_contract_attachments_staff') as $m) {
                                                if (!$advAttachments->contains('url', $m->getUrl())) {
                                                    $advAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
                                                }
                                            }
                                            foreach ($biz->getMedia('sms_contract_attachments_client') as $m) {
                                                if (!$advAttachments->contains('url', $m->getUrl())) {
                                                    $advAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
                                                }
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td class="ps-3">{{ $biz->id }}</td>
                                        <td class="fw-bold">{{ $biz->title }}</td>
                                        <td>
                                            @if($effAdv && $effAdv->contract)
                                                <span class="fw-bold text-dark">{{ $effAdv->contract->title }}</span>
                                            @else
                                                <span class="text-muted small">بدون قرارداد</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($specAdv && $specAdv->contract)
                                                <span class="badge bg-success">قرارداد اختصاصی</span>
                                            @elseif($advContractUsage && $advContractUsage->contract)
                                                <span class="badge bg-light text-dark border">الگوی عمومی پنل</span>
                                            @else
                                                <span class="badge bg-secondary">فاقد قرارداد</span>
                                            @endif
                                        </td>
                                        <td class="font-monospace small">{{ $effAdv?->contract_number ?: '—' }}</td>
                                        <td class="small">{{ $effAdv?->contract_date ? verta($effAdv->contract_date)->format('Y/m/d') : '—' }}</td>
                                        <td>
                                            @if($biz->sms_contract_accepted)
                                                <span class="badge bg-success">امضا شده</span>
                                                <div class="small text-muted">{{ $biz->sms_contract_signature_name }}</div>
                                            @else
                                                <span class="badge bg-warning text-dark">در انتظار امضا</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="btn-group btn-group-sm">
                                                @if($effAdv && $effAdv->contract)
                                                    <button type="button"
                                                            class="btn btn-outline-secondary view-contract-btn"
                                                            title="مشاهده متن قرارداد (غیرقابل ویرایش)"
                                                            data-title="{{ $effAdv->contract->title }}"
                                                            data-panel="پنل عادی و تبلیغاتی — {{ $biz->title }}"
                                                            data-number="{{ $effAdv->contract_number ?: '—' }}"
                                                            data-date="{{ $effAdv->contract_date ? verta($effAdv->contract_date)->format('Y/m/d') : '—' }}"
                                                            data-content="{{ $effAdv->contract->content ?: 'بدون متن (فایل پیوست)' }}"
                                                            data-signer="{{ $biz->sms_contract_accepted ? ($biz->sms_contract_signature_name . ' (تایید شده با OTP)') : 'در انتظار امضا' }}"
                                                            data-attachments="{{ json_encode($advAttachments) }}">
                                                        <i class="fa fa-eye"></i> مشاهده
                                                    </button>
                                                @endif
                                                @if(auth()->user()?->isSuperAdmin())
                                                    <button type="button"
                                                            class="btn btn-outline-success open-assign-modal"
                                                            data-service-type="advertising"
                                                            data-business-id="{{ $biz->id }}"
                                                            data-business-title="{{ $biz->title }}">
                                                        <i class="fa fa-link"></i> انتساب قرارداد جدید
                                                    </button>
                                                    @if($specAdv)
                                                        <form action="{{ route('admin.sms.settings.contracts.destroy', $specAdv) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از لغو انتساب قرارداد اختصاصی این کسب‌وکار اطمینان دارید؟');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger" title="لغو انتساب اختصاصی">
                                                                <i class="fa fa-unlink"></i> لغو انتساب
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">هیچ کسب‌وکاری یافت نشد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if(!$business && $advertisingContracts && $advertisingContracts->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $advertisingContracts->links() }}
            </div>
        @endif
    </div>
    @else
    <!-- =========================================================================
         BUSINESS USER VIEW: READ-ONLY CONTRACTS, LEGAL RULES & SIGN-READY OTP BOX
         (No external links, no new tabs, read-only or sign-ready right here)
    ========================================================================= -->
    @php
        $effOtpBiz = $businessOtpUsage ?: $otpContractUsage;
        $effAdvBiz = $businessAdvUsage ?: $advContractUsage;
    @endphp
    @php
        $effOtpBizAttachments = collect();
        if ($effOtpBiz) {
            foreach ($effOtpBiz->getMedia('staff_attachments') as $m) {
                $effOtpBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
            }
            foreach ($effOtpBiz->getMedia('client_attachments') as $m) {
                $effOtpBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
            }
        }
        if ($business) {
            foreach ($business->getMedia('sms_contract_attachments_staff') as $m) {
                if (!$effOtpBizAttachments->contains('url', $m->getUrl())) {
                    $effOtpBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
                }
            }
            foreach ($business->getMedia('sms_contract_attachments_client') as $m) {
                if (!$effOtpBizAttachments->contains('url', $m->getUrl())) {
                    $effOtpBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
                }
            }
        }

        $effAdvBizAttachments = collect();
        if ($effAdvBiz) {
            foreach ($effAdvBiz->getMedia('staff_attachments') as $m) {
                $effAdvBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
            }
            foreach ($effAdvBiz->getMedia('client_attachments') as $m) {
                $effAdvBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
            }
        }
        if ($business) {
            foreach ($business->getMedia('sms_contract_attachments_staff') as $m) {
                if (!$effAdvBizAttachments->contains('url', $m->getUrl())) {
                    $effAdvBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'staff', 'label' => 'پیوست مدیریت']);
                }
            }
            foreach ($business->getMedia('sms_contract_attachments_client') as $m) {
                if (!$effAdvBizAttachments->contains('url', $m->getUrl())) {
                    $effAdvBizAttachments->push(['name' => $m->file_name, 'url' => $m->getUrl(), 'type' => 'client', 'label' => 'پیوست کاربر']);
                }
            }
        }
    @endphp
    <div class="row g-3 mb-4">
        <!-- OTP Contract Card (Read-Only) -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #f59e0b !important;">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block mb-1">
                            <i class="fa fa-shield text-warning me-1"></i> قرارداد پنل پیامک خدماتی (OTP)
                        </span>
                        @if($effOtpBiz && $effOtpBiz->contract)
                            <strong class="text-dark d-block">{{ $effOtpBiz->contract->title }}</strong>
                            <div class="small text-muted mt-1">
                                شماره: <span class="font-monospace">{{ $effOtpBiz->contract_number ?: '—' }}</span>
                                <span class="mx-1">|</span>
                                تاریخ: {{ $effOtpBiz->contract_date ? verta($effOtpBiz->contract_date)->format('Y/m/d') : '—' }}
                            </div>
                        @else
                            <span class="text-muted small">قرارداد فعالی ثبت نشده است</span>
                        @endif
                    </div>
                    @if($effOtpBiz && $effOtpBiz->contract)
                        <button type="button" class="btn btn-sm btn-outline-secondary view-contract-btn"
                                data-title="{{ $effOtpBiz->contract->title }}"
                                data-panel="پنل خدماتی (OTP) — {{ $business?->title }}"
                                data-number="{{ $effOtpBiz->contract_number ?: '—' }}"
                                data-date="{{ $effOtpBiz->contract_date ? verta($effOtpBiz->contract_date)->format('Y/m/d') : '—' }}"
                                data-content="{{ $effOtpBiz->contract->content ?: 'بدون متن (فایل پیوست)' }}"
                                data-signer="{{ $isContractSigned ? ($contractSigner . ' (تایید شده با OTP)') : 'در انتظار امضا' }}"
                                data-attachments="{{ json_encode($effOtpBizAttachments) }}">
                            <i class="fa fa-eye me-1"></i> مشاهده متن قرارداد
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Advertising Contract Card (Read-Only) -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #10b981 !important;">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block mb-1">
                            <i class="fa fa-bullhorn text-success me-1"></i> قرارداد پنل پیامک عادی و تبلیغاتی
                        </span>
                        @if($effAdvBiz && $effAdvBiz->contract)
                            <strong class="text-dark d-block">{{ $effAdvBiz->contract->title }}</strong>
                            <div class="small text-muted mt-1">
                                شماره: <span class="font-monospace">{{ $effAdvBiz->contract_number ?: '—' }}</span>
                                <span class="mx-1">|</span>
                                تاریخ: {{ $effAdvBiz->contract_date ? verta($effAdvBiz->contract_date)->format('Y/m/d') : '—' }}
                            </div>
                        @else
                            <span class="text-muted small">قرارداد فعالی ثبت نشده است</span>
                        @endif
                    </div>
                    @if($effAdvBiz && $effAdvBiz->contract)
                        <button type="button" class="btn btn-sm btn-outline-secondary view-contract-btn"
                                data-title="{{ $effAdvBiz->contract->title }}"
                                data-panel="پنل عادی و تبلیغاتی — {{ $business?->title }}"
                                data-number="{{ $effAdvBiz->contract_number ?: '—' }}"
                                data-date="{{ $effAdvBiz->contract_date ? verta($effAdvBiz->contract_date)->format('Y/m/d') : '—' }}"
                                data-content="{{ $effAdvBiz->contract->content ?: 'بدون متن (فایل پیوست)' }}"
                                data-signer="{{ $isContractSigned ? ($contractSigner . ' (تایید شده با OTP)') : 'در انتظار امضا' }}"
                                data-attachments="{{ json_encode($effAdvBizAttachments) }}">
                            <i class="fa fa-eye me-1"></i> مشاهده متن قرارداد
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Usage Agreement Text & Read-Only / Sign-Ready OTP Section -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-file-text-o text-primary me-1"></i> متن تعهدنامه استفاده از سرویس پیامک
                    </h6>
                    <span class="badge bg-light text-secondary border"><i class="fa fa-lock me-1"></i> متن رسمی سامانه (فقط خواندنی)</span>
                </div>
                <div class="card-body" style="max-height: 320px; overflow-y: auto;">
                    <p class="mb-0 small text-dark" style="white-space: pre-wrap; line-height: 1.9;">{{ $contractText }}</p>
                </div>
            </div>

            @if($isContractSigned)
                <!-- Already Signed: Read-Only Digital Signature Certificate -->
                <div class="card border-0 shadow-sm" style="border-right: 4px solid #10b981 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; min-width: 46px;">
                                <i class="fa fa-check fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-success mb-1">تعهدنامه و ضوابط حقوقی با موفقیت امضا و ثبت نهایی شده است</h6>
                                <p class="text-muted small mb-0">این سند به صورت الکترونیکی با رمز یکبارمصرف (OTP) تایید گردیده و در وضعیت <strong>فقط خواندنی (Read-Only)</strong> قرار دارد.</p>
                            </div>
                        </div>

                        <div class="row g-3 p-3 bg-light rounded border small">
                            <div class="col-12 col-md-4">
                                <span class="text-muted d-block">نام و نام خانوادگی امضاکننده:</span>
                                <strong class="text-dark">{{ $contractSigner ?: 'ثبت شده' }}</strong>
                            </div>
                            <div class="col-12 col-md-4">
                                <span class="text-muted d-block">تاریخ و زمان ثبت امضا:</span>
                                <strong class="text-dark">{{ $contractSignedAt ? verta($contractSignedAt)->format('Y/m/d - H:i:s') : '—' }}</strong>
                            </div>
                            <div class="col-12 col-md-4">
                                <span class="text-muted d-block">شناسه یکتای گواهی امضا:</span>
                                <strong class="font-monospace text-primary">SMS-AGR-{{ $business?->id }}-{{ $contractSignedAt ? \Carbon\Carbon::parse($contractSignedAt)->timestamp : '0' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($business)
                <!-- Not Yet Signed: Sign-Ready Form with 6-Digit OTP -->
                <div class="card border-0 shadow-sm" style="border-right: 4px solid #3b82f6 !important;">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fa fa-pencil-square-o text-primary me-1"></i> امضای الکترونیکی و تایید تعهدنامه (با کد تایید OTP)
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('admin.sms.contract.accept') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="business_id" value="{{ $business->id }}">

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" required>
                                <label class="form-check-label small fw-bold" for="agree">
                                    متن تعهدنامه، قراردادها و ضوابط مخابراتی ارسال پیامک را به دقت مطالعه نموده و کلیه مفاد آن را می‌پذیرم.
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">نام و نام خانوادگی امضاکننده <span class="text-danger">*</span></label>
                                <input type="text" name="signature_name" id="signatureName" value="{{ old('signature_name') }}" class="form-control" placeholder="نام و نام خانوادگی خود را به‌عنوان امضای الکترونیکی وارد کنید" required>
                            </div>

                            <!-- Client Attachments Upload (Max 5 files) -->
                            <div class="mb-3 p-3 bg-light rounded border">
                                <label class="form-label fw-bold small mb-1">
                                    <i class="fa fa-paperclip text-primary me-1"></i> پیوست مدارک، مجوزها یا مستندات احراز هویت و شرکت (حداکثر ۵ فایل - اختیاری)
                                </label>
                                <p class="text-muted small mb-2">فرمت‌های مجاز: PDF، تصاویر (JPG/PNG)، اسناد Word (حداکثر حجم هر فایل ۱۰ مگابایت)</p>
                                <input type="file" name="client_attachments[]" id="clientIndexAttachmentsInput" class="form-control form-control-sm" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div id="clientIndexFileNotice" class="small text-muted mt-1">حداکثر می‌توانید تا ۵ فایل را به‌صورت هم‌زمان انتخاب فرمایید.</div>
                            </div>

                            <div class="border rounded p-3 bg-light" id="otpBox"
                                 data-send-url="{{ route('admin.sms.contract.otp') }}"
                                 data-business-id="{{ $business->id }}"
                                 data-resend="{{ $otpResendSeconds ?? 60 }}">
                                @if(!empty($otpPhoneMasked))
                                    <p class="small text-muted mb-2">
                                        کد تایید ۶ رقمی به شماره همراه ثبت‌شده شما (<strong dir="ltr">{{ $otpPhoneMasked }}</strong>) ارسال خواهد شد.
                                    </p>
                                    <div class="row g-2 align-items-center">
                                        <div class="col-12 col-sm-auto">
                                            <button type="button" class="btn btn-primary btn-sm" id="sendOtpBtn">
                                                <i class="fa fa-paper-plane me-1"></i> دریافت کد تایید (OTP)
                                            </button>
                                        </div>
                                        <div class="col-12 col-sm-auto">
                                            <input type="text" name="otp" id="otpInput" class="form-control form-control-sm text-center font-monospace"
                                                   inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9۰-۹]{6}"
                                                   placeholder="کد ۶ رقمی" style="width: 10rem; letter-spacing: .25rem;" required>
                                        </div>
                                        <div class="col-12 col-sm-auto">
                                            <button type="submit" class="btn btn-success btn-sm px-3" id="submitSignBtn">
                                                <i class="fa fa-check me-1"></i> تایید نهایی و ثبت امضا
                                            </button>
                                        </div>
                                    </div>
                                    <div id="otpMessage" class="small mt-2" role="status" aria-live="polite"></div>
                                    @error('otp')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                @else
                                    <div class="alert alert-warning mb-0 small">
                                        شماره موبایل معتبری برای حساب کاربری شما ثبت نشده است؛ جهت امضای الکترونیکی ابتدا شماره موبایل خود را در پروفایل کاربری تکمیل نمایید.
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column: Read-Only Telecom Regulatory Rules -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-balance-scale text-warning me-1"></i> ضوابط و مقررات مخابراتی
                    </h6>
                    <span class="badge bg-light text-secondary border">فقط خواندنی</span>
                </div>
                <div class="card-body" style="max-height: 520px; overflow-y: auto;">
                    <p class="small text-muted mb-0" style="white-space: pre-wrap; line-height: 1.9;">{{ $regulatoryRulesText }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- =========================================================================
     POPUP 1: READ-ONLY CONTRACT VIEWER MODAL (مشاهده قرارداد - غیرقابل ویرایش)
========================================================================= -->
<div class="modal fade" id="viewContractModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="viewModalTitle">مشاهده قرارداد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border small text-muted mb-3">
                    <i class="fa fa-lock me-1"></i>
                    به استناد ضوابط حقوقی، مفاد قراردادهای ثبت‌شده غیرقابل ویرایش (Read-Only) است.
                </div>
                <div class="row g-2 p-3 bg-light rounded border mb-3 small">
                    <div class="col-12 col-md-6">
                        <span class="text-muted">پنل / طرف قرارداد:</span>
                        <strong class="text-dark" id="viewModalPanel">—</strong>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="text-muted">وضعیت امضای دیجیتال:</span>
                        <strong class="text-success" id="viewModalSigner">—</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">شماره قرارداد:</span>
                        <strong class="text-dark font-monospace" id="viewModalNumber">—</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted">تاریخ قرارداد:</span>
                        <strong class="text-dark" id="viewModalDate">—</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">متن کامل قرارداد</label>
                    <div id="viewModalContent" class="p-3 bg-white border rounded small text-secondary" style="max-height: 360px; overflow-y: auto; white-space: pre-wrap; line-height: 1.9;"></div>
                </div>
                <div id="viewModalAttachmentsSection" style="display: none;">
                    <label class="form-label fw-bold small"><i class="fa fa-paperclip text-primary me-1"></i> مدارک و فایل‌های پیوست شده</label>
                    <div id="viewModalAttachmentsList" class="row g-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()?->isSuperAdmin())
<!-- =========================================================================
     POPUP 2: TEMPLATES & GENERAL CONTRACTS MODAL (الگوها و قراردادهای عمومی)
========================================================================= -->
<div class="modal fade" id="templatesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">الگوها و قراردادهای عمومی پنل‌ها</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    <!-- Left Column: Active General Templates for OTP & Advertising -->
                    <div class="col-12 col-lg-5">
                        <h6 class="fw-bold mb-3">قراردادهای عمومی فعال پنل‌ها</h6>

                        <!-- OTP Panel General Template Box -->
                        <div class="p-3 border rounded mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small"><i class="fa fa-shield text-warning me-1"></i> پنل خدماتی (OTP)</span>
                                @if($otpContractUsage && $otpContractUsage->contract)
                                    <span class="badge bg-success">فعال: {{ $otpContractUsage->contract->title }}</span>
                                @else
                                    <span class="badge bg-secondary">تنظیم نشده</span>
                                @endif
                            </div>
                            <form action="{{ route('admin.sms.settings.contracts.store') }}" method="POST" class="row g-2 align-items-end">
                                @csrf
                                <input type="hidden" name="service_type" value="otp">
                                <input type="hidden" name="mode" value="existing">
                                <input type="hidden" name="is_global_template" value="1">
                                <div class="col-8">
                                    <label class="form-label small text-muted mb-1">انتخاب الگوی پیش‌فرض پنل OTP</label>
                                    <select name="contract_id" class="form-select form-select-sm" required>
                                        <option value="">— انتخاب الگو از بانک —</option>
                                        @foreach($bankContracts as $bc)
                                            <option value="{{ $bc->id }}" {{ $otpContractUsage?->contract_id === $bc->id ? 'selected' : '' }}>
                                                {{ $bc->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">تنظیم الگو</button>
                                </div>
                            </form>
                        </div>

                        <!-- Advertising Panel General Template Box -->
                        <div class="p-3 border rounded mb-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small"><i class="fa fa-bullhorn text-success me-1"></i> پنل عادی و تبلیغاتی</span>
                                @if($advContractUsage && $advContractUsage->contract)
                                    <span class="badge bg-success">فعال: {{ $advContractUsage->contract->title }}</span>
                                @else
                                    <span class="badge bg-secondary">تنظیم نشده</span>
                                @endif
                            </div>
                            <form action="{{ route('admin.sms.settings.contracts.store') }}" method="POST" class="row g-2 align-items-end">
                                @csrf
                                <input type="hidden" name="service_type" value="advertising">
                                <input type="hidden" name="mode" value="existing">
                                <input type="hidden" name="is_global_template" value="1">
                                <div class="col-8">
                                    <label class="form-label small text-muted mb-1">انتخاب الگوی پیش‌فرض پنل تبلیغاتی</label>
                                    <select name="contract_id" class="form-select form-select-sm" required>
                                        <option value="">— انتخاب الگو از بانک —</option>
                                        @foreach($bankContracts as $bc)
                                            <option value="{{ $bc->id }}" {{ $advContractUsage?->contract_id === $bc->id ? 'selected' : '' }}>
                                                {{ $bc->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-success btn-sm w-100">تنظیم الگو</button>
                                </div>
                            </form>
                        </div>

                        <!-- Existing Templates List -->
                        <h6 class="fw-bold mb-2 mt-4">فهرست الگوهای موجود ({{ $bankContracts->count() }})</h6>
                        <div class="border rounded" style="max-height: 220px; overflow-y: auto;">
                            <ul class="list-group list-group-flush small">
                                @forelse($bankContracts as $bc)
                                    <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <strong class="text-dark">{{ $bc->title }}</strong>
                                            @if($bc->folder)
                                                <span class="badge bg-light text-muted border ms-1">{{ $bc->folder->title }}</span>
                                            @endif
                                        </div>
                                        <span class="text-muted font-monospace">#{{ $bc->id }}</span>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted py-3">هنوز الگویی ثبت نشده است.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Right Column: Create New Template / Contract -->
                    <div class="col-12 col-lg-7 border-end">
                        <h6 class="fw-bold mb-3">ایجاد الگو / قرارداد جدید</h6>
                        <form action="{{ route('admin.sms.settings.contracts.store') }}" method="POST" enctype="multipart/form-data" id="createTemplateOrContractForm">
                            @csrf
                            <input type="hidden" name="mode" value="new">
                            <input type="hidden" name="is_global_template" value="0">
                            <input type="hidden" name="template_only" id="tplOnlyInput" value="1">

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">عنوان الگو / قرارداد <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" placeholder="مثال: تعهدنامه پیامک خدماتی ۱۴۰۵" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">پنل مربوطه <span class="text-danger">*</span></label>
                                    <select name="service_type" class="form-select" required>
                                        <option value="otp">پنل پیامک خدماتی (OTP)</option>
                                        <option value="advertising">پنل پیامک عادی و تبلیغاتی</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6" id="tplFolderGroup">
                                    <label class="form-label fw-bold small">پوشه الگو</label>
                                    <select name="contract_folder_id" class="form-select">
                                        @foreach($folders as $fid => $ftitle)
                                            <option value="{{ $fid }}">{{ $ftitle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-bold small">شماره الگو / قرارداد</label>
                                    <input type="text" name="contract_number" class="form-control" placeholder="مثال: TPL-OTP-01">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">متن الگو / قرارداد <span class="text-danger">*</span></label>
                                <textarea name="content" class="form-control" rows="6" placeholder="متن رسمی قرارداد یا الگو را وارد کنید..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">فایل پیوست (اختیاری)</label>
                                <input type="file" name="file" class="form-control">
                            </div>

                            <div class="mb-3 p-3 bg-light rounded border">
                                <div class="small text-muted mb-2">حداقل یکی از دو مورد زیر باید انتخاب شود:</div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="save_as_template" id="tplSaveAsTemplateCheck" value="1" checked>
                                    <label class="form-check-label fw-bold small" for="tplSaveAsTemplateCheck">
                                        ذخیره به‌عنوان الگو در بانک الگوها (اختیاری)
                                    </label>
                                </div>
                                <div>
                                    <label class="form-label fw-bold small mb-1">انتساب مستقیم به کسب‌وکار (اختیاری)</label>
                                    <select name="business_id" id="tplAssignBusinessSelect" class="form-select form-select-sm">
                                        <option value="">— بدون انتساب به کسب‌وکار —</option>
                                        @foreach($all_businesses as $b)
                                            <option value="{{ $b->id }}">{{ $b->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="tplAtLeastOneError" class="text-danger small mt-2" style="display: none;">
                                    لطفاً حداقل یکی از دو گزینه «ذخیره به‌عنوان الگو» یا «انتساب به کسب‌وکار» را انتخاب کنید.
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="fa fa-check me-1"></i> ثبت و ذخیره
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================================
     POPUP 3: ASSIGN CONTRACT MODAL (انتساب قرارداد به کسب‌وکار)
========================================================================= -->
<div class="modal fade" id="assignContractModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.sms.settings.contracts.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="is_global_template" value="0">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="assignModalTitle">انتساب قرارداد به کسب‌وکار</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">کسب‌وکار (طرف قرارداد) <span class="text-danger">*</span></label>
                            <select name="business_id" id="assignBusinessSelect" class="form-select" required>
                                <option value="">— انتخاب کسب‌وکار —</option>
                                @foreach($all_businesses as $b)
                                    <option value="{{ $b->id }}">{{ $b->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">پنل پیامک <span class="text-danger">*</span></label>
                            <select name="service_type" id="assignServiceTypeSelect" class="form-select" required>
                                <option value="otp">پنل پیامک خدماتی (OTP)</option>
                                <option value="advertising">پنل پیامک عادی و تبلیغاتی</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded border">
                        <label class="form-label fw-bold small mb-2">نحوه تنظیم قرارداد برای این کسب‌وکار</label>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="assignModeExisting" value="existing" checked>
                                <label class="form-check-label small" for="assignModeExisting">انتساب از الگوهای موجود</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" id="assignModeNew" value="new">
                                <label class="form-check-label small" for="assignModeNew">ایجاد قرارداد اختصاصی جدید</label>
                            </div>
                        </div>
                    </div>

                    <!-- Option A: Select from existing templates -->
                    <div id="assignExistingSection" class="mb-3">
                        <label class="form-label fw-bold small">انتخاب الگو / قرارداد <span class="text-danger">*</span></label>
                        <select name="contract_id" class="form-select">
                            <option value="">— انتخاب از فهرست الگوها —</option>
                            @foreach($bankContracts as $bc)
                                <option value="{{ $bc->id }}">{{ $bc->title }} (#{{ $bc->id }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Option B: Create a new contract (with option to also save as template or keep one-off) -->
                    <div id="assignNewSection" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">عنوان قرارداد <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="assignNewTitle" class="form-control" placeholder="عنوان قرارداد اختصاصی...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">متن قرارداد <span class="text-danger">*</span></label>
                            <textarea name="content" class="form-control" rows="5" placeholder="متن کامل قرارداد را وارد کنید..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">فایل پیوست قرارداد (PDF، تصویر یا سند - اختیاری)</label>
                            <input type="file" name="file" class="form-control">
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="save_as_template" id="saveAsTemplateCheck" value="1">
                            <label class="form-check-label small" for="saveAsTemplateCheck">
                                این قرارداد به‌عنوان الگو نیز در بانک الگوها ذخیره شود (در صورت عدم انتخاب، صرفاً به همین کسب‌وکار منتسب می‌شود)
                            </label>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">شماره قرارداد</label>
                            <input type="text" name="contract_number" class="form-control" placeholder="مثال: CNT-1405-101">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small">تاریخ قرارداد</label>
                            <input type="date" name="contract_date" class="form-control" value="{{ now()->toDateString() }}">
                        </div>
                    </div>

                    <!-- Staff Attachments (Max 5 files) -->
                    <div class="p-3 bg-light rounded border">
                        <label class="form-label fw-bold small mb-1">
                            <i class="fa fa-paperclip text-primary me-1"></i> پیوست مدارک و مستندات اداری / مخابراتی مدیریت (حداکثر ۵ فایل - اختیاری)
                        </label>
                        <p class="text-muted small mb-2">مدارک تایید اپراتور، استعلامات یا معرفی‌نامه‌ها (PDF، تصویر یا Word حداکثر ۱۰ مگابایت)</p>
                        <input type="file" name="staff_attachments[]" id="staffAttachmentsInput" class="form-control form-control-sm" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <div id="staffFileCountNotice" class="small text-muted mt-1">حداکثر می‌توانید تا ۵ فایل را به‌صورت هم‌زمان انتخاب فرمایید.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-check me-1"></i> ثبت و انتساب قرارداد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- =========================================================================
     POPUP 4: LEGAL & REGULATORY RULES MODAL (متون حقوقی و ضوابط مخابراتی)
========================================================================= -->
<div class="modal fade" id="legalContentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.sms.settings.content.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa fa-balance-scale text-primary me-1"></i> متون حقوقی و ضوابط مخابراتی پیامک
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">متن ضوابط و مقررات مخابراتی (محدودیت‌های محتوای پیامک)</label>
                        <textarea name="regulatory_rules_text" class="form-control" rows="7" placeholder="متن ضوابط و مقررات مخابراتی را اینجا وارد کنید...">{{ old('regulatory_rules_text', $regulatoryRulesText) }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">متن تعهدنامه استفاده از سرویس پیامک</label>
                        <textarea name="contract_text" class="form-control" rows="7" placeholder="متن تعهدنامه را اینجا وارد کنید...">{{ old('contract_text', $contractText) }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-check me-1"></i> ذخیره متون حقوقی
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

{{-- Scripts live outside the Vue-compiled #app root (inline <script> inside it is dropped). --}}
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Read-Only Contract Viewer Modal
    const viewModalEl = document.getElementById('viewContractModal');
    const viewModal = (viewModalEl && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(viewModalEl) : null;

    document.querySelectorAll('.view-contract-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('viewModalTitle').textContent = this.getAttribute('data-title') || 'مشاهده قرارداد';
            document.getElementById('viewModalPanel').textContent = this.getAttribute('data-panel') || '—';
            document.getElementById('viewModalNumber').textContent = this.getAttribute('data-number') || '—';
            document.getElementById('viewModalDate').textContent = this.getAttribute('data-date') || '—';
            document.getElementById('viewModalSigner').textContent = this.getAttribute('data-signer') || '—';
            document.getElementById('viewModalContent').textContent = this.getAttribute('data-content') || '—';

            // Attachments rendering
            const rawAttachments = this.getAttribute('data-attachments');
            const attachmentsSection = document.getElementById('viewModalAttachmentsSection');
            const attachmentsList = document.getElementById('viewModalAttachmentsList');
            if (attachmentsSection && attachmentsList) {
                attachmentsList.innerHTML = '';
                let items = [];
                try {
                    items = rawAttachments ? JSON.parse(rawAttachments) : [];
                } catch (e) {
                    items = [];
                }

                if (items.length > 0) {
                    attachmentsSection.style.display = 'block';
                    items.forEach(function (item) {
                        const col = document.createElement('div');
                        col.className = 'col-12 col-md-6';
                        col.innerHTML = `
                            <div class="d-flex align-items-center justify-content-between p-2 border rounded bg-light small">
                                <div class="text-truncate me-2">
                                    <i class="fa ${item.type === 'staff' ? 'fa-file-pdf-o' : 'fa-file-text-o'} text-muted me-1"></i>
                                    <span title="${item.name}">${item.name}</span>
                                    <span class="badge ${item.type === 'staff' ? 'bg-primary' : 'bg-secondary'} ms-1">${item.label}</span>
                                </div>
                                <a href="${item.url}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" title="دانلود / مشاهده">
                                    <i class="fa fa-download"></i>
                                </a>
                            </div>
                        `;
                        attachmentsList.appendChild(col);
                    });
                } else {
                    attachmentsSection.style.display = 'none';
                }
            }

            if (viewModal) {
                viewModal.show();
            } else if (typeof jQuery !== 'undefined') {
                jQuery(viewModalEl).modal('show');
            }
        });
    });

    // Staff attachments 5-file validator
    const staffFileInput = document.getElementById('staffAttachmentsInput');
    const staffFileNotice = document.getElementById('staffFileCountNotice');
    if (staffFileInput) {
        staffFileInput.addEventListener('change', function () {
            if (staffFileInput.files.length > 5) {
                alert('حداکثر می‌توانید ۵ فایل پیوست انتخاب فرمایید.');
                staffFileInput.value = '';
                if (staffFileNotice) {
                    staffFileNotice.className = 'small text-danger mt-1';
                    staffFileNotice.textContent = 'تعداد فایل‌های انتخابی بیش از ۵ مورد بود و حذف شدند. لطفاً حداکثر ۵ فایل انتخاب نمایید.';
                }
            } else if (staffFileNotice) {
                staffFileNotice.className = 'small text-success mt-1';
                staffFileNotice.textContent = staffFileInput.files.length + ' فایل جهت بارگذاری انتخاب گردید (حداکثر ۵ فایل).';
            }
        });
    }

    // Client attachments 5-file validator in business view
    const clientIndexFileInput = document.getElementById('clientIndexAttachmentsInput');
    const clientIndexFileNotice = document.getElementById('clientIndexFileNotice');
    if (clientIndexFileInput) {
        clientIndexFileInput.addEventListener('change', function () {
            if (clientIndexFileInput.files.length > 5) {
                alert('حداکثر می‌توانید ۵ فایل پیوست انتخاب فرمایید.');
                clientIndexFileInput.value = '';
                if (clientIndexFileNotice) {
                    clientIndexFileNotice.className = 'small text-danger mt-1';
                    clientIndexFileNotice.textContent = 'تعداد فایل‌های انتخابی بیش از ۵ مورد بود و حذف شدند. لطفاً حداکثر ۵ فایل انتخاب نمایید.';
                }
            } else if (clientIndexFileNotice) {
                clientIndexFileNotice.className = 'small text-success mt-1';
                clientIndexFileNotice.textContent = clientIndexFileInput.files.length + ' فایل جهت بارگذاری انتخاب گردید (حداکثر ۵ فایل).';
            }
        });
    }

    // Assign Contract Modal
    const assignModalEl = document.getElementById('assignContractModal');
    const assignModal = (assignModalEl && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(assignModalEl) : null;
    const assignModeExisting = document.getElementById('assignModeExisting');
    const assignModeNew = document.getElementById('assignModeNew');
    const assignExistingSection = document.getElementById('assignExistingSection');
    const assignNewSection = document.getElementById('assignNewSection');

    function syncAssignMode() {
        if (assignModeNew && assignModeNew.checked) {
            assignNewSection.style.display = 'block';
            assignExistingSection.style.display = 'none';
        } else if (assignExistingSection && assignNewSection) {
            assignNewSection.style.display = 'none';
            assignExistingSection.style.display = 'block';
        }
    }

    if (assignModeExisting && assignModeNew) {
        assignModeExisting.addEventListener('change', syncAssignMode);
        assignModeNew.addEventListener('change', syncAssignMode);
    }

    document.querySelectorAll('.open-assign-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const serviceType = this.getAttribute('data-service-type') || 'otp';
            const businessId = this.getAttribute('data-business-id') || '';
            const businessTitle = this.getAttribute('data-business-title') || '';

            const serviceSelect = document.getElementById('assignServiceTypeSelect');
            if (serviceSelect) serviceSelect.value = serviceType;

            const bizSelect = document.getElementById('assignBusinessSelect');
            if (bizSelect && businessId) bizSelect.value = businessId;

            const titleEl = document.getElementById('assignModalTitle');
            if (titleEl) {
                titleEl.textContent = businessTitle
                    ? ('انتساب قرارداد جدید — ' + businessTitle)
                    : 'انتساب قرارداد به کسب‌وکار';
            }

            if (assignModeExisting) {
                assignModeExisting.checked = true;
                syncAssignMode();
            }

            if (assignModal) {
                assignModal.show();
            } else if (typeof jQuery !== 'undefined') {
                jQuery(assignModalEl).modal('show');
            }
        });
    });

    // Template / Contract Creation Form: Both "save as template" & "assign to business" are optional, at least one must be chosen
    const tplForm = document.getElementById('createTemplateOrContractForm');
    const tplSaveCheck = document.getElementById('tplSaveAsTemplateCheck');
    const tplBizSelect = document.getElementById('tplAssignBusinessSelect');
    const tplOnlyInput = document.getElementById('tplOnlyInput');
    const tplFolderGroup = document.getElementById('tplFolderGroup');
    const tplErrorEl = document.getElementById('tplAtLeastOneError');

    function syncTplFormState() {
        if (!tplSaveCheck || !tplBizSelect || !tplOnlyInput) return;

        const saveAsTpl = tplSaveCheck.checked;
        const hasBiz = Boolean(tplBizSelect.value);

        tplOnlyInput.value = (saveAsTpl && !hasBiz) ? '1' : '0';

        if (tplFolderGroup) {
            tplFolderGroup.style.display = saveAsTpl ? '' : 'none';
        }

        if (tplErrorEl && (saveAsTpl || hasBiz)) {
            tplErrorEl.style.display = 'none';
        }
    }

    if (tplSaveCheck && tplBizSelect) {
        tplSaveCheck.addEventListener('change', syncTplFormState);
        tplBizSelect.addEventListener('change', syncTplFormState);
        syncTplFormState();
    }

    if (tplForm && tplSaveCheck && tplBizSelect) {
        tplForm.addEventListener('submit', function (e) {
            if (!tplSaveCheck.checked && !tplBizSelect.value) {
                e.preventDefault();
                if (tplErrorEl) {
                    tplErrorEl.style.display = 'block';
                }
            }
        });
    }

    // Business User 6-Digit OTP Signing Handler
    const otpBox = document.getElementById('otpBox');
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    if (otpBox && sendOtpBtn) {
        const agree = document.getElementById('agree');
        const nameInput = document.getElementById('signatureName');
        const otpInput = document.getElementById('otpInput');
        const message = document.getElementById('otpMessage');
        let timer = null;
        let sentName = null;

        function say(kind, text) {
            if (!message) return;
            message.className = 'small mt-2 text-' + kind;
            message.textContent = text;
        }

        function countdown(seconds) {
            clearInterval(timer);
            let left = seconds;
            sendOtpBtn.disabled = true;
            (function tick() {
                if (left <= 0) {
                    sendOtpBtn.disabled = false;
                    sendOtpBtn.textContent = 'ارسال مجدد کد';
                    return;
                }
                sendOtpBtn.textContent = 'ارسال مجدد تا ' + left.toLocaleString('fa-IR') + ' ثانیه دیگر';
                left--;
                timer = setTimeout(tick, 1000);
            })();
        }

        if (nameInput) {
            nameInput.addEventListener('input', function () {
                if (sentName !== null && nameInput.value.trim() !== sentName) {
                    say('warning', 'با تغییر نام امضاکننده، کد قبلی بی‌اعتبار می‌شود و باید کد جدید دریافت کنید.');
                }
            });
        }

        sendOtpBtn.addEventListener('click', function () {
            if (!agree || !agree.checked || !nameInput || nameInput.value.trim().length < 3) {
                say('danger', 'ابتدا تعهدنامه را تایید کنید و نام و نام خانوادگی خود را وارد کنید.');
                return;
            }
            sendOtpBtn.disabled = true;
            say('muted', 'در حال ارسال کد...');

            const data = new FormData();
            data.append('agree', '1');
            data.append('signature_name', nameInput.value.trim());
            data.append('business_id', otpBox.getAttribute('data-business-id'));

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            fetch(otpBox.getAttribute('data-send-url'), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '',
                    'Accept': 'application/json'
                },
                body: data
            }).then(function (response) {
                return response.json().then(function (body) { return {status: response.status, body: body}; });
            }).then(function (res) {
                if (res.status === 200 && res.body.status === 'success') {
                    say('success', res.body.message);
                    sentName = nameInput.value.trim();
                    if (otpInput) otpInput.focus();
                    countdown(res.body.resend_after || parseInt(otpBox.getAttribute('data-resend'), 10) || 60);
                    return;
                }
                const errors = res.body && res.body.errors ? Object.keys(res.body.errors).map(function (k) { return res.body.errors[k][0]; }).join(' ') : '';
                say('danger', (res.body && res.body.message) || errors || 'ارسال کد ناموفق بود.');
                if (res.body && res.body.retry_after && res.status === 429) {
                    countdown(res.body.retry_after);
                } else {
                    sendOtpBtn.disabled = false;
                }
            }).catch(function () {
                say('danger', 'ارتباط با سرور برقرار نشد.');
                sendOtpBtn.disabled = false;
            });
        });

        if (otpInput) {
            otpInput.addEventListener('input', function () {
                const cleanVal = otpInput.value.replace(/\D/g, '');
                if (cleanVal.length === 6) {
                    const form = otpInput.closest('form');
                    if (form && agree && agree.checked && nameInput && nameInput.value.trim().length >= 3) {
                        const submitBtn = document.getElementById('submitSignBtn');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> در حال تایید و ثبت امضا...';
                        }
                        form.submit();
                    }
                }
            });
        }
    }
});
</script>
@endsection
