@extends('Admin.master')

@section('title', 'ویرایش گروه پیامک: ' . $group->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">ویرایش گروه پیامک: {{ $group->name }}</h3>
            <p class="text-muted small mb-0">به‌روزرسانی نام و ساختار درختی گروه</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.sms.settings.groups.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-right me-1"></i> بازگشت به فهرست
            </a>
        </div>
    </div>

    @include('Admin.sms.settings.navigation')

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm" style="max-width: 700px;">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">مشخصات گروه</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sms.settings.groups.update', $group) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label fw-bold">نام گروه <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $group->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">نامک انگلیسی (Slug)</label>
                    <input type="text" name="slug" class="form-control font-monospace" value="{{ old('slug', $group->slug) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">گروه والد</label>
                    <select name="parent_id" class="form-select">
                        <option value="">— گروه اصلی (سطح ریشه) —</option>
                        @foreach($rootGroups as $rg)
                            <option value="{{ $rg->id }}" {{ (old('parent_id', $group->parent_id) == $rg->id) ? 'selected' : '' }}>
                                {{ $rg->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-check me-1"></i> به‌روزرسانی گروه
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
