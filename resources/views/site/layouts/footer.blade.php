<footer class="footer">
    <div class="container">
        <div class="row">
			<div class="logo mt-md-3 d-block d-md-none" style="left: 0; bottom: 10px;">
                    {!! $setting->enamad !!}
				                    <img referrerpolicy="origin" id = 'jxlzfukznbqergvjnbqefukz' style = 'cursor:pointer' onclick = 'window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")' alt = 'logo-samandehi' src = 'https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />

				<img src="../../images/logo.png" alt="" onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=430, height=630, top=30')" style="cursor:pointer;width:29%;">
				</div>
            <div class="col-12 col-md-6 col-top-right ">
                <a href="http://maps.google.com/maps?daddr=35.82454554187213,50.93747301678106" target="_blank"><img class="img-fluid"
                                                                                                     src="/assets/site/images/map.png"
                                                                                                     alt=""></a>
                <div class="location mt-3 mb-3">
                    <span>مسیر یابی: </span>
                    <a href="http://maps.google.com/maps?daddr=35.82454554187213,50.93747301678106" target="_blank"><img class="icon"
                                                                                                         src="/assets/site/images/gmap.png"
                                                                                                         alt=""></a>
                    <a href="https://waze.com/ul?ll=35.82454554187213,50.93747301678106&navigate=yes" target="_blank"><img class="icon"
                                                                                                           src="/assets/site/images/wmap.png"
                                                                                                           alt=""></a>
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
            </div>
        </div>
    </div>

    <div class="copy-right position-relative">
{{--        <img src="{{asset('/assets/site/images/copy-right3.svg')}}" class="position-absolute cover-img"--}}
{{--             draggable="false">--}}
        <div class="container py-md-5">
            <div class="row py-md-5">
				<div class="d-md-block d-none position-fixed col-12" style="left: 0; bottom: 0;">
					 <div class="row mt-2" style="font-size:10px;">
						    <div class="col-1 mr-5 mb-n1">
                                <a href="/contact">
                                     <img class="rounded-circle" src="/img/payment.jpg" width="50px">
                                     <p class="text-dark mr-1 font-weight-bolder">ارسال فیش</p>
                                 </a>
                            </div>
                             <div class="col-1 mb-n1">
                                 <a href="/profile/factor">
                                     <img class="rounded-circle" src="/img/order.png" width="55px">
                                     <p class="text-dark mr-1 font-weight-bolder">پرداختی های من</p>
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
                                     <p class="text-dark mr-1 font-weight-bolder">ارسال فیش</p>
                                 </a>
                          </div>
                           <div class="col-3">
                            <a href="/profile/factor">
                                <img class="rounded-circle" src="/img/order.png" width="55px">
								<p class="text-dark mr-1 font-weight-bolder">پرداختی من</p>
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
                <div class="col-md-6 col-bottom-right mb-5" style="position: initial">
                    <ul>
                        <li class="item">
                        <span>
                            <i class="fas fa-map-marker"></i>
                        </span>
                            <span>{{ $setting->address }}</span>
                        </li>
						 <li class="item">
                        <span>
                            <i class="fa fa-phone"></i>
                        </span>
                            @if(auth()->check() && auth()->user()->level == 'organ')
                                <span>{{ $setting->call }}( داخلی 101 )</span>
                            @else
                                <span>{{ $setting->call }}</span>
                            @endif
                        </li>
                        <li class="item">
                        <span>
                         <i class="fas fa-envelope-open"></i>
                        </span>
                            <span>{{ $setting->email }}</span>
                        </li>
                    </ul>
                </div>

                <div class="col-md-6 col-bottom-left" style="position: initial">
                    <ul>
                        <li class="item">
                        <span>
                           <i class="fab fa-telegram-plane"></i>
                        </span>
                            <a href="{{ $setting->telegram }}"><span>telegram</span></a>
                        </li>
                        <li class="item">
                        <span>
                       <i class="fab fa-instagram"></i>
                        </span>
                            <a href="{{ $setting->instagram }}"><span>instagram</span></a>
                        </li>
                    </ul>
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

