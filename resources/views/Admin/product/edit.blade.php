@extends('Admin.master')

@section('content')

<div class="card">
    <div class="card-body">
        <div class="x_title disInline">
            <h4>ویرایش کالا</h4>
        </div>

        <form class="form-horizontal" method="post" action="{{route('products.update',$product->id)}}"
            enctype="multipart/form-data">
            {{csrf_field()}} {{ method_field('PATCH') }}

            @if($product->type == 0)
            <div class="row">
                <div class="col-12 col-md-12 mb-3">
                    <h4 class="text-center"> مواد اولیه مصرفی</h4>
                </div>

                <div class="col-12 col-md-6 mb-3">
                    <label>عنوان *</label>
                    <input id="name" type="text" class="form-control" name="name" placeholder="لطفا عنوان را وارد کنید"
                        value="{{$product->name}}">
                    @if($errors->has("name"))
                    <span class="help-block sub-error"> {{$errors->first("name")}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label>نام فارسی برند </label>
                    <input id="brand_name_fa" type="text" class="form-control" name="brand_name_fa"
                        placeholder="لطفا نام فارسی برند را وارد کنید"
                        value="@if(isset($brand)){{$brand->title}}@endif">
                    @if($errors->has("brand_name_fa"))
                    <span class="help-block sub-error"> {{$errors->first("brand_name_fa")}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label>نام انگلیسی برند </label>
                    <input id="brand_name_en" type="text" class="form-control" name="brand_name_en"
                        placeholder="لطفا نام انگلیسی برند را وارد کنید"
                        value="@if(isset($brand)){{$brand->title_en}}@endif">
                    @if($errors->has("brand_name_en"))
                    <span class="help-block sub-error"> {{$errors->first("brand_name_en")}} </span>
                    @endif
                </div>
            </div>
            <div class="col-12 col-md-6 mb-3">
                <button type="submit" class="btn btm-sm btn-info">ویرایش</button>
            </div>

            @endif

            @if($product->type == 1)
            <section class="signup-step-container">
                <div class="container">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-12">
                            <div class="wizard">
                                <div class="wizard-inner">
                                    <div class="connecting-line"></div>

                                    <ul class="nav nav-tabs" role="tablist">
                                        <li role="presentation"
                                            class="active  @if($center_id != null ) width-Nav-tabs @endif">
                                            <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab"
                                                aria-expanded="true"> <i>قدم اول</i>
                                                <small>گروه کالا</small>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != null ) width-Nav-tabs @endif">
                                            <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab"
                                                aria-expanded="false"> <i>قدم دوم</i>
                                                <small>اطلاعات کالا</small>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != null ) width-Nav-tabs @endif">
                                            <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab"><i>قدم
                                                    سوم</i>
                                                <small>درج ویژگی های کالا</small>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != null ) width-Nav-tabs @endif">
                                            <a href="#step4" data-toggle="tab" aria-controls="step4" role="tab"><i>قدم
                                                    چهارم</i>
                                                <small>بارگذاری تصاویر</small>
                                            </a>
                                        </li>
                                        @if($center_id == null)
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
                                        <h5 class="text-center"> گروه کالا</h5><br />
                                        @include('Admin.partials.products.showErrorProduct',['field'=>'category'])
                                        <div class="row">

                                            <div class="col-12 col-md-12 mb-4">
                                                <label> دسته بندی انتخاب شده :</label>

                                                @foreach($categories_product as $ind=>$category_product)
                                                @if($ind != 0)
                                                @if(count($categories_product) - 1 == $ind)
                                                {{$category_product->title}}
                                                @else
                                                {{$category_product->title}} >
                                                @endif
                                                @endif
                                                @endforeach
                                                <input type="hidden" name="root_category_id"
                                                    value="{{$product->root_category_id}}">
                                                <a class="btn btn-sm btn-warning changeCategory">تغییر دسته بندی </a>
                                            </div>

                                            <div class="col-12 col-md-12 mb-4 changeCategoryBox" style="display: none;">
                                                @include('Admin.step.step_one')
                                            </div>

                                        </div>
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
                                            @include('Admin.partials.attributeDescription')

                                            <div class="col-12 col-md-4 mb-3">
                                                <label for="name_lbl">نام کالا <span class="required">*</span>:</label>
                                                @include('Admin.partials.products.showErrorProduct',['field'=>'name'])
                                                <input id="name_lbl" type="text" class="form-control" name="good[name]"
                                                    placeholder="ماهیت کالا + برند + مدل" value="{{$product->name}}">
                                                <p>مثال : گوشی موبایل سامسونگ سری A</p>
                                                @if($errors->has('good.name'))
                                                <span class="help-block sub-error"> {{$errors->first('good.name')}}
                                                </span>
                                                @endif
                                            </div>

                                            <div class="col-12 col-md-4 mb-3">
                                                <label for="en_name_lbl">نام انگلیسی کالا </label>
                                                @include('Admin.partials.products.showErrorProduct',['field'=>'en_name'])
                                                <input id="en_name_lbl" type="text" class="form-control text-left"
                                                    name="good[en_name]"
                                                    placeholder="Brand + Model + Nature of the Product"
                                                    value="{{$product->en_name}}">
                                                @if($errors->has('good.en_name'))
                                                <span class="help-block sub-error"> {{$errors->first('good.en_name')}}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="col-12 col-md-4 mb-3">
                                                <label for="currency-field">قیمت مرجع (ریال) <span
                                                        class="required">*</span> :</label>
                                                <input id="currency-field" data-type="currency" type="text"
                                                    class="form-control reference_price price_by_attr"
                                                    name="good[reference_price]"
                                                    value="{{number_format($product->reference_price)}}">
                                                <p>بالاترین قیمتی که این محصول میتواند به فروش برسد را وارد نمایید.</p>
                                                @if($errors->has('good.reference_price'))
                                                <span class="help-block sub-error">
                                                    {{$errors->first('good.reference_price')}} </span>
                                                @endif
                                            </div>
                                            <div class="col-12 col-md-6 mb-3 tagBox">
                                                <label for="name_lbl">تگ <span class="required">*</span> :</label>
                                                @include('Admin.partials.products.showErrorProduct',['field'=>'tag'])
                                                <select class="selectMultiTag form-control" name="tag[]" title="tag"
                                                    multiple="multiple">
                                                    {{-- @foreach($tags as $name)
                                                    <option value="{{ $name }}" {{ in_array(trim($name) , $product->
                                                        tagNames()) ? 'selected' : '' }}>
                                                        {{ $name }}
                                                    </option>
                                                    @endforeach --}}
                                                </select>

                                                @if($errors->has('tag'))
                                                <span class="help-block sub-error"> {{$errors->first('tag')}} </span>
                                                @endif
                                            </div>

                                            <div class="col-12 col-md-6 mb-3 brandsBox">
                                                <label for="name_lbl">برند <span class="required">*</span> :</label>
                                                @include('Admin.partials.products.showErrorProduct',['field'=>'brand_id'])
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

                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="length_lbl">ابعاد بسته بندی (سانتی متر) <span
                                                        class="required">*</span> :</label>
                                                <div class="row">
                                                    <div class="col-12 col-md-4">
                                                        <input id="length_lbl" type="number" min="1"
                                                            class="form-control disInline"
                                                            name="good[length]" placeholder="طول"
                                                            value="{{$product->length}}">
                                                        @if($errors->has('good.length'))
                                                        <span class="help-block sub-error">
                                                            {{$errors->first('good.length')}}
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <input id="width_lbl" type="number" min="1"
                                                            class="form-control disInline"
                                                            name="good[width]" placeholder="عرض"
                                                            value="{{$product->width}}">
                                                        @if($errors->has('good.width'))
                                                        <span class="help-block sub-error">
                                                            {{$errors->first('good.width')}}
                                                        </span>
                                                        @endif
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <input id="height_lbl" type="number" min="1"
                                                            class="form-control disInline"
                                                            name="good[height]" placeholder="ارتفاع"
                                                            value="{{$product->height}}">
                                                        @if($errors->has('good.height'))
                                                        <span class="help-block sub-error">
                                                            {{$errors->first('good.height')}}
                                                        </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label for="weight_lbl">وزن بسته بندی (گرم) <span
                                                        class="required">*</span> :</label>
                                                <input id="weight_lbl" type="number" min="1" class="form-control"
                                                    name="good[weight]" placeholder="لطفا وزن را وارد کنید"
                                                    value="{{$product->weight}}">
                                                @if($errors->has('good.weight'))
                                                <span class="help-block sub-error">
                                                    {{$errors->first('good.weight')}}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-xs-6">
                                                <div class="checkbox">
                                                    <label>
                                                        <input class="good_is_sale" type="checkbox" name="good[is_sale]"
                                                            @if($product->is_sale == 1) checked
                                                        @endif
                                                        value="1"> جهت عرضه در سایت
                                                    </label>
                                                    @if($errors->has('good.is_sale'))<span
                                                        class="help-block sub-error">{{$errors->first('good.is_sale')}}</span>@endif
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-2 mb-3">
                                                @include('Admin.partials.products.showErrorProduct',['field'=>'dimensions'])
                                            </div>

                                            <br /><br />
                                            <div class="col-12 col-md-12 mb-3">
                                                <label for="description_lbl">شرح کالا :</label>
                                                @include('Admin.partials.products.showErrorProduct',['field'=>'description'])
                                                <textarea id="description_lbl" class="editor-content" title="شرح کالا"
                                                    rows="3" cols="5" placeholder="شرح کالا"
                                                    name="good[description]">{{$product->description}}</textarea>
                                                @if($errors->has('good.description'))
                                                <span class="help-block sub-error">
                                                    {{$errors->first('good.description')}} </span>
                                                @endif
                                            </div>
                                            <br /><br />

                                            <input class="counter_review_strength" value="1" type="hidden">
                                            <div class="col-12 col-md-6 mb-3">
                                                <div class="input-group mrg10">
                                                    <input type="text" name="reviewstrength[0][text]"
                                                        class="form-control" placeholder="نقاط قوت">
                                                    <span class="input-group-btn addInputStrength"><button type="button"
                                                            class="btn btn-info">+</button></span>
                                                </div>
                                                <div id="reviewstrength">

                                                </div>
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <div class="input-group mrg10">
                                                    <input type="text" name="reviewWeakness[0][text]"
                                                        class="form-control" placeholder="نقاط ضعف">
                                                    <span class="input-group-btn addInputWeakness"><button type="button"
                                                            class="btn btn-info">+</button></span>
                                                </div>
                                                <div id="reviewWeakness">

                                                </div>
                                            </div>
                                            @if(count($reviews_strength) > 0)
                                            <div class="col-12 col-md-6 mb-4 disInline">
                                                <label>نقاط قوت ثبت شده</label>
                                                @foreach($reviews_strength as $index=>$review_strength)
                                                <div id="dynamicReview{{$review_strength->id}}"
                                                    class="input-group mb-4">
                                                    <input type="text"
                                                        name="reviewStrengthOld[{{$review_strength->id}}][text]"
                                                        class="form-control" placeholder="نقاط قوت"
                                                        value="{{$review_strength->text}}">

                                                    <span class='input-group-btn'>
                                                        <button id="{{$review_strength->id}}" type='button'
                                                            class='btn btn-danger remove_review'><i
                                                                class="fa fa-trash"></i></button>
                                                    </span>

                                                    @include('Admin.partials.products.showErrorProduct',['field'=>'review'])
                                                </div>
                                                @endforeach
                                            </div>
                                            @endif
                                            @if(count($reviews_weakness) > 0)
                                            <div class="col-12 col-md-6 mb-4 disInline"><label>نقاط ضعف ثبت
                                                    شده</label>
                                                @foreach($reviews_weakness as $index=>$review_weakness)
                                                <div id="dynamicReview{{$review_weakness->id}}"
                                                    class="input-group mb-4">
                                                    <input type="text"
                                                        name="reviewWeaknessOld[{{$review_weakness->id}}][text]"
                                                        class="form-control" placeholder="نقاط ضعف"
                                                        value="{{$review_weakness->text}}">

                                                    <span class='input-group-btn'>
                                                        <button id="{{$review_weakness->id}}" type="button"
                                                            class="btn btn-danger remove_review"><i
                                                                class="fa fa-trash"></i></button>
                                                    </span>
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
                                            @include('Admin.modal.editAttributeGoodModal',['attributes' =>
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
                                        <h5 class="text-center mb-4">بارگذاری تصاویر</h5>
                                        <div class="row mb-4">
                                            <div class="col-12 col-md-12 mb-4">
                                                <ul>
                                                    <li>ابعاد تصویر بایستی در بازه 600*600 تا 2500*2500 و حجم آن
                                                        باید کمتر
                                                        از 6 مگا بایت باشد.
                                                    </li>
                                                    <li>کالا باید 85% تصویر را در برگیرد و پس زمینه تصویر اصلی باید
                                                        کاملا
                                                        سفید باشد.
                                                    </li>
                                                    <li>تصویر باید فقط کالایی که قصد فروش آن را دارید نمایش دهد و
                                                        بدون هیچ
                                                        لوگو ، نوشته و یا واترمارکی باشد.
                                                    </li>
                                                    <li>تصویر شما باید مربعی باشد یا ابعاد یک در یک داشته باشد.</li>
                                                    <li>فرمت تصاویر بایستی JPG باشد.</li>
                                                    <li>ثبت یک عکس ضروری می باشد و در صورت ثبت یک عکس می توانید
                                                        کالا را ثبت
                                                        کنید.
                                                    </li>
                                                    <li>پرچم: برای مشخص کردن تصویر اصلی کالا، علامت پرچم را انتخاب
                                                        کنید.
                                                    </li>
                                                    <li>سطل زباله: اگر تصویر را به اشتباه انتخاب کرده‌اید، روی علامت
                                                        سطل
                                                        زباله کلیک کنید تا تصویر مورد نظر حذف شود
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <div class="col-12 col-md-12 alert alert-warning">
                                                <a>برای ادامه ، تصاویر دارای خطا باید حذف شوند.</a>
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <h4 class="col-12 col-md-12 mb-4"> تصویر اصلی و گالری تصاویر</h4>
                                            @php($mainImage = $product->getFirstMedia('image'))
                                            @php($__legacyMain = (! $mainImage && method_exists($product, 'legacyMediaUrl')) ? $product->legacyMediaUrl('image') : '')
                                            @if($mainImage)
                                            <div class="col-12 col-md-3 mb-3 div_remove_btn text-center" id="media_{{$mainImage->id}}">
                                                <img src="{{$mainImage->getUrl()}}" alt="{{$mainImage->title}}"
                                                    style="height:100px;width:100%;object-fit:contain;background:#f8f9fa;" class="border rounded">
                                                <div class="mt-1">
                                                    <span class="badge badge-success">تصویر اصلی</span>
                                                    {{-- no ✕ while the legacy `image` column still holds a path:
                                                         the delete would undo itself (accessor falls back to it,
                                                         next save re-adopts). See AdoptsLegacyMedia::mediaIsRemovable(). --}}
                                                    @if($product->mediaIsRemovable('image'))
                                                    <a data-id="{{$mainImage->id}}" title="حذف عکس" class="btn btn-sm remove_media_btn"><i class="fa fa-close" aria-hidden="true"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                            @foreach($product->getMedia('gallery') as $image)
                                            <div class="col-12 col-md-3 mb-3 div_remove_btn text-center" id="media_{{$image->id}}">
                                                <img src="{{$image->getUrl()}}" alt="{{$image->title}}"
                                                    style="height:100px;width:100%;object-fit:contain;background:#f8f9fa;" class="border rounded">
                                                <div class="mt-1">
                                                    <a data-id="{{$image->id}}" title="حذف عکس" class="btn btn-sm remove_media_btn"><i class="fa fa-close" aria-hidden="true"></i></a>
                                                </div>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                        @include('Admin.partials.legacyGalleryFallback', ['model' => $product, 'collections' => ['gallery']])
                                        <div class="row mb-4">
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <label for="file-upload" class="custom-file-upload">
                                                    <i class="fa fa-cloud-upload"></i> بارگذاری تصاویر
                                                </label>
                                                <input id="file-upload" type="file" onchange="readURL(this);" />

                                                <div>
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="imagesList">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <br /><br />
                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت
                                                </button>
                                            </li>
                                            @if($center_id != null)
                                            <li>
                                                <button type="submit" class="badge badge-info">ویرایش</button>
                                            </li>
                                            @else
                                            <li>
                                                <button type="button" class="badge badge-info next-step">ادامه</button>
                                            </li>
                                            @endif

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
                                                name="type_good_selected" title="غرفه من" value="1" checked><span
                                                class="lblRadioBtn font-weight-bold font16">غرفه من </span>
                                        </div>

                                        {{-- <div class="goodStockBox" style="display: none;"><br /><br />
                                            @include('Admin.partials.editProductStoreRoom',['arrayName'=>'good','stock'=>false])
                                        </div> --}}
                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت</button>
                                                <button type="submit" class="badge badge-info prev-step">ویرایش</button>
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
            @endif

        </form>

    </div>
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
<script src="{{ asset('/assets/script/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/product/stepWizard.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/product/productEdit.js') }}"></script>
<script>
    $(document).ready(function () {
            CKEDITOR.replace('description_lbl',ck_uploader) ;
        })
</script>
@stop
