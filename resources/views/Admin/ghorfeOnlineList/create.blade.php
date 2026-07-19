@extends('Admin.master')
@section('title')ایجاد مغازه دیجیتال@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post" action="{{route('ghorfeOnlineList.store')}}"
            enctype="multipart/form-data">
            @csrf
            @include('Admin.layouts.errors')
            <div class="row">
                <div class="col-12 col-md-4 mb-2">
                    <label>عنوان فارسی(این عنوان در سایت نمایش داده میشود)</label>
                    <input type="text" value="{{old('title')}}" class="form-control" name="title" required>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <label>نشان اینترنتی مغازه (لطفا به انگلیسی وارد کنید)</label>
                    <input type="text" value="{{old('en_title')}}" class="form-control" name="en_title" required>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <label>انتخاب مالک مغازه</label>
                    <select class="form-control select2" name="user_id" required>
                        <option value="">انتخاب کنید</option>
                        @foreach ($employees as $employee)
                        <option value="{{$employee->user->id}}">{{$employee->user->name.' '.$employee->user->family}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <label>انتخاب نوع حوزه کاری</label>
                    <select class="form-control" name="type_work" required>
                        @foreach (App\Models\GhorfeOnlineList::TYPE as $key => $item)
                        <option value="{{$key}}">{{$item}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <label>عدد پیامک تخصیص داده شده(اعداد را با , از هم جدا کنید)</label>
                    <input type="text" value="{{old('sms_code')}}" required class="form-control" name="sms_code">
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <label>شماره تماس</label>
                    <input type="text" value="{{old('call')}}" class="form-control" name="call">
                </div>

                <div class="col-12 mb-2">
                    <label>آدرس</label>
                    <input type="text" value="{{old('address')}}" class="form-control" name="address">
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <label>دامنه فعال</label>
                    <input type="text" value="{{old('domain_active')}}" class="form-control" name="domain_active">
                    <label>مانند نمونه: 'lalecard.ir'</label>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <label>وضعیت</label>

                    <select class="form-control" name="status">
                        <option value="0">
                            غیر فعال
                        </option>
                        <option value="1">
                            فعال
                        </option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <label>تاریخ شروع اجاره نامه</label>
                    <input type="text" name="contract_start_date" class="form-control date">
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <label>تاریخ پایان اجاره نامه</label>
                    <input type="text" name="contract_expiration_date" class="form-control date">
                </div>

                <div class="row">
                    @include('Admin.layouts.errors')
                    <div class="col-12 col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-dark-gradient text-white">
                                <h6 class="mb-0 text-center">قالب ها</h6>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    @if(isset($templates) && count($templates))
                                        @foreach($templates as $template)
                                            @if($template->active)
                                                <div class="col-md-3 mb-3">
                                                    <label class="template-label w-100">
                                                        <input type="radio"
                                                               name="template_id"
                                                               value="{{ $template->id }}"
                                                               class="d-none template-radio"
                                                               id="template_{{ $template->id }}"
                                                            {{ $template->id ? 'checked' : '' }}>

                                                        <div class="template-card p-3 text-center border rounded">
                                                            <img src="{{asset('img/upload.jpg')}}"
                                                                 class="img-fluid mb-2"
                                                                 style="height: 32px">
                                                            <h6 class="font-weight-bold">{{ $template->name }}</h6>
                                                            <strong>{{$template->type}}</strong>

                                                            @if($template->menus)
                                                                <ul class="list-unstyled small text-muted mt-2 mb-2">
                                                                    @foreach($template->menus as $menu)
                                                                        <li>✔ {{ $menu->title }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>

                                            @endif
                                        @endforeach
                                    @else
                                        <p class="text-muted text-center w-100">پکیجی وجود ندارد</p>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="row">
                    @include('Admin.layouts.errors')
                    <div class="col-12 col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-dark-gradient text-white">
                                <h6 class="mb-0 text-center">پکیج ها</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if(isset($packages) && count($packages))
                                        @foreach($packages as $package)
                                            @if($package->is_active)

                                                <div class="col-md-3 mb-3">
                                                    <label class="package-label w-100">
                                                        <input type="radio"
                                                               name="shahin_package_id"
                                                               value="{{ $package->id }}"
                                                               class="d-none package-radio"
                                                               id="package_{{ $package->id }}"
                                                            {{ $package->id ? 'checked' : '' }}>

                                                        <div class="package-card p-3 text-center border rounded">

                                                            <img src="{{ asset($package->image ?? 'images/default-package.png') }}"
                                                                 class="img-fluid mb-2"
                                                                 style="height:80px; object-fit:cover; border-radius:10px;">

                                                            <h6 class="font-weight-bold">{{ $package->name }}</h6>

                                                            <small class="text-muted d-block">
                                                                {{ $package->type }} | {{ $package->duration_days }} روز
                                                            </small>

                                                            @php
                                                                $features = is_array($package->features)
                                                                    ? $package->features
                                                                    : json_decode($package->features, true);
                                                            @endphp

                                                            @if($features)
                                                                <ul class="list-unstyled small text-muted mt-2 mb-2">
                                                                    @foreach($features as $feature)
                                                                        <li>✔ {{ $feature }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif

                                                            <div class="text-success font-weight-bold">
                                                                {{ number_format($package->price) }} تومان
                                                            </div>

                                                        </div>
                                                    </label>
                                                </div>

                                            @endif
                                        @endforeach
                                    @else
                                        <p class="text-muted text-center w-100">پکیجی وجود ندارد</p>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4 mt-2">
                    <div class="card">
                        <div id="logo_src" class="show">
                            <img class="card-img-top" height="150px">
                        </div>
                        <div class="card-body">
                            <label>لوگو</label>
                            <br>
                            <label for="logo" id="up-label" class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                                                           aria-hidden="true"></i> انتخاب تصویر </label>
                            <input name="logo" type="file" id="logo"
                                   class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white"
                                   accept=".jpg,.jpeg,.png">
                            <input type="hidden" id="logo" class="path" name="urllogo">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <input type="submit" class="btn btn-success border-0" value="ثبت">
                </div>

            </div>
        </form>
    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@endsection
