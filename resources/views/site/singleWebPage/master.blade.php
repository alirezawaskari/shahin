<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="@yield('description','   لاله کارت سامانه هوشمند یکپارچه اقتصادی با هدف کاهش هزینه های زندگی مردم عزیز کشورمان درحوزه دندانپزشکی اقساطی و ایجاد رقابت بین تولید کنندگان و خدمات دهندگان')">
    {{--
    <meta name="keywords" content="دندانپزشکی اقساط">--}}
    <meta name="author" content="خدمات درمانی - رفاهی لاله">
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
    <title>@yield('title','خدمات درمانی - رفاهی لاله')</title>
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

    <div class="position-fixed d-none d-md-block" style="left: 0; bottom: 0;">
        <img src="../../images/logo.png" alt=""
            onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=450, height=630, top=30')"
            style="cursor:pointer">
        <img referrerpolicy="origin" id='jxlzfukznbqergvjnbqefukz' style='cursor:pointer'
            onclick='window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")'
            alt='logo-samandehi' src='https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />
        <a target="_blank" href="https://trustseal.enamad.ir/?id=40920&amp;Code=6abmm9WQ9521uXon0exd"><img
                src="https://Trustseal.eNamad.ir/logo.aspx?id=40920&amp;Code=6abmm9WQ9521uXon0exd" alt=""
                style="cursor:pointer" id="6abmm9WQ9521uXon0exd"></a>
        {{-- <script class="d-none" type="text/javascript">
            window.$crisp=[];window.CRISP_WEBSITE_ID="9aa6b7fc-b1ba-437f-800d-b26efe273153";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();
        </script> --}}
        <script type="text/javascript">
            !function () { var i = "7aaX4j", a = window, d = document; function g() { var g = d.createElement("script"), s = "https://www.goftino.com/widget/" + i, l = localStorage.getItem("goftino_" + i); g.async = !0, g.src = l ? s + "?o=" + l : s; d.getElementsByTagName("head")[0].appendChild(g); } "complete" === d.readyState ? g() : a.attachEvent ? a.attachEvent("onload", g) : a.addEventListener("load", g, !1); }();
        </script>

    </div>
    <div class="position-fixed d-none - d-md-block" style="left: 120px;bottom: 0;">
    </div>
    @yield('js')
</body>

</html>
