@extends('Admin.master')

@section('content')
<form class="form-horizontal" role="form" method="post" action="{{route('center.store')}}"
    enctype="multipart/form-data">
    @csrf
    <div class="col-12">
        @include('Admin.layouts.errors')
        <div class="card">
            <div class="card-body">
                <div class="form-row">
                    <input type="hidden" name="item[user_id]" value="{{ auth()->user()->id }}">
                    {{-- Present only when the form was opened from a business's branch tab:
                    it says which business the new branch belongs to. --}}
                    @isset($ghorfeOnlineListId)
                    <input type="hidden" name="ghorfe_online_list_id" value="{{ $ghorfeOnlineListId }}">
                    @endisset
                    <div class="col-12 mb-3">
                        <h5>1 - انتخاب نوع دسته بندی همکاری</h5>
                        <br>
                        <br>
                        فروش کالا <input id="category-type" type="radio" name="cat_type" value="category"
                            onclick="catType(this.value)" checked>
                        ارائه خدمات <input id="service-type" type="radio" name="cat_type" value="service"
                            onclick="catType(this.value)">
                    </div>
                    <div class="col-12 mb-5">
                        <div class="col-12 mb-3 mt-2 service_category {{ auth()->user()->center ? 'd-none' : ''}}">
                            <select class="form-control" id="service_category_id1"
                                onchange="selectByCategory(this.id , this.value)" name="item[service_category]">
                                <option selected disabled> انتخاب دسته بندی</option>
                                @foreach ($service_categories as $category)
                                <option value="{{ $category->id }}">{{
                                    $category->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mb-3 mt-2 service">
                            <select class="form-control" id="service_id1" multiple="multiple" name="service[]">
                                @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{
                                    $service->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <select class="form-control mb-3" name="item[product_category]" id="product_category">
                            <option selected disabled>انتخاب دسته بندی کالا مربوطه</option>
                            @foreach ($product_categories as $product_category)
                            <option value="{{ $product_category->id }}">{{ $product_category->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-row">
                    <div class="col-12 mb-5">
                        <h5>2 - انتخاب جایگاه شغلی</h5>
                        <br>
                        <br>
                        فروشنده <input type="radio" value="seller" name="item[type]" checked>
                        تولید کننده <input type="radio" value="producer" name="item[type]">
                        تامین کننده <input type="radio" value="supplier" name="item[type]">
                        خدمات دهنده <input type="radio" value="service_provider" name="item[type]">
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <h5> 3 - سایر اطلاعات</h5>
                    <br>
                    <br>
                    <div class="form-row">
                        <div class="col-12 col-md-4 mb-3">
                            <label>موضوع</label>
                            <input name="item[subject]" class="form-control"
                                placeholder="موضوع درخواست خود را وارد نمایید">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نام و نام خانوادگی</label>
                            <input type="text" name="item[name]" placeholder="نام و نام خانوادگی" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>شماره تماس شعبه</label>
                            <input type="number" name="phone" placeholder="شماره تماس شعبه" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-12 col-md-4 mb-3">
                            <label>نحوه آشنایی</label>
                            <input name="item[connections]" class="form-control"
                                placeholder="نحوه آشنایی با شرکت را وارد نمائید">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>استان</label>
                            <select class="form-control" type="int" name="item[province_id]" id="province_id" required>
                                <option value="">انتخاب استان</option>
                                @foreach(\App\Models\Province::all() as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>شهر</label>
                            <select class="form-control" type="int" name="item[city_id]" id="city_id" required>
                                <option value="">انتخاب شهر</option>
                                @foreach(\App\Models\City::all() as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-12 col-md-4 mb-3">
                            <label>عنوان (در سایت نمایش داده می شود)</label>
                            <input name="title" class="form-control" placeholder="عنوان شعبه را وارد نمایید">
                        </div>

                        @if(!auth()->user()->admin_representation_id)
                        <div class="col-12 col-md-4 mb-3">
                            <label>وضعیت</label>
                            <select class="form-control" name="status">
                                <option value="1">
                                    فعال
                                </option>
                                <option value="0">
                                    غیرفعال
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نمایندگی ایجاد کننده</label>
                            <select name="creator_representation_id" class="form-control">
                                <option value="">م تهران</option>
                                @foreach($representations as $representation)
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نمایندگی های ذینفع</label>
                            <select name="representation_id[]" class="custom-select select2" multiple>
                                @foreach($representations as $representation)
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-4">
                            <label class="col-md-2 col-sm-2 col-xs-12 control-label"></label>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="is_show" value="1" @if(old('is_show')==1) checked
                                        @endif>نمایش در سایت نمایندگی کل
                                </label>
                                @if($errors->has('is_show'))<span
                                    class="help-block sub-error  ">{{$errors->first('is_show')}}</span>@endif
                            </div>
                        </div>
                        @endif
                        <div class="col-12 mb-3">
                            <label>آدرس</label>
                            <input name="address" class="form-control" placeholder="آدرس دقیق شعبه را وارد نمایید">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label>شماره قرارداد</label>
                            <input type="text" class="form-control" name="contract_number">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label>تاریخ قرارداد</label>
                            <input type="text" name="contract_start_date" class="form-control date">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-12 mb-3">
                            <label>توضیحات</label>
                            <textarea name="item[description]" class="form-control no-resize" rows="6"
                                placeholder="توضیحات خود را وارد نمایید"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- The customer-facing side. Only the three fields that are awkward to leave blank
        on day one; the rest of the branch page (ساعات کاری، طرح های مالی، تصاویر،
        سوالات متداول) is filled in on the edit page, which opens right after saving. --}}
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <h5>4 - اطلاعات صفحه عمومی</h5>
                    <br>
                    <div class="form-row">
                        <div class="col-12 col-md-4 mb-3">
                            <label>عنوان منو</label>
                            <select class="form-control" name="label_type">
                                @foreach(\App\Models\Center::LABELS as $key => $word)
                                <option value="{{ $key }}" @selected(old('label_type', 'branch' )===$key)>{{ $word }}
                                </option>
                                @endforeach
                            </select>
                            <small class="text-muted">این عنوان در منو و سرتیتر صفحه عمومی استفاده می‌شود.</small>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>ایمیل</label>
                            <input type="text" name="email" class="form-control" value="{{ old('email') }}"
                                placeholder="info@example.com">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-12 mb-3">
                            <label>متن معرفی (در صفحه عمومی نمایش داده می‌شود)</label>
                            <textarea name="intro_text" class="form-control no-resize" rows="5"
                                placeholder="معرفی کوتاه برای صفحه عمومی">{{ old('intro_text') }}</textarea>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        پس از ثبت، صفحه ویرایش باز می‌شود تا ساعات کاری، طرح های مالی، تصاویر و سوالات متداول را کامل
                        کنید.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-success border-0" value="ثبت">
    </div>
</form>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/sendLink.js?v=1.9')}}"></script>
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/center.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.services-select').select2({
            placeholder: "انتخاب خدمت مربوطه"
        });
    });
</script>
@if (session()->get('center_success') == 'success')
<span id="contact_success"></span>
@elseif(session()->get('center_error')=='success')
<span id="contact_error"></span>
@endif

@endsection
