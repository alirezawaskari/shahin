{{-- مرحله ۲: وارد کردن کد و تایید --}}
{{-- این فرم در ابتدا مخفی است؛ پس از موفقیت مرحله ۱ نمایش داده می‌شود --}}
<form id="otp-verify-form"
      class="login-form"
      data-action="{{ route('admin.login.otp.verify') }}"
      style="display:none;"
      autocomplete="off">
    @csrf

    {{-- فیلد مخفی برای شماره موبایل --}}
    <input type="hidden" name="phone" id="otp-verify-phone">

    {{-- محل نمایش پیام --}}
    <div id="otp-verify-alert"
         class="alert"
         role="alert"
         style="display:none;"></div>

    {{-- نمایش شماره مقصد + امکان تغییر --}}
    <div class="otp-info">
        کد به شماره
        <strong id="otp-verify-phone-display" dir="ltr"></strong>
        ارسال شد.
        <a href="#" id="otp-change-phone">تغییر شماره</a>
    </div>

    {{-- ورودی کد --}}
    <div class="form-group">
        <label for="otp-code">کد یکبار مصرف</label>
        <input type="text"
               id="otp-code"
               name="code"
               class="form-control otp-input"
               required
               maxlength="{{ config('admin_auth.otp.length', 6) }}"
               dir="ltr"
               inputmode="numeric"
               autocomplete="one-time-code"
               placeholder="------">
    </div>

    {{-- تایمر و دکمه ارسال مجدد --}}
    <div class="otp-timer">
        <span id="otp-timer-display"></span>
        <button type="button"
                id="otp-resend-btn"
                class="btn btn-link"
                style="display:none;">
            ارسال مجدد کد
        </button>
    </div>

    <button type="submit"
            id="otp-verify-btn"
            class="btn btn-success btn-block">
        <span class="btn-text">ورود</span>
        <span class="btn-loader" style="display:none;">در حال ورود...</span>
    </button>
</form>
