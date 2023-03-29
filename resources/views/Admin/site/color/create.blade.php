@extends('Admin.master')
@section('css')

@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ثبت رنگ</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('color.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif

                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>پالت رنگ *</label>
                        <input type="color" class="form-control rounded" style="width: 45px"
                               placeholder="لطفا پالت رنگ را وارد کنید" name="color_palette">
                        @if($errors->has('color_palette'))
                            <span class="help-block sub-error  "> {{$errors->first('color_palette')}} </span>
                        @endif

                    </div>

                    <br/>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('color.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ذخیره و ثبت رنگ بعدی</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
@section('js')

@stop
