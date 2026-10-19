@extends('Admin.master')

@section('title', 'مدیریت گروه‌بندی پیام‌ها')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark">گروه‌بندی پیام‌ها</h3>
            <p class="text-muted small mb-0">سازمان‌دهی الگوها و پیام‌ها در دسته‌ها و زیرگروه‌های موضوعی</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.settings.groups.index') }}" class="d-flex flex-wrap gap-2 m-0">
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
            <a href="{{ route('admin.sms.settings.groups.create', $business && auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : []) }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus ms-2" style="margin-left: 6px !important; margin-right: 0 !important;"></i> افزودن گروه جدید
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


    <div class="row g-4">
        @forelse($groups as $group)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center" style="border-bottom: 2px solid #f1f5f9;">
                        <div>
                            <h5 class="fw-bold mb-1" style="color: #0f172a !important;">
                                <i class="fa fa-folder text-primary ms-2" style="margin-left: 8px !important; margin-right: 0 !important;"></i>{{ $group->name }}
                            </h5>
                            <span class="font-monospace small" style="color: #64748b !important; direction: ltr; display: inline-block;">
                                ({{ $group->slug }})
                            </span>
                            @if(!$business && $group->business)
                                <span class="badge bg-light text-dark border ms-1">{{ $group->business->title }}</span>
                            @endif
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('admin.sms.settings.groups.edit', $group) }}" class="btn btn-outline-secondary" title="ویرایش گروه">
                                <i class="fa fa-pencil"></i>
                            </a>
                            <form action="{{ route('admin.sms.settings.groups.destroy', $group) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف گروه «{{ $group->name }}» مطمئن هستید؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="حذف گروه">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom d-flex justify-content-between small" style="background: #f8fafc; color: #334155 !important;">
                            <span>تعداد الگوهای مستقیم: <strong class="text-dark">{{ $group->templates_count }}</strong></span>
                            <span>زیرگروه‌ها: <strong class="text-dark">{{ $group->children->count() }}</strong></span>
                        </div>
                        <ul class="list-group list-group-flush mb-0">
                            @forelse($group->children as $subgroup)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3" style="background: #ffffff;">
                                    <div>
                                        <i class="fa fa-angle-left text-muted ms-2" style="margin-left: 6px !important; margin-right: 0 !important;"></i>
                                        <span class="fw-bold small text-dark" style="color: #1e293b !important;">{{ $subgroup->name }}</span>
                                        <span class="font-monospace small ms-1" style="color: #64748b !important; direction: ltr; display: inline-block;">({{ $subgroup->slug }})</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark border small">{{ $subgroup->templates_count }} الگو</span>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.sms.settings.groups.edit', $subgroup) }}" class="btn btn-link p-0 text-secondary ms-2" title="ویرایش">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.sms.settings.groups.destroy', $subgroup) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف زیرگروه «{{ $subgroup->name }}» اطمینان دارید؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link p-0 text-danger" title="حذف">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center py-3 small" style="background: #ffffff;">
                                    بدون زیرگروه
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-top p-2 text-center">
                        <a href="{{ route('admin.sms.settings.groups.create', ['parent_id' => $group->id]) }}" class="small text-decoration-none fw-bold text-primary">
                            <i class="fa fa-plus ms-2" style="margin-left: 6px !important; margin-right: 0 !important;"></i> افزودن زیرگروه به این دسته
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center bg-white">
                    <p class="text-muted mb-3">هنوز هیچ گروهی تعریف نشده است.</p>
                    <div>
                        <a href="{{ route('admin.sms.settings.groups.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus ms-2" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ایجاد اولین گروه
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
