<nav class="navbar  navbar-expand-lg navbar-light header py-0 noprint">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo03"
                aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a class="navbar-brand mx-3 text-center my-1 d-none d-md-block" href="{{ route('index') }}"
           style="display: none!important;">
            <img src="{{ asset('assets/dashboard/images/l-d.png') }}" width="55px" alt="{{env('APP_NAME')}}"
                 class="img-fluid">
            <h1 class="text-white h6 mb-0">شبکه خدمات رفاهی ، درمانی و اقساطی لاله</h1>
        </a>
        @if(auth()->check())
            <div class="d-md-none d-lg-none ">
                <a href="{{route('profile')}}">
                <span class="btn btn-sm btn-primary ">
                    <i class="fa fa-user-circle" aria-hidden="true"></i>
                    حساب کاربری من
                </span>
                </a>
            </div>
            <a href="/cart"
               class="btn btn-sm btn-warning d-flex align-items-center position-relative ml-2 d-block d-md-none my-3">
                <i class="fas fa-shopping-cart font-16 ml-2"></i>
                سبدخرید
                <span
                    class="cart-count d-flex justify-content-center align-items-center position-absolute font-12 text-dark"
                    id="cart-count">{{ session()->has('cart.total.count') ? session()->get('cart.total.count') : 0 }}</span>
            </a>

        @else
            <div class="row">
                @if(isset($is_service))
                    <a href="#check_mobile_box"
                       class="btn btn-primary d-flex align-items-center position-relative ml-2 d-block d-md-none my-3">ثبت
                        نام</a>
                @else
                    <div class="dropdown">
                        <button
                            class="btn btn-warning dropdown-toggle d-flex align-items-center position-relative ml-2 d-block d-md-none my-3"
                            type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            ورود
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a href="{{ route('login_by_mobile') }}" class="dropdown-item">ورود اعضاء</a>
                            <a href="{{ route('login') }}" class="dropdown-item">ورود کارکنان | مستمری بگیران سجا</a>
                        </div>
                    </div>
                    <div class="dropdown">
                        <button
                            class="btn btn-primary dropdown-toggle d-flex align-items-center position-relative ml-2 d-block d-md-none my-3"
                            type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false">
                            ثبت نام
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a href="{{ route('register') }}" class="dropdown-item">ثبت نام اعضا</a>
                            <a href="{{ route('employee.register') }}" class="dropdown-item">ثبت نام همکاران</a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
        <nav class="navbar navbar-expand-lg navbar-light col-md-12  ">
            {{-- <nav class="navbar fixed-top navbar-light bg-light">--}}
            <a class="navbar-brand" href="#"> </a>
            <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                <ul class="navbar-nav ml-auto pr-0 pr-md-3">
                    @if(auth()->check() && auth()->user()->level=='admin' && auth()->user()->active==1)
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="/dashboard/dailyDesktop">
                                <strong>
                                    <i class="fa fa-angle-double-right "></i>
                                    پنل مدیریت
                                </strong>
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        @if (session()->has('representation'))
                            <a class="nav-link text-dark" href="/{{ session()->get('representation')->slug }}">خانه</a>
                        @else
                            <a class="nav-link text-dark" href="/">خانه</a>
                        @endif
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark" href="{{ route('site.help') }}">قوانین سایت</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark" href="#" id="navbarDropdownMenuLink"
                           role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            خدمات
                        </a>
                        <div class="dropdown-menu dropdown-menu-right text-right mt-3 "
                             aria-labelledby="navbarDropdownMenuLink">
                            @if (session()->has('representation'))
                                @foreach(session()->get('representation')->services->where('status' , true) as $item)
                                    <a class="dropdown-item text-orange py-2"
                                       href="{{ url('/'.session()->get('representation')->slug.'/'.$item->slug.'/'.(isset($callcenter) ? $callcenter->id : '')) }}">{{$item->title
                                    }}</a>
                                @endforeach
                            @else
                                @foreach(\App\Models\Service::filter()->whereStatus(true)->where('isShow' ,
                                true)->orderBy('created_at', 'desc')->get() as $item)
                                    <a
                                        class="dropdown-item text-orange py-2"
                                        href="{{ $item->path().'/'.(isset($callcenter) ? $callcenter->id : '') }}">{{
                                    $item->title }}</a>
                                @endforeach
                            @endif
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link text-dark dropdown-toggle  " href="#" id="navbarDropdownMenuLink"
                           role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            همکاری باما
                        </a>
                        <div class="dropdown-menu dropdown-menu-right text-right mt-3"
                             aria-labelledby="navbarDropdownMenuLink">
                            {{-- <a class="dropdown-item text-orange py-2" href="{{ url('/agency') }}">--}}
                            {{-- اعطاء مجوز همکاری به مراکز و فروشگاهها--}}
                            {{-- </a>--}}
                            <a class="dropdown-item text-orange py-2"
                               href="{{ url('/Permanent-employment') }}">استخدام و تامین نیروی انسانی</a>
                            <a class="dropdown-item text-orange py-2"
                               href="{{ url('/centerRequest/create') }}">همکاری با فروشندگان و خدمات دهندگان</a>
                            @if (!session()->has('representation'))
                                <a class="dropdown-item text-orange py-2"
                                   href="{{ url('/services/business-agency') }}">اعطای مجوز نمایندگی</a>
                            @endif
                        </div>
                    </li>

                    {{-- <li class="nav-item">--}}
                    {{-- <a class="nav-link text-dark " href="/rules">قوانین سایت</a>--}}
                    {{-- </li>--}}

                    @foreach($menus as $menu)
                        @if($menu->getChild()->get()->count() > 0)
                            <li class="nav-item dropdown">
                                <a class="nav-link text-dark dropdown-toggle" href="#" id="navbarDropdownMenuLink"
                                   role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{ $menu->title }}
                                </a>
                                <div class="dropdown-menu dropdown-menu-right text-right mt-3"
                                     aria-labelledby="navbarDropdownMenuLink">

                                    @foreach($menu->getChild()->get() as $child)
                                        <a class="dropdown-item text-orange py-2" href="{{ $child->link }}">{{ $child->title
                                    }}</a>
                                    @endforeach
                                </div>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link text-dark " href="{{ $menu->link }}">{{ $menu->title }}</a>
                            </li>
                        @endif
                    @endforeach
                    <li class="nav-item dropdown">
                        <a class="nav-link text-dark dropdown-toggle  " href="#" id="navbarDropdownMenuLink"
                           role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            پویش ها
                        </a>
                        <div class="dropdown-menu dropdown-menu-right text-right mt-3">
                            @foreach(\App\Models\Campaign::available()->get() as $campaign)
                                @if((new \App\Services\V1\Campaign\CampaignService())->checkCampaign($campaign))
                                    <a
                                        class="dropdown-item text-orange py-2"
                                        href="{{ route('campaign',['slug' => $campaign->slug]) }}"
                                    >
                                        {{$campaign->name}}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </li>
                </ul>
                @if(auth()->check())
                    <ul
                        class="navbar-nav left-nav mr-auto pr-0 pr-md-3 nav-after text-center text-lg-right pb-3 pb-lg-0 d-flex align-items-center justify-content-center">
                        <li class="nav-item d-none d-md-block">
                            <a href="/cart" class="btn btn-warning d-flex align-items-center position-relative ml-2">
                                <i class="fas fa-shopping-cart font-16 ml-2"></i>
                                سبدخرید
                                <span
                                    class="cart-count d-flex justify-content-center align-items-center position-absolute font-12 text-dark"
                                    id="cart-count">{{ session()->has('cart.total.count') ?
                                    session()->get('cart.total.count') : 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item profile">
                            <a class="nav-link text-dark" href="{{ route('profile') }}">
                                <img width="40px" height="40px" src="{{ asset(auth()->user()->avatar) }}"
                                     class="rounded-circle">
                                <span>{{ auth()->user()->family }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('logout') }}" class="nav-link text-dark"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                خروج</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                {{ csrf_field() }}</form>
                        </li>
                    </ul>
                @else
                    <ul
                        class="navbar-nav left-nav mr-auto pr-0 pr-md-3 nav-after text-center text-lg-right pb-3 pb-lg-0">
                        <div class="row">
                            <div class="col-12 px-1">
                                <li class="nav-item">
                                    <div class="row">
                                        @if(isset($is_service))
                                            <a href="#check_mobile_box"
                                               class="d-none d-lg-flex navbar-nav nav-after btn btn-primary align-items-center position-relative ml-2 p-2">ثبت
                                                نام</a>
                                        @else
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-warning dropdown-toggle d-none d-lg-flex navbar-nav nav-after align-items-center position-relative ml-2 p-2"
                                                    type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    ورود
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a href="{{ route('login_by_mobile') }}" class="dropdown-item">ورود
                                                        اعضاء</a>
                                                    <a href="{{ route('login') }}" class="dropdown-item">ورود کارکنان |
                                                        مستمری
                                                        بگیران سجا</a>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-primary dropdown-toggle d-none d-lg-flex navbar-nav nav-after align-items-center position-relative ml-2 p-2"
                                                    type="button" id="dropdownMenuButton" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                    ثبت نام
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a href="{{ route('register') }}" class="dropdown-item">ثبت نام
                                                        اعضا</a>
                                                    <a href="{{ route('employee.register') }}" class="dropdown-item">ثبت
                                                        نام
                                                        همکاران </a>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                    <a href="/cart"
                                       class="btn btn-warning d-flex align-items-center position-relative ml-2 d-block d-md-none">
                                        <i class="fas fa-shopping-cart font-16 ml-2"></i>
                                        سبدخرید
                                        <span
                                            class="cart-count d-flex justify-content-center align-items-center position-absolute font-12 text-dark"
                                            id="cart-count">{{ session()->has('cart.total.count') ?
                                            session()->get('cart.total.count') : 0 }}</span>
                                    </a>
                                </li>
                            </div>
                        </div>
                    </ul>
                @endif

                <ul class="d-none d-lg-flex navbar-nav mr-0 pr-0 pr-md-3 pt-2 nav-after">
                    <li class="nav-item mr-3">
                        <a href="tel:{{ session()->get('representation') ? session()->get('representation')->call : $setting->call }}"
                           class="nav-link text-dark">
                                <span>{{ session()->get('representation') ? session()->get('representation')->call :
                                    $setting->call }}</span>
                            <i class="fa fa-phone"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ $setting->telegram }}" class="nav-link text-dark">
                            <i class="fab fa-telegram"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ $setting->instagram }}" class="nav-link text-dark">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</nav>
