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
                        <form action="{{route('settings.update.content.campaign')}}" method="post">
                            @csrf
                            @method('PATCH')
                            <div class="form-row border  ">
                                <div class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">اطلاعات کمپین</div>
                                <div class="col-12 form-group mb-2">
                                    <label>توضیح</label>
                                    <textarea  name="content">{!! $company_info->content_campaign !!}</textarea>
                                </div>
                                <div class="form-group col-md-12 ">
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
@stop
