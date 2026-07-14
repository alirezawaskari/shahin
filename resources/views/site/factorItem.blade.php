@extends('site.master')

@section('title')
    فاکتور: {{ $factorItem->resnumber }}
@endsection

@section('content')
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
                        @if(!is_null($factorItem->representation))
                            <p>{{ $factorItem->representation->fa_name }}</p>
                        @else
                            <p>دفتر مرکزی تهران</p>
                        @endif
                    </div>
                    @if(now() > $factorItem->expired_at)
                        <p class="text-danger font-18 text-center">فاکتور: {{ $factorItem->resnumber }} منقضی شده است و امکان پرداخت وجود ندارد، لطفا سفارش جدید ثبت نمایید یا با پشتیبانی تماس بگیرید.</p>
                        <p class="font-18 text-center text-dark">پشتیبانی: 02149175000</p>
                    @else
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <img class="w-50" src="/assets/site/images/footer-logo.png" alt="">
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <h5 class="my-0">صورتحساب قطعی فروش کالا و خدمات</h5>
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
                                    <span>استان: تهران / شهرستان: تهران</span>
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>کد پستی: 1478654579</span>
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>شماره تماس: / فکس: 43851905</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12  d-flex align-items-center">
                                    <span>نشانی کامل:</span>
                                </div>
                            </div>
                            @if(!is_null($factorItem->center))
                                <div class="row mb-3">

                                    <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                        <span>آدرس شعبه: {{ $factorItem->center->address }}</span>
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="col-12 mt-5">
                            <p class="text-center font-18 bold">مشخصات خریدار</p>
                            <div class="row mb-3">
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>نام حقیقی و حقوقی: {{ $factorItem->factor->user->name }} {{ $factorItem->factor->user->family }}</span>
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>شماره اقتصادی / کد ملی: {{ $factorItem->factor->user->melicode }}</span>
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>استان: {{ $factorItem->factor->user->province_id != null ? \App\Models\Province::find($factorItem->factor->user->province_id)->name : '' }}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>شهرستان: {{$factorItem->factor->user->city_id != null ?  \App\Models\City::find($factorItem->factor->user->city_id)->name : '' }}</span>
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>کد پستی: {{ $factorItem->factor->user->postalcode  }}</span>
                                </div>
                                <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                                    <span>شماره تماس: {{ $factorItem->factor->user->phone }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12  d-flex align-items-center">
                                    <span>آدرس: {{ $factorItem->factor->user->address }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-5">
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
                                        <tr>
                                            @if(!is_null($factorItem->service_item_id))
                                                <th>
                                                    {{ $factorItem->factoritemable->title.'-'.$factorItem->serviceItem->title }} پیش پرداخت
                                                </th>
                                                <th class="pay-invoice">
                                                    {{ number_format($factorItem->prepayment['price']) }} ریال
                                                </th>
                                            @else
                                                <th>
                                                    پیش پرداخت {{ $factorItem->factoritemable->title}}
                                                </th>
                                                <th class="pay-invoice">
                                                    {{ number_format($factorItem->prepayment['price']) .' ریال' }}
                                                </th>
                                            @endif
                                        </tr>
                                    <tr>
                                        <th>9 درصد مالیات بر ارزش افزوده</th>
                                        <th>{{ number_format($factorItem->tax) }} ریال</th>
                                    </tr>

                                    </tbody>
                                </table>
                                <p>در پرداخت فاکتور فوق دقت فرمائید این فاکتور تا 24 ساعت قابلیت انصراف دارد پس از انقضای زمان قابلیت عودت وجه وجود ندارد</p>
                                <div class="col-12 d-flex justify-content-center justify-content-md-end align-items-center my-3">
                                     <span class="bold font-14 my-0 py-0 mx-2">کل: {{ number_format($factorItem->prepayment['price'])}}
                                                ریال</span>
                                    @if(!is_null($factorItem->coupon))
                                        <span class="bold font-13 my-0 py-0 mx-2 text-danger">تخفیف: {{ number_format($factorItem->prepayment['price'] * $factorItem->coupon->discount / 100) }}
                                                ریال</span>

                                        <span class="bold font-13 my-0 py-0 mx-2">قابل پرداخت: {{ number_format($factorItem->prepayment['price']) - ($factorItem->prepayment['price'] * $factorItem->coupon->discount / 100) }}
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
                                        @if(!is_null($factorItem->coupon_id))
                                            <span class="font-13 text-success">کد تخفیف {{ $factorItem->coupon->discount }} % اعمال شد</span>
                                        @else
                                            <form action="{{ route('coupon') }}" method="POST" class="d-flex">
                                                @csrf
                                                <input required class="form-control col-8" type="text" name="code"
                                                       placeholder="کد تخفیف">
                                                <input type="hidden" name="factor_id" value="{{ $factorItem->id }}">
                                                <input type="submit" class="btn btn-primary font-12 col-3 mx-1"
                                                       value="اعمال کد">
                                            </form>
                                            @foreach($errors->all() as $error)
                                                <span class="text-danger font-12">{{ $error }}</span>
                                            @endforeach
                                        @endif
                                        <a target="_blank" href="{{ asset('images/tax.jpg') }}"><p class="mt-3">مشاهده گواهی ارزش افزوده</p></a>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 justify-content-end align-items-center">
                                    <div class="row d-flex align-items-center justify-content-center">
                                        <div class="col-12 d-flex justify-content-center flex-column align-items-center">
                                            <form action="{{route('payPrepayment')}}" method="post" class="d-flex flex-column justify-content-center align-items-center">
                                                @csrf
                                                <input name="coupon" id="couponinputwallet" type="hidden" value="">
                                                <input name="factor_id" type="hidden" value="{{ $factorItem->id }}">
                                                    <p class="mb-0">انتخاب روش پرداخت:</p>
                                                    <div class="btn-group btn-group-toggle mt-3" data-toggle="buttons">
                                                        <label class="rounded btn btn-outline-warning text-dark active mx-2 d-flex align-items-center justify-content-center pointer">
                                                            <input type="radio" name="payment" id="option1" value="online" autocomplete="off" checked>
                                                            <span class="font-14">پرداخت از طریق کارت شتاب</span>
                                                        </label>

                                                        @auth
                                                            @if($factorItem->factor->user->wallet >= $factorItem->prepayment['price'])
                                                                <label class="rounded btn btn-outline-warning text-dark mx-2 d-flex flex-column align-items-center justify-content-center pointer">
                                                                    <input type="radio" name="payment" value="wallet" id="option2" autocomplete="off">
                                                                    <span class="font-12">کسر {{ number_format($factorItem->coupon ? $factorItem->prepayment['price'] - ($factorItem->prepayment['price'] * $factorItem->coupon->discount / 100 ) : $factorItem->prepayment['price']) }} ریال از کیف شما</span>
                                                                    <span class="font-12">موجودی شما: {{ number_format($factorItem->factor->user->wallet) }} ریال</span>
                                                                </label>
                                                            @else
                                                                <button type="button" class="rounded btn btn-secondary disabled d-flex flex-column align-items-center justify-content-center" disabled>
                                                                    <span class="font-12">کسر {{ number_format($factorItem->coupon ? $factorItem->prepayment['price'] - ($factorItem->prepayment['price'] * $factorItem->coupon->discount / 100 ) : $factorItem->prepayment['price']) }} ریال از کیف شما</span>
                                                                    <span class="font-12">موجودی شما: {{ number_format($factorItem->factor->user->wallet) }} ریال</span>
                                                                </button>
                                                            @endif
                                                        @endauth

                                                    </div>
                                                    <input id="btn_factor" type="submit" value="جهت پرداخت اینجا کلیک کنید" class="btn btn-success font-14 my-2 btn-block">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3 d-flex justify-content-end align-items-center">

                                    @if(!is_null($factorItem->callcenter_id))
                                        <span class="mx-2">
                                                    اپراتور: {{ \App\User::find($factorItem->callcenter_id)->name }} {{ \App\User::find($factorItem->callcenter_id)->family }}
                                            این فاکتور را برای شما ایجاد کرده است.</span>
                                    @endif

                                    <span>مهر و امضا فروشنده</span>
                                    @if(!is_null($factorItem->representation))
                                        @if($factorItem->representation->stamp)
                                            <img src="{{ $factorItem->representation->stamp }}" alt="">
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
