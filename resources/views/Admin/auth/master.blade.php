<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF token برای استفاده در AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'ورود به پنل مدیریت')</title>

    {{-- Bootstrap موجود پروژه --}}
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/bootstrap.min.css') }}">

    {{-- استایل اختصاصی صفحه‌ی لاگین --}}
    <link rel="stylesheet" href="{{ asset('assets/dashboard/css/admin-login.css') }}">

    @yield('styles')
</head>
<body>

@yield('content')

{{-- jQuery از مسیر بومی پروژه --}}
{{-- اگر مسیر jQuery در پروژه‌ی شما متفاوت است، این خط را اصلاح کنید --}}
<script src="{{ asset('assets/script/jquery-ui-1.13.2.custom/external/jquery/jquery.js') }}"></script>

{{-- منطق صفحه‌ی لاگین (tabs، تایمر، AJAX) --}}
<script src="{{ asset('assets/dashboard/js/admin-login.js') }}"></script>

@yield('scripts')
</body>
</html>
