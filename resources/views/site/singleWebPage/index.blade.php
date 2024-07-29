<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="description" content="">
    <link rel="icon" href="{{asset('assets/site/images/fav.png')}}" type="image/png">
    <title>{{ $singleWebPage->fa_name }}</title>
    <link rel="stylesheet" href="{{ asset('assets/site/css/landing.css') }}">
</head>

<body>
    <div class="agency">
        <div class="header position-relative d-md-flex justify-content-md-center flex-md-column align-items-md-center">
            <img src="{{ asset($singleWebPage->image) }}" width="100%" class="h-md-5 h-5">
            <div class="logo bg-white position-absolute d-flex justify-content-end flex-column align-items-center">
                <img src="{{ asset('assets/site/images/footer-logo.png') }}" alt="شبکه خدمات رفاهی لاله" width="150px">
            </div>
            <div class="px-2 px-md-5 mt-md-n6 mt-n6">
                <h1 class="text-white text-center text-shadow mt-5 mt-md-0">{{ $singleWebPage->fa_name }}</h1>
            </div>
        </div>
        <div class="bg-white my-5 pt-md-5 pt-3">
            <div class="container">
                <h4 class="text-center font-22 text-black-50 mb-5 mt-5">فهرست خدمات</h4>
                <div class="row">
                    @foreach($singleWebPage->services as $service)
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="shadow rounded-2x position-relative box-service mb-4">
                            <div class="row">
                                <div class="col-12">
                                    <a href="/singleWebPage/{{ $singleWebPage->slug }}/{{ $service->slug }}">
                                        <h2 class="p-3 text-orange font-16 text-center">{{ $service->title }}</h2>
                                    </a>


                                </div>
                                <div class="col-12">
                                    <img src="{{ asset($service->image_app) }}" alt="{{ $service->title }}"
                                        width="100%">
                                </div>
                                <div class="col-12 my-3 mx-2">
                                    <p class="text-success font-14 text-right mb-0">
                                        <strong>{{ $service->price == 0 ? 'انتخابی' : number_format($service->price) . '
                                            ریال' }}</strong>
                                    </p>
                                </div>
                            </div>
                            <a href="/singleWebPage/{{ $singleWebPage->slug }}/{{ $service->slug }}"
                                class="btn btn-success px-3 py-1 position-absolute btn-order">مشاهده</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="position-fixed" style="left: 0; bottom: 0;">
        <img src="../../images/logo.png" alt=""
            onclick="window.open('https://ecunion.ir/verify/lalecard.ir?token=85848845e539a3c460cf', 'Popup','toolbar=no, location=no, statusbar=no, menubar=no, scrollbars=1, resizable=0, width=450, height=630, top=30')"
            style="cursor:pointer">
        <img id='jxlzfukznbqergvjnbqefukz' style='cursor:pointer'
            onclick='window.open("https://logo.samandehi.ir/Verify.aspx?id=162326&p=rfthgvkauiwkxlaouiwkgvka", "Popup","toolbar=no, scrollbars=no, location=no, statusbar=no, menubar=no, resizable=0, width=450, height=630, top=30")'
            alt='logo-samandehi' src='https://logo.samandehi.ir/logo.aspx?id=162326&p=nbpdwlbqodrfqftiodrfwlbq' />
            <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=40920&Code=6abmm9WQ9521uXon0exd'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=40920&Code=6abmm9WQ9521uXon0exd' alt='' style='cursor:pointer' code='6abmm9WQ9521uXon0exd'></a>
    </div>
@include('site.singleWebPage.footer')
</body>
</html>
