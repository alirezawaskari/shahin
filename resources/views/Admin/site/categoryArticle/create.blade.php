@extends('Admin.master')

@section('css')
@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ثبت دسته بندی مقاله ها</h4>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('categoryArticle.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید" name="title"
                               value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-12 mb-3">
                        <label>دسته بندی های مشابه </label>
                        <select name="similar_categories[]" class="selectMulti form-control" multiple="multiple">
                            @foreach($all_categories as $all_category)
                                <option value="{{$all_category->id}}">{{$all_category->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('similar_categories'))
                            <span class="help-block sub-error"> {{$errors->first('similar_categories')}} </span>
                        @endif
                    </div>
                    <br/><br/>
                    <div class="col-12 col-md-12 mb-3">
                        <label>خدمات مشابه </label>
                        <select name="similar_services[]" class="selectMulti form-control" multiple="multiple">
                            @foreach($all_services as $all_service)
                                <option value="{{$all_service->id}}">{{$all_service->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('similar_services'))
                            <span class="help-block sub-error"> {{$errors->first('similar_services')}} </span>
                        @endif
                    </div>
                    <br/><br/>

                    <div class="ln_solid"></div>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('categoryArticle.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>


@stop
@section('js')

@stop