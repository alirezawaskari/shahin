@extends('Admin.master')
@section('title')ایجاد منو جدید @endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد منو جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb flex-wrap">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="{{ route('menuSetting.index') }}">فهرست منو ها</a></li>
                <li class="breadcrumb-item active" aria-current="page">ایجاد منو جدید</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="card-header bg-dark-gradient col-12 border border-right-0 border-left-0">
            مشخصات منو
        </div>

        <form class="form-horizontal" role="form" method="post" action="{{ route('menuSetting.store') }}">
            @csrf
            @include('Admin.layouts.errors')

            <div class="row mt-3">
                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>عنوان منو <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>ایکون</label>
                    <input type="text" class="form-control" name="icon" value="{{ old('icon') }}"
                        placeholder="مثال: ti-home">
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>وضعیت نمایش در منو</label>
                    <select class="form-control" name="is_active">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>فعال</option>
                        <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-3 mb-2">
                    <label>ترتیب نمایش</label>
                    <input type="number" min="0" class="form-control" name="position" value="{{ old('position', 0) }}">
                </div>
            </div>

            <div class="alert alert-info mt-2">
                منو تا زمانی که حداقل یک زیرمنوی فعال با لینک معتبر نداشته باشد در منوی کناری نمایش داده نمی‌شود.
                پس از ثبت، به صفحه ویرایش همین منو هدایت می‌شوید تا زیرمنوها را اضافه کنید.
            </div>

            <div class="col-12 mb-3 text-left px-0">
                <a href="{{ route('menuSetting.index') }}" class="btn btn-secondary">انصراف</a>
                <input type="submit" class="btn btn-success border-0" value="ثبت">
            </div>
        </form>

    </div>
</div>
@stop
