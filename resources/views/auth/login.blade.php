@extends('site.master')

@section('content')

    <div class="login">

        {{-- login --}}
        <div class="login-section bg-gray py-5">
            <div class="container">
                <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5">
                    <div class="row">
                        <div class="col-12 col-md-6 px-md-5 d-flex justify-content-center flex-column">
                            <h2 class="text-orange bold mb-4 text-black-50">ورود همکاران </h2>
                            @foreach($errors->all() as $error)
                                <div class="alert alert-danger" role="alert">
                                    {{ $error }}
                                </div>
                            @endforeach
                            <form method="POST" id="login_form" class="mt-2" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group position-relative box">
                                    <label for="phone" class="position-absolute"><i class="fas fa-mobile-alt"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="text" name="phone" id="phone" required placeholder="شماره موبایل"/>
                                </div>
                                <div class="form-group position-relative box">
                                    <label for="password" class="position-absolute"><i class="fas fa-key"></i></label>
                                    <input class="border-0 py-2 px-2 w-100 pr-4" type="password" id="pass" required name="password" placeholder="رمز عبور"/>
                                </div>
                                <div class="custom-control custom-checkbox my-1 mr-sm-2">

                                    <input class="custom-control-input" type="checkbox" name="remember" id="customControlInline" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="custom-control-label login-checkbox" for="customControlInline">مرا به خاطر
                                        بسپار</label>
                                </div>
                                <div class="form-group form-button  d-flex flex-column">
                                    <div class="row my-2">
                                        <div class="col-12 col-md-6 mb-1">
                                            <input class="btn btn-orange text-white font-16 px-4  w-100" type="submit" onclick="convertEnglish()" value="ورود"/>
                                        </div>
                                        <div class="col-12 col-md-6 mb-1">
                                            <a href="{{ route('employee.register') }}" class="btn btn-primary align-items-center d-block">ثبت نام همکاران </a>
                                        </div>
                                    </div>
                                    <a href="{{ route('reset_pass_form') }}" class="text-black-50 border-0 font-14 bold mr-2 mt-2">رمز عبور خود را فراموش کرده اید؟</a>
								   {{-- <a href="https://admin.lalecard.ir/" class="text-black-50 border-0 font-14 bold mr-2 mt-2">ورود فروشندگان و تامین کنندگان کالا و خدمات (برای ورود اینجا کلیک کنید)</a> --}}
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

    @if(session()->get('reset_pass')=='success')
        <span id="resetpass_success"></span>
    @endif
    <script>
        function convertEnglish()
        {
            String.prototype.toEnglishDigit = function()
            {
                var find = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
                var replace = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                var replaceString = this; var regex;
                for (var i = 0; i < find.length; i++)
                {
                    regex = new RegExp(find[i], "g");
                    replaceString = replaceString.replace(regex, replace[i]);
                }
                return replaceString;
            };
            var fa_number = document.getElementById('phone').value;
            document.getElementById('phone').value = fa_number.toEnglishDigit();
            var fa_number = document.getElementById('pass').value;
            document.getElementById('pass').value = fa_number.toEnglishDigit();
        }



    </script>
@endsection
@section('footer')

@endsection
@section('js')
    <script>
        String.prototype.toEnglishDigit = function() {
            const find = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            const replace = ['0','1','2','3','4','5','6','7','8','9'];
            let str = this;
            find.forEach((f, i) => {
                str = str.replace(new RegExp(f, 'g'), replace[i]);
            });
            return str;
        };

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login_form');
            form.addEventListener('submit', function(e) {
                const phoneInput = form.querySelector('input[name="phone"]');
                phoneInput.value = phoneInput.value.toEnglishDigit();

                const passInput = form.querySelector('input[name="password"]');
                passInput.value = passInput.value.toEnglishDigit();

            });
        });
    </script>
    <script src="/assets/site/js/login.js?v=1"></script>
@endsection
