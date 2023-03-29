<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description','شبکه خدمات رفاهی لاله سامانه هوشمند یکپارچه اقتصادی با هدف کاهش هزینه های زندگی مردم عزیز کشورمان و ایجاد رقابت بین تولید کنندگان و خدمات دهندگان')">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <link rel="icon" href="{{asset('assets/site/images/fav.png')}}" type="image/png">

    <title>@yield('title','لاله کارت')</title>
    <link rel="stylesheet" href="{{asset('assets/site/css/app.css')}}?ver=6">
    <link rel="stylesheet" href="{{ asset('assets/site/css/landing.css') }}">
    <link rel="stylesheet" href="{{asset('assets/css/persian-datepicker.min.css')}}">


    @yield('header')
	    <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="9aa6b7fc-b1ba-437f-800d-b26efe273153";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
</head>
<body>

@yield('content')

<script src="{{asset('assets/site/js/app.js')}}"></script>
<script src="{{asset('assets/js/persian-date.min.js')}}"></script>
<script src="{{asset('assets/js/persian-datepicker.min.js')}}"></script>
@yield('footer')
<div class="position-fixed d-none - d-md-block" style="left: 0; bottom: 0;">
	<img src="../../images/logo.png" alt="" onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=450, height=630, top=30')" style="cursor:pointer">
    <img id = 'jxlzfukznbqergvjnbqefukz' style = 'cursor:pointer' onclick = 'window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")' alt = 'logo-samandehi' src = 'https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />
    <a target="_blank" href="https://trustseal.enamad.ir/?id=40920&amp;Code=6abmm9WQ9521uXon0exd"><img src="https://Trustseal.eNamad.ir/logo.aspx?id=40920&amp;Code=6abmm9WQ9521uXon0exd" alt="" style="cursor:pointer" id="6abmm9WQ9521uXon0exd"></a>
	</div>
<div class="position-fixed d-none - d-md-block" style="left: 120px;bottom: 0;">
</div>
</div>

</body>
</html>
