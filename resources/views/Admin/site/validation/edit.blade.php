@extends('Admin.master')
@section('content')
    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش اعتبارسنجی محصولات</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('validation.update',$validation->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">
                    <div class="col-12 col-md-12 mb-3">
                        <label>عنوان *</label>
                        <textarea class="form-control" name="title" rows="5"
                                  cols="5" title="لطفا عنوان را وارد کنید">{{$validation->title}}</textarea>
                        @if($errors->has('title'))
                            <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                        @endif
                    </div>


                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('validation.index')}}" class="btn btn-warning">بازگشت</a>
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

