@extends('site.master')

@section('content')

    <div class="login">

        {{-- reserPassword --}}
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5">
                    <div class="row">
                        <div class="col-12 col-md-6 mx-auto d-flex justify-content-center flex-column">
                            <h2 class="text-orange bold mb-4 text-black-50 text-center">فراموشی رمز عبور</h2>

                            <form method="POST" class="mt-3" action="{{ route('reset_pass') }}">
                                @csrf
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-mobile-alt"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="text" minlength="11" maxlength="11" required name="phone" placeholder="شماره موبایل"/>
                                </div>
                                <div class="form-group form-button mt-4">
                                    <input class="btn btn-orange text-white font-16 px-4" type="submit" value="ارسال رمز جدید"/>
                                    <a href="{{ route('login') }}" class="text-black-50 border-0 font-14 bold mr-2">ورود به سایت</a>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @if(session()->get('reset_pass')=='success')
        <span id="resetpass_success"></span>
    @elseif(session()->get('reset_pass')=='error')
        <span id="resetpass_error"></span>
    @endif

@endsection
