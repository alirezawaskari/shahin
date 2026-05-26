{{-- مرحله ۱: گرفتن شماره موبایل و درخواست ارسال کد --}}
{{-- این فرم با AJAX کار می‌کند --}}
<form id="otp-request-form"
      class="login-form"
      data-action="{{ route('admin.login.otp.request') }}"
      autocomplete="off">
    @csrf

    {{-- محل نمایش پیام (success یا error) --}}
    <div id="otp-request-alert"
         class="alert"
         role="alert"
         style="display:none;"></div>

    <p class="otp-help-text">
        شماره موبایل ثبت شده در سیستم را وارد کنید تا کد ورود برای شما ارسال شود.
    </p>

    <div class="form-group">
        <label for="otp-phone">شماره موبایل</label>
        <input type="text"
               id="otp-phone"
               name="phone"
               class="form-control numeric-input"
               placeholder="09123456789"
               required
               maxlength="11"
               dir="ltr"
               inputmode="numeric"
               autocomplete="username">
    </div>

    <button type="submit"
            id="otp-request-btn"
            class="btn btn-primary btn-block">
        <span class="btn-text">دریافت کد</span>
        <span class="btn-loader" style="display:none;">در حال ارسال...</span>
    </button>
</form>
