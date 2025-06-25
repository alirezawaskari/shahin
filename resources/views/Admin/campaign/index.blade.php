@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="page-header">
        <div>
            <h3>اطلاعات کمپین</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تنظیمات</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-8 ">
                        <form action="{{route('update.campaign')}}" method="post">
                            @csrf
                            @method('PATCH')
                            <div class="form-row border">
                                <div class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">اطلاعات کمپین</div>
                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                    <label>نام پویش</label>
                                    <input class="form-control" name="name" />
                                </div>
                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                    <label>موضوع پویش</label>
                                    <input class="form-control" name="subject" />
                                </div>
                                <div class="col-12 col-md-4 form-group mb-2 mt-2">
                                    <label>ظرفیت پویش</label>
                                    <input class="form-control" name="capacity" />
                                </div>
                                <div class="col-12 form-group mb-2">
                                    <label>توضیح</label>
                                    <textarea  name="content">{!! $company_info->content_campaign !!}</textarea>
                                </div>
                                <div class="col-12 col-md-6 mt-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <label>تصویر نمایه</label>
                                            <br>
                                            <label for="profile_image" id="profile_up_label" class="btn badge-secondary">
                                                <i class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                انتخاب تصویر
                                            </label>
                                            <input name="profile_image" type="file" id="profile_image" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="profile_image" class="path" name="url_profile_image">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 mt-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <label>تصویر بالا</label>
                                            <br>
                                            <label for="header_image" id="header_up_label" class="btn badge-secondary">
                                                <i class="fa fa-upload ml-2" aria-hidden="true"></i>
                                                انتخاب تصویر
                                            </label>
                                            <input name="header_image" type="file" id="header_image" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="header_image" class="path" name="url_header_image">
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
                                    <button type="submit" class="btn btn-success">ثبت </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="/assets/script/ckeditor/ckeditor.js"></script>
    <script>
        CKEDITOR.replace('content',ck_uploader);
    </script>
    <script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@stop
