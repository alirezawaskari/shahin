@extends('site.representation.master')

@section('title')
فاکتور: {{ $factor->resnumber }}
@endsection

@section('content')
<div class="row pt-3">
    <div class="col-5 d-flex align-items-center">
        <a href=""><img class="mr-md-5 d-none d-md-block" src="{{ asset('assets/site/images/footer-logo.png') }}"
                width="150px" alt="شبکه خدمات رفاهی لاله"></a>
        @auth
        <a href="/cart" class="btn btn-warning position-relative ml-2">
            <i class="fas fa-shopping-cart font-16 ml-2"></i>
            سبدخرید
            <span
                class="cart-count d-flex justify-content-center align-items-center position-absolute font-12 text-dark"
                id="cart-count">{{ session()->has('cart.total.count') ? session()->get('cart.total.count') : 0 }}</span>
        </a>
        @endauth
    </div>
    <div class="col-4 d-flex align-items-center text-center">
        <a href="{{ $representation->path() }}">
            @if(!is_null($factor->representation))
            <h5 class="text-dark text-center">{{ $factor->representation->fa_name }}</h5>
            @endif
        </a>
    </div>
    <div class="col-3 d-flex justify-content-end">
        <a href="{{ $representation->path() }}">
            <span class="ml-md-5 text-dark font-16 d-flex align-items-center">
                برگشت
                <i class="fas fa-arrow-left text-orange font-18 m-2"></i>
            </span>
        </a>
    </div>
</div>
<div class="factor">
    <div class="factor-section bg-white py-5 position-relative">
        <div class="overlay position-absolute"></div>
        <div class="container">
            <div class="row">
            </div>
            <div class="factor-box col-12 rounded py-5 position-relative overflow-hidden">
                <div class="overlay position-absolute"></div>
                <div class="logo d-flex align-items-center flex-column">
                    <img src="/assets/site/images/factor-logo.png" alt="">
                    <h5 class="mt-3">شرکت جانبازان آینده ساز</h5>
                    <p class="my-0">سهامی خاص (478000)</p>
                    @if(!is_null($factor->representation))
                    <p>{{ $factor->representation->fa_name }}</p>
                    @else
                    <p>دفتر مرکزی </p>
                    @endif
                </div>
                @if(now() > $factor->expired_at)
                <p class="text-danger font-18 text-center">فاکتور: {{ $factor->resnumber }} منقضی شده است و امکان پرداخت
                    وجود ندارد، لطفا سفارش جدید ثبت نمایید یا با پشتیبانی تماس بگیرید.</p>
                <p class="font-18 text-center text-dark">پشتیبانی: 09429000029</p>
                @else
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <img class="w-50" src="/assets/site/images/footer-logo.png" alt="">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            @if(isset($factor->factorItems[0]))
                            <h5 class="my-0">صورتحساب قطعی فروش کالا و خدمات</h5>
                            @else
                            <h5 class="my-0">قبض پرداخت اقساط و دیون</h5>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>تاریخ: {{ verta(\Carbon\Carbon::now())->format('%d %B ، %Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-5">
                    <p class="text-center font-18 bold">مشخصات فروشنده</p>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>نام حقیقی و حقوقی: شرکت جانبازان آینده ساز</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره اقتصادی: 411495546645</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره ثبت: 478000</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>استان: البرز / شهرستان: کرج</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>کد پستی: 1478654579</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره تماس: {{ $setting->call }} / فکس: 43851905</span>
                        </div>
                    </div>
                    @if(!is_null($factor->representation))
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره تماس نمایندگی: {{ $factor->representation->call }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>آدرس نمایندگی: {{ $factor->representation->address }}</span>
                        </div>
                    </div>
                    @else
                    <div class="row mb-3">
                        <div class="col-12  d-flex align-items-center">
                            <span>نشانی کامل: {{ $setting->address }}</span>
                        </div>
                    </div>
                    @endif

                </div>
                <div class="col-12 mt-5">
                    <p class="text-center font-18 bold">مشخصات خریدار</p>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>نام حقیقی و حقوقی: {{ $factor->user->name }} {{ $factor->user->family }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره اقتصادی / کد ملی: {{ $factor->user->melicode }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>استان: {{ $factor->user->province_id != null ?
                                \App\Models\Province::find($factor->user->province_id)->name : '' }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شهرستان: {{$factor->user->city_id != null ?
                                \App\Models\City::find($factor->user->city_id)->name : '' }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>کد پستی: {{ $factor->user->postalcode }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره تماس: {{ $factor->user->phone }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12  d-flex align-items-center">
                            <span>آدرس: {{ $factor->user->address }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-5">
                    @if(isset($factor->factorItems[0]))
                    <p class="text-center font-18 bold">مشخصات کالا یا خدمات مورد معامله</p>
                    <div class="table-responsive">
                        <table class="table bg-white rounded">
                            <thead>
                                <tr class="bg-gray text-orange">
                                    <th scope="col">خدمت</th>
                                    <th scope="col">مبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($factor->factorItems as $item)
                                <tr>
                                    @if(!is_null($item->financialPlansType))
                                    <th>
                                        {{ $item->financialPlansType->title }}
                                        {{-- {{ $item->financialPlansType->title .'-'.
                                        $item->factoritemable->factor_title}}--}}
                                    </th>
                                    <th class="pay-invoice">
                                        {{ number_format($item->financialPlansType->price) }} ریال
                                    </th>
                                    @else
                                    <th>
                                        {{ $item->factoritemable->title.'-'. $item->factoritemable->factor_title}}
                                    </th>
                                    <th class="pay-invoice">
                                        {{ number_format($item->factoritemable->price) .' ریال' }}
                                        {{ $item->factoritemable->price == 0 ? '(هدیه)' : '' }}
                                    </th>
                                    @endif
                                </tr>
                                <tr>
                                    <th> {{ number_format($item->factoritemable->tax) }} درصد مالیات بر ارزش افزوده</th>
                                    <th>{{ number_format($factor->tax) }} ریال</th>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @foreach($factor->factorItems as $item)
                        @if(in_array(trim($item->factoritemable->id) ,
                        App\Models\AgencyType::pluck('service_id')->toArray()) )
                        <p>3-پرداخت مابقی هزینه فاکتور فوق بصورت چک رمزدار طی 72 ساعت قابل اجرا بوده و پس از انقضای این
                            مدت شرکت هیچگونه تعهدی در قبال این فاکتور ندارد.</p>
                        @endif
                        @endforeach
                        @endif
                        <div
                            class="col-12 d-flex justify-content-center justify-content-md-end align-items-center my-3">
                            <span class="bold font-14 my-0 py-0 mx-2">کل: {{ number_format($factor->price) }}
                                ریال</span>
                            @if(!is_null($factor->coupon))
                            <span class="bold font-13 my-0 py-0 mx-2 text-danger">تخفیف: {{ number_format($factor->price
                                * $factor->coupon->discount / 100) }}
                                ریال</span>

                            <span class="bold font-13 my-0 py-0 mx-2">قابل پرداخت: {{ number_format($factor->price -
                                ($factor->price * $factor->coupon->discount / 100)) }}
                                ریال</span>
                            @endif

                            <span class="bold font-12 my-0 py-0 text-red" id="offprice"></span>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 col-md-3 justify-content-end align-items-center">
                            <div class="form-group row mr-1">
                                @if(!is_null($factor->coupon_id))
                                <span class="font-13 text-success">کد تخفیف {{ $factor->coupon->discount }} % اعمال
                                    شد</span>
                                @else
                                <form action="{{ route('coupon') }}" method="POST" class="d-flex">
                                    @csrf
                                    <input required class="form-control col-8" type="text" name="code"
                                        placeholder="کد تخفیف">
                                    <input type="hidden" name="factor_id" value="{{ $factor->id }}">
                                    <input type="submit" class="btn btn-primary font-12 col-3 mx-1" value="اعمال کد">
                                </form>
                                @foreach($errors->all() as $error)
                                <span class="text-danger font-12">{{ $error }}</span>
                                @endforeach
                                @endif
                                <a target="_blank" href="{{ asset('images/IMG-20191230-WA0000.jpg') }}">
                                    <p class="mt-3">مشاهده گواهی ارزش افزوده</p>
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 justify-content-end align-items-center">
                            <div class="row d-flex align-items-center justify-content-center">
                                <div class="col-12 d-flex justify-content-center flex-column align-items-center">
                                    <form action="{{route('pay')}}" method="post"
                                        class="d-flex flex-column justify-content-center align-items-center">
                                        @csrf
                                        <input name="coupon" id="couponinputwallet" type="hidden" value="">
                                        <input name="factor_id" type="hidden" value="{{ $factor->id }}">
                                        {{-- <span class="alert alert-primary bold">
                                            با توجه به اختلالات الکترونیکی پیش آمده لطفا مبلغ
                                            {{number_format($factor->price)}}
                                            ریال را به شماره کارت
                                            '5047061065198174'
                                            به نام سید مصطفی میرحسینی واریز نموده و فیش واریزی را از طریق واتس اپ امور
                                            مالی ارسال فرمایید .
                                        </span> --}}
                                        <p class="mb-0">انتخاب روش پرداخت:</p>
                                        <div class="btn-group btn-group-toggle mt-3" data-toggle="buttons">
                                            <label
                                                class="rounded btn btn-outline-warning text-dark active mx-2 d-flex align-items-center justify-content-center pointer">
                                                <input type="radio" name="payment" id="option1" value="online"
                                                    autocomplete="off" checked>
                                                <span class="font-14">پرداخت از طریق کارت شتاب</span>
                                            </label>

                                            @auth
                                            @if($factor->user->wallet >= $factor->price)
                                            <label
                                                class="rounded btn btn-outline-warning text-dark mx-2 d-flex flex-column align-items-center justify-content-center pointer">
                                                <input type="radio" name="payment" value="wallet" id="option2"
                                                    autocomplete="off">
                                                <span class="font-12">کسر {{ number_format($factor->coupon ?
                                                    $factor->price - ($factor->price * $factor->coupon->discount / 100 )
                                                    : $factor->price) }} ریال از کیف شما</span>
                                                <span class="font-12">موجودی شما: {{
                                                    number_format($factor->user->wallet) }} ریال</span>
                                            </label>
                                            @else
                                            <button type="button"
                                                class="rounded btn btn-secondary disabled d-flex flex-column align-items-center justify-content-center"
                                                disabled>
                                                <span class="font-12">کسر {{ number_format($factor->coupon ?
                                                    $factor->price - ($factor->price * $factor->coupon->discount / 100 )
                                                    : $factor->price) }} ریال از کیف شما</span>
                                                <span class="font-12">موجودی شما: {{
                                                    number_format($factor->user->wallet) }} ریال</span>
                                            </button>
                                            @endif
                                            @endauth

                                        </div>
                                        <input id="btn_factor" type="submit" value="جهت پرداخت اینجا کلیک کنید"
                                            class="btn btn-success font-14 my-2 btn-block">
                                        {{-- <input id="btn_factor" type="submit"
                                            value=" پرداخت , دریافت شماره کارت جهت پرداخت"
                                            class="btn btn-success font-14 my-2 btn-block"> --}}
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-3 d-flex justify-content-end align-items-center">

                            @if(!is_null($factor->callcenter_id))
                            <span class="mx-2">
                                مسئول صدور : {{ \App\Models\User::find($factor->callcenter_id)->name }} {{
                                \App\Models\User::find($factor->callcenter_id)->family }}
                                این فاکتور را برای شما ایجاد کرده است.</span>
                            @endif

                            <span>مهر و امضا فروشنده</span>
                            @if(!is_null($factor->representation))
                            @if(!is_null($factor->representation->stamp))
                            <img src="{{ asset($factor->representation->stamp) }}" alt="">
                            @else
                            <img src="/assets/site/images/mohr.png" alt="">
                            @endif
                            @else
                            <img src="/assets/site/images/mohr.png" alt="">
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
