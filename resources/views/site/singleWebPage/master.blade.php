<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="@yield('description','   لاله کارت سامانه هوشمند یکپارچه اقتصادی با هدف کاهش هزینه های زندگی مردم عزیز کشورمان درحوزه دندانپزشکی اقساطی و ایجاد رقابت بین تولید کنندگان و خدمات دهندگان')">
    {{--
    <meta name="keywords" content="دندانپزشکی اقساط">--}}
    <meta name="author" content="شبکه خدمات رفاهی ، درمانی و اقساطی لاله">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="google-site-verification" content="2LiK03QLZtoxWosDgNx_gXH9ffVfCJmTiMduAI643K8" />
    <!-- Global site tag (gtag.js) - Google Analytics -->
    {!! SEO::generate() !!}
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-133477056-1"></script>
    <script src="{{asset('assets/script/ckeditor/ckeditor.js')}}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-5SCCYKP8ZJ');
    </script>

    <link rel="shortcut icon" href="{{ asset('assets/dashboard/images/laleh_icon.png') }}">
    <title>@yield('title','شبکه خدمات رفاهی ، درمانی و اقساطی لاله')</title>
    <link rel="stylesheet" href="{{asset('assets/site/css/app.css?v=1.6')}}?ver=6">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/persian-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/font-awesome.css')}}">

    @yield('header')
    <link rel="manifest" href="/manifest.json">

</head>

<body class="position-relative">
    @include('site.singleWebPage.header')

    @yield('content')

    @include('site.singleWebPage.footer')

    <script src="{{asset('assets/site/js/app.js?v:1.4')}}"></script>
    <script src="{{asset('assets/dashboard/js/persian-date.min.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/persian-datepicker.min.js')}}"></script>
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js">
    </script>
    @yield('footer')
    {{-- @yield('footer')--}}

    <div class="position-fixed" style="left: 0; bottom: 0;">
        <script type="text/javascript">
            !function () { var i = "7aaX4j", a = window, d = document; function g() { var g = d.createElement("script"), s = "https://www.goftino.com/widget/" + i, l = localStorage.getItem("goftino_" + i); g.async = !0, g.src = l ? s + "?o=" + l : s; d.getElementsByTagName("head")[0].appendChild(g); } "complete" === d.readyState ? g() : a.attachEvent ? a.attachEvent("onload", g) : a.addEventListener("load", g, !1); }();
        </script>

    </div>
    <div class="position-fixed d-none - d-md-block" style="left: 120px;bottom: 0;">
    </div>
    <script src="{{ asset('assets/site/js/site-upload.js') }}"></script>
    @yield('js')
</body>

</html>
