@extends('Admin.master')

@section('title', 'مدیریت خطوط پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">خطوط پیامک</h3>
            <p class="text-muted small mb-0">
                @if(auth()->user()?->isSuperAdmin())
                    مدیریت مخزن شماره‌های فرستنده (OTP و تبلیغاتی) و تخصیص اشتراکی یا اختصاصی خطوط به کسب‌وکارها
                @else
                    مشاهده خطوط پیامک تخصیص‌یافته از مخزن خطوط سامانه به کسب‌وکار شما
                @endif
            </p>
        </div>
        @if(auth()->user()?->isSuperAdmin())
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.sms.settings.senders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus me-1"></i> افزودن خط جدید به مخزن
                </a>
            </div>
        @endif
    </div>

    @include('Admin.sms.settings.navigation')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
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

    @if($business && !auth()->user()?->isSuperAdmin())
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #3b82f6 !important;">
                    <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block mb-1">خط خدماتی احراز هویت (OTP) سامانه</span>
                            @if(!empty($otpSender))
                                <strong class="font-monospace fs-5 text-dark" dir="ltr">{{ $otpSender->number }}</strong>
                            @else
                                <span class="text-muted small">تنظیم نشده</span>
                            @endif
                        </div>
                        <span class="badge bg-success px-3 py-2">فعال و مشترک سامانه</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100" style="border-right: 4px solid #10b981 !important;">
                    <div class="card-body py-3 px-3 d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block mb-1">خطوط تبلیغاتی و اطلاع‌رسانی کسب‌وکار ({{ $business->title }})</span>
                            <strong class="text-dark">{{ $senderNumbers->total() }} خط فعال در دسترس</strong>
                        </div>
                        <span class="badge bg-light text-secondary border px-3 py-2">تخصیص‌یافته از مخزن سامانه</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4" style="background: #ffffff !important;">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.settings.senders.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="جستجو بر اساس شماره خط یا توضیحات..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select form-select-sm">
                        <option value="all">همه انواع خطوط</option>
                        <option value="otp" {{ ($filters['type'] ?? '') === 'otp' ? 'selected' : '' }}>کد تایید (OTP)</option>
                        <option value="advertising" {{ ($filters['type'] ?? '') === 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all">همه وضعیت‌ها</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>
                @if(auth()->user()?->isSuperAdmin() && !$business)
                    <div class="col-12 col-md-2">
                        <select name="ghorfe_id" class="form-select form-select-sm">
                            <option value="all">همه کسب‌وکارها</option>
                            @foreach($businesses as $b)
                                <option value="{{ $b->id }}" {{ ($filters['ghorfe_id'] ?? '') == $b->id ? 'selected' : '' }}>
                                    {{ $b->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-12 col-md-2 text-end ms-auto">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">
                        <i class="fa fa-filter me-1"></i> اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sender Numbers Table -->
    <div class="card border-0 shadow-sm" style="background: #ffffff !important;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: #1e293b !important;">
                    <thead class="table-light">
                        <tr style="color: #475569 !important; font-weight: 600;">
                            <th scope="col" class="ps-3 py-3">#</th>
                            <th scope="col" class="py-3">شماره خط</th>
                            <th scope="col" class="py-3">نوع سرویس</th>
                            @if(auth()->user()?->isSuperAdmin())
                                <th scope="col" class="py-3">درگاه مخابراتی</th>
                            @endif
                            <th scope="col" class="py-3">وضعیت خط</th>
                            @if(auth()->user()?->isSuperAdmin())
                                <th scope="col" class="py-3">کسب‌وکارهای مشترک (منتسب)</th>
                            @endif
                            @if(auth()->user()?->isSuperAdmin())
                                <th scope="col" class="text-end pe-3 py-3">عملیات</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($senderNumbers as $sender)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td class="ps-3 fw-bold text-muted">{{ $sender->id }}</td>
                                <td>
                                    <span dir="ltr" class="font-monospace fw-bold fs-6 d-inline-block" style="color: #0f172a !important;">
                                        {{ $sender->number }}
                                    </span>
                                    <div class="mt-1">
                                        @if($sender->line_type === 'service')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0" style="font-size: 11px;">
                                                <i class="fa fa-check-circle me-1"></i> خط خدماتی (عبور از بلک‌لیست)
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0" style="font-size: 11px;">
                                                <i class="fa fa-info-circle me-1"></i> خط عادی (تبلیغاتی)
                                            </span>
                                        @endif
                                    </div>
                                    @if($sender->description)
                                        <div class="small text-muted fw-normal mt-1">{{ Str::limit($sender->description, 60) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($sender->type === 'otp')
                                        <span class="badge bg-warning text-dark px-2 py-1">
                                            <i class="fa fa-shield me-1"></i> پنل OTP
                                        </span>
                                    @else
                                        <span class="badge bg-primary text-white px-2 py-1">
                                            <i class="fa fa-bullhorn me-1"></i> پنل تبلیغاتی/اطلاع‌رسانی
                                        </span>
                                    @endif
                                </td>
                                @if(auth()->user()?->isSuperAdmin())
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $sender->sms_panel }}</span>
                                    </td>
                                @endif
                                <td>
                                    @if(auth()->user()?->isSuperAdmin())
                                        <form action="{{ route('admin.sms.settings.senders.toggle-status', $sender) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
                                                @if($sender->status === 'active')
                                                    <span class="badge bg-success px-2 py-1">فعال</span>
                                                @else
                                                    <span class="badge bg-secondary px-2 py-1">غیرفعال</span>
                                                @endif
                                            </button>
                                        </form>
                                    @else
                                        @if($sender->status === 'active')
                                            <span class="badge bg-success px-2 py-1">فعال</span>
                                        @else
                                            <span class="badge bg-secondary px-2 py-1">غیرفعال</span>
                                        @endif
                                    @endif
                                </td>
                                @if(auth()->user()?->isSuperAdmin())
                                    <td>
                                        @if($sender->type === 'otp')
                                            <span class="text-muted small">مشترک سراسری کل سامانه</span>
                                        @else
                                            @php
                                                $assigned = $sender->businesses;
                                            @endphp
                                            @if($assigned && $assigned->isNotEmpty())
                                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                                    @foreach($assigned->take(3) as $b)
                                                        <span class="badge bg-light text-dark border small">{{ $b->title }}</span>
                                                    @endforeach
                                                    @if($assigned->count() > 3)
                                                        <span class="badge bg-secondary small">+{{ $assigned->count() - 3 }} کسب‌وکار دیگر</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted small">تخصیص نیافته (در مخزن)</span>
                                            @endif
                                        @endif
                                    </td>
                                @endif
                                @if(auth()->user()?->isSuperAdmin())
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            @if($sender->type === 'advertising')
                                                <button type="button"
                                                        class="btn btn-outline-primary open-share-line-modal"
                                                        title="تخصیص اشتراکی به کسب‌وکارها"
                                                        data-action="{{ route('admin.sms.settings.senders.assign-businesses', $sender) }}"
                                                        data-number="{{ $sender->number }}"
                                                        data-assigned="{{ json_encode($sender->businesses->pluck('id')->values()) }}">
                                                    <i class="fa fa-share-alt me-1"></i> تخصیص به کسب‌وکارها
                                                </button>
                                            @endif
                                            <a href="{{ route('admin.sms.settings.senders.edit', $sender) }}" class="btn btn-outline-secondary" title="ویرایش">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.sms.settings.senders.destroy', $sender) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این خط از مخزن اطمینان دارید؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ auth()->user()?->isSuperAdmin() ? 8 : 5 }}" class="text-center text-muted py-4">
                                    هیچ خطی یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($senderNumbers->hasPages())
            <div class="card-footer bg-white border-top py-2">
                {{ $senderNumbers->links() }}
            </div>
        @endif
    </div>
</div>

@if(auth()->user()?->isSuperAdmin())
<!-- Modal: Assign / Share Line Across Multiple Businesses (Many-to-Many Pool Assignment) -->
<div class="modal fade" id="shareLineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="shareLineForm" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        تخصیص اشتراکی خط <span class="font-monospace text-primary" id="shareLineNumber" dir="ltr"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">
                        می‌توانید این خط را به یک یا چند کسب‌وکار به صورت مشترک اختصاص دهید تا در فهرست خطوط مجاز آن‌ها قرار گیرد:
                    </p>
                    <div class="mb-2">
                        <input type="text" id="shareLineBizSearch" class="form-control form-control-sm" placeholder="جستجوی نام کسب‌وکار...">
                    </div>
                    <div class="border rounded p-2 bg-light" style="max-height: 260px; overflow-y: auto;" id="shareLineCheckboxes">
                        @foreach($businesses as $b)
                            <div class="form-check py-1 border-bottom share-biz-item" data-title="{{ $b->title }}">
                                <input class="form-check-input share-biz-check" type="checkbox" name="business_ids[]" value="{{ $b->id }}" id="shareBiz_{{ $b->id }}">
                                <label class="form-check-label small fw-bold d-block cursor-pointer" for="shareBiz_{{ $b->id }}">
                                    {{ $b->title }} <span class="text-muted font-monospace">(#{{ $b->id }})</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-check me-1"></i> ذخیره تخصیص خط
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
@if(auth()->user()?->isSuperAdmin())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('shareLineModal');
    const modal = (modalEl && typeof bootstrap !== 'undefined') ? new bootstrap.Modal(modalEl) : null;
    const form = document.getElementById('shareLineForm');
    const numSpan = document.getElementById('shareLineNumber');
    const searchInput = document.getElementById('shareLineBizSearch');

    document.querySelectorAll('.open-share-line-modal').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = this.getAttribute('data-action');
            const number = this.getAttribute('data-number');
            let assigned = [];
            try {
                assigned = JSON.parse(this.getAttribute('data-assigned') || '[]');
            } catch (e) {
                assigned = [];
            }

            if (form) form.action = action;
            if (numSpan) numSpan.textContent = number;

            document.querySelectorAll('.share-biz-check').forEach(function (chk) {
                chk.checked = assigned.includes(parseInt(chk.value, 10));
            });

            if (modal) {
                modal.show();
            } else if (typeof jQuery !== 'undefined') {
                jQuery(modalEl).modal('show');
            }
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.share-biz-item').forEach(function (row) {
                const title = (row.getAttribute('data-title') || '').toLowerCase();
                row.style.display = (!q || title.includes(q)) ? '' : 'none';
            });
        });
    }
});
</script>
@endif
@endsection
