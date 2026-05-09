@extends('Admin.master')
@section('title')ایجاد آیتم@endsection

@section('content')

<div class="page-header">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="/service/{{ $service->slug }}/edit">خدمت<span class="green"> {{
                            $service->title }}</span></a></li>
                <li class="breadcrumb-item active" aria-current="page">ثبت آیتم جدید</li>
            </ol>
        </nav>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form class="form-horizontal" method="post" action="{{route('service.serviceItem.store' , [$service->slug])}}"
            enctype="multipart/form-data">
            @csrf
            @include('Admin.layouts.errors')
            <div class="container">
                <div class="row">
                    <div class="col-12 col-md-4 mb-4">
                        <label>عنوان</label>
                        <input type="text" value="{{old('title')}}" class="form-control"
                            placeholder="لطفا عنوان را وارد کنید" name="title" required>
                    </div>

                    <div class="col-12 col-md-8 mb-4">
                        <label>عنوان لاتین (توجه داشته باشید که این عنوان در url نمایش داده میشود و حتما باید به صورت
                            انگلیسی وارد شود.)</label>
                        <input type="text" value="{{old('en_title')}}" class="form-control"
                            placeholder="لطفا عنوان انگلیسی را وارد کنید" name="en_title" required>
                    </div>

                    <div class="col-12 col-md-12 mb-4">
                        <label>توضیحات</label>
                        <input type="text" value="{{old('description')}}" class="form-control" name="description"
                            required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت</label>

                        <select class="form-control" name="status">
                            <option selected="selected" value="0">
                                فعال
                            </option>
                            <option value="1">
                                غیر فعال
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>نمایش در سایت</label>

                        <select class="form-control" name="isShow">
                            <option selected="selected" value="0">
                                غیر فعال
                            </option>
                            <option value="1">
                                فعال
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان منو</label>
                        <input id="menu_title_lbl" type="text" class="form-control mb-2" name="menu_title"
                            placeholder="لطفا عنوان منو را وارد کنید">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_menu" value="1"> نمایش در منو
                            </label>
                            @if($errors->has('is_menu'))<span
                                class="help-block sub-error">{{$errors->first('is_menu')}}</span>@endif
                        </div>
                    </div>
                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
@stop
