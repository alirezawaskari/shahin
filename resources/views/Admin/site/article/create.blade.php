@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('article.store')}}"
                  enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <div class="col-12 col-md-4 mb-4">
                        <label>عنوان</label>
                        <input type="text" value="{{ old('title') }}" class="form-control" name="title"  placeholder="لطفا عنوان را وارد کنید">
                        @if($errors->has('title'))<span
                                class="help-block sub-error  ">{{$errors->first('title')}}</span>@endif
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <label>عنوان مرتبط با url</label>
                        <input type="text" value="{{ old('slug_title') }}" class="form-control" name="slug_title"  placeholder="لطفا متن url مختص این مقاله را را وارد کنید">
                        @if($errors->has('slug_title'))<span
                                class="help-block sub-error  ">{{$errors->first('slug_title')}}</span>@endif
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <label>دسته بندی *</label>
                        <select class="form-control" name="category_article_id">
                            <option value="">لطفا انتخاب کنید</option>
                            @foreach($category as $cat)
                                <option value="{{$cat->id}}"
                                        @if(old('category_article_id') == $cat->id) selected @endif>{{$cat->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('category_article_id'))<span
                                class="help-block sub-error  ">{{$errors->first('category_article_id')}}</span>@endif
                    </div>
                    @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-4 mb-3">
                        <label>نمایندگی</label>
                        <select name="representation_id" class="custom-select select2" multiple>
                            @foreach($representations as $representation)
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <div class="col-12 col-md-6 mb-4">
                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"></label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="status" value="1"
                                       @if(old('status') == 1) checked @endif>نمایش در سایت نمایندگی کل
                            </label>
                            @if($errors->has('status'))<span
                                    class="help-block sub-error  ">{{$errors->first('status')}}</span>@endif
                        </div>
                    </div> --}}
                    @endif
                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت</label>

                        <select class="form-control" name="status">
                            <option value="0">
                                پیش نویس
                            </option>
                            <option value="1">
                                منتشر شده
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-4">
                        <label>عکس *</label>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                               name="old_image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="image_path" value="">
                        @if($errors->has('image_path'))
                            <span class="help-block sub-error  "> {{$errors->first('image_path')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-4">
                        <label class="col-md-2 col-sm-2 col-xs-12 control-label">تگ</label>
                        <select class="selectMultiTag form-control" name="tag[]" multiple="multiple">
                            @foreach($tags as $tag)
                                <option value="{{$tag->name}}">{{$tag->name}}</option>
                            @endforeach

                        </select>
                    </div>

                    {{-- <div class="col-12 col-md-12 mb-4">
                        <label>خلاصه *</label>
                        <textarea class="form-control" name="summary" rows="8" cols="5"
                                  title="خلاصه ">{{old('summary')}}</textarea>
                        @if($errors->has('summary'))
                            <span class="help-block sub-error  "> {{$errors->first('summary')}} </span>
                        @endif
                    </div> --}}

                    <div class="col-12 col-md-12 mb-5">
                        <label>متن مقاله * </label>
                        <textarea id="description" class="form-control" title="متن" rows="5" cols="5"
                                  name="description">{{old('description')}}</textarea>
                        @if($errors->has('description'))
                            <span class="help-block sub-error  "> {{$errors->first('description')}} </span>
                        @endif
                    </div>


                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
@stop
@section('js')
    <script src="{{ asset('/assets/script/ckeditor/ckeditor.js') }}"></script>
    <script>
        $(document).ready(function () {
            CKEDITOR.replace('summary',ck_uploader) ;
            CKEDITOR.replace( 'description', ck_uploader);
        })
    </script>

    <script>
        $(document).ready(function () {
            $(".selectMultiTag").select2({
                tags: true,
                tokenSeparators: [',', ' ']
            })
        });
    </script>
@stop
