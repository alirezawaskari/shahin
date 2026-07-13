@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ویرایش درباره ما</h4>
            </div>
            @include('Admin.layouts.errors')
            <form class="form-horizontal" method="post"
                  action="{{route('about.update',$about->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <div class="row">
                    <div class="alert alert-primary"> سایت قدیمی</div>
                    {{--content--}}
                    <div class="col-12 col-md-12 mb-3">
                        <label>متن سایت قدیمی قسمت اول :</label>

                        <textarea id="content" class="form-control content" name="content"
                                  rows="5"
                                  cols="5" placeholder="لطفا متن باکس اول را وارد کنید "
                                  title=" متن باکس اول">{!! $about->content
 !!}</textarea>
                        @if($errors->has('content'))
                            <span class="help-block sub-error"> {{$errors->first('content')}} </span>
                        @endif

                    </div>
                    {{--content--}}
                    <div class="col-12 col-md-12 mb-3">
                        <label> متن سایت قدیمی قسمت دوم :</label>

                        <textarea id="content2" class="form-control content2" name="content2"
                                  rows="5"
                                  cols="5" placeholder="لطفا متن باکس اول را وارد کنید "
                                  title=" متن باکس اول">{!! $about->content2
 !!}</textarea>
                        @if($errors->has('content'))
                            <span class="help-block sub-error"> {{$errors->first('content2')}} </span>
                        @endif

                    </div>
                    <br/><br/>
                 <div class="alert alert-primary"> سایت جدید</div>
                    {{--content_one--}}
                    <div class="col-12 col-md-12 mb-3">
                        <label>متن باکس اول *</label>

                        <textarea id="content_one" class="form-control content_one" name="content_one"
                                  rows="5"
                                  cols="5" placeholder="لطفا متن باکس اول را وارد کنید "
                                  title=" متن باکس اول">{!! $about->content_one !!}</textarea>
                        @if($errors->has('content_one'))
                            <span class="help-block sub-error"> {{$errors->first('content_one')}} </span>
                        @endif

                    </div>
                    <br/><br/>

                    {{--content_two--}}
                    <div class="col-12 col-md-12 mb-3">
                        <label>متن باکس دوم *</label>
                        <textarea id="content_two" class="form-control content_two" name="content_two"
                                  rows="15"
                                  cols="5" placeholder="لطفا متن دوم را وارد کنید"
                                  title=" متن باکس دوم">{{$about->content_two}}</textarea>
                        @if($errors->has('content_two'))
                            <span class="help-block sub-error"> {{$errors->first('content_two')}} </span>
                        @endif
                    </div>

                    <br/><br/>


                    {{--image_two--}}
                    <div class="col-12 col-md-4 mb-3">
                        <label> عکس باکس دوم ذخیره شده :</label>
                        @if($about->image_two)
                            <img src="{{ $about->image_two }}" class="border-radius5" width="80px">
                        @else
                            <span class="text-muted">بدون تصویر</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>عکس باکس دوم </label>
                        <label for="image_two" class="btn btn-primary custom-file-upload border border-1"><i
                                    class="fa fa-cloud-upload"></i> بارگذاری تصویر</label>
                        <input id="image_two" class="d-none file-upload file_upload_ajax" type="file"
                               name="image_two"/>
                        <div id="image_two_src" class="show"></div>
                        <input type="hidden" id="image_two_path" class="path" name="image_two_path" value="">
                        @if($errors->has('image_two_path'))
                            <span class="help-block sub-error"> {{$errors->first('image_two_path')}} </span>
                        @endif
                    </div>


                    <br/><br/>
                    {{--content_three--}}
                    <div class="col-12 col-md-12 mb-3">
                        <label>متن باکس سوم *</label>
                        <textarea id="content_three" class="form-control content_three" name="content_three"
                                  rows="15" cols="5" placeholder="لطفا متن باکس سوم را وارد کنید"
                                  title="متن  باکس سوم">{{$about->content_three}}</textarea>
                        @if($errors->has('content_three'))
                            <span class="help-block sub-error"> {{$errors->first('content_three')}} </span>
                        @endif
                    </div>


                    <br/><br/>
                    {{--image_three--}}
                    <div class="col-12 col-md-6 mb-3">
                        <label> عکس باکس سوم ذخیره شده :</label>
                        @if($about->image_three)
                            <img src="{{ $about->image_three }}" class="border-radius5" width="80px">
                        @else
                            <span class="text-muted">بدون تصویر</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>عکس باکس سوم*</label>
                        <label for="image_three" class="btn btn-primary custom-file-upload border border-1"><i
                                    class="fa fa-cloud-upload"></i> بارگذاری تصویر</label>
                        <input id="image_three" class="d-none file-upload file_upload_ajax" type="file"
                               name="image_three"/>
                        <div id="image_three_src" class="show"></div>
                        <input type="hidden" id="image_three_path" class="path" name="image_three_path" value="">
                        @if($errors->has('image_three_path'))
                            <span class="help-block sub-error"> {{$errors->first('image_three_path')}} </span>
                        @endif
                    </div>

                    <br/><br/>


                    {{--content_four--}}
                    <div class="col-12 col-md-12 mb-3">
                        <label>متن باکس چهارم *</label>
                        <textarea id="content_four" class="form-control content_four" name="content_four"
                                  rows="15" cols="5" placeholder="لطفا متن باکس چهارم را وارد کنید"
                                  title="متن  باکس چهارم">{{$about->content_four}}</textarea>
                        @if($errors->has('content_four'))
                            <span class="help-block sub-error"> {{$errors->first('content_four')}} </span>
                        @endif
                    </div>
                    <br/><br/>


                    {{--image_four--}}
                    <div class="col-12 col-md-6 mb-3">
                        <label> عکس باکس چهارم ذخیره شده :</label>
                        @if($about->image_four)
                            <img src="{{ $about->image_four }}" class="border-radius5" width="80px">
                        @else
                            <span class="text-muted">بدون تصویر</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>عکس باکس چهارم*</label>
                        <label for="image_four" class="btn btn-primary custom-file-upload border border-1"><i
                                    class="fa fa-cloud-upload"></i> بارگذاری تصویر</label>
                        <input id="image_four" class="d-none file-upload file_upload_ajax" type="file"
                               name="image_four"/>
                        <div id="image_four_src" class="show"></div>
                        <input type="hidden" id="image_four_path" class="path" name="image_four_path" value="">
                        @if($errors->has('image_four_path'))
                            <span class="help-block sub-error"> {{$errors->first('image_four_path')}} </span>
                        @endif
                    </div>

                    <br/><br/>

                    {{--content_five--}}
                    <div class="col-12 col-md-12 mb-3">
                        <label>متن باکس پنجم *</label>
                        <textarea id="content_five" class="form-control content_five" name="content_five"
                                  rows="15" cols="5" placeholder="لطفا متن باکس پنجم را وارد کنید"
                                  title="متن باکس پنجم">{{$about->content_five}}</textarea>
                        @if($errors->has('content_five'))
                            <span class="help-block sub-error"> {{$errors->first('content_five')}} </span>
                        @endif
                    </div>
                    <br/><br/>

                    {{--image_five--}}
                    <div class="col-12 col-md-6 mb-3">
                        <label> عکس باکس پنجم ذخیره شده :</label>
                        @if($about->image_five)
                            <img src="{{ $about->image_five }}" class="border-radius5" width="80px">
                        @else
                            <span class="text-muted">بدون تصویر</span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>عکس باکس پنجم *</label>
                        <label for="image_five" class="btn btn-primary custom-file-upload border border-1"><i
                                    class="fa fa-cloud-upload"></i> بارگذاری تصویر</label>
                        <input id="image_five" class="d-none file-upload file_upload_ajax" type="file"
                               name="image_five"/>
                        <div id="image_five_src" class="show"></div>
                        <input type="hidden" id="image_five_path" class="path" name="image_five_path" value="">
                        @if($errors->has('image_five_path'))
                            <span class="help-block sub-error"> {{$errors->first('image_five_path')}} </span>
                        @endif
                    </div>

                    <div class="ln_solid"></div>
                    <div class="col-md-12 col-sm-12 col-xs-12">
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

        CKEDITOR.replace('content',ck_uploader);

        CKEDITOR.replace('content2',ck_uploader);

        CKEDITOR.replace('content_one',ck_uploader);

        CKEDITOR.replace('content_two',ck_uploader);

        CKEDITOR.replace('content_three', ck_uploader);

        CKEDITOR.replace('content_four', ck_uploader);

        CKEDITOR.replace('content_five', ck_uploader);

    </script>

@stop

