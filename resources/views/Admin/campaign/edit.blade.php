@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="page-header">
        <div>
            <h3>ایجاد کمپین</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">کمپین</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link active " id="firstData-tab" data-toggle="tab" href="#home" role="tab"
                       aria-controls="home" aria-selected="true"><i class="fa fa-list"></i>اطلاعات اولیه</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link  " id="firstData-tab" data-toggle="tab" href="#form" role="tab"
                       aria-controls="rules" aria-selected="true"><i class="fa fa-file-text-o"></i>فرم</a>
                </li>
                <li class="nav-item  flex-fill text-center nav-item-service">
                    <a class="nav-link  " id="firstData-tab" data-toggle="tab" href="#collaboration" role="tab"
                       aria-controls="rules" aria-selected="true"><i class="fa fa-file-text-o"></i>شرکت کنندگان</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div
                    class="tab-pane fade show active"
                    id="home"
                    role="tabpanel"
                >
                    <div class="card position-relative">
                        <div class="card-body">
                            <div class="container">
                                <div class="row justify-content-center mt-5">
                                    <div class="col-md-12">
                                        <form action="{{ route('campaign.update',['campaign' => $campaign]) }}" method="POST">
                                            @csrf
                                            @method('put')
                                            <div class="form-row border">
                                                <div
                                                    class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">
                                                    اطلاعات کمپین
                                                </div>
                                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                    <label>نام پویش</label>
                                                    <input class="form-control" name="name" value="{{$campaign->name}}"/>
                                                </div>
                                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                    <label>موضوع پویش</label>
                                                    <input class="form-control" name="subject" value="{{$campaign->subject}}"/>
                                                </div>
                                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                    <label>ظرفیت پویش</label>
                                                    <input class="form-control" name="capacity" value="{{$campaign->capacity}}"/>
                                                </div>
                                                <div class="col-12 form-group mb-2">
                                                    <label>توضیح</label>
                                                    <textarea name="content">{{$campaign->content}}</textarea>
                                                </div>
                                                <div class="col-12 col-md-6 mt-2">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            @if($campaign->image)
                                                                <div>
                                                                    <img height="150px" src="{{$campaign->image}}" class="card-img-top">
                                                                </div>
                                                            @endif
                                                            <label>تصویر بالا</label>
                                                            <br>
                                                            <label for="profile_image" id="profile_up_label"
                                                                   class="btn badge-secondary">
                                                                <i class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                                انتخاب تصویر
                                                            </label>
                                                            <input name="profile_image" type="file" id="profile_image"
                                                                   class="filestyle file-upload file_upload_ajax file-up"
                                                                   data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                            <input type="hidden" id="profile_image" class="path"
                                                                   name="url_profile_image">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6 mt-2">
                                                    <div class="card">
                                                        @if($campaign->image_header)
                                                            <div>
                                                                <img height="150px" src="{{$campaign->image_header}}" class="card-img-top">
                                                            </div>
                                                        @endif
                                                        <div class="card-body">
                                                            <label>تصویر نمایه</label>
                                                            <br>
                                                            <label for="header_image" id="header_up_label"
                                                                   class="btn badge-secondary">
                                                                <i class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                                انتخاب تصویر
                                                            </label>
                                                            <input name="header_image" type="file" id="header_image"
                                                                   class="filestyle file-upload file_upload_ajax file-up"
                                                                   data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                            <input type="hidden" id="header_image" class="path"
                                                                   name="url_header_image">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label for="start_date" class="form-label fw-bold mb-2">تاریخ شروع پویش</label>
                                                    <date-picker
                                                        id="start_date"
                                                        name="start_date"
                                                        placeholder="تاریخ شروع پویش"
                                                        locale="fa,en"
                                                        format="YYYY-M-D"
                                                        value="{{ $campaign->start_date }}"
                                                    ></date-picker>
                                                </div>
                                                <div class="col-12 col-md-6 mb-3">
                                                    <label for="end_date" class="form-label fw-bold mb-2">تاریخ پایان پویش</label>
                                                    <date-picker
                                                        id="end_date"
                                                        name="end_date"
                                                        placeholder="تاریخ پایان پویش"
                                                        locale="fa,en"
                                                        format="YYYY-M-D"
                                                        value="{{ $campaign->end_date }}"
                                                    ></date-picker>
                                                </div>

                                                <div class="form-group col-md-12 mt-2">
                                                    <button type="submit" class="btn btn-success">ثبت</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="tab-pane fade"
                    id="form"
                    role="tabpanel"
                >
                    <div class="card position-relative">
                        <div class="card-body">
                            <div class="container">
                                <div class="row justify-content-center mt-5">
                                    <div class="col-md-12">
                                        <form action="{{route('campaign.store')}}" method="post">
                                            @csrf
                                            <div class="form-row border">

                                                <div
                                                    class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">
                                                    اطلاعات فرم
                                                </div>
                                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                    <label>نام پویش</label>
                                                    <input class="form-control" name="name"/>
                                                </div>
                                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                    <label>موضوع پویش</label>
                                                    <input class="form-control" name="subject"/>
                                                </div>
                                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                    <label>ظرفیت پویش</label>
                                                    <input class="form-control" name="capacity"/>
                                                </div>
                                                <div class="col-12 form-group mb-2">
                                                    <label>توضیح</label>
                                                    <textarea name="content"></textarea>
                                                </div>
                                                <div class="col-12 col-md-6 mt-2">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <label>تصویر نمایه</label>
                                                            <br>
                                                            <label for="profile_image" id="profile_up_label"
                                                                   class="btn badge-secondary">
                                                                <i class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                                انتخاب تصویر
                                                            </label>
                                                            <input name="profile_image" type="file" id="profile_image"
                                                                   class="filestyle file-upload file_upload_ajax file-up"
                                                                   data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                            <input type="hidden" id="profile_image" class="path"
                                                                   name="url_profile_image">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6 mt-2">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <label>تصویر بالا</label>
                                                            <br>
                                                            <label for="header_image" id="header_up_label"
                                                                   class="btn badge-secondary">
                                                                <i class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                                انتخاب تصویر
                                                            </label>
                                                            <input name="header_image" type="file" id="header_image"
                                                                   class="filestyle file-upload file_upload_ajax file-up"
                                                                   data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                            <input type="hidden" id="header_image" class="path"
                                                                   name="url_header_image">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-6 mb-2">
                                                    <date-picker
                                                        placeholder="تاریخ شروع پویش"
                                                        locale="fa,en"
                                                        format="YYYY-M-D"
                                                        name="start_date"
                                                    >
                                                    </date-picker>
                                                </div>
                                                <div class="col-12 col-md-6 mb-2">
                                                    <date-picker
                                                        placeholder="تاریخ پایان پویش"
                                                        locale="fa,en"
                                                        format="YYYY-M-D"
                                                        name="end_date"
                                                    >
                                                    </date-picker>
                                                </div>
                                                <div class="form-group col-md-12 mt-2">
                                                    <button type="submit" class="btn btn-success">ثبت</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div
                    class="tab-pane fade"
                    id="collaboration"
                    role="tabpanel"
                >
                    <div class="card position-relative">
                        <div class="card-body">
                            <div class="container">
                                <div class="row justify-content-center mt-5">
                                    <div class="col-md-12">
                                        <form
                                            action="{{route('campaign.create.company',['campaign_id' => $campaign->id])}}"
                                            method="POST"
                                            enctype="multipart/form-data"
                                        >
                                            @csrf
                                            <div class="form-row border">
                                                <div
                                                    class="card-header col-md-12 border border-right-0 border-left-0  text-center ">
                                                    اطلاعات شرکت کنندگان
                                                </div>
                                                <div id="companies" class="container-fluid">
                                                    <div class="row">
                                                        <table class="vuetable table table-bordered table-striped table-responsive-md w-100"
                                                               id="data-table-factors">
                                                            <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>نام</th>
                                                                <th>ادرس</th>
                                                                <th>لوگو</th>
                                                                <th>عملیات</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($campaign->campaign_collaboration as $campaign_collaboration)
                                                                    <tr>
                                                                        <td>{{$loop->iteration}}</td>
                                                                        <td>{{$campaign_collaboration->name}}</td>
                                                                        <td>{{$campaign_collaboration->url}}</td>
                                                                        <td>
                                                                            <a href="{{ asset($campaign_collaboration->logo) }}" target="_blank">
                                                                                <img src="{{ asset($campaign_collaboration->logo) }}" width="70px" alt="">
                                                                            </a>
                                                                        </td>
                                                                        <td>
                                                                            <a
                                                                                class="btn btn-danger rounded-1"
                                                                                href="{{ route('campaign.delete.company',['campaign_id' => $campaign_collaboration->id]) }}"
                                                                                target="_blank"
                                                                            >
                                                                                حذف
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <div id="companies" class="container-fluid">
                                                    <div class="row" id="company">
                                                        <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                            <label>نام</label>
                                                            <input class="form-control" name="name_company[]"/>
                                                        </div>
                                                        <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                                            <label>وب سایت</label>
                                                            <input class="form-control" name="url_company[]"/>
                                                        </div>
                                                        <div class="col-12 col-md-4 mt-2">
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <label>تصویر نمایه</label>
                                                                    <label for="profile_image" id="profile_up_label"
                                                                           class="btn badge-secondary">
                                                                        <i class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                                        انتخاب تصویر
                                                                    </label>
                                                                    <input
                                                                        name="logo_company[]"
                                                                        type="file"
                                                                        id="profile_image"
                                                                        class="filestyle file-upload file_upload_ajax file-up"
                                                                        data-buttonname="btn-white"
                                                                        accept=".jpg,.jpeg,.png"
                                                                    >
                                                                    <input
                                                                        type="hidden"
                                                                        id="profile_image"
                                                                        class="path"
                                                                        name="logo_company_url[]">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-2">
                                                    <button type="button" id="addCompany" class="btn btn-info">افزودن</button>
                                                </div>
                                                <div class="form-group col-md-12 mt-2">
                                                    <button type="submit" class="btn btn-success">ثبت</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/campaign.js') }}"></script>
    <script src="/assets/script/ckeditor/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('content', ck_uploader);
    </script>
    <script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@stop
