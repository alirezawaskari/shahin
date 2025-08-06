@extends('site.master')

@section('title')
فهرست انواع وام‌ها
@endsection

@section('content')
<style>
    /* کارت های وام */
    .loan-card {
        transition: all 0.3s;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
        cursor: pointer;
        border-radius: 8px;
        background-color: #ffffff;
        direction: rtl;
    }

    .loan-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    /* استایل هدر کارت */
    .card-header {
        background-color: #74b9ff;
        color: #fff;
        font-weight: 700;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    /* متن توضیحات */
    .card-text {
        font-size: 0.95em;
        color: #636e72;
    }

    /* عنوان وام */
    .card-title {
        font-weight: 700;
        color: #0984e3;
    }

    /* قسمت اطلاعات */
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-top: 8px;
    }

    .info-label {
        font-weight: 600;
        color: #636e72;
    }

    .info-value {
        color: #2d3436;
    }

    /* بخش استایل */
    .container {
        margin-bottom: 50px;
    }

    .btn-success {
        width: -webkit-fill-available;
    }
</style>
</head>

<body>

    @component('site.layouts.breadcrumb',['title'=>'دریافت
    وام','background'=>asset('https://imgv3.fotor.com/images/share/Free-blue-gradient-pattern-background-from-Fotor.jpg')])
    @endcomponent
    <div class="container">
        <div class="row gy-4 justify-content-center" id="loanCards">
            @foreach ($financial_plans_types as $item)
            <div class="col-md-6 col-12">
                <div class="card loan-card">
                    <div class="card-header text-center">{{$item->title}}</div>
                    <div class="card-body">
                        <h6 class="card-text">
                            {!!$item->description!!}
                        </h6>
                        <h6 class="card-title">خدمات شامل این طرح</h6>
                        <p class="card-text">
                            @foreach ($item->services as $service)
                            {{ $service->title.' , '}}
                            @endforeach
                        </p>
                        <hr>
                        <div class="info-row">
                            <div class="info-label">کل ظرفیت:</div>
                            <div class="info-value">{{$item->capacity}}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">ظرفیت باقی‌مانده:</div>
                            <div class="info-value">{{$item->capacity - $item->reserved}}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">تاریخ انقضا:</div>
                            <div class="info-value">{{verta($item->end_date)->format('H:i:s Y/m/d')}}</div>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-success">دریافت وام</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="loginModal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel"
                class="modal fade show" style="padding-right: 17px;" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"></h5>
                            <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                    aria-hidden="true">×</span></button>
                        </div>
                        <div class="modal-body">
                            @if(auth()->check() || isset($factor['id']))
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
                            @include('site.layouts.add_to_cart', ['service' => $service, 'representation' => 0,
                            'callcenter' =>
                            $callcenter->id])
                            @endif
                            @else
                            @include('site.layouts.add_to_cart', ['service' => $service, 'representation' => 0,
                            'callcenter' =>
                            $callcenter->id])
                            @endif
                            @else
                            <span>شما قبلا این خدمت را خریده اید و امکان خرید مجدد وجود ندارد.</span>
                            @endif
                            @else
                            @include('auth.layout.check_register')
                            @include('auth.layout.login')
                            @foreach($errors->all() as $error)
                            <div class="alert alert-danger my-3" role="alert">
                                {{ $error }}
                            </div>
                            @endforeach
                            <form action="/fastregister" method="POST" class="my-4" id="registerForm">
                                @csrf
                                <div class="row" id="get-service-box" style="display: none">
                                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                                    <div class="col-12 col-md-6">
                                        <input class="form-control my-2" type="text" name="name" required
                                            placeholder="نام" value="{{ old('name') }}" pattern="^[\u0600-\u06FF\s]+$"
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
                                        <input class="form-control my-2 phone" type="text" id="user_service_phone"
                                            name="phone" required placeholder="شماره موبایل" value="{{ old('phone') }}">
                                        @if ($errors->has('phone'))
                                        <span class="d-block text-red text-left">{{ $errors->first('phone') }}</span>
                                        @endif
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
                                        <select class="form-control my-2" type="int" name="city_id" id="city_id"
                                            required>
                                            <option value="">انتخاب شهر</option>
                                            @foreach(\App\Models\City::all() as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <select class="form-control my-2" type="int" name="financial_plans_type_id"
                                            id="financial_plans_type_id" required>
                                            @foreach($financial_plans_types as $item)
                                            <option value="{{ $item->id }}" {{ !$item->status ? 'disabled' : '' }}>
                                                {{ $item->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <input type="submit"
                                            class="mt-4 boutton-single blue-silver-theme-text-color blue-silver-theme-border-color"
                                            value="ثبت نام جدید دریافت خدمت">
                                    </div>
                                </div>
                                <input type="hidden" name="callcenter_id" value="{{ $callcenter->id }}">
                            </form>

                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endsection
