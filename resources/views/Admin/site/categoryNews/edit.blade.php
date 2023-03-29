@extends('Admin.master')

@section('css')
@stop

@section('content')
    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش دسته بندی اخبار</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('categoryNews.update',$category_news->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$category_news->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                        @endif
                    </div>

                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <a href="{{route('categoryNews.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ویرایش</button>
                    </div>
                </div>

            </form>

        </div>
    </div>
@stop
@section('js')

@stop