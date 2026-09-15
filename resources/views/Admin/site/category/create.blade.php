@extends('Admin.master')

@section('css')

@stop

@section('content')
@php $in_wizard = (bool) request('wizard'); @endphp

{{-- در حالت ویزارد، مرحله جاری و راه بازگشت به لیست دسته‌بندی‌ها نشان داده می‌شود. --}}
@if($in_wizard)
    @include('Admin.productWizard.partials.steps',[
        'current' => 'category',
        'kind'    => \App\Http\Controllers\Admin\ProductWizardController::kindOfCategory($parent),
    ])
    <div class="mb-3">
        <a href="{{route('category.subCategory',[$parent->id,'wizard'=>1])}}" class="btn btn-outline-secondary btn-sm">
            بازگشت به لیست دسته‌بندی‌ها
        </a>
    </div>
@endif

<div class="card p-4"><br />
    <h4 class="disTable">ثبت زیر مجموعه در دسته بندی
        @if($parent) {{$parent->title}} @endif
    </h4>
    <hr />
    <div class="card-body">
        <form id="bannerImage" class="form-horizontal" method="post" action="{{route('category.store')}}"
            enctype="multipart/form-data">
            {{csrf_field()}}
            @if($in_wizard)<input type="hidden" name="wizard" value="1">@endif
            <input type="hidden" name="parent_id" value="{{$parent->id}}">
            <input type="hidden" name="depth" value="{{$depth}}">
            @if($parent->depth == 0) <input type="hidden" name="path" value="{{$parent->id}}"> @endif
            @if($parent->depth >= 1) <input type="hidden" name="path" value="{{$path}}"> @endif

            <div class="row">
                <div class="col-12 col-md-12 mb-2">
                    <h5>اطلاعات کلی</h5>
                </div>
                <div class="col-12 col-md-4 mb-5">
                    <label>عنوان*</label>
                    <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید" name="title"
                        value="{{old('title')}}">
                    @if($errors->has('title'))
                    <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                    @endif
                </div>

                @if($parent->depth == 1)
                <div class="col-12 col-md-4 mb-5">
                    <label> عنوان دوم</label>
                    <input type="text" class="form-control" placeholder="لطفا عنوان دوم را وارد کنید" name="subTitle"
                        value="{{old('subTitle')}}">
                    @if($errors->has('subTitle'))
                    <span class="help-block sub-error   "> {{$errors->first('subTitle')}} </span>
                    @endif
                </div>
                @endif

                <div class="col-12 col-md-4 mb-5">
                    <label>عنوان انگلیسی*</label>
                    <input type="text" class="form-control" placeholder="لطفا عنوان انگلیسی را وارد کنید"
                        name="en_title" value="{{old('en_title')}}">
                    @if($errors->has('en_title'))
                    <span class="help-block sub-error  "> {{$errors->first('en_title')}} </span>
                    @endif
                </div>

                {{-- <div class="col-12 col-md-4 mb-5">
                    <label> حداقل تخفیف (%)</label>
                    <input type="text" class="form-control" placeholder="لطفا حداقل تخفیف (%) را وارد کنید"
                        name="minimum_discount" value="{{old('minimum_discount')}}">
                    @if($errors->has('minimum_discount'))
                    <span class="help-block sub-error  "> {{$errors->first('minimum_discount')}} </span>
                    @endif
                </div> --}}

                <div class="col-12 col-md-2 mb-5">
                    <label></label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="isActive" id="customCheck_isActive"
                            value="1" checked>
                        <label class="custom-control-label" for="customCheck_isActive">نمایش در سایت</label>
                    </div>
                    @if($errors->has('isActive'))<span
                        class="help-block sub-error  ">{{$errors->first('isActive')}}</span>@endif
                </div>
                <div class="col-12 col-md-2 mb-5">
                    <label></label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="displayOrder"
                            id="customCheck_displayOrder" value="1" checked>
                        <label class="custom-control-label" for="customCheck_displayOrder">قابلیت سفارش</label>
                    </div>
                    @if($errors->has('displayOrder'))<span
                        class="help-block sub-error  ">{{$errors->first('displayOrder')}}</span>@endif
                </div>
            </div>
            {{-- @if($depth == 0 || $depth == 1)
            <hr />
            <div class="row">
                <div class="col-12 col-md-12 mb-2">
                    <h5> قیمت و اعتبار برای فروش اقساطی و نقدی</h5>
                </div>
                <div class="col-12 col-md-3 mb-5">
                    <label>قیمت </label>
                    <input type="text" name="price" id="currency-field" value="{{old('price')}}" class="form-control"
                        data-type="currency" placeholder="لطفا قیمت را وارد کنید">
                    @if($errors->has('price'))
                    <span class="help-block sub-error  "> {{$errors->first('price')}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-3 mb-5">
                    <label>مالیات </label>
                    <input type="text" name="tax" value="{{old('tax')}}" class="form-control"
                        placeholder="لطفا مالیات را وارد کنید">
                    @if($errors->has('tax'))
                    <span class="help-block sub-error  "> {{$errors->first('tax')}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-2 mb-5">
                    <label>کمترین اعتبار </label>
                    <input type="text" name="min_credit" id="currency-field" value="{{old('min_credit')}}"
                        class="form-control" data-type="currency" placeholder="لطفا کمترین اعتبار را وارد کنید">
                    @if($errors->has('min_credit'))
                    <span class="help-block sub-error  "> {{$errors->first('min_credit')}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-2 mb-5">
                    <label>بیشترین اعتبار </label>
                    <input type="text" name="max_credit" id="currency-field" value="{{old('max_credit')}}"
                        class="form-control" data-type="currency" placeholder="لطفا بیشترین اعتبار را وارد کنید">
                    @if($errors->has('max_credit'))
                    <span class="help-block sub-error  "> {{$errors->first('max_credit')}} </span>
                    @endif
                </div>

                <div class="col-12 col-md-2 mb-5">
                    <label>درصد پیش پرداخت (%)</label>
                    <input id="percent_lbl" type="text" class="form-control" name="percent"
                        placeholder="لطفا درصد را وارد کنید" value="{{old('percent')}}">
                    @if($errors->has('percent'))
                    <span class="help-block sub-error  "> {{$errors->first('percent')}} </span>
                    @endif
                </div>
                <br />
            </div>
            @endif --}}
            <hr />
            <div class="row">
                <div class="col-12 col-md-12 mb-2">
                    <h5>تصاویر</h5>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-9">
                                        <label class="form-label fw-bold">آپلود تصویر جدید</label>

                                        <div class="input-group mb-2">
                                            <input id="image" class="d-none form-control file-upload file_upload_ajax"
                                                type="file" name="image" accept="image/*" />
                                            <label for="image"
                                                class="btn btn-primary custom-file-upload border border-1">
                                                <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                            </label>
                                        </div>

                                        <input type="hidden" id="image_path" class="path" name="image_path" value="">
                                        @if($errors->has('image'))
                                        <span class="text-danger small">{{ $errors->first('image') }}</span>
                                        @endif

                                        <small class="text-muted d-block mt-1">
                                            فرمت‌های مجاز: jpg, png, webp | حداکثر ۲ مگابایت | سایز 220*190
                                        </small>
                                    </div>
                                    <div id="image_src" class="show disInline col-md-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center g-3">
                                    <div class="col-md-9">
                                        <label class="form-label fw-bold">آپلود آیکن جدید</label>

                                        <div class="input-group mb-2">
                                            <input id="icon" class="d-none form-control file-upload file_upload_ajax"
                                                type="file" name="icon" accept="image/*" />
                                            <label for="icon"
                                                class="btn btn-primary custom-file-upload border border-1">
                                                <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                                            </label>
                                        </div>

                                        <input type="hidden" id="icon_path" class="path" name="icon_path" value="">
                                        @if($errors->has('icon'))
                                        <span class="text-danger small">{{ $errors->first('icon') }}</span>
                                        @endif

                                        <small class="text-muted d-block mt-1">
                                            فرمت‌های مجاز: jpg, png, webp | حداکثر ۲ مگابایت | سایز 40*40
                                        </small>
                                    </div>
                                    <div id="icon_src" class="show disInline col-md-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($depth == 1)
            <hr />
            <div class="row">
                <div class="col-12 col-md-12 mb-2">
                    <h5>برای قسمت دیتاماینیگ صفحه اصلی استفاده می شود</h5>
                </div>
                <div class="col-12 col-md-6 mb-5">
                    <label>دسته بندی های مشابه</label>
                    <select name="similar_categories[]" class="selectMulti form-control" multiple="multiple">
                        @foreach($all_categories as $all_category)
                        <option value="{{$all_category->id}}">{{$all_category->title}}</option>
                        @endforeach
                    </select>
                    @if($errors->has('similar_categories'))
                    <span class="help-block sub-error  "> {{$errors->first('similar_categories')}} </span>
                    @endif
                </div>

                <div class="col-12 col-md-6 mb-5">
                    <label>خدمات مشابه</label>
                    <select title="" name="similar_services[]" class="selectMulti form-control" multiple="multiple">
                        @foreach($all_services as $all_service)
                        <option value="{{$all_service->id}}">{{$all_service->title}}</option>
                        @endforeach
                    </select>
                    @if($errors->has('similar_services'))
                    <span class="help-block sub-error  "> {{$errors->first('similar_services')}} </span>
                    @endif
                </div>
            </div>
            <hr />
            @endif
            <hr />
            <div class="row">
                <div class="col-12 col-md-12 mb-2">
                    <h5>ارتباط دسته بندی ها</h5>
                    <span>تمام کالاهای این دسته در دسته انتخاب شده در این قسمت هم نمایش داده می شوند</span>
                    <span>مثلا دسته زنانه و مردانه به دسته زنانه ارتباط دارد.</span>
                </div>

                <div class="col-12 col-md-4 mb-5">
                    <label>مرتبط با دسته بندی </label>
                    <select title="" name="family_cat_ids_list[]" class="selectMulti form-control" multiple="multiple">
                        @foreach($family_cat_ids_list as $family_cat_id)
                        <option value="{{ $family_cat_id->id }}">{{ $family_cat_id->title }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('family_cat_ids_list'))
                    <span class="help-block sub-error  "> {{$errors->first('family_cat_ids_list')}} </span>
                    @endif
                </div>
            </div>
            <hr />

            <div class="row">
                <div class="col-12 col-md-12 mb-2">
                    <h5>منو</h5>
                </div>

                <div class="col-12 col-md-4 mb-5">
                    <label>عنوان منو(در منو سایت نمایش داده می شود)</label>
                    <input id="menu_title_lbl" type="text" class="form-control" name="menu_title"
                        placeholder="لطفا عنوان منو را وارد کنید">
                </div>

                <div class="col-12 col-md-4 mb-5"><br />
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="is_menu" id="customCheck_is_menu"
                            value="1" checked>
                        <label class="custom-control-label" for="customCheck_is_menu">نمایش در منو</label>
                    </div>
                    @if($errors->has('is_menu'))<span
                        class="help-block sub-error ">{{$errors->first('is_menu')}}</span>@endif
                </div>
                <div class="col-12 col-md-4 mb-5"><br />
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" name="duplicate_subset"
                            id="custom_duplicate_subset" value="1" checked>
                        <label class="custom-control-label" for="custom_duplicate_subset">نمایش با زیر مجموعه</label>
                    </div>
                    @if($errors->has('duplicate_subset'))<span
                        class="help-block sub-error ">{{$errors->first('duplicate_subset')}}</span>@endif
                </div>


                @if($depth > 1 )
                <div class="col-12 col-md-12 mb-2">
                    <h5>نمایش در دسته بندی دیگر</h5>
                    <span>این دسته بندی در گروه دیگری هم نمایش داده شود</span>
                </div>
                <div class="col-12 col-md-6 mb-5">
                    <label> عنوان منو (در منو سایت نمایش داده می شود) </label>
                    <input id="other_parent_title_lbl" type="text" class="form-control" name="other_parent_title"
                        placeholder="لطفا عنوان منو را وارد کنید">

                </div>
                <div class="col-12 col-md-6 mb-5">
                    <label>انتخاب دسته بندی</label>
                    <select class="form-control" name="other_parent_id">
                        <option value="">لطفا انتخاب کنید</option>
                        @foreach($cateDepthOne as $cat)
                        <option value="{{$cat->id}}">{{$cat->title}}</option>
                        @endforeach
                    </select>
                </div>
                <br /> <br />
                @endif

            </div>
            <hr />
            <br />
            <div class="row">
                <div class="col-12">
                    <input type="submit" class="btn btn-success border-0" value="ثبت">
                </div>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('assets/dashboard/js/site/category/categoryEdit.js') }}?v={{ filemtime(public_path('assets/dashboard/js/site/category/categoryEdit.js')) }}"></script>
@stop
