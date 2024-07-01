<footer class="footer">
    <div class="">
        <div class="container ">
            <div class="row">
                <div class="col-12 col-md-6 col-top-right   ">
                    <a href="http://maps.google.com/maps?daddr=35.822979,50.986682" target="_blank"><img
                            class="img-fluid" src="/assets/site/images/map.png" alt=""></a>
                    <div class="location mt-3 mb-3">
                        <span>مسیر یابی: </span>
                        <a href="http://maps.google.com/maps?daddr=35.822979,50.986682" target="_blank"><img
                                class="icon" src="/assets/site/images/gmap.png" alt=""></a>
                        <a href="https://waze.com/ul?ll=35.822979,50.986682&navigate=yes" target="_blank"><img
                                class="icon" src="/assets/site/images/wmap.png" alt=""></a>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-top-left">
                    <img src="/assets/site/images/footer-logo.png" alt="" class="footer-logo">
                    {{-- <form action="{{ route('newsletter') }}" method="post">--}}
                        {{-- @csrf--}}
                        {{-- <input class="email" type="email" name="email" required
                            placeholder="لطفا ایمیل خود را وارد نمایید">--}}
                        {{-- <button class="send pt-1" type="submit">--}}
                            {{-- <i class="far fa-envelope-open"></i>--}}
                            {{-- </button>--}}
                        {{-- </form>--}}

                    <div class="logo mt-3">
                        <a href="/upload/documents/doc1.jpg" target="_blank">
                            <img src="/assets/site/images/delete/p-img-4.png">
                        </a>
                        <a href="/upload/documents/doc4.jpg" target="_blank">
                            <img src="/assets/site/images/delete/p-img-5.png">
                        </a>
                        <a href="/upload/documents/doc3.jpg" target="_blank">
                            <img src="/assets/site/images/delete/p-img-6.png">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="copy-right position-relative">
        {{-- <img src="{{asset('/assets/site/images/copy-right3.svg')}}" class="position-absolute cover-img" --}} {{--
            draggable="false">--}}
        <div style="padding-right: 50px;padding-left: 50px">
            <div class="row ">
                <div class="col-md-2">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:130px " class="rounded-left">
                                ارتباط با ما
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <strong>
                                آدرس :
                            </strong>
                            <span>{{ session()->has('representation') ? session()->get('representation')->address :
                                $setting->address }}</span>
                        </div>
                        <div class="col-md-12 mt-2">
                            <strong>
                                تلفن تماس :
                            </strong>
                            <span>{{ session()->has('representation') ? session()->get('representation')->call :
                                $setting->call }}</span>
                            <br>
                            <span>زمان پاسخگویی روزهای غیر تعطیل ساعت 10:00 الی 17:00 </span>
                        </div>
                        <div class="col-md-12 pt-3">
                            <img width="100px" src="{{asset('assets/dashboard/images/laleh_icon.png')}}"
                                alt="company_logo">
                            <span>شرکت خدماتی رفاهی لاله</span>
                        </div>

                    </div>
                </div>
                <div class="col-md-2 ">
                    <div class="row">
                        <div class="col-md-12 ">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:130px " class="rounded-left">
                                دسترسی سریع
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/contact">
                                <span>ارتباط با ما</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/contact">
                                <span>تماس با ما</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/">
                                <span>خانه</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/ ">
                                <span>فرصت های شغلی و استخدام در لاله </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/ ">
                                <span>قوانین و مقررات اخذ تسهیلات از لاله </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/ ">
                                <span>همکاری با مراکز و فروشندگان کالا وخدمات </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/ ">
                                <span>اخذ مجوز نمایندگی </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/help">
                                <span>قوانین سایت</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services">
                                <span>خدمات</span>
                            </a>
                        </div>

                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/about">
                                <span>درباره ما</span>
                            </a>
                        </div>

                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-lg-2">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:130px " class="rounded-left text-center">
                                لینک های مفید
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/articles">
                                <span>مقالات</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/calculate">
                                <span>محاسبه اقساط </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="{{ route('profile.factorItem')}}">
                                <span>پرونده های من</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <i class="fa fa-question" aria-hidden="true"></i>
                            <a class="text-dark" href="{{ route('site.help') }}">
                                <span>راهنما</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-lg-2 ">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:170px " class="rounded-left text-center">
                                خدمات دندانپزشکی
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/articles">
                                <span>مقالات</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>دندان پزشکی لاله </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>دندانپزشکی اقساطی </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>دندانپزشکی کرج </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>ایمپلنت قسطی در کرج </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>لمینت دندان کرج </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>لمینت دندان </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>دندانپزشکی مرکزی کرج </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>ترمیم ریشه دندان کرج </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-lg-2">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:130px " class="rounded-left text-center">
                                خدمات ساختمانی
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>بازسازی ساختمان در کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>بازسازی ساختمان در چیتگر
                                </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>خدمات ساختمانی در کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>خدمات ساختمانی اقساطی در کرج </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>خدمات ساختمانی اقساطی تهران </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>خدمات بازسازی ساختمان اقساطی مشهد</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/standardelevatorequipments">
                                <span>آسانسور اقساطی</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/standardelevatorequipments">
                                <span>نصب آسانسور کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>تغییرات دکوراسیون در کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/building">
                                <span>تغییرات دکوراسیون اقساطی در کرج </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-lg-2">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:130px " class="rounded-left text-center">
                                وکیل اقساطی کرج
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/legal">
                                <span>تنظیم لایحه در کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/legal">
                                <span>خدمات وکیل کرج </span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/legal">
                                <span>بهترین وکیل کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/legal">
                                <span>وکیل عظیمیه کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/legal">
                                <span>خدمات حقوقی کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/legal">
                                <span>وکیل خانواده در کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/legal">
                                <span>وکیل با تجربه در تهران</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-lg-2">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:130px " class="rounded-left text-center">
                                تجهیزات پزشکی
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/medical-equipment">
                                <span>دستگاه فشار خون رزمکس</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/medical-equipment">
                                <span>دستگاه سنجش قند خون</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/glasses">
                                <span>عینک طبی</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/glasses">
                                <span>عینک آفتابی</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/medical-equipment">
                                <span>دستگاه فشار خون</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 col-sm-4 col-lg-2">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 style="border-bottom: 3px solid #00c1ef; width:130px " class="rounded-left text-center">
                                جراحی زیبایی
                            </h6>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/dentistry">
                                <span>جراحی دندان در کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی انحراف بینی کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی زیبایی بینی</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی انحراف بینی در کرج</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی شکم و پهلو</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی اندامها</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی لایپوساکشن</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی ابدومینو پلاستی</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی پیکر تراشی</span>
                            </a>
                        </div>
                        <div class="col-md-12 mt-2">
                            <a class="text-dark" href="/services/beauty">
                                <span>جراحی بایپس معده</span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 row align-items-end d-md-none">

                    <div class="col-md-4">
                        {!! $setting->enamad !!}
                    </div>
                    <div class="col-md-4">
                        <img referrerpolicy="origin" id='jxlzfukznbqergvjnbqefukz' style='cursor:pointer;'
                            onclick='window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")'
                            alt='logo-samandehi'
                            src='https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />
                    </div>
                    <div class="col-md-4">
                        <img src="../../images/logo.png" alt="logo_ecunion"
                            onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=430, height=630, top=30')"
                            style="cursor:pointer;width:29%;">
                    </div>
                    <div class="col-md-12">
                        <div class="row justify-content-between">
                            <div class="col-md-1 p-2">
                                <a href="{{ $setting->instagram }}">
                                    <img src="{{asset('assets/site/images/socialmedia/instagram.png')}}" width="40px"
                                        alt="">
                                </a>
                            </div>
                            <div class="col-md-1 p-2">
                                <a href="{{ $setting->telegram }}">
                                    <img src="{{asset('assets/site/images/socialmedia/telegram.png')}}" width="40px"
                                        alt="">
                                </a>

                            </div>
                            <div class="col-md-1 p-2">
                                <a href="{{ $setting->aparat }}">
                                    <img src="{{asset('assets/site/images/socialmedia/aparat.png')}}" width="40px"
                                        alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <div class="container py-md-5" style="display: block">
        <div class="row py-md-5">
            <div class="d-md-block d-none position-fixed col-12" style="left: 0; bottom: 0;display: none">
                <div class="row mt-2" style="font-size:10px;">
                    <div class="col-1 mr-5 mb-n1">
                        <a href="/contact">
                            <img class="rounded-circle" src="/img/payment.jpg" width="50px">
                            <p class="text-dark mr-1 font-weight-bolder">تماس با ما</p>
                        </a>
                    </div>
                    <div class="col-1 mb-n1">
                        <a href="{{ route('profile.factorItem')}}">
                            <img class="rounded-circle" src="/img/order.png" width="55px">
                            <p class="text-dark mr-1 font-weight-bolder">پرونده های من</p>
                        </a>
                    </div>
                    <div class="col-1">
                        <a href="{{ route('site.help') }}">
                            <img class="rounded-circle" src="/img/help.png" width="60px">
                            <p class="text-dark mr-3 font-weight-bolder">راهنما</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="wrapper logo d-block d-md-none position-fixed col-12 bg-white bt-1" style="left: 0; bottom: 0;">
            <div class="row mt-2" style="font-size:10px;">
                <div class="col-3">
                </div>

                <div class="col-3">
                    <a href="/contact">
                        <img class="rounded-circle" src="/img/payment.jpg" width="50px">
                        <p class="text-dark mr-1 font-weight-bolder"> تماس با ما</p>
                    </a>
                </div>
                <div class="col-3">
                    <a href="{{ route('profile.factorItem')}}">
                        <img class="rounded-circle" src="/img/order.png" width="55px">
                        <p class="text-dark mr-1 font-weight-bolder">پرونده های من</p>
                    </a>
                </div>
                <div class="col-3">
                    <a href="{{ route('site.help') }}">
                        <img class="rounded-circle" src="/img/help.png" width="60px">
                        <p class="text-dark mr-3 font-weight-bolder">راهنما</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</footer>


@if(session()->get('register')=='success')
<span id="register_success"></span>
@elseif(session()->get('factor')=='success')
<span id="factor_success"></span>
@endif

@if(session()->get('newsletter_exist')=='success')
<span id="newsletter_exist"></span>
@elseif(session()->get('newsletter_error')=='success')
<span id="newsletter_error"></span>
@elseif(session()->get('newsletter_success')=='success')
<span id="newsletter_success"></span>
@endif
