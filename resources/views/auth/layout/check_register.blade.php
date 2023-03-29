<div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5" id="check_mobile_box">
    <form id="check_register">
        <div class="form-group col-md-6 position-relative box">
            <label>شماره موبایل خود را وارد نمایید .</label>
            <br>
            <label for="phone" class="position-absolute"><i
                    class="fas fa-mobile-alt"></i></label>
            <input id="phone_number" type="text"
                   class="border-0 py-2 px-2 w-100 pr-4"
                   name="phone_number" value="{{ old('phone_number') }}" required autofocus
                   placeholder="شماره موبایل">
        </div>
        <div class="form-group form-button mt-4">
            <div class="row my-2">
                <div class="col-12 col-md-6 mb-1">
                    <input id="first_submit" type="submit" class="btn btn-orange text-white font-16 px-4  w-100"
                           value="تایید"/>
                </div>
            </div>
        </div>
    </form>
</div>
