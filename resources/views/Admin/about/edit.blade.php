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
                            {{-- legacy here is an image_*_id MediaLibrary FK, not a path --}}
                            @if($about->mediaLibraryIsLegacy('image_two'))
                                <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                            @endif
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
                            @if($about->mediaLibraryIsLegacy('image_three'))
                                <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                            @endif
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
                            @if($about->mediaLibraryIsLegacy('image_four'))
                                <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                            @endif
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
                            @if($about->mediaLibraryIsLegacy('image_five'))
                                <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                            @endif
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
                        <button type="submit" class="btn btn-success" id="about-submit">ویرایش</button>
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

    <script>
        // The box images are required (store() validates image_*_path). On EDIT a re-upload
        // isn't required — the saved image is kept — so the real rule is "must not end up
        // empty": a box needs either an existing image or a new upload.
        //
        // The HTML5 `required` attribute can't do this: #image_*_path is a hidden input, and
        // a required hidden/display:none field aborts the submit as "not focusable" with no
        // visible message. So the check runs on submit against the hidden temp-media id.
        //
        // DOMContentLoaded: this block sits in the page body and jQuery loads at the bottom.
        document.addEventListener('DOMContentLoaded', function () {
            var boxes = [
                { field: 'image_two',   label: 'عکس باکس دوم',  saved: @json((bool) $about->image_two) },
                { field: 'image_three', label: 'عکس باکس سوم',  saved: @json((bool) $about->image_three) },
                { field: 'image_four',  label: 'عکس باکس چهارم', saved: @json((bool) $about->image_four) },
                { field: 'image_five',  label: 'عکس باکس پنجم',  saved: @json((bool) $about->image_five) }
            ];

            // content_one..five are `required` in update(). content / content2 (the old-site
            // boxes) are NOT, so they are deliberately left out — the client must not demand
            // more than the server does.
            var contents = [
                { field: 'content_one',   label: 'متن اول' },
                { field: 'content_two',   label: 'متن دوم' },
                { field: 'content_three', label: 'متن سوم' },
                { field: 'content_four',  label: 'متن چهارم' },
                { field: 'content_five',  label: 'متن پنجم' }
            ];

            // Read from CKEditor, not the <textarea>: the textarea is only synced back on
            // submit, and this handler may run before that — it would see the ORIGINAL text
            // and pass a box the user just emptied.
            function editorValue(field) {
                var instance = (window.CKEDITOR && CKEDITOR.instances) ? CKEDITOR.instances[field] : null;
                var raw = instance ? instance.getData() : ($('#' + field).val() || '');
                return raw.trim();
            }

            function scrollTo($el) {
                if (! $el.length) { return; }
                $('html, body').animate({ scrollTop: $el.offset().top - 120 }, 400);
            }

            $('#about-submit').closest('form').on('submit', function (e) {
                var missingImages = boxes.filter(function (b) {
                    return !b.saved && !$('#' + b.field + '_path').val();
                });
                var missingContent = contents.filter(function (c) {
                    return editorValue(c.field) === '';
                });

                if (missingImages.length || missingContent.length) {
                    e.preventDefault();
                    e.stopImmediatePropagation(); // keep the .submit_btn loading overlay from showing
                    $('#gif').hide();

                    missingContent.forEach(function (c) {
                        toastr.error('لطفا ' + c.label + ' را وارد کنید');
                    });
                    missingImages.forEach(function (b) {
                        toastr.error('لطفا ' + b.label + ' را بارگذاری کنید');
                    });

                    // jump to whichever problem comes first in the page
                    var $first = missingContent.length
                        ? $('#' + missingContent[0].field).closest('.col-12')
                        : $('#' + missingImages[0].field + '_path').closest('.col-12');
                    scrollTo($first);
                    return false;
                }
            });
        });
    </script>

@stop

