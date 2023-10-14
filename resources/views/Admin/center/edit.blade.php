@extends('Admin.master')
@section('title')مشاهده مرکز@endsection

@section('content')
<form class="form-horizontal" role="form" method="post" action="{{route('center.update' , [$center->id])}}"
    enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    @include('Admin.layouts.errors')
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <div class="col-12 mb-3">
                    <h5>1 - انتخاب نوع دسته بندی همکاری</h5>
                    <br>
                    <br>
                    فروش کالا <input id="category-type" type="radio" name="cat_type" value="category" onclick="catType(this.value)" {{
                        $center->centerable_type == 'App\Category' ? 'checked' : ''}}>
                    ارائه خدمات <input id="service-type" type="radio" name="cat_type" value="service" onclick="catType(this.value)" {{
                        $center->centerable_type == 'App\Models\Service' ? 'checked' : ''}}>
                </div>
                <div class="col-12 mb-5">
                    <select class="form-control mb-3" name="item[service_category]" id="service_category">
                        <option selected disabled>انتخاب دسته بندی خدمت مربوطه</option>
                        @foreach ($service_categories as $service_category)
                        <option value="{{ $service_category->id }}" {{ $center_request->center_category_id == $service_category->id ? 'selected' :
                            ''
                            }}>{{ $service_category->title }}
                        </option>
                        @endforeach
                    </select>
{{--                    @dd($center->services->where('id',2)->count())--}}
                    <select class="form-control mb-3 services-select" multiple="multiple" name="services[]" id="service">
{{--                        <option  disabled>انتخاب خدمت مربوطه</option>--}}
                        @foreach ($services as $service)
                        <option value="{{ $service->id }}" {{$center->services->where('id',$service->id )->count()==1?'selected' :
                            '' }}>{{ $service->title }}
                        </option>
                        @endforeach
                    </select>

                    <select class="form-control mb-3" name="item[product_category]" id="product_category">
                        <option selected disabled>انتخاب دسته بندی محصول مربوطه</option>
                        @foreach ($product_categories as $product_category)
                        <option value="{{ $product_category->id }}" {{ $center_request->center_category_id == $product_category->id ? 'selected'
                            : ''
                            }}>{{ $product_category->title }}
                        </option>
                        @endforeach
                    </select>
                    <select class="form-control mb-3" name="category" id="category">
                        <option selected disabled>انتخاب دسته بندی مربوطه</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ $category->id == $center->centerable_id ? 'selected' :
                            '' }}>{{ $category->title }}
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
                    فروشنده <input type="radio" value="seller" name="item[type]" {{ $center_request->type == 'seller' ? 'checked' : ''
                    }}>
                    تولید کننده <input type="radio" value="producer" name="item[type]" {{ $center_request->type == 'producer' ?
                    'checked' : ''
                    }}>
                    تامین کننده <input type="radio" value="supplier" name="item[type]" {{ $center_request->type == 'supplier' ?
                    'checked' : ''
                    }}>
                    خدمات دهنده <input type="radio" value="service_provider" name="item[type]" {{ $center_request->type ==
                    'service_provider'
                    ? 'checked' : '' }}>
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
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>موضوع</label>
                        <input type="text" value="{{ $center_request->subject }}" class="form-control" name="item[subject]" required>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>نام و نام خانوادگی</label>
                        <input type="text" name="item[name]" placeholder="نام و نام خانوادگی" class="form-control"
                            value="{{ $center_request->name}}">
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>شماره موبایل</label>
                        <input type="number" name="item[phone]" placeholder="شماره موبایل" class="form-control"
                            value="{{ $center_request->phone }}">
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>نحوه آشنایی</label>
                        <input type="text" value="{{ $center_request->connections }}" class="form-control" name="item[connections]">
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>استان</label>
                        <select class="form-control" type="int" name="item[province_id]" id="province_id" required>
                            <option value="">انتخاب استان</option>
                            @foreach(\App\Models\Province::all() as $province)
                            <option value="{{ $province->id }}" {{ $center_request->province_id == $province->id ? 'selected' :
                                '' }}>{{
                                $province->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>شهر</label>
                        <select class="form-control" type="int" name="item[city_id]" id="city_id" required>
                            <option value="">انتخاب شهر</option>
                            @foreach(\App\Models\City::all() as $city)
                            <option value="{{ $city->id }}" {{ $center_request->city_id == $city->id ? 'selected' : '' }}>{{
                                $city->name
                                }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>عنوان</label>
                        <input type="text" value="{{ $center->title }}" class="form-control" name="title">
                    </div>
                    @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-4 mb-3">
                        <label>وضعیت</label>
                        <select class="form-control" name="status">
                            <option value="1" {{ $center->status == 1 ? 'selected' : '' }}>
                                فعال
                            </option>
                            <option value="0" {{ $center->status == 0 ? 'selected' : '' }}>
                                غیرفعال
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>نمایندگی</label>
                        <select name="representation_id[]" class="custom-select select2" multiple>
                            @foreach($representations as $representation)
                                <option value="{{$representation->id}}" {{ in_array(trim($representation->id) , $center->representations()->pluck('id')->toArray()) ? 'selected' : '' }}>{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 mb-4">
                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"> </label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_show" value="1"
                                       @if($center->is_show == 1) checked @endif>نمایش در سایت نمایندگی کل
                            </label>
                            @if($errors->has('is_show'))<span
                                    class="help-block sub-error  ">{{$errors->first('is_show')}}</span>@endif
                        </div>
                    </div>
                    @endif
                    <div class="col-12 mb-3">
                        <label>آدرس</label>
                        <input type="text" value="{{ $center->address }}" class="form-control" name="address">
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label>توضیحات مرکز</label>
                        <textarea type="text" class="form-control"
                            name="item[description]">{{ $center_request->description }}</textarea>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>توضیحات بررسی همکار</label>
                        <textarea type="text" class="form-control" name="description">{{ $center->description }}</textarea>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </div>
        </div>
    </div>
</form>
@stop
@section('js')
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
