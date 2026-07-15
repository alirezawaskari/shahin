@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ثبت قرارداد</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('contract.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error "> {{$errors->first('title')}} </span>
                        @endif

                    </div>
                    <br/>
                    <div class="col-12 col-md-4 mb-3">
                        <label>نوع *</label>
                        <select name="type" class="form-control" title="">
                            <option value="">لطفا انتخاب کنید..</option>
                            <option value="1">خرید</option>
                            <option value="2">فروش</option>
                            <option value="3">واگذاری</option>
                        </select>
                        @if($errors->has('type'))
                            <span class="help-block sub-error"> {{$errors->first('type')}} </span>
                        @endif

                    </div>
                    <br/> <br/>
                    <div class="col-12 col-md-4 mb-3">
                        <label>فایل *</label><br/>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="file_path" value="">
                        @if($errors->has('file_path'))
                            <span class="help-block sub-error"> {{$errors->first('file_path')}} </span>
                        @endif
                    </div>
                    <br/>
                    <div class="col-12 col-md-10 mb-3">
                        <label>محتوا </label>
                        <textarea class="form-control" name="content" rows="5"
                                  cols="5" title="محتوا">{{old('content')}}</textarea>
                        @if($errors->has('content'))
                            <span class="help-block sub-error"> {{$errors->first('content')}} </span>
                        @endif
                    </div>
                    <br/>

                    <br/>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="/dashboard/contract" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
@section('js')

@stop
