@extends('Admin.master')

@section('title', 'مدیریت پنل‌های پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">پنل‌های پیامک</h3>
            <p class="text-muted small mb-0">پیکربندی ارائه‌دهندگان سرویس پیامک، اتصال و تنظیمات ارسال و دریافت</p>
        </div>
        <a href="{{ route('admin.sms.settings.panels.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> افزودن پنل جدید
        </a>
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
            <form action="{{ route('admin.sms.settings.panels.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس نام یا توضیحات..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-6 col-md-3">
                    <select name="type" class="form-select">
                        <option value="all">همه انواع سرویس</option>
                        <option value="otp" {{ ($filters['type'] ?? '') === 'otp' ? 'selected' : '' }}>کد تایید (OTP)</option>
                        <option value="normal-advertising" {{ ($filters['type'] ?? '') === 'normal-advertising' ? 'selected' : '' }}>تبلیغاتی / عادی</option>
                        <option value="all" {{ ($filters['type'] ?? '') === 'all' ? 'selected' : '' }}>سراسری / چندمنظوره</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select">
                        <option value="all">همه وضعیت‌ها</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 text-end">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="fa fa-filter me-1"></i> فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Panels Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3">#</th>
                            <th scope="col">نام پنل</th>
                            <th scope="col">نوع سرویس</th>
                            <th scope="col">وضعیت</th>
                            <th scope="col">آدرس اتصال / درگاه</th>
                            <th scope="col">خط پیش‌فرض</th>
                            <th scope="col">تاریخ ثبت</th>
                            <th scope="col" class="text-end pe-3">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($panels as $panel)
                            <tr>
                                <td class="ps-3">{{ $panel->id }}</td>
                                <td class="fw-bold">
                                    {{ $panel->name }}
                                    @if($panel->description)
                                        <div class="small text-muted fw-normal">{{ Str::limit($panel->description, 40) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($panel->type === 'otp')
                                        <span class="badge bg-warning text-dark">OTP خدماتی</span>
                                    @elseif($panel->type === 'normal-advertising' || $panel->type === 'advertising')
                                        <span class="badge bg-info text-dark">عادی / تبلیغاتی</span>
                                    @else
                                        <span class="badge bg-secondary">چندمنظوره</span>
                                    @endif
                                </td>
                                <td>
                                    <form action="{{ route('admin.sms.settings.panels.toggle-status', $panel) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
                                            @if($panel->status === 'active')
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-secondary">غیرفعال</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="font-monospace small text-muted">
                                    {{ $panel->getConnectionSetting('base_url') ?: 'پیش‌فرض درگاه' }}
                                </td>
                                <td class="font-monospace small">
                                    {{ $panel->getSendSetting('default_sender') ?: '—' }}
                                </td>
                                <td class="small text-muted">
                                    {{ verta($panel->created_at)->format('Y/m/d') }}
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.sms.settings.panels.edit', $panel) }}" class="btn btn-outline-primary" title="ویرایش">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.sms.settings.panels.destroy', $panel) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این پنل پیامک مطمئن هستید؟');">
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
                                    هیچ پنل پیامکی تعریف نشده است.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($panels->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $panels->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
