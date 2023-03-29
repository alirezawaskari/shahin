@extends('site.master')

@section('title')
    ثبت نام در لاله
@endsection

@section('content')

    <div class="login">

        {{-- register --}}
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5">
                    <div class="row">
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center flex-column">
                            <h2 class="text-orange bold mb-4 text-black-50">ثبت نام</h2>
                            <form method="POST" action="">
                                <div class="form-group position-relative box">
                                    <label for="name" class="position-absolute"><i class="far fa-user"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="text" name="name" placeholder="نام"/>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="family" class="position-absolute"><i class="far fa-user"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="text" name="family" placeholder="نام خانوادگی"/>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-mobile-alt"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="text" name="phone" placeholder="شماره موبایل"/>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="password" class="position-absolute"><i class="fas fa-key"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="password" name="password" placeholder="رمز عبور"/>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="password" class="position-absolute"><i class="fas fa-key"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="password" name="password" placeholder="تکرار رمز عبور"/>
                                </div>
                                <div class="form-group form-button mt-4">
                                    <input class="btn btn-orange text-white font-16 px-4" type="submit" value="ثبت نام"/>
                                    <a href="" class="text-black-50 border-0 font-14 bold mr-2">قبلا ثبت نام کرده اید؟!</a>
                                </div>
                            </form>

                        </div>
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center align-items-center">
                            <img src="{{ asset("/assets/site/images/register-img.jpg") }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection