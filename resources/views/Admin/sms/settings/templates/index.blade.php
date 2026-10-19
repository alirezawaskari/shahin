@extends('Admin.master')

@section('title', 'مدیریت الگوهای پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">الگوهای پیامک (Templates)</h3>
            <p class="text-muted small mb-0">تعریف متن‌های پیش‌فرض، متغیرهای پویا و تاریخچه تغییرات</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.settings.templates.index') }}" class="d-flex flex-wrap gap-2 m-0">
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
            <a href="{{ route('admin.sms.settings.templates.create', $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : []) }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ایجاد الگوی جدید
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

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.sms.settings.templates.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="جستجو در عنوان یا متن الگو..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-6 col-md-3">
                    <select name="service_type" class="form-select">
                        <option value="all">همه انواع سرویس</option>
                        <option value="normal" {{ ($filters['service_type'] ?? '') === 'normal' ? 'selected' : '' }}>عادی</option>
                        @if(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()))
                        <option value="otp" {{ ($filters['service_type'] ?? '') === 'otp' ? 'selected' : '' }}>کد تایید (OTP)</option>
                        @endif
                        <option value="advertising" {{ ($filters['service_type'] ?? '') === 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="all">همه وضعیت‌ها</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="group_id" class="form-select">
                        <option value="all">همه گروه‌ها</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp->id }}" {{ ($filters['group_id'] ?? '') == $grp->id ? 'selected' : '' }}>
                                {{ $grp->name }}
                            </option>
                            @foreach($grp->children as $sub)
                                <option value="{{ $sub->id }}" {{ ($filters['group_id'] ?? '') == $sub->id ? 'selected' : '' }}>
                                    — {{ $sub->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 text-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fa fa-filter me-1"></i> فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">عنوان الگو</th>
                            <th scope="col">گروه / زیرگروه</th>
                            <th scope="col">نوع سرویس</th>
                            <th scope="col">وضعیت</th>
                            <th scope="col">متن الگو</th>
                            <th scope="col">ویرایش‌ها</th>
                            <th scope="col">ایجادکننده</th>
                            <th scope="col" class="text-end pe-3">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $tmpl)
                            <tr>
                                <td class="ps-3">{{ $tmpl->id }}</td>
                                <td class="fw-bold">
                                    {{ $tmpl->title }}
                                    @if(!$business && $tmpl->business)
                                        <div class="small fw-normal text-muted">{{ $tmpl->business->title }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($tmpl->group)
                                        @if($tmpl->group->parent)
                                            <span class="badge bg-light text-dark border">
                                                {{ $tmpl->group->parent->name }} / {{ $tmpl->group->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-light text-dark border">{{ $tmpl->group->name }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted small">بدون گروه</span>
                                    @endif
                                </td>
                                <td>
                                    @if($tmpl->service_type === 'otp')
                                        <span class="badge bg-warning text-dark">OTP</span>
                                        @if($tmpl->otp_category)
                                            <span class="badge bg-light text-dark border">{{ \App\Enums\SmsOtpCategory::labelFor($tmpl->otp_category) }}</span>
                                        @endif
                                    @elseif($tmpl->service_type === 'advertising')
                                        <span class="badge bg-info text-dark">تبلیغاتی</span>
                                    @else
                                        <span class="badge bg-secondary">عادی</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.sms.settings.templates.toggle-status', $tmpl) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
                                            @if($tmpl->status === 'active')
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-secondary">غیرفعال</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="small text-muted" style="max-width: 250px;">
                                    {{ Str::limit($tmpl->content, 70) }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.sms.settings.templates.history', $tmpl) }}" class="badge bg-light text-dark border text-decoration-none" title="مشاهده تاریخچه">
                                        <i class="fa fa-history me-1"></i> {{ $tmpl->histories_count }} نگارش
                                    </a>
                                </td>
                                <td class="small text-muted">
                                    {{ $tmpl->creator?->name ?: 'سیستم' }}
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.sms.settings.templates.history', $tmpl) }}" class="btn btn-outline-secondary" title="تاریخچه و مقایسه">
                                            <i class="fa fa-history"></i>
                                        </a>
                                        <a href="{{ route('admin.sms.settings.templates.edit', $tmpl) }}" class="btn btn-outline-primary" title="ویرایش">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.sms.settings.templates.destroy', $tmpl) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این الگو اطمینان دارید؟');">
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
                                <td colspan="9" class="text-center py-4 text-muted">
                                    هیچ الگوی پیامکی یافت نشد.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($templates->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
