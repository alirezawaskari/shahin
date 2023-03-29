@extends('Admin.master')

@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش دسته بندی مقاله</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('categoryArticle.update',$category_article->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="col-12 col-md-4 mb-3">
                    <label>عنوان *</label>
                    <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                           name="title"
                           value="{{$category_article->title}}">
                    @if($errors->has('title'))
                        <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                    @endif
                </div>
                <br/>
                <div class="col-12 col-md-12 mb-3">
                    <label>دسته بندی مشابه </label>
                    <select name="similar_categories[]" class="selectMulti form-control" multiple="multiple">
                        @foreach($all_categories as $all_category)
                            <option value="{{ $all_category->id }}" {{ in_array(trim($all_category->id) , $similar_categories) ? 'selected' : '' }}>{{ $all_category->title }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('similar_categories'))
                        <span class="help-block sub-error"> {{$errors->first('similar_categories')}} </span>
                    @endif
                </div>
                <br/>
                <div class="col-12 col-md-12 mb-3">
                    <label>خدمات مشابه </label>
                    <select name="similar_services[]" class="selectMulti form-control" multiple="multiple">
                        @foreach($all_services as $all_service)
                            <option value="{{ $all_service->id }}" {{ in_array(trim($all_service->id) , $similar_services) ? 'selected' : '' }}>{{ $all_service->title }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('similar_services'))
                        <span class="help-block sub-error"> {{$errors->first('similar_services')}} </span>
                    @endif
                </div>
                <br/>

                <div class="ln_solid"></div>
                <div class="col-12 col-md-12 mb-3">
                    <a href="{{route('categoryArticle.index')}}" class="btn btn-warning">بازگشت</a>
                    <button type="submit" class="btn btn-success">ویرایش</button>
                </div>

            </form>

        </div>
    </div>
@stop
@section('js')

@stop