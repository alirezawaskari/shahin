@extends('site.master')

@section('title')
    ورود به حساب کاربری
@endsection

@section('content')

    <div class="login">

        {{-- login --}}
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5">
                    <div class="row">
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center flex-column">
                            <h2 class="text-orange bold mb-4 text-black-50">ورود</h2>

                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach

                            <form method="POST" action="{{ route('login') }}" class="mt-2">
                                @csrf
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-mobile-alt"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="int" name="phone"
                                           value="{{ old('phone') }}" required placeholder="شماره موبایل">
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="password" class="position-absolute"><i class="fas fa-key"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4"
                                           value="{{ old('password') }}" required type="password" name="password"
                                           placeholder="رمز عبور"><br>
                                </div>
                                <div class="custom-control custom-checkbox my-1 mr-sm-2">
                                    <input type="checkbox" class="custom-control-input" id="customControlInline" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="custom-control-label login-checkbox" for="customControlInline">مرا به خاطر
                                        بسپار</label>
                                </div>
                                <div class="form-group form-button mt-4">
                                    <input class="btn btn-orange text-white font-16 px-4" type="submit" value="ورود"/>
                                    <a href="{{ route('register') }}" class="text-black-50 border-0 font-14 bold mr-2">هنوز ثبت نام نکرده اید؟!</a>
									<a href="https://admin.lalecard.com/" class="text-black-50 border-0 font-14 bold mr-2">ورود فروشندگان و تامین کنندگان کالا و خدمات</a>
                                </div>
                            </form>

                        </div>
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center align-items-center">
                            <img src="{{ asset("/assets/site/images/login-img.jpg") }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection