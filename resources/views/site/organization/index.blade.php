<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="description" content="">
    <link rel="icon" href="{{asset('assets/site/images/fav.png')}}" type="image/png">
    <title>{{ $organization->fa_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/site/css/landing.css?v=1.1') }}">
	    <script type="text/javascript">window.$crisp=[];window.CRISP_WEBSITE_ID="9aa6b7fc-b1ba-437f-800d-b26efe273153";(function(){d=document;s=d.createElement("script");s.src="https://client.crisp.chat/l.js";s.async=1;d.getElementsByTagName("head")[0].appendChild(s);})();</script>
</head>
<body>

<div class="agency">
    <div class="header position-relative d-md-flex justify-content-md-center flex-md-column align-items-md-center">
		<img src="{{ asset($organization->image) }}" width="100%"  class="h-md-5 h-5">
        <div class="logo bg-white position-absolute d-flex justify-content-end flex-column align-items-center">
            <img src="{{ asset('assets/site/images/footer-logo.png') }}" alt="شبکه خدمات رفاهی لاله" width="150px">
			<a href="{{ route('logout') }}" class="btn btn-warning nav-link mb-2" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                خروج</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>
        </div>
        <div class="px-2 px-md-5 mt-md-n6 mt-n6">
            <h1 class="text-white text-center text-shadow mt-5 mt-md-0">{{ $organization->fa_name }}</h1>
            <p class="text-white text-center px-md-5 text-shadow font-16">{{ $organization->header_description }}</p>
        </div>
    </div>
    <div class="bg-white my-5 pt-md-5 pt-3">
        <div class="container">
            <h4 class="text-center font-22 text-black-50 mb-5 mt-5">فهرست خدمات</h4>
            <div class="row">
                @foreach($organization->services as $service)
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="shadow rounded-2x position-relative box-service mb-4">
                            <div class="row">
                                <div class="col-12">
									 <a href="/{{ $organization->slug }}/services/{{ $service->slug }}">
                                        <h2 class="p-3 text-orange font-16 text-center">{{ $service->title }}</h2>
                                    </a>


                                </div>
                                <div class="col-12">
                                    <img src="{{ asset($service->image_app) }}" alt="{{ $service->title }}" width="100%">
                                </div>
                                <div class="col-12 my-3 mx-2">
                                    <p class="text-success font-14 text-right mb-0"><strong>{{ $service->price == 0 ? 'انتخابی' : number_format($service->price) . ' ریال' }}</strong></p>
                                </div>
                            </div>
                            <a href="/{{ $organization->slug }}/services/{{ $service->slug }}" class="btn btn-success px-3 py-1 position-absolute btn-order">خرید</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="position-fixed d-none - d-md-block" style="left: 0; bottom: 0;">
	<img src="../../images/logo.png" alt="" onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=450, height=630, top=30')" style="cursor:pointer">
    <img id = 'jxlzfukznbqergvjnbqefukz' style = 'cursor:pointer' onclick = 'window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")' alt = 'logo-samandehi' src = 'https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />
    <a target="_blank" href="https://trustseal.enamad.ir/?id=40920&amp;Code=6abmm9WQ9521uXon0exd"><img src="https://Trustseal.eNamad.ir/logo.aspx?id=40920&amp;Code=6abmm9WQ9521uXon0exd" alt="" style="cursor:pointer" id="6abmm9WQ9521uXon0exd"></a>
	</div>
<div class="position-fixed d-none - d-md-block" style="left: 120px;bottom: 0;">
</div>
</div>
<footer class="footer">
    <div class="container pt-5">
        <div class="row">
            <div class="col-12 col-md-6 col-top-right ">
                <a href="http://maps.google.com/maps?daddr=35.764742,51.288443" target="_blank"><img class="img-fluid"
                                                                                                     src="/assets/site/images/map.png"
                                                                                                     alt=""></a>
                <div class="location mt-3 mb-3">
                    <span>مسیر یابی: </span>
                    <a href="http://maps.google.com/maps?daddr=35.764742,51.288443" target="_blank"><img class="icon"
                                                                                                         src="/assets/site/images/gmap.png"
                                                                                                         alt=""></a>
                    <a href="https://waze.com/ul?ll=35.764742,51.288443&navigate=yes" target="_blank"><img class="icon"
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
        <div class="container py-5">
            <div class="row py-5">
                <div class="col-8 col-md-6 col-bottom-right">
                    <ul>

                    </ul>
                </div>
                <div class="col-4 col-md-6 col-bottom-left">
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
    </div>

</footer>
</body>
</html>
