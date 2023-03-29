@extends('Admin.master')

@section('css')
    <link href="/assets/dashboard/css/dashboard/bootstrap-colorpicker.min.css" rel="stylesheet">
@stop

@section('content')
    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش رنگ</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('color.update',$color->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$color->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label> پالت رنگ *</label>
                        <input type="color" class="demo1 form-control colorpicker-element colorpicker"
                               placeholder="لطفا رنگ را وارد کنید" name="color_palette"
                               value="{{$color->color_palette}}" autocomplete="off">
                        @if($errors->has('color_palette'))
                            <span class="help-block sub-error  "> {{$errors->first('color_palette')}} </span>
                        @endif
                    </div>

                </div>

                <div class="row mb-4">
                        <div class="col-12 col-md-4 ">
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" id="status" class="custom-control-input" name="status"
                                       value="1" @if($color->status == 1) checked @endif>
                                <label for="status" class="custom-control-label">فعال / غیر فعال</label>
                            </div>
                        </div>
                    </div>

                <div class="row">
                    <div class="ln_solid"></div>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <a href="{{route('color.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ویرایش</button>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop
@section('js')
    <!-- Bootstrap Colorpicker -->
    <script src="/assets/dashboard/js/dashboard/bootstrap-colorpicker.min.js"></script>
    <script type="text/javascript">
        $('.colorpicker').colorpicker({});
    </script>
@stop
