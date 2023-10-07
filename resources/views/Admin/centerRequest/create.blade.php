@extends('Admin.master')

@section('content')
<form class="form-horizontal" role="form" method="post" action="{{route('centerRequest.store')}}"
    enctype="multipart/form-data">
    @csrf

    <div class="col-12">
        @include('Admin.layouts.errors')
        <div class="card">
            <div class="card-body">
                <div class="form-row">
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    <div class="col-12 mb-3">
                        <h5>1 - انتخاب نوع دسته بندی همکاری</h5>
                        <br>
                        <br>
                        فروش کالا <input type="radio" name="cat_type" value="category" onclick="catType(this.value)"
                            checked>
                        ارائه خدمات <input type="radio" name="cat_type" value="service" onclick="catType(this.value)">
                    </div>
                    <div class="col-12 mb-5">
                        <select class="form-control mb-3" name="service_category" id="service_category">
                            <option selected disabled>انتخاب دسته بندی خدمت مربوطه</option>
                            @foreach ($service_categories as $service_category)
                            <option value="{{ $service_category->id }}">{{ $service_category->title }}
                            </option>
                            @endforeach
                        </select>

                        <select class="form-control mb-3" name="product_category" id="product_category">
                            <option selected disabled>انتخاب دسته بندی محصول مربوطه</option>
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
                        فروشنده <input type="radio" value="seller" name="type" checked>
                        تولید کننده <input type="radio" value="producer" name="type">
                        تامین کننده <input type="radio" value="supplier" name="type">
                        خدمات دهنده <input type="radio" value="service_provider" name="type">
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
                        <div class="col-12 col-md-3 mb-3">
                            <label>موضوع</label>
                            <input name="subject" class="form-control" required
                                placeholder="موضوع درخواست خود را وارد نمایید">
                        </div>
                        <div class="col-12 col-md-3 mb-3">
                            <label>نام </label>
                            <input type="text" name="name" placeholder="نام " class="form-control">
                        </div>
                        <div class="col-12 col-md-3 mb-3">
                            <label> نام خانوادگی</label>
                            <input type="text" name="family" placeholder="  نام خانوادگی" class="form-control">
                        </div>

                        <div class="col-12 col-md-3 mb-3">
                            <label>شماره موبایل</label>
                            <input type="number" name="phone" placeholder="شماره موبایل" class="form-control">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-12 col-md-3 mb-3">
                            <label>نحوه آشنایی</label>
                            <input name="connections" class="form-control"
                                placeholder="نحوه آشنایی با شرکت را وارد نمائید">
                        </div>
                        <div class="col-12 col-md-3 mb-3">
                            <label>استان</label>
                            <select class="form-control" type="int" name="province_id" id="province_id" required>
                                <option value="">انتخاب استان</option>
                                @foreach(\App\Models\Province::all() as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-3">
                            <label>شهر</label>
                            <select class="form-control" type="int" name="city_id" id="city_id" required>
                                <option value="">انتخاب شهر</option>
                                @foreach(\App\Models\City::all() as $city)
                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if(!auth()->user()->admin_representation_id)
                        <div class="col-12 col-md-3 mb-3">
                            <label>نمایندگی</label>
                            <select name="representation_id" class="form-control select2">
                                <option value="">نمایندگی کل</option>
                                @foreach($representations as $representation)
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="form-row">
                        <div class="col-12 mb-3">
                            <label>توضیحات مرکز</label>
                            <textarea name="description" class="form-control no-resize" rows="6"
                                placeholder="توضیحات خود را وارد نمایید"></textarea>
                        </div>
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
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/center.js') }}"></script>

@if (session()->get('center_success') == 'success')
<span id="contact_success"></span>
@elseif(session()->get('center_error')=='success')
<span id="contact_error"></span>
@endif

@endsection
