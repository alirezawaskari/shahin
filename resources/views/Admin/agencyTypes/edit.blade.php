@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ویرایش عاملیت</h4>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('agencyType.update',$agencyTypes->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                <div class="row">

                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$agencyTypes->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error "> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان انگلیسی *</label>
                        <input type="text" class="form-control"
                               placeholder="لطفا عنوان انگلیسی را وارد کنید" name="en_title"
                               value="{{$agencyTypes->en_title}}">
                        @if($errors->has('en_title'))
                            <span class="help-block sub-error"> {{$errors->first('en_title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-4 mb-5">
                        <label>قیمت کل *</label>
                        <input type="text" name="total_price" id="currency-field" data-type="currency" value="{{number_format($agencyTypes->total_price)}}"
                                class="form-control" placeholder="لطفا قیمت کل را وارد کنید">
                    @if($errors->has('total_price'))
                            <span class="help-block sub-error"> {{$errors->first('total_price')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label>نماینده *</label>
                        <select class="form-control" name="user_id" title="">
                            <option value="">لطفا انتخاب کنید</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}" @if($user->id == $agencyTypes->user_id) selected @endif>{{$user->name}} {{$user->family}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('user_id'))
                            <span class="help-block sub-error"> {{$errors->first('user_id')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label>پیش پرداخت *</label>
                        <input type="text" name="prepayment" id="currency-field" value="{{number_format($agencyTypes->prepayment)}}"
                                class="form-control" data-type="currency" placeholder="لطفا پیش پرداخت را وارد کنید">

                    @if($errors->has('prepayment'))
                            <span class="help-block sub-error"> {{$errors->first('prepayment')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-5">
                        <label></label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_show" value="1"
                                       @if($agencyTypes->is_show == 1) checked @endif>نمایش در سایت
                            </label>
                            @if($errors->has('is_show'))<span
                                    class="help-block sub-error">{{$errors->first('is_show')}}</span>@endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label> عکس ذخیره شده :</label>
                        @include('Admin.partials.currentMedia',['owner'=>$agencyTypes,'field'=>'image','boxH'=>120])
                    </div>
                    <div class="col-12 col-md-6 mb-5">
                        <label> تغییر عکس :</label>
                        <label for="image" class="btn btn-primary custom-file-upload border border-1">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image_path" class="path" name="image_path" value="">
                        @if($errors->has('image'))
                            <span class="help-block sub-error"> {{$errors->first('image')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-5">
                        <label>توضیحات *</label>
                        <textarea id="editor1" class="editor1 form-control" name="content" rows="15"
                                  title="متن ">{{$agencyTypes->content}}</textarea>
                        @if($errors->has('content'))
                            <span class="help-block sub-error"> {{$errors->first('content')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-12 mb-5">
                        @if($agencyTypes->getMedia('gallery')->count() > 0)
                            <label> عکس های ذخیره شده</label>
                            <table class="table table-striped">
                                <tbody>
                                @foreach($agencyTypes->getMedia('gallery') as $gallery)
                                    <tr class="row_image" id="media_{{$gallery->id}}">
                                        <td><img src="{{$gallery->getUrl()}}" alt="{{$gallery->getCustomProperty('title')}}"
                                                 class="border-radius5" width="80px"></td>
                                        <td><input type="text" class="form-control" placeholder="عنوان"
                                                   name="gallery_title[{{$gallery->id}}]"
                                                   value="{{$gallery->getCustomProperty('title')}}"></td>
                                        {{-- no ✕ on the last image while legacy `galleries` rows remain —
                                             see AdoptsLegacyMedia::galleryMediaIsRemovable() --}}
                                        <td>@if($agencyTypes->galleryMediaIsRemovable('gallery'))<a data-id="{{$gallery->id}}" title="حذف عکس"
                                               class="btn btn-sm remove_media_btn"><i class="fa fa-close" aria-hidden="true"></i></a>@endif</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @endif
                        @include('Admin.partials.legacyGalleryFallback', ['model' => $agencyTypes, 'collections' => ['gallery']])
                    </div>
                    <div class="col-12 col-md-12 mb-3">
                    <label>سایر عکس ها</label>
                    </div>
                    @include('Admin.partials.multipleImages')

                    <div class="ln_solid"></div>
                    <div class="col-12 col-md-6 mb-5">
                        <a href="{{route('agencyType.index')}}" class="btn btn-warning">بازگشت</a>
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

@stop

