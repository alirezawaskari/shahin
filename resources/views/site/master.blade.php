<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description"
        content="@yield('description', '   لاله کارت سامانه هوشمند یکپارچه اقتصادی با هدف کاهش هزینه های زندگی مردم عزیز کشورمان درحوزه دندانپزشکی اقساطی و ایجاد رقابت بین تولید کنندگان و خدمات دهندگان')">
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
    <script>
        // توابع کمکی کوکی
  function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + (days*24*60*60*1000));
    document.cookie = name + "=" + value + ";expires=" + d.toUTCString() + ";path=/";
  }

  function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
      let c = ca[i].trim();
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
    }
    return null;
  }

  document.addEventListener('DOMContentLoaded', function() {
    if (!getCookie('modal_seen_first_visit')) {
      // نمایش مودال با Bootstrap 4
      $('#whatsappModal').modal('show');
      // ست کردن کوکی برای یک سال
      setCookie('modal_seen_first_visit', '1', 365);
    }
  });
    </script>
    <link rel="shortcut icon" href="{{ asset('assets/dashboard/images/laleh_icon.png') }}">
    <title>@yield('title','شبکه خدمات رفاهی ، درمانی و اقساطی لاله')</title>
    <link rel="stylesheet" href="{{asset('assets/site/css/app.css?v=1.6')}}?ver=6">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/persian-datepicker.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/font-awesome.css')}}">
    {!! $setting->extra_logo !!}

    @yield('header')

    <link rel="manifest" href="/manifest.json">

</head>

<body class="position-relative">
    @include('site.layouts.header')

    @yield('content')

    @include('site.layouts.footer')

    <script src="{{asset('assets/site/js/app.js?v:1.4')}}">
    </script>
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
        <a referrerpolicy='origin' target='_blank'
            href='https://trustseal.enamad.ir/?id=40920&Code=6abmm9WQ9521uXon0exd'><img referrerpolicy='origin'
                src='https://trustseal.enamad.ir/logo.aspx?id=40920&Code=6abmm9WQ9521uXon0exd' alt=''
                style='cursor:pointer' code='6abmm9WQ9521uXon0exd'></a>
        <!-- <script type="text/javascript">
            window.$crisp = []; window.CRISP_WEBSITE_ID = "9aa6b7fc-b1ba-437f-800d-b26efe273153"; (function () { d = document; s = d.createElement("script"); s.src = "https://client.crisp.chat/l.js"; s.async = 1; d.getElementsByTagName("head")[0].appendChild(s); })();
        </script> -->

        <script type="text/javascript">
            !function () { var i = "7aaX4j", a = window, d = document; function g() { var g = d.createElement("script"), s = "https://www.goftino.com/widget/" + i, l = localStorage.getItem("goftino_" + i); g.async = !0, g.src = l ? s + "?o=" + l : s; d.getElementsByTagName("head")[0].appendChild(g); } "complete" === d.readyState ? g() : a.attachEvent ? a.attachEvent("onload", g) : a.addEventListener("load", g, !1); }();
        </script>
    </div>

    {{-- <div class="modal fade" id="whatsappModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-start">
                    <i class="fas fa-mobile-alt font-18 text-orange ml-2"></i>
                    <h6 class="modal-title" id="phoneModalLable">استخدام فوری :</h6>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <img width="100%" src="https://zoomtech.org/wp-content/uploads/2018/01/download.jpg">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-center">
                        <strong>
                            جهت حمایت شغلی از شما اعضاء خوب لاله
                            نیروی کار اداری، فروش ، پاسخگوی مرکز تماس و نظافتچی در البرز
                            جهت بازدید از موقعیتهای شغلی روی لینک زیر کلیک کنید
                        </strong>
                        <br>
                    </h6>
                </div>
                <div class="row col-12">
                    <div class="col-12 mb-3">
                        <a href="https://lalecard.ir/Permanent-employment" class="w-100 btn btn-info">
                            فهرست موقعیت های شغلی
                        </a>
                    </div>
                </div>
                <div class="mb-1 p-1" style="margin-right: auto">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div> --}}
    <script src="{{ asset('assets/site/js/site-upload.js') }}"></script>
    @yield('js')
</body>

</html>
