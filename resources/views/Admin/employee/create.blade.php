@extends('Admin.master')
@section('title')ایجاد همکار@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد همکار</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">فهرست همکاران</a></li>
                <li class="breadcrumb-item active" aria-current="page">ایجاد</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @include('Admin.layouts.errors')

        <form method="post" action="{{ route('employee.store') }}">
            @csrf
            <div class="row">
                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                    اطلاعات فردی
                </div>

                <div class="col-12 mb-3">
                    {{-- An existing customer is promoted, not duplicated: the phone number is
                         the identity, so store() adopts a users row that already has it. --}}
                    <div class="alert alert-info mb-0">
                        اگر کاربری با این شماره موبایل از قبل در سامانه باشد، همان کاربر به همکار تبدیل می‌شود و کاربر تکراری ساخته نمی‌شود.
                    </div>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>نام *</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نام خانوادگی *</label>
                    <input type="text" class="form-control" name="family" value="{{ old('family') }}" required>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>موبایل *</label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                           placeholder="09121234567" required>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>کد ملی</label>
                    <input type="text" class="form-control" name="melicode" value="{{ old('melicode') }}">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>جنسیت</label>
                    <select class="form-control" name="sex">
                        <option value="0" @selected(old('sex') === '0')>مرد</option>
                        <option value="1" @selected(old('sex') === '1')>زن</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>شماره تماس اضطراری</label>
                    <input type="text" class="form-control" name="call" value="{{ old('call') }}">
                </div>

                <div class="col-12 mb-3">
                    <label>آدرس</label>
                    <input type="text" class="form-control" name="address" value="{{ old('address') }}">
                </div>

                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                    اطلاعات همکاری
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>عنوان شغلی</label>
                    <input type="text" class="form-control" name="work_title" value="{{ old('work_title') }}">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>شماره تماس کاری</label>
                    <input type="text" class="form-control" name="business_phone" value="{{ old('business_phone') }}">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>انتخاب سرپرست</label>
                    <select class="form-control selectMulti" name="parent_id">
                        <option value="">ندارد</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id') == $parent->id)>{{ $parent->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>وضعیت همکار *</label>
                    <select class="form-control" name="status" required>
                        <option value="1" @selected(old('status', '1') === '1')>فعال</option>
                        <option value="0" @selected(old('status') === '0')>غیرفعال</option>
                    </select>
                    <small class="text-muted">همکار فعال، دسترسی پنل (level = admin) می‌گیرد.</small>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>وضعیت نمایش *</label>
                    <select class="form-control" name="show_status" required>
                        <option value="1" @selected(old('show_status', '1') === '1')>نمایان</option>
                        <option value="0" @selected(old('show_status') === '0')>پنهان</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>نقش کاربر</label>
                    <select class="form-control selectMulti" name="role_id">
                        <option value="">بدون نقش</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->display_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>نمایندگی</label>
                    <select class="form-control selectMulti" name="admin_representation_id">
                        <option value="">بدون نمایندگی (دفتر تهران)</option>
                        @foreach($representations as $representation)
                            <option value="{{ $representation->id }}" @selected(old('admin_representation_id') == $representation->id)>{{ $representation->fa_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label>شعبه</label>
                    <select class="form-control selectMulti" name="center_id">
                        <option value="">بدون شعبه</option>
                        @foreach($centers as $center)
                            <option value="{{ $center->id }}" @selected(old('center_id') == $center->id)>{{ $center->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>رمز عبور</label>
                    <input type="password" class="form-control" name="password">
                    <small class="text-muted">خالی بگذارید تا رمز تصادفی ساخته شود (ورود با کد پیامکی).</small>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>تکرار رمز عبور</label>
                    <input type="password" class="form-control" name="password_confirmation">
                </div>

                <div class="col-12 my-3 d-flex justify-content-end">
                    <input type="submit" class="btn btn-success border-0" value="ایجاد همکار">
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
@stop
