@extends('site.master')

@section('content')

    <div class="login">

        {{-- register --}}
        <div class="login-section bg-gray py-5">
            <div class="container">
                @include('auth.employee.check_register')
                <br>
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5 " id="register_box" style="display: none">
                    <div class="row">
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center flex-column">
                            <h2 class="text-orange bold mb-4 text-black-50">ثبت نام همکار</h2>

                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach

                            <form id="user_register_form" class="mt-3" method="POST" action="/employeeRegister">
                                @csrf
                                
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i
                                            class="fas fa-mobile-alt"></i></label>
                                    <input id="phone_register" type="text"
                                           class="border-0 py-2 px-2 w-100 pr-4 phone"
                                           name="phone" value="{{ old('phone') }}" required autofocus
                                           placeholder="شماره موبایل">
                                </div>

                                <div class="form-group position-relative box">
                                    <label class="position-absolute"><i
                                            class="fas fa-mobile-alt"></i></label>
                                    <input type="number"
                                           class="border-0 py-2 px-2 w-100 pr-4"
                                           name="verificationCode" required autofocus
                                           placeholder="کد ارسال شده">
                                </div>

                                <div class="form-group position-relative box">
                                    <label class="position-absolute"><i
                                            class="fas fa-mobile-alt"></i></label>
                                    <input type="text"
                                           class="border-0 py-2 px-2 w-100 pr-4"
                                           name="nationalCode" value="{{ old('nationalCode') }}" required autofocus
                                           placeholder="کدملی">
                                </div>
                                
                                <div class="form-group position-relative box">
                                    <label class="position-absolute"><i
                                            class="fas fa-mobile-alt"></i></label>
                                    <input type="text"
                                           class="border-0 py-2 px-2 w-100 pr-4" id="date"
                                           name="birthDate" value="{{ old('birthDate') }}" required>
                                </div>

                                <div class="form-group form-button mt-4">
                                    <div class="row my-2">
                                        <div class="col-12 col-md-6 mb-1">
                                            <input class="btn btn-orange text-white font-16 px-4  w-100" type="submit"
                                                   value="ثبت نام"/>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <a href="{{ route('login') }}" class="text-black-50 border-0 font-14 bold mr-2 mt-2">قبلا ثبت نام کرده اید؟اینجا کلیک کنید</a>
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
@section('js')
    <script src="/assets/site/js/fix-number.js?v=1"></script>
    <script src="/assets/site/js/employeeRegister.js?v=1"></script>
    <script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@endsection
