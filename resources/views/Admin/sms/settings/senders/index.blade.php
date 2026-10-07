@extends('Admin.master')

@section('title', 'مدیریت خطوط اختصاصی پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">خطوط اختصاصی پیامک</h3>
            <p class="text-muted small mb-0">مدیریت شماره‌های فرستنده OTP و تبلیغاتی و تخصیص به کسب‌وکارها</p>
        </div>
        <a href="{{ route('admin.sms.settings.senders.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> افزودن خط جدید
        </a>
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
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.sms.settings.senders.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس شماره یا پنل..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-6 col-md-2">
                    <select name="type" class="form-select">
                        <option value="all">همه انواع خطوط</option>
                        <option value="otp" {{ ($filters['type'] ?? '') === 'otp' ? 'selected' : '' }}>کد تایید (OTP)</option>
                        <option value="advertising" {{ ($filters['type'] ?? '') === 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="all">همه وضعیت‌ها</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>
                @if(!$business)
                    <div class="col-12 col-md-3">
                        <select name="ghorfe_id" class="form-select">
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
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fa fa-filter me-1"></i> فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sender Numbers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">شماره خط</th>
                            <th scope="col">نوع</th>
                            <th scope="col">پنل درگاه</th>
                            <th scope="col">وضعیت</th>
                            <th scope="col">کسب‌وکارهای منتسب</th>
                            <th scope="col">وضعیت قرارداد</th>
                            <th scope="col" class="text-end pe-3">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($senderNumbers as $sender)
                            <tr>
                                <td class="ps-3">{{ $sender->id }}</td>
                                <td class="font-monospace fw-bold fs-6">
                                    {{ $sender->number }}
                                    @if($sender->description)
                                        <div class="small text-muted fw-normal font-sans-serif">{{ Str::limit($sender->description, 35) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($sender->type === 'otp')
                                        <span class="badge bg-warning text-dark">
                                            <i class="fa fa-shield me-1"></i> OTP (سراسری)
                                        </span>
                                    @else
                                        <span class="badge bg-info text-dark">
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
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-secondary">غیرفعال</span>
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
                                    <span class="badge bg-light text-secondary border">
                                        {{ $sender->contract_status ?: 'تایید شده' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.sms.settings.senders.edit', $sender) }}" class="btn btn-outline-primary" title="ویرایش">
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    هیچ خط اختصاصی ثبت نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($senderNumbers->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $senderNumbers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
