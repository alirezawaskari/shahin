@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش خبر</h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('news.update',$news->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$news->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>دسته بندی *</label>
                        <select class="form-control" name="category_news_id">
                            <option value="">لطفا انتخاب کنید</option>
                            @foreach($category_news as $cat)
                                <option value="{{$cat->id}}" @if($news->category_news_id == $cat->id) selected @endif>{{$cat->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('category_news_id'))
                            <span class="help-block sub-error  "> {{$errors->first('category_news_id')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>موضوع خبر *</label>
                        <input type="text" class="form-control" placeholder="لطفا موضوع خبر را وارد کنید"
                               name="subject" value="{{$news->subject}}">
                        @if($errors->has('subject'))
                            <span class="help-block sub-error  "> {{$errors->first('subject')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-12 mb-3">
                        <label></label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="status" value="1"
                                       @if($news->status == 1) checked @endif>نمایش در سایت
                            </label>
                            @if($errors->has('status'))<span
                                    class="help-block sub-error  ">{{$errors->first('status')}}</span>@endif
                        </div>

                    </div>
                    <br/> <br/>

                    <div class="col-12 col-md-6 mb-3">
                        <label> عکس ذخیره شده :</label>
                        {{-- required: store() validates image_path, so the image must never be
                             deleted to empty — it can only be replaced by uploading a new one
                             (the ✕ deletes immediately, which would bypass that validation). --}}
                        @include('Admin.partials.currentMedia', ['owner' => $news, 'field' => 'image', 'required' => true])
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label> تغییر عکس :</label>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="image_path" value="">
                        @if($errors->has('image'))
                            <span class="help-block sub-error  "> {{$errors->first('image')}} </span>
                        @endif
                    </div>

                    <br/> <br/>
                    <div class="col-12 col-md-12 mb-3">

                        <label>تگ</label>
                        <select class="selectMultiTag form-control" name="tag[]" multiple="multiple">
                            {{--@foreach($tags as $name)--}}
                                {{--<option value="{{ $name }}"--}}
                                        {{--{{ in_array(trim($name) , $news->tagNames()) ? 'selected' : '' }}>--}}
                                    {{--{{ $name }}--}}
                                {{--</option>--}}
                            {{--@endforeach--}}
                        </select>
                    </div>
                    <br/> <br/>
                    <div class="col-12 col-md-12 mb-3">
                        <label>متن *</label>
                        <textarea id="editor1" class="form-control description" name="description" rows="15"
                                  cols="5" title="متن ">{!! $news->description !!}</textarea>
                        @if($errors->has('description'))
                            <span class="help-block sub-error  "> {{$errors->first('description')}} </span>
                        @endif
                    </div>
                    <br/>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('news.index')}}" class="btn btn-warning">بازگشت</a>
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

    <script src="/assets/script/ckeditor/ckeditor.js"></script>
    <script type="text/javascript">

        CKEDITOR.replace('editor1', {
            filebrowserUploadUrl: "{{route('ckeditor.upload', ['_token' => csrf_token()])}}",
            filebrowserUploadMethod: 'form'
        });

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

