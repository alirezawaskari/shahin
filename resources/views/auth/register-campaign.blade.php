@extends('site.master')

@section('content')
    <div class="login">
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5 " id="register_box">
                    <div class="row">
                        <div class="col-12 px-md-5 d-flex justify-content-center align-items-center flex-column">
                            {!! $content_campaign !!}
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach
                            <form id="user_register_form" class="mt-3" method="POST"
                                  action="{{ route('campaign.register.insert') }}">
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
                                <div class="form-group position-relative box d-none" id="register_description">
                                    <label>نیاز خرید خود را وارد کنید</label>
                                    <textarea
                                        rows="4"
                                        cols="33"
                                        placeholder="مثال : روغن مایع"
                                        class="p-2 border-primary text-black-500"
                                        name="description"
                                    ></textarea>
                                </div>
                                <div class="form-group form-button mt-4">
                                    <div class="row my-2">
                                        <div class="col-12 col-md-6 mb-1">
                                            <input class="btn btn-orange text-white font-16 px-4  w-100" type="submit"
                                                   value="ثبت"/>
                                        </div>
                                    </div>
                                </div>
                            </form>
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
