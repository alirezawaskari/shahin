<footer class="footer">
    <div class="container pt-5">
        <div class="row">
            <div class="col-4">
                <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=40920&Code=6abmm9WQ9521uXon0exd'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=40920&Code=6abmm9WQ9521uXon0exd' alt='' style='cursor:pointer' code='6abmm9WQ9521uXon0exd'></a>
            </div>
            <div class="col-4">
                <img referrerpolicy="origin" id='jxlzfukznbqergvjnbqefukz' style='cursor:pointer;'
                    onclick='window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")'
                    alt='logo-samandehi'
                    src='https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />
            </div>
            <div class="col-4">
                <img src="../../images/logo.png" alt="logo_ecunion"
                    onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=430, height=630, top=30')"
                    style="cursor:pointer;">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 col-top-right ">
                <a href="http://maps.google.com/maps?daddr=35.764742,51.288443" target="_blank"><img class="img-fluid"
                        src="/assets/site/images/map.png" alt=""></a>
                <div class="location mt-3 mb-3">
                    <span>مسیر یابی: </span>
                    <a href="http://maps.google.com/maps?daddr=35.764742,51.288443" target="_blank"><img class="icon"
                            src="/assets/site/images/gmap.png" alt=""></a>
                    <a href="https://waze.com/ul?ll=35.764742,51.288443&navigate=yes" target="_blank"><img class="icon"
                            src="/assets/site/images/wmap.png" alt=""></a>
                </div>
            </div>
            <div class="col-12 col-md-6 col-top-left">
                <img src="/assets/site/images/footer-logo.png" alt="" class="footer-logo">
                <form action="{{ route('newsletter') }}" method="post">
                    @csrf
                    <input class="email" type="email" name="email" required placeholder="لطفا ایمیل خود را وارد نمایید">
                    <button class="send pt-1" type="submit">
                        <i class="far fa-envelope-open"></i>
                    </button>
                </form>
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
                <div class="logo mt-3">
                        <a href="{{asset('assets/site/images/logo/1.jpg')}}" target="_blank">
                            <img src="{{asset('assets/site/images/logo/1.jpg')}}">
                        </a>
                        <a href="{{asset('assets/site/images/logo/2.jpg')}}" target="_blank">
                            <img src="{{ asset('assets/site/images/logo/2.jpg') }}">
                        </a>
                        <a href="{{asset('assets/site/images/logo/3.jpg')}}" target="_blank">
                            <img src="{{ asset('assets/site/images/logo/3.jpg') }}">
                        </a>
                </div>
            </div>
        </div>
    </div>

    <div class="copy-right position-relative">
        {{-- <img src="{{asset('/assets/site/images/copy-right3.svg')}}" class="position-absolute cover-img"
            draggable="false">--}}
        {{-- <div class="container py-5">--}}
            <div class="row py-5">
                <div class="col-md-4 col-bottom-right mb-5" style="position: initial">
                    <ul>
                        <li class="item">
                            <span>
                                <i class="fa fa-phone"></i>
                            </span>
                            <span>09120934070 (واحد امورقراردادها)</span>
                        </li>
                        <li class="item">
                            <span>
                                <i class="fas fa-envelope-open"></i>
                            </span>
                            <span>info@lalecard.ir</span>
                        </li>
                        <li class="item">
                            <span>
                                <i class="fas fa-dot-circle"></i>
                            </span>
                            <span>شماره ثبت 478000 اداره کل استان تهران</span>
                        </li>
                        <li class="item">
                            <span>
                                <i class="fas fa-map-marker"></i>
                            </span>
                            <span>{{$setting->address}}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-4 col-md-4 col-bottom-center">
                    <ul>
                        <li class="item">
                            <img src="/assets/site/images/factor-logo.png" alt="">
                            <img src="{{ asset('assets/site/images/footer-logo.png') }}" width="150px"
                                    alt="شبکه خدمات رفاهی لاله">
                        </li>
                    </ul>
                </div>
                <div class="col-4 col-md-4 col-bottom-left">
                    <ul>
                        <li class="item">
                            <span>
                                <i class="fab fa-telegram-plane"></i>
                            </span>
                            <a href="#"><span>telegram</span></a>
                        </li>
                        <li class="item">
                            <span>
                                <i class="fab fa-instagram"></i>
                            </span>
                            <a href="#"><span>instagram</span></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="position-fixed d-none d-md-block" style="right: 5px; bottom: 0;color:gray">
            ارتباط با کارشناسان
            <script type="text/javascript">
                !function () { var i = "7aaX4j", a = window, d = document; function g() { var g = d.createElement("script"), s = "https://www.goftino.com/widget/" + i, l = localStorage.getItem("goftino_" + i); g.async = !0, g.src = l ? s + "?o=" + l : s; d.getElementsByTagName("head")[0].appendChild(g); } "complete" === d.readyState ? g() : a.attachEvent ? a.attachEvent("onload", g) : a.addEventListener("load", g, !1); }();
            </script>
        </div>
</footer>
