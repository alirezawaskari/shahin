@extends('site.master')

@section('title'){{$campaign->name}}@endsection
@section('content')
    <div class="login">
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5 " id="register_box">
                    <div class="row">
                        <div class="col-12 px-md-5 d-flex flex-column">
                            <h2 class="mb-3">{{$campaign->subject}}</h2>
                            <img
                                src="{{ asset($campaign->image) }}"
                                alt="{{ $campaign->subject }}"
                                class="img-fluid w-100 mb-3"
                            />
                            {!! $campaign->content !!}
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach
                            <form id="user_register_form" class="mt-3" method="POST"
                                  action="{{ route('campaign.register.insert',['campaign_id' => $campaign->id]) }}">
                                @csrf
                                <div class="form-group position-relative box">
                                    <label for="name" class="position-absolute"><i class="far fa-user"></i></label>
                                    <input
                                        id="name"
                                        type="text"
                                        class="border-0 py-2 px-2 w-100 pr-4 name"
                                        name="name"
                                        value="{{ auth()?->user()->name }}"
                                        placeholder="نام"
                                        required
                                    >
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="family" class="position-absolute"><i class="far fa-user"></i></label>
                                    <input
                                        id="family"
                                        type="text"
                                        class="border-0 py-2 px-2 w-100 pr-4 family"
                                        name="family"
                                        value="{{ auth()?->user()->family }}"
                                        placeholder="نام خانوادگی"
                                        required
                                    >
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="gender" class="position-absolute">
                                        <i class="fas fa-user"></i>
                                    </label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50 gender"
                                            name="gender" id="gender" required>
                                        <option {{ auth()?->user()?->sex == null ? 'selected' : '' }} disabled>جنسیت
                                        </option>
                                        <option {{ auth()?->user()?->sex === 0 ? 'selected' : '' }} value="0">آقا
                                        </option>
                                        <option {{ auth()?->user()?->sex === 1 ? 'selected' : '' }} value="1">خانم
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute">
                                        <i class="fas fa-mobile-alt"></i>
                                    </label>
                                    <input
                                        id="phone_register"
                                        type="text"
                                        class="border-0 py-2 px-2 w-100 pr-4 phone"
                                        name="phone"
                                        value="{{ auth()?->user()?->phone }}"
                                        placeholder="شماره موبایل"
                                        required
                                    >
                                </div>

                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50"
                                            name="province_id" id="province_id" required>
                                        <option value="">انتخاب استان</option>
                                        @foreach($provinces as $pro)
                                            <option
                                                {{ auth()?->user()?->province_id == $pro->id ? 'selected' : '' }} value="{{ $pro->id }}">{{ $pro->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50"
                                            name="city_id" id="city_id" required>
                                        <option value="">انتخاب شهر</option>
                                        @foreach($citys as $city)
                                            <option
                                                {{ auth()?->user()?->city_id == $city->id ? 'selected' : '' }} value="{{ $city->id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group position-relative box" id="area_id">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50"
                                            name="area_id">
                                        <option value="">انتخاب متطقه</option>
                                        @foreach(\App\Models\Area::all() as $area)
                                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50"
                                            name="type_id" id="type_id" required>
                                        <option disabled selected>انتخاب نوع خدمات</option>
                                        <option value="1">کالا</option>
                                        <option value="2">خدمات</option>
                                    </select>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50" disabled
                                            name="category_id" id="category_id" required>
                                        <option value="">انتخاب دسته بندی</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->title }}</option>
                                        @endforeach
                                        <option value="more">سایر</option>
                                    </select>
                                </div>
                                <div class="form-group position-relative box service_id">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50" disabled
                                            name="service_id" id="service_id">
                                        <option value="">انتخاب نوع خدمت</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                        @endforeach
                                        <option value="more">سایر</option>
                                    </select>
                                </div>
                                <div class="form-group position-relative box service_item_id">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50" disabled
                                            name="service_item_id" id="service_item_id">
                                        <option value="">انتخاب آیتم</option>
                                        @foreach($service_items as $service_item)
                                            <option value="{{ $service_item->id }}">{{ $service_item->title }}</option>
                                        @endforeach
                                        <option value="more">سایر</option>
                                    </select>
                                </div>
                                <div class="form-group position-relative d-none" id="register_description">
                                    <label for="description" class="form-label fw-bold mb-2">نیاز خرید خود را وارد کنید</label>
                                    <textarea
                                        id="description"
                                        name="description"
                                        rows="4"
                                        class="form-control border-primary text-black-50"
                                        placeholder="مثال: روغن مایع"
                                    ></textarea>
                                </div>

                                <div class="form-group form-button mt-4">
                                    <div class="row my-2">
                                        <div class="col-12 mb-1 d-flex justify-content-center">
                                            <input
                                                class="btn btn-orange text-white font-16 px-4 col-6 col-md-3"
                                                type="submit"
                                                value="ثبت"
                                            />
                                        </div>

                                    </div>
                                </div>
                            </form>
                            @if(count($campaign->campaign_collaboration) > 0)
                            <div class="col-12  mx-auto">
                                <h3 class="text-center mb-4">مشارکت‌کنندگان این کمپین</h3>
                                <div class="row justify-content-center">
                                    @foreach($campaign->campaign_collaboration as $campaign_collaboration)
                                        <div class="col-12 col-md-6 mb-4 d-flex justify-content-center">
                                            <div class="card shadow-sm h-100 w-100 text-center">
                                                <div class="card-body">
                                                    <a
                                                        target="_blank"
                                                        class="text-black-50"
                                                        href="{{$campaign_collaboration->url}}"
                                                    >
                                                    <h4 class="card-title mb-3">{{ $campaign_collaboration->name }}</h4>
                                                    <img
                                                        src="{{ asset($campaign_collaboration->logo) }}"
                                                        alt="{{ $campaign_collaboration->name }}"
                                                        class="img-fluid mb-3"
                                                        style="max-height: 150px; object-fit: contain;"
                                                    >
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
@section('js')
    <script src="/assets/site/js/fix-number.js?v=1"></script>
    <script src="/assets/site/js/register-campaign.js?v=1"></script>
@endsection
