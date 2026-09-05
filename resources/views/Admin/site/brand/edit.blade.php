@extends('Admin.master')

@section('css')
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ویرایش برند</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('brand.update',$brand->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">
                    <div class="col-12 col-md-6 mb-3">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید" name="title"
                               value="{{$brand->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>عنوان انگلیسی</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان انگلیسی را وارد کنید"
                               name="title_en"
                               value="{{$brand->title_en}}">
                        @if($errors->has('title_en'))
                            <span class="help-block sub-error  "> {{$errors->first('title_en')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-5">
                        <label for="description_lbl">شرح برند :</label>
                        <textarea id="description_lbl" class="form-control" title="شرح کالا" rows="3" cols="5"
                                  maxlength="100"
                                  placeholder="توضیحات برند باید بین 70 تا 100 کلمه درباره ی تاریخچه و کالا ها برند باشد"
                                  name="description">{{$brand->description}}</textarea>
                        @if($errors->has('description'))
                            <span class="help-block sub-error  "> {{$errors->first('description')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-5">
                        <label for="type_lbl">نوع برند :</label>
                        <input type="radio" name="type" title="ایرانی" class="type_zero"
                               @if($brand->type == 0) checked @endif value="0">
                        <span class="lblRadioBtn font-weight-bold font16">ایرانی </span>

                        <input type="radio" name="type" title="خارجی" class="type_one"
                               @if($brand->type == 1) checked @endif value="1">
                        <span class="lblRadioBtn font-weight-bold font16">خارجی </span>
                    </div>

                </div>

                {{-- <div class="row type_zero_box mb-5" style="display: none;">
                    <div class="col-12 col-md-6 disInline mb-5">
                        <label>برگه ثبت برند ذخیره شده :</label>
                        @include('Admin.partials.currentMedia', ['owner' => $brand, 'field' => 'registration_form', 'required' => true])
                    </div>

                    <div class="col-12 col-md-6 mb-5 disInline">
                        <label>برگه ثبت برند:</label><br/>
                        <label for="registration_form" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="registration_form" class="d-none file-upload file_upload_ajax" type="file"
                               name="registration_form"/>
                        <div id="registration_form_src" class="show disInline"></div>
                        <input type="hidden" id="registration_form_path" class="path" name="registration_form_path" value="">
                        @if($errors->has('registration_form'))
                            <span class="help-block sub-error  "> {{$errors->first('registration_form')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-6 mb-3 disInline">
                        <label for="link_zero_lbl"> ویا لینک قوه قضاییه: </label>
                        <input id="link_zero" type="text" class="form-control" name="link_zero"
                               placeholder="لطفا لینک را وارد کنید"
                               value="@if($brand->type == 0) {{$brand->link}} @endif">
                        @if($errors->has('link_zero'))
                            <span class="help-block sub-error  "> {{$errors->first('link_zero')}} </span>
                        @endif
                    </div>

                </div> --}}

                {{-- <div class="row type_one_box mb-5" style="display: none;">
                    <div class="col-12 col-md-6 disInline mb-5">
                        <label for="link_lbl">لینک سایت معتبر خارجی :</label>
                        <input id="link_one" type="text" class="form-control" name="link_one"
                               placeholder="لطفا لینک را وارد کنید"
                               value="@if($brand->type == 1) {{$brand->link}} @endif">
                        @if($errors->has('link'))
                            <span class="help-block sub-error  "> {{$errors->first('link')}} </span>
                        @endif
                    </div>
                </div> --}}

                <div class="row">
                    <div class="col-12 col-md-6 disInline mb-5">
                        <label>لوگوی برند ذخیره شده :</label>
                        @include('Admin.partials.currentMedia', ['owner' => $brand, 'field' => 'logo', 'required' => true])
                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label>لوگوی برند :</label><br/>
                        <label for="logo" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-cloud-upload"></i>
                            بارگذاری تصویر</label>
                        <input id="logo" class="d-none file-upload file_upload_ajax" type="file" name="logo"/>
                        <div id="logo_src" class="show disInline"></div>
                        <input type="hidden" id="logo_path" class="path" name="logo_path" value="">
                        @if($errors->has('logo'))
                            <span class="help-block sub-error  "> {{$errors->first('logo')}} </span>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('brand.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ویرایش</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>

    <script>

        var type_zero = $(".type_zero").is(":checked");
        var type_one = $(".type_one").is(":checked");
        if (type_zero) {
            $('.type_zero_box').css({'display': 'block'});
            $('.type_one_box').css({'display': 'none'});
        }
        if (type_one) {
            $('.type_one_box').css({'display': 'block'});
            $('.type_zero_box').css({'display': 'none'});
        }

    </script>

@stop
