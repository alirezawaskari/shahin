@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش اسلایدر</h4>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('slider.update',$slider->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$slider->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>لینک *</label>
                        <input type="text" class="form-control" placeholder="لطفا لینک را وارد کنید"
                               name="link"
                               value="{{$slider->link}}">
                        @if($errors->has('link'))
                            <span class="help-block sub-error  "> {{$errors->first('link')}} </span>
                        @endif
                    </div>
                    <br/> <br/>
                    <div class="col-12 col-md-12 mb-5">
                        <label> عکس ذخیره شده :</label>
                        <div class="width30 marginAuto">
                            <a href="{{asset($slider->img)}}"> <img src="{{$slider->img}}" class="border-radius5" width="200px"></a>
                        </div>
                        <label> تغییر عکس :</label>
                        <label for="image" class="custom-file-upload">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="file-upload file_upload_ajax mb-5" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image" class="path" name="image_path" value="">
                        @if($errors->has('image'))
                            <span class="help-block sub-error  "> {{$errors->first('image')}} </span>
                        @endif
                    </div>
                    <br/>
                    <div class="ln_solid"></div>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('slider.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ویرایش</button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@stop
@section('css')

@stop

@section('js')


@stop

