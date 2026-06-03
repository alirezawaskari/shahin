@extends('Admin.auth.master')

@section('title', 'ورود به پنل مدیریت')

@section('content')
    <div class="login-wrapper">
        <div class="login-card">

            <h1 class="login-title">ورود به پنل مدیریت</h1>
            <p class="login-subtitle">برای ورود، یکی از روش‌های زیر را انتخاب کنید</p>

            {{-- ─── Tabs ─── --}}
            <ul class="nav nav-tabs login-tabs" role="tablist">
                <li class="nav-item">
                    <a href="#tab-password"
                       class="nav-link active"
                       data-tab="password"
                       role="tab">
                        <i class="login-tab-icon">🔒</i>
                        ورود با رمز عبور
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#tab-otp"
                       class="nav-link"
                       data-tab="otp"
                       role="tab">
                        <i class="login-tab-icon">📱</i>
                        ورود با کد یکبار مصرف
                    </a>
                </li>
            </ul>

            {{-- ─── Tab content ─── --}}
            <div class="tab-content login-tab-content">

                {{-- تب ۱: ورود با پسورد --}}
                <div class="tab-pane active" id="tab-password" role="tabpanel">
                    @include('Admin.auth.partials.password-form')
                </div>

                {{-- تب ۲: ورود با OTP (دو مرحله‌ای) --}}
                <div class="tab-pane" id="tab-otp" role="tabpanel">
                    @include('Admin.auth.partials.otp-request-form')
                    @include('Admin.auth.partials.otp-verify-form')
                </div>

            </div>

        </div>
    </div>
@endsection
