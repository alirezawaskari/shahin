{{-- فرم ورود با شماره موبایل و رمز عبور --}}
{{-- این فرم با POST معمولی کار می‌کند (نه AJAX) --}}
<form method="POST"
      action="{{ route('admin.login.password') }}"
      class="login-form"
      autocomplete="on">
    @csrf

    {{-- نمایش خطای کلی auth (شماره/پسورد اشتباه، غیرفعال بودن حساب، throttle) --}}
    @if ($errors->has('auth'))
        <div class="alert alert-danger" role="alert">
            {{ $errors->first('auth') }}
        </div>
    @endif

    {{-- فیلد شماره موبایل --}}
    <div class="form-group">
        <label for="password-phone">شماره موبایل</label>
        <input type="text"
               id="password-phone"
               name="phone"
               class="form-control numeric-input @error('phone') is-invalid @enderror"
               placeholder="09123456789"
               value="{{ old('phone') }}"
               required
               maxlength="11"
               dir="ltr"
               inputmode="numeric"
               autocomplete="username">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- فیلد رمز عبور --}}
    <div class="form-group">
        <label for="password-password">رمز عبور</label>
        <input type="password"
               id="password-password"
               name="password"
               class="form-control @error('password') is-invalid @enderror"
               required
               autocomplete="current-password">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- دکمه‌ی ورود --}}
    <button type="submit" class="btn btn-primary btn-block">
        ورود
    </button>
</form>
