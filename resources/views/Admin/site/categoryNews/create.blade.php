@extends('Admin.master')

@section('css')
@stop

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ثبت دسته بندی اخبار</h4>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('categoryNews.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                        @endif

                    </div>

                    <br/>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('categoryNews.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
@section('js')

@stop