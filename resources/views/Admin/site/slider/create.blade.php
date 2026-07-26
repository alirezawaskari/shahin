@extends('Admin.master')
@section('content')

    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ثبت اسلایدر </h4>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('slider.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">

                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title" value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>لینک *</label>
                        <input type="text" class="form-control" placeholder="لطفا لینک را وارد کنید"
                               name="link" value="{{old('link')}}">
                        @if($errors->has('link'))
                            <span class="help-block sub-error  "> {{$errors->first('link')}} </span>
                        @endif
                    </div>
                    @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-4 mb-3">
                        <label>نمایندگی *</label>
                        <select name="representation_id" class="custom-select">
                        <option value="">نمایندگی کل</option>
                            @foreach($representations as $representation)
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <br/> <br/>
                    <div class="col-12 col-md-6 mb-3">
                        <label>عکس*</label>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        {{-- accept: the shared ajax endpoint also takes pdf/doc/zip, which makes no
                             sense for a slider — restrict the picker to image types --}}
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                               name="image" accept=".jpg,.jpeg,.png,.webp,.gif,.svg"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="image_path" value="">
                        {{-- keyed on image_path (the hidden temp-media id), which is what the
                             controller validates — `image` is the file input and is cleared by
                             the ajax widget, so an error was never reported under that key --}}
                        @if($errors->has('image_path'))
                            <span class="help-block sub-error  "> {{$errors->first('image_path')}} </span>
                        @endif
                    </div>

                    <br/> <br/>
                    <div class="ln_solid"></div>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('slider.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success" id="slider-submit">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>


@stop


@section('css')

@stop

@section('js')

    <script>
        // The image is required, but HTML5 `required` can't express it: #image_path is a
        // hidden input (a required hidden field aborts the submit as "not focusable" with
        // no message) and the visible #image file input is cleared by the ajax widget.
        // So check the hidden temp-media id on submit. The server enforces the same rule.
        //
        // DOMContentLoaded: this block runs before the bottom-loaded jQuery otherwise.
        document.addEventListener('DOMContentLoaded', function () {
            $('#slider-submit').closest('form').on('submit', function (e) {
                if (! $('#image_path').val()) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    $('#gif').hide();
                    toastr.error('لطفا عکس را وارد کنید');
                    return false;
                }
            });
        });
    </script>

@stop

