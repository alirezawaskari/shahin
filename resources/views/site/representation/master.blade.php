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
</head>
<body>

    @include('site.representation.header')

    @yield('content')

    @include('site.representation.footer')
<script src="{{asset('assets/site/js/app.js')}}"></script>
<script src="{{asset('assets/js/persian-date.min.js')}}"></script>
<script src="{{asset('assets/js/persian-datepicker.min.js')}}"></script>
@yield('footer')
<div class="position-fixed d-none d-md-block" style="right: 5px; bottom: 0;color: gray">
    ارتباط با کارشناسان
    <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="9aa6b7fc-b1ba-437f-800d-b26efe273153";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
</div>
<div class="position-fixed d-none - d-md-block" style="left: 0; bottom: 0;">
	<img src="../../images/logo.png" alt="" onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=450, height=630, top=30')" style="cursor:pointer">
    <img id = 'jxlzfukznbqergvjnbqefukz' style = 'cursor:pointer' onclick = 'window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")' alt = 'logo-samandehi' src = 'https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />
    <a target="_blank" href="https://trustseal.enamad.ir/?id=40920&amp;Code=6abmm9WQ9521uXon0exd"><img src="https://Trustseal.eNamad.ir/logo.aspx?id=40920&amp;Code=6abmm9WQ9521uXon0exd" alt="" style="cursor:pointer" id="6abmm9WQ9521uXon0exd"></a>
	</div>
<div class="d-md-block d-none position-fixed col-12" style="left: 0; bottom: 0;">
{{--					 <div class="row mt-2" style="font-size:10px;">--}}
{{--                            <div class="col-3">--}}
{{--                                <a data-toggle="modal" data-target="#whatsappModal">--}}
{{--                                <img class="rounded-circle" src="/img/whatsapp.png" width="50px">--}}
{{--                                <p class="text-dark mr-1 font-weight-bolder">پشتیبانی</p>--}}
{{--                                </a>--}}
{{--                            </div>--}}
{{--					 </div>--}}
{{--                    </div>--}}
</div>
{{--<div class="modal fade" id="whatsappModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">--}}
{{--    <div class="modal-dialog" role="document">--}}
{{--        <div class="modal-content">--}}
{{--                    <div class="modal-header d-flex justify-content-start">--}}
{{--					    <button type="button" class="close" data-dismiss="modal">&times;</button>--}}
{{--                        <i class="fas fa-mobile-alt font-18 text-orange ml-2"></i>--}}
{{--                        <h6 class="modal-title" id="phoneModalLable">پشتیبانی</h6>--}}
{{--                    </div>--}}
{{--                    <div class="modal-body">--}}
{{--                            <h6 >کاربران گرامی:</h6>--}}
{{--                            <p>--}}
{{--                              درنظر داشته باشید تمامی پرداخت ها برای دریافت خدمات تنها از طریق لینک معتبر سایت و به حساب شرکت جانبازان آینده ساز می باشد.از پرداخت وجه به حساب دیگر خودداری فرمایید. درصورت مشاهده چنین درخواست هایی از قسمت ثبت شکایت درخواست کننده را معرفی نمایید.--}}
{{--                            </p>--}}
{{--                            <div class="row my-4" style="font-size:10px;">--}}
{{--                               @if(isset($callcenter->id))--}}
{{--                    <div class="col-12 col-md-6 mb-3">--}}
{{--                        <a href="/contact" class="btn btn-danger w-100 d-flex align-items-center">--}}
{{--                            <i class="fa fa-user ml-2 font-18" aria-hidden="true"></i>--}}
{{--						    ثبت شکایت--}}
{{--                        </a>--}}
{{--                    </div>--}}
{{--                        <div class="col-12 col-md-6">--}}
{{--                            <a href="https://wa.me/98{{$callcenter->phone}}" class="btn btn-success w-100 d-flex align-items-center">--}}
{{--                                <i class="fab fa-whatsapp ml-2 font-18"></i>--}}
{{--                                شروع چت و پشتیبانی--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    @else--}}
{{--                        <div class="col-3">--}}
{{--                            <a href="/contact">--}}
{{--                                <img class="rounded-circle mb-2" src="/img/user.png" width="50px">--}}
{{--								<p class="text-dark mr-1 font-weight-bolder">ثبت شکایت</p>--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                        <div class="col-3">--}}
{{--                            <a href="https://wa.me/989391143833">--}}
{{--                            <img class="rounded-circle mb-2" src="/img/whatsapp.png" width="50px">--}}
{{--                            <p class="text-dark mr-1 font-weight-bolder">پشتیبانی</p>--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                        <div class="col-3">--}}
{{--                            <a href="https://wa.me/989050647423">--}}
{{--                                <img class="rounded-circle mb-2" src="/img/whatsapp.png" width="50px">--}}
{{--                                <p class="text-dark mr-1 font-weight-bolder">امور اداری</p>--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                        <div class="col-3">--}}
{{--                            <a href="https://wa.me/989129750635">--}}
{{--                                <img class="rounded-circle mb-2" src="/img/whatsapp.png" width="50px">--}}
{{--                                <p class="text-dark mr-1 font-weight-bolder"> امور مالی</p>--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    @endif--}}
{{--                            </div>--}}
{{--                    </div>--}}
{{--        </div>--}}
{{--     </div>--}}
</div>
</body>
</html>
