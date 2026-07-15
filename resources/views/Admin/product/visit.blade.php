@extends('Admin.master')

@section('content')

<div class="card">
    <div class="card-body">
        <div class="x_title disInline">
            <h4>مشاهده کالا</h4>
        </div>
        <a class="btn btn-sm btn-info floatLeft colorWhite" data-toggle="modal"
            data-target="#similarProducts{{$product->id}}">کالاها
            مشابه</a>
        @include('Admin.modal.similarProducts',['product_id'=>$product->id,'message'=>$product->msg,'product_status'=>$product->status])
        <form class="form-horizontal" method="post" action="{{route('products.store')}}" enctype="multipart/form-data">
            {{csrf_field()}}
            <section class="signup-step-container">
                <div class="container">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-12">
                            <div class="wizard">
                                <div class="wizard-inner">
                                    <div class="connecting-line"></div>

                                    <ul class="nav nav-tabs" role="tablist">
                                        <li role="presentation"
                                            class="active  @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab"
                                                aria-expanded="true"> <i>قدم اول</i>
                                                <small>گروه کالا</small>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab"
                                                aria-expanded="false"> <i>قدم دوم</i>
                                                <small>اطلاعات کالا</small>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab"><i>قدم
                                                    سوم</i>
                                                <small>درج ویژگی های کالا</small>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step4" data-toggle="tab" aria-controls="step4" role="tab"><i>قدم
                                                    چهارم</i>
                                                <small>بارگذاری تصاویر</small>
                                            </a>
                                        </li>
                                        @if($center_id =='')
                                        <li role="presentation" class="disabled">
                                            <a href="#step5" data-toggle="tab" aria-controls="step5" role="tab"><i>قدم
                                                    پنجم</i>
                                                <small>لوکیشن کالا</small>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>

                                <div class="tab-content" id="main_form">
                                    <div class="tab-pane active" role="tabpanel" id="step1">
                                        <h5 class="text-center"> گروه کالا</h5>
                                        @include('Admin.modal.validation',['field'=>'category'])
                                        <br />
                                        @foreach($categories_product as $ind=>$category_product)
                                        @if($ind != 0)
                                        @if(count($categories_product) - 1 == $ind)
                                        {{$category_product->title}}
                                        @else
                                        {{$category_product->title}} >
                                        @endif
                                        @endif
                                        @endforeach
                                        <br /> <br />
                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info next-step">ادامه
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" role="tabpanel" id="step2">
                                        <h5 class="text-center">اطلاعات کالا</h5><br />
                                        <div class="row">
                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="name_lbl">نام کالا <span class="required">*</span>:</label>
                                                @include('Admin.modal.validation',['field'=>'name'])
                                                <input id="name_lbl" type="text" class="form-control" name="good[name]"
                                                    placeholder="ماهیت کالا + برند + مدل" value="{{$product->name}}">
                                                <p>مثال : گوشی موبایل سامسونگ سری A</p>
                                                @if($errors->has('good.name'))
                                                <span class="help-block sub-error"> {{$errors->first('good.name')}}
                                                </span>
                                                @endif
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="en_name_lbl">نام انگلیسی کالا </label>
                                                @include('Admin.modal.validation',['field'=>'en_name'])
                                                <input id="en_name_lbl" type="text" class="form-control text-left"
                                                    name="good[en_name]"
                                                    placeholder="Brand + Model + Nature of the Product"
                                                    value="{{$product->en_name}}">
                                                @if($errors->has('good.en_name'))
                                                <span class="help-block sub-error"> {{$errors->first('good.en_name')}}
                                                </span>
                                                @endif
                                            </div>

                                            <div class="col-12 col-md-6 mb-3 tagBox">
                                                <label for="name_lbl">تگ <span class="required">*</span> :</label>
                                                @include('Admin.modal.validation',['field'=>'tag'])
                                                <ul>
                                                    {{--@foreach($product->tagNames() as $ind => $name)--}}
                                                    {{--<li class="tag_lbl"> {{ $name }}</li>--}}
                                                    {{--@endforeach--}}
                                                </ul>

                                            </div>

                                            <div class="col-12 col-md-6 mb-3 brandsBox">
                                                <label for="name_lbl">برند <span class="required">*</span> :</label>
                                                @include('Admin.modal.validation',['field'=>'brand_id'])
                                                <select title="brand_id" class="form-control brands selectSearch"
                                                    name="brand_id">
                                                    @foreach($brands as $brand)
                                                    <option value="{{$brand->id}}" @if($brand->id == $product->brand_id)
                                                        selected @endif >@if($brand->title_en != null)
                                                        {{$brand->title_en}}
                                                        | @endif {{$brand->title}} </option>
                                                    @endforeach
                                                </select>
                                                @if($errors->has('good.brand_id'))
                                                <span class="help-block sub-error"> {{$errors->first('good.brand_id')}}
                                                </span>
                                                @endif
                                            </div>

                                            @if($center_id =='')
                                            <div class="col-12 col-md-12 mb-3"><br /><br />
                                                {{--checkBox--}}
                                                <div class="col-md-3 col-sm-3 col-xs-6">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input class="good_is_sale" type="checkbox"
                                                                name="good[is_sale]" @if($product->is_sale == 1) checked
                                                            @endif
                                                            value="1">جهت عرضه در سایت
                                                        </label>
                                                        @if($errors->has('good.is_sale'))<span
                                                            class="help-block sub-error">{{$errors->first('good.is_sale')}}</span>@endif
                                                    </div>
                                                </div>
                                                @if(isset($internal_use) && $internal_use == true)
                                                <div class="col-md-3 col-sm-3 col-xs-6">
                                                    <div class="checkbox">
                                                        <label class="internal_use">
                                                            <input type="checkbox" name="good[internal_use]"
                                                                @if($product->internal_use == 1) checked
                                                            @endif value="1">استفاده داخلی
                                                        </label>
                                                        @if($errors->has('good.internal_use'))<span
                                                            class="help-block sub-error">{{$errors->first('good.internal_use')}}</span>@endif
                                                    </div>
                                                </div>
                                                <br /><br />
                                                @endif
                                            </div>
                                            @endif
                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="length_lbl">ابعاد بسته بندی (سانتی متر) <span
                                                        class="required">*</span> :</label>
                                                <br />
                                                <input id="length_lbl" type="number" class="form-control disInline"
                                                    style="width: 20%;" name="good[length]" placeholder="طول"
                                                    value="{{$product->length}}">
                                                @if($errors->has('good.length'))
                                                <span class="help-block sub-error"> {{$errors->first('good.length')}}
                                                </span>
                                                @endif

                                                <input id="width_lbl" type="number" class="form-control disInline"
                                                    style="width: 20%;" name="good[width]" placeholder="عرض"
                                                    value="{{$product->width}}">
                                                @if($errors->has('good.width'))
                                                <span class="help-block sub-error"> {{$errors->first('good.width')}}
                                                </span>
                                                @endif

                                                <input id="height_lbl" type="number" class="form-control disInline"
                                                    style="width: 20%;" name="good[height]" placeholder="ارتفاع"
                                                    value="{{$product->height}}">
                                                @if($errors->has('good.height'))
                                                <span class="help-block sub-error"> {{$errors->first('good.height')}}
                                                </span>
                                                @endif

                                            </div>

                                            <div class="col-12 col-md-4 mb-3">
                                                <label for="weight_lbl">وزن بسته بندی (گرم) <span
                                                        class="required">*</span> :</label>
                                                <input id="weight_lbl" type="number" class="form-control"
                                                    name="good[weight]" placeholder="لطفا وزن را وارد کنید"
                                                    value="{{$product->weight}}">
                                                @if($errors->has('good.weight'))
                                                <span class="help-block sub-error"> {{$errors->first('good.weight')}}
                                                </span>
                                                @endif
                                            </div>

                                            <div class="col-12 col-md-2 mb-3">
                                                @include('Admin.modal.validation',['field'=>'dimensions'])
                                            </div>

                                            <br /><br /><br /><br /><br />
                                            <div class="col-12 col-md-12 mb-3">
                                                <label for="description_lbl">شرح کالا :</label>
                                                @include('Admin.modal.validation',['field'=>'description'])
                                                <textarea id="description_lbl" class="form-control" title="شرح کالا"
                                                    rows="3" cols="5" placeholder="شرح کالا"
                                                    name="good[description]">{{$product->description}}</textarea>
                                                @if($errors->has('good.description'))
                                                <span class="help-block sub-error">
                                                    {{$errors->first('good.description')}} </span>
                                                @endif
                                            </div>
                                            <br /><br />

                                            @if(count($reviews_strength) > 0)
                                            <div class="col-12 col-md-8 mb-4 disInline">
                                                <label>نقاط قوت ثبت شده</label>
                                                @foreach($reviews_strength as $index=>$review_strength)
                                                <div id="dynamicReview{{$review_strength->id}}"
                                                    class="input-group mb-4">

                                                    <input type="text"
                                                        name="reviewStrengthOld[{{$review_strength->id}}][text]"
                                                        class="form-control" placeholder="نقاط قوت"
                                                        value="{{$review_strength->text}}">
                                                    @include('Admin.modal.validation',['field'=>'review'.$review_strength->id,'validationable_id'=>$review_strength->id,'validationable_type'=>'App\Models\Review'])

                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                            @if(count($reviews_weakness) > 0)
                                            <div class="col-12 col-md-8 mb-4 disInline"><label>نقاط ضعف ثبت
                                                    شده</label>
                                                @foreach($reviews_weakness as $index=>$review_weakness)
                                                <div id="dynamicReview{{$review_weakness->id}}"
                                                    class="input-group mb-4">
                                                    <input type="text"
                                                        name="reviewWeaknessOld[{{$review_weakness->id}}][text]"
                                                        class="form-control" placeholder="نقاط ضعف"
                                                        value="{{$review_weakness->text}}">
                                                    @include('Admin.modal.validation',['field'=>'review'.$review_weakness->id,'validationable_id'=>$review_weakness->id,'validationable_type'=>'App\Models\Review'])
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif

                                        </div>

                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="badge badge-info next-step">ادامه
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" role="tabpanel" id="step3">
                                        <h5 class="text-center">درج ویژگی‌های کالا</h5>
                                        <div class="col-md-12 col-sm-12 col-xs-12">
                                            @include('Admin.modal.showAttributeGoodModal',['attributes' =>
                                            $attributes,'product_attributes'=>$product_attributes,'product_id'=>$product->id])
                                        </div>
                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="badge badge-info next-step">ادامه
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="tab-pane" role="tabpanel" id="step4">
                                        <h5 class="text-center">بارگذاری تصاویر</h5>
                                        <br /><br />
                                        <ul>
                                            <li>ابعاد تصویر بایستی در بازه 600*600 تا 2500*2500 و حجم آن باید کمتر
                                                از 6 مگا بایت باشد.
                                            </li>
                                            <li>کالا باید 85% تصویر را در برگیرد و پس زمینه تصویر اصلی باید کاملا
                                                سفید باشد.
                                            </li>
                                            <li>تصویر باید فقط کالایی که قصد فروش آن را دارید نمایش دهد و بدون هیچ
                                                لوگو ، نوشته و یا واترمارکی باشد.
                                            </li>
                                            <li>تصویر شما باید مربعی باشد یا ابعاد یک در یک داشته باشد.</li>
                                            <li>فرمت تصاویر بایستی JPG باشد.</li>
                                            <li>ثبت یک عکس ضروری می باشد و در صورت ثبت یک عکس می توانید کالا را ثبت
                                                کنید.
                                            </li>
                                            <li>پرچم: برای مشخص کردن تصویر اصلی کالا، علامت پرچم را انتخاب کنید.
                                            </li>
                                            <li>سطل زباله: اگر تصویر را به اشتباه انتخاب کرده‌اید، روی علامت سطل
                                                زباله کلیک کنید تا تصویر مورد نظر حذف شود
                                            </li>
                                        </ul>
                                        <br /><br />
                                        <h4>تصویر اصلی و گالری تصاویر</h4>
                                        <br />
                                        <div class="col-12 col-md-12 mb-4">
                                            <img src="{{$product->image}}" alt="{{$product->title}}"
                                                class="border-radius5" width="80px">
                                            <i class="fa fa-lg fa-flag-o"></i>
                                            @include('Admin.modal.validation',['field'=>'image'])
                                        </div>

                                        @if($product->galleries()->count() > 0)
                                        <div class="col-12 col-md-12 mb-4">
                                            @foreach($product->galleries()->get() as $index=>$gallery)
                                            <div class="div_remove_btn" id="gallery_{{$gallery->id}}">
                                                <img src="{{$gallery->image}}" alt="{{$gallery->title}}" width="80px"
                                                    class="border-radius5">
                                                @include('Admin.modal.validation',['field'=>'gallery'.$gallery->id,'validationable_id'=>$gallery->id,'validationable_type'=>'App\Models\Gallery'])
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif

                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="badge badge-info next-step">ادامه
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    @if($center_id == null)
                                    <div class="tab-pane" role="tabpanel" id="step5">

                                        <div class="disTable">

                                            {{-- <input class="type_good_selected" type="radio"
                                                name="type_good_selected" title="انبار" @if(isset($store_room_id) &&
                                                $store_room_id !=null) checked @endif value="0"><span
                                                class="lblRadioBtn font-weight-bold font16">انبار </span> --}}

                                            <input class="type_good_selected disInline" type="radio"
                                                name="type_good_selected" title="غرفه من" value="1"><span
                                                class="lblRadioBtn font-weight-bold font16" selected>غرفه من </span>
                                        </div>


                                        <div class="form-group">
                                            <div class="col-md-12 col-sm-12 col-xs-12">
                                                <button type="submit" class="btn btn-success submitGood floatLeft"
                                                    style="display: none;">ثبت و درج کالا بعدی
                                                </button>
                                            </div>
                                        </div>

                                        <br /><br />
                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    @endif
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </form>

    </div>

</div>
<div class="row ">
    <div class="col-12 col-md-12 mb-3 ">
        <a class="btn btn-sm btn-success buttonsConfirm" href="/dashboard/products/{{$product->id}}/5/status"
            @if($product_validations_count==0) @if($product->status == 3 || $product->status == 4)
            style="display: inline-block"
            @else
            style="display: none"
            @endif
            @else
            style="display: none"
            @endif
            >تایید</a>

        @if($product->status == 5 )
        <a class="btn btn-sm btn-success"> تایید شده</a>
        @endif

        @if($product->status != 8)
        <a class="btn btn-sm btn-danger" href="/dashboard/products/{{$product->id}}/8/status">حذف</a>
        @elseif($product->status == 8)
        <a class="btn btn-sm btn-success"> حذف شده</a>
        @endif
    </div>
    <div class="col-12 col-md-12 mb-3 hasErrorFields" @if($product_validations_count> 0) style="display: block;"
        @else style="display: none;" @endif>
        <h4>تعدادی از فیلد های کالا دارای خطا می باشند.</h4>
    </div>
</div>

<br /><br />
<div class="row">
    @if($product_by_center === 'false')
    <div class="col-12 col-md-2 mb-3">
        <label for="currency-field">قیمت مرجع (ریال) <span class="required">*</span> :</label>
        <input id="currency-field" data-type="currency" type="text" class="form-control reference_price price_by_attr"
            name="reference_price" value="{{number_format($product->reference_price)}}">
    </div>

    <div class="col-12 col-md-3 mb-3 checkbox">
        <label></label>
        <label><input type="checkbox" class="isShow" name="isShow" @if($product->isShow == 1) checked
            @endif value="1">نمایش در
            سایت
        </label>
        @if($errors->has('isShow'))<span class="help-block sub-error">{{$errors->first('isShow')}}</span>@endif
    </div>

    <div class="col-12 col-md-3 mb-4 checkbox">
        <label></label>
        <label>
            <input type="checkbox" class="is_sale" name="is_sale" @if($product->is_sale == 1) checked
            @endif value="1">جهت عرضه در سایت
        </label>
        @if($errors->has('is_sale'))<span class="help-block sub-error">{{$errors->first('is_sale')}}</span>@endif

    </div>

    <div class="col-12 col-md-3 mb-4 checkbox">
        <label class="internal_use">
            <input type="checkbox" class="internal_use" name="internal_use" @if($product->internal_use == 1) checked
            @endif value="1">استفاده داخلی
        </label>
        @if($errors->has('internal_use'))<span
            class="help-block sub-error">{{$errors->first('internal_use')}}</span>@endif

    </div>
    <div class="col-12 col-md-3 mb-4">
        <input type="button" id="{{$product->id}}" class="btn btn-sm btn-success saveChangeVisitProduct"
            value=" ثبت تغییرات">
    </div>

    @endif
</div>

@stop

@section('css')

<style>
    input[type="file"] {
        display: none;
    }

    .width-Nav-tabs {
        width: 25% !important;
    }
</style>

<link rel="stylesheet" href="{{asset('assets/dashboard/css/site/product/stepWizard.css')}}">
@stop

@section('js')
<script src="{{ asset('assets/dashboard/js/site/product/stepWizard.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/product/productVisit.js') }}"></script>


@stop
