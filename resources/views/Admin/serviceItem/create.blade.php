@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ثبت آیتم خدمت
                    <span class="green"> {{ App\Models\Service::serviceById($service->id,'title')}}</span>
                </h4>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('serviceItem.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                    <input type="hidden" name="service_id" value="{{$service->id}}">
                    <div class="row">

                        <div class="col-12 col-md-6 mb-5">
                            <label>عنوان *</label>
                            <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                                   name="title" value="{{old('title')}}">
                            @if($errors->has('title'))
                                <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-5">
                            <label>قیمت *</label>
                            <input type="text" name="price" id="currency-field" value="{{old('price')}}"
                                   class="form-control" data-type="currency" placeholder="لطفا قیمت را وارد کنید" oninput="separatePrices(this)">
                            @if($errors->has('price'))
                                <span class="help-block sub-error"> {{$errors->first('price')}} </span>
                            @endif
                        </div>

                        <div class="col-12 col-md-12 mb-5">
                            <label>متن </label>
                            <textarea class="form-control" name="content" rows="8" cols="5"
                                      title="خلاصه ">{{old('content')}}</textarea>
                            @if($errors->has('content'))
                                <span class="help-block sub-error"> {{$errors->first('content')}} </span>
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-5">
                            <label>عنوان منو</label>
                            <input id="menu_title_lbl" type="text" class="form-control" name="menu_title"
                                   placeholder="لطفا عنوان منو را وارد کنید">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_menu" value="1">نمایش در منو
                                </label>
                                @if($errors->has('is_menu'))<span
                                        class="help-block sub-error">{{$errors->first('is_menu')}}</span>@endif
                            </div>
                        </div>

                        <div class="col-12 col-md-3 mb-5">
                            <label></label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="isShow" @if(old('isShow') == 1) checked
                                           @endif value="1">نمایش در سایت
                                </label>
                                @if($errors->has('isShow'))<span
                                        class="help-block sub-error">{{$errors->first('isShow')}}</span>@endif
                            </div>
                        </div>

                        <div class="col-12 col-md-3 mb-5">
                            <label></label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="status" @if(old('status') == 1) checked
                                           @endif value="1"> وضعیت
                                </label>
                                @if($errors->has('status'))<span
                                        class="help-block sub-error">{{$errors->first('status')}}</span>@endif
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-5">
                            <label>عکس اصلی </label><br/>
                            <label for="image" class="custom-file-upload">
                                <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                            </label>
                            <input id="image" class="file-upload file_upload_ajax" type="file"
                                   name="image"/>
                            <div id="image_src" class="show"></div>
                            <input type="hidden" id="image" class="path" name="image_path" value="">
                            @if($errors->has('image'))
                                <span class="help-block sub-error"> {{$errors->first('image')}} </span>
                            @endif
                        </div>

                        <div class="col-12 col-md-12 mb-2">
                        <label>سایر عکس ها </label>
                        </div>
                        @include('Admin.partials.multipleImages',['has_link'=>0])
                        <br/><br/>

                        <div class="ln_solid"></div>
                        <div class="col-12 col-md-6 mb-5">
                            <button type="submit" class="btn btn-success">ارسال</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
@stop
