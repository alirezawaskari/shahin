@extends('site.master')

@section('content')

    <div class="login">

        {{-- register --}}
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5">
                    <div class="row">
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center flex-column">
                            <p class="text-orange bold mb-4 text-black-50">ثبت نام جهت دریافت کارگزاری عرضه محصولات و خدمات لاله</p>

                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach

                            <form class="mt-3" method="POST" action="{{ route('register') }}">
                                @csrf
                                <div class="form-group position-relative box">
                                    <label for="name" class="position-absolute"><i class="far fa-user"></i></label>
                                    <input id="name" type="text"
                                           class="border-0 py-2 px-2 w-100 pr-4"
                                           name="name" value="{{ old('name') }}" required autofocus placeholder="نام">
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="family" class="position-absolute"><i class="far fa-user"></i></label>
                                    <input id="family" type="text"
                                           class="border-0 py-2 px-2 w-100 pr-4"
                                           name="family" value="{{ old('family') }}" required autofocus
                                           placeholder="نام خانوادگی">
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i
                                                class="fas fa-mobile-alt"></i></label>
                                    <input id="phone" type="text"
                                           class="border-0 py-2 px-2 w-100 pr-4"
                                           name="phone" value="{{ old('phone') }}" required autofocus
                                           placeholder="شماره موبایل">
                                </div>

                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50"
                                            name="province_id" id="province_id" required>
                                        <option value="">انتخاب استان</option>
                                        @foreach($provinces as $pro)
                                            <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-map-pin"></i></label>
                                    <select class="border-0 py-2 px-2 w-100 pr-4 text-black-50"
                                            name="city_id" id="city_id" required>
                                        <option value="">انتخاب شهر</option>
                                        @foreach($citys as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
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
								<input type="hidden" id="service_id" value="100" name="service_id">
                                <div class="form-group position-relative box">
                                    <label for="password" class="position-absolute"><i class="fas fa-key"></i></label>
                                    <input id="password" type="password"
                                           class="border-0 py-2 px-2 w-100 pr-4"
                                           name="password" required placeholder="رمز عبور">

                                </div>
                                <div class="form-group position-relative box">
                                    <label for="password" class="position-absolute"><i class="fas fa-key"></i></label>
                                    <input id="password-confirm" type="password" class="border-0 py-2 px-2 w-100 pr-4"
                                           name="password_confirmation" required placeholder="تکرار رمز عبور">

                                </div>
                                <div class="form-group form-button mt-4">
                                    <div class="row my-2">
                                        <div class="col-12 col-md-6 mb-1">
                                            <input class="btn btn-orange text-white font-16 px-4  w-100" type="submit"
                                                   value="ثبت نام"/>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <a class="btn btn-primary text-white font-16 px-4 w-100"
                                               href="{{ route('login') }}">ورود</a>
                                        </div>
                                    </div>
                                </div>
                            </form>

                        </div>
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center align-items-center">
                            <img class="img-fluid" src="{{ asset("/assets/site/images/login-img.gif") }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
