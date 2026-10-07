@extends('Admin.master')

@section('title', 'مدیریت گروه‌بندی پیام‌ها')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">گروه‌بندی پیام‌ها</h3>
            <p class="text-muted small mb-0">سازمان‌دهی الگوها و پیام‌ها در دسته‌ها و زیرگروه‌های موضوعی</p>
        </div>
        <a href="{{ route('admin.sms.settings.groups.create') }}" class="btn btn-primary">
            <i class="fa fa-plus me-1"></i> افزودن گروه جدید
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

    <div class="row g-4">
        @forelse($groups as $group)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <span class="p-2 rounded bg-primary bg-opacity-10 text-primary me-2">
                                <i class="fa fa-folder-open"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0">{{ $group->name }}</h6>
                                <small class="text-muted font-monospace">{{ $group->slug }}</small>
                            </div>
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
                        <div class="p-3 border-bottom bg-light d-flex justify-content-between small text-muted">
                            <span>تعداد الگوهای مستقیم: <strong>{{ $group->templates_count }}</strong></span>
                            <span>زیرگروه‌ها: <strong>{{ $group->children->count() }}</strong></span>
                        </div>
                        <ul class="list-group list-group-flush">
                            @forelse($group->children as $subgroup)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3">
                                    <div>
                                        <i class="fa fa-angle-left text-muted me-1"></i>
                                        <span class="fw-bold small">{{ $subgroup->name }}</span>
                                        <span class="font-monospace text-muted small ms-1">({{ $subgroup->slug }})</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark border small">{{ $subgroup->templates_count }} الگو</span>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.sms.settings.groups.edit', $subgroup) }}" class="btn btn-link p-0 text-muted me-1" title="ویرایش">
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
                                <li class="list-group-item text-muted text-center py-3 small">
                                    بدون زیرگروه
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-0 py-2 text-end">
                        <a href="{{ route('admin.sms.settings.groups.create', ['parent_id' => $group->id]) }}" class="small text-decoration-none">
                            <i class="fa fa-plus me-1"></i> افزودن زیرگروه
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center text-muted">
                    <p class="mb-3">هیچ گروه پیامکی تعریف نشده است.</p>
                    <div>
                        <a href="{{ route('admin.sms.settings.groups.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus me-1"></i> ایجاد اولین گروه
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
