@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ثبت عاملیت ها</h4>
            </div>
            <form class="form-horizontal" method="post" action="{{route('agencyType.store')}}"
                  enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error "> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان انگلیسی *</label>
                        <input type="text" class="form-control"
                               placeholder="لطفا عنوان انگلیسی را وارد کنید"
                               name="en_title" value="{{old('en_title')}}">
                        @if($errors->has('en_title'))
                            <span class="help-block sub-error"> {{$errors->first('en_title')}} </span>
                        @endif

                    </div>
                    <div class="col-12 col-md-4 mb-5">
                        <label>قیمت کل *</label>
                        <input type="text" name="total_price" id="currency-field" value="{{old('total_price')}}"
                                class="form-control" data-type="currency" placeholder="لطفا قیمت کل را وارد کنید">

                        @if($errors->has('total_price'))
                            <span class="help-block sub-error"> {{$errors->first('total_price')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-6 mb-5">
                        <label>نماینده *</label>
                        <select class="form-control" name="user_id" title="">
                            <option value="">لطفا انتخاب کنید</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->name}} {{$user->family}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('user_id'))
                            <span class="help-block sub-error"> {{$errors->first('user_id')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label>پیش پرداخت *</label>
                        <input type="text" name="prepayment" id="currency-field" value="{{old('prepayment')}}"
                                class="form-control" data-type="currency" placeholder="لطفا پیش پرداخت را وارد کنید">
                    @if($errors->has('prepayment'))
                            <span class="help-block sub-error"> {{$errors->first('prepayment')}} </span>
                        @endif
                    </div>
                    <br/> <br/><br/> <br/>
                    <div class="col-12 col-md-6 mb-5">
                        <label>عکس *</label>

                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="image_path" value="">
                        @if($errors->has('image'))
                            <span class="help-block sub-error"> {{$errors->first('image')}} </span>
                        @endif

                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_show" @if(old('is_show') == 1) checked
                                       @endif value="1">نمایش در سایت
                            </label>
                            @if($errors->has('is_show'))<span
                                    class="help-block sub-error">{{$errors->first('is_show')}}</span>@endif
                        </div>
                    </div>
                    <br/> <br/>
                    <div class="col-12 col-md-12 mb-5">
                        <label>توضیحات *</label>
                        <textarea id="editor1" class="editor1 form-control" name="content" rows="15"
                                  title="متن ">{{old('content')}}</textarea>
                        @if($errors->has('content'))
                            <span class="help-block sub-error"> {{$errors->first('content')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-12 mb-3">
                    <label>سایر عکس ها </label>
                    </div>
                    @include('Admin.partials.multipleImages',['has_link'=>0])
                    <br/><br/>

                    <div class="ln_solid"></div>
                    <div class="col-12 col-md-6 mb-5">
                        <a href="/Admin/agencyType" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop


@section('css')
@stop

@section('js')

    <script src="{{asset('assets/script/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">
        CKEDITOR.replace('editor1', {
            filebrowserUploadUrl: "{{route('ckeditor.upload', ['_token' => csrf_token()])}}",
            filebrowserUploadMethod: 'form'
        });

    </script>

@stop

