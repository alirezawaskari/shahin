@extends('Admin.master')

@section('title', 'مدیریت خطوط اختصاصی پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">خطوط اختصاصی پیامک</h3>
            <p class="text-muted small mb-0">مدیریت شماره‌های فرستنده OTP و تبلیغاتی و تخصیص به کسب‌وکارها</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.sms.settings.senders.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus me-1"></i> افزودن خط جدید
            </a>
        </div>
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

    @if($business)
        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="fa fa-info-circle fa-lg me-2"></i>
            <div>
                شما در حال مشاهده خطوط اختصاص داده شده به کسب‌وکار <strong>{{ $business->title }}</strong> هستید.
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4" style="background: #ffffff !important;">
        <div class="card-body p-3">
            <form action="{{ route('admin.sms.settings.senders.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="جستجو بر اساس شماره یا پنل..." value="{{ $filters['search'] ?? '' }}">
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
                @if(!$business)
                    <div class="col-12 col-md-3">
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
                <div class="col-12 col-md-2 text-end">
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
                            <th scope="col" class="py-3">نوع</th>
                            <th scope="col" class="py-3">پنل درگاه</th>
                            <th scope="col" class="py-3">وضعیت</th>
                            <th scope="col" class="py-3">کسب‌وکارهای منتسب</th>
                            <th scope="col" class="py-3">وضعیت قرارداد</th>
                            <th scope="col" class="text-end pe-3 py-3">عملیات</th>
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
                                    @if($sender->description)
                                        <div class="small text-muted fw-normal mt-1">{{ Str::limit($sender->description, 45) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($sender->type === 'otp')
                                        <span class="badge bg-warning text-dark px-2 py-1">
                                            <i class="fa fa-shield me-1"></i> OTP (سراسری)
                                        </span>
                                    @else
                                        <span class="badge bg-primary text-white px-2 py-1">
                                            <i class="fa fa-bullhorn me-1"></i> تبلیغاتی
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $sender->sms_panel }}</span>
                                </td>
                                <td>
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
                                </td>
                                <td>
                                    @if($sender->type === 'otp')
                                        <span class="text-muted small">سراسری کل سامانه</span>
                                    @else
                                        @php
                                            $assigned = $sender->businesses;
                                        @endphp
                                        @if($assigned && $assigned->isNotEmpty())
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($assigned->take(3) as $b)
                                                    <span class="badge bg-light text-dark border small">{{ $b->title }}</span>
                                                @endforeach
                                                @if($assigned->count() > 3)
                                                    <span class="badge bg-secondary small">+{{ $assigned->count() - 3 }} دیگر</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">تخصیص داده نشده</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $cStatus = $sender->contract_status ?: 'در انتظار تایید تعهدنامه';
                                        $isApproved = ($cStatus === 'تایید شده');
                                        $isRejected = str_contains($cStatus, 'رد شده');
                                    @endphp
                                    <span class="badge {{ $isApproved ? 'bg-success' : ($isRejected ? 'bg-danger' : 'bg-warning text-dark') }} px-2 py-1">
                                        <i class="fa {{ $isApproved ? 'fa-check' : ($isRejected ? 'fa-times' : 'fa-clock-o') }} me-1"></i>
                                        {{ $cStatus }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.sms.settings.senders.edit', $sender) }}" class="btn btn-outline-secondary" title="ویرایش">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.sms.settings.senders.destroy', $sender) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این خط اختصاصی اطمینان دارید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="حذف">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    هیچ خط اختصاصی یافت نشد.
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
@endsection
