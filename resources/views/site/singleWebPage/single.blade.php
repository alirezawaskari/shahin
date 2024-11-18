@extends('site.singleWebPage.master')
@section('title'){{ $service->title }}@endsection
@section('description'){{ $service->description }}@endsection

@section('content')
<div class="service">
    <div class="register-section py-4" id="one-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-5 py-5 my-auto mr-auto">
                    <h6 class="alert alert-warning text-center">برای تکمیل فرایند ثبت نام <a href="#login_box">اینجا</a> کلیک کنید</h6>
                    {!! $service->left_content !!}
                    @foreach($errors->all() as $error)
                    <div class="alert alert-danger my-3" role="alert">
                        {{ $error }}
                    </div>
                    @endforeach
                    @if(auth()->check() || isset($factor['id']))
                    @if($service->special == true)
                    <h6 class="text-green my-2 font-16">مرکز واقع در کرج می باشد.</h6>
                    @endif
                    <div id="login_box">
                    @if(isset($check) && !$check)
                    @if(!is_null($required))
                    @if($required == false)
                    <div class="rol2">
                        <p>برای خرید این خدمت باید ابتدا خدمات زیر را خریداری نمایید.</p>
                        @foreach($requireServices as $ser)
                        <a href="{{ $ser->path() }}" class="btn btn-orange text-white mb-2">
                            {{ $ser->title }}
                        </a>
                        @endforeach
                    </div>
                    @else
                    @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => 0 , 'callcenter' => 0])
                    @endif
                    @else
                    @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => 0 , 'callcenter' => 0])
                    @endif
                    @else
                    <span class="alert alert-primary">شما قبلا در این آیتم ثبت نام شده اید ، جهت بارگذاری مدارک اقدام بفرمائید.</span>
                    <br>
                    <br>
                    <a href="{{asset("/singleWebPage/checkout/$singleWebPage->slug/$factor->resnumber")}}">
                        <span id="upload-docs" class="btn btn-primary">تکمیل فرایند ثبت نام
                        <i class="fa fa-mouse-pointer"></i>
                        </span>
                    </a>
                    @endif
                    @else
                    @include('auth.layout.check_register')
                    @include('auth.layout.login')
                    <form action="/fastregister" method="POST" class="my-4" id="registerForm">
                        @csrf
                        <div class="row" id="get-service-box" style="display: none">
                            <input type="hidden" name="service_id" value="{{ $service->id }}">
                            <div class="col-12 col-md-6">
                                <input class="form-control my-2" type="text" name="name" required placeholder="نام"
                                    value="{{ old('name') }}" pattern="^[\u0600-\u06FF\s]+$"
                                    title="لطفا با حروف فارسی وارد نمایید">
                                @if ($errors->has('name'))
                                <span class="d-block text-red text-left">{{ $errors->first('name') }}</span>
                                @endif
                            </div>
                            <div class="col-12 col-md-6">
                                <input class="form-control my-2" type="text" name="family" required
                                    placeholder="نام خانوادگی" value="{{ old('family') }}"
                                    pattern="^[\u0600-\u06FF\s]+$" title="لطفا با حروف فارسی وارد نمایید">
                                @if ($errors->has('family'))
                                <span class="d-block text-red text-left">{{ $errors->first('family') }}</span>
                                @endif
                            </div>
                            <div class="col-12 col-md-6">
                                <select class="form-control my-2" type="int" name="gender" id="gender" required>
                                    <option value="" disabled selected>جنسیت</option>
                                    <option value="0">آقا</option>
                                    <option value="1">خانم</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <input class="form-control my-2 phone" type="text" id="user_service_phone" name="phone"
                                    required placeholder="شماره موبایل" value="{{ old('phone') }}">
                                @if ($errors->has('phone'))
                                <span class="d-block text-red text-left">{{ $errors->first('phone') }}</span>
                                @endif
                            </div>
                            <div class="col-12">
                                <select class="form-control my-2" type="int" name="financial_plans_type_id"
                                    id="financial_plans_type_id" required>
                                    @foreach($financial_plans_types as $item)
                                    <option value="{{ $item->id }}" {{ !$item->status ? 'disabled' : '' }}>{{
                                        $item->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <select class="form-control my-2" type="int" name="province_id" id="province_id"
                                    required>
                                    <option value="">انتخاب استان</option>
                                    @foreach(\App\Models\Province::all() as $province)
                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <select class="form-control my-2" type="int" name="city_id" id="city_id" required>
                                    <option value="">انتخاب شهر</option>
                                    @foreach(\App\Models\City::all() as $city)
                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <select class="form-control my-2" type="int" name="area_id" id="area_id">
                                    <option value="">انتخاب منطقه</option>
                                    @foreach(\App\Models\Area::all() as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    {{--<input style="cursor: pointer"
                                        class="form-check-input form-control border border-warning" type="checkbox"
                                        value="" id="invalidCheck2" required>--}}
                                    <label class="form-check-label mr-3 alert alert-dark" for="invalidCheck2">
                                        توضیحات مربوط به خدمت درخواستی را مطالعه نمودم
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary text-white px-3 mt-2 btn-block">ثبت نام
                                    جدید دریافت خدمت
                                </button>
                            </div>
                        </div>
                    </form>
                    {{-- <a href="{{ route('login') }}" class="btn btn-orange text-white px-3 mt-2 btn-block">قبلا ثبت
                        نام کرده اید؟اینجا کلیک کنید</a>--}}
                    @endif
                    </div>
                </div>
                <div class="d-none d-md-block col-md-6 pl-0 text-left">
                    <img src="{{ $service->img_box_1 }}" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <div class="banner position-relative">
        <img class="img-fluid banner-img" src="{{ $service->thumb }}" alt="{{ $service->title }}">
    </div>

    <div class="section py-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-5 py-5 my-auto mr-auto">
                    {!! $service->txt_box_1 !!}
                </div>
                <div class="d-none d-md-block col-md-6 pl-0 text-left">
                    <img src="{{ $service->img_box_3 }}" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
    <div class="video-section py-5 bg-gray">
        <div class="container">
            <div class="row py-5">
                <div class="col-12">
                    {!! $service->content !!}
                </div>
            </div>
        </div>
    </div>

    <h6 class="text-center mt-3">تصاویر</h6>
    <div class="col-12 row mb-4">
        @if(isset($service->workSamples))
        @foreach($service->workSamples as $work_sample)
        <div class="col-md-2 p-2">
            <a href="{{ asset($work_sample->image) }}" target="_blank">
                <img src="{{ $work_sample->image }}" class="card-img-top" height="250px">
            </a>
        </div>
        @endforeach
        @endif
    </div>
</div>
@endsection

@section('footer')
<script>
    $(document).ready(function () {
        $('#service-description-check').prop('checked', false);
        $('#service-description-check').change(function () {
            if (this.checked) {
                $('#step-two-cover').slideUp();
            } else {
                $('#step-two-cover').slideDown();
            }
        });
    });
    $('#date').persianDatepicker({
        format: 'YYYY/MM/D',
        autoClose: true,
    });

</script>
<script src="/assets/site/js/fix-number.js?v=1"></script>
<script src="/assets/site/js/get-service.js?v=1.1"></script>

@endsection
