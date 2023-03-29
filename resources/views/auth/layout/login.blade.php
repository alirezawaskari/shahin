<div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5" id="login_box" style="display: none" >
    <form method="POST"  action="{{ route('login') }}">
        @csrf
        <input id="phone_login" type="hidden"
               class="border-0 py-2 px-2 w-100 pr-4 phone"
               name="phone"  required autofocus
               placeholder="شماره موبایل">
        <div class="form-group position-relative box col-md-6">
            <label for="password" class="">رمز دریافتی خود را وارد نمایید <i class="fas fa-key"></i></label>
            <input class="border-0 py-2 px-2 w-100 pr-4" type="password" id="pass" required="" name="password" placeholder="رمز عبور">
        </div>
        <div class="custom-control custom-checkbox my-1 mr-sm-2">

            <input class="custom-control-input" type="checkbox" name="remember" id="customControlInline">

            <label class="custom-control-label login-checkbox" for="customControlInline">مرا به خاطر
                بسپار</label>
        </div>
        <div class="form-group form-button mt-4">
            <div class="row my-2">
                <div class="col-12 col-md-6 mb-1">
                    <input id="" type="submit" class="btn btn-orange text-white font-16 px-4  w-100"
                           value="تایید"/>
                </div>
            </div>
        </div>
    </form>
</div>
