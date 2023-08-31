@extends('site.representation.master')
@section('title')
    {{ $representation->fa_title }}
@endsection
@section('header')
    <style>
        .active{
            background: white;
        }
    </style>
@endsection
@section('content')
    <div class="index">
        @auth
            @if($factor_count > 0)
                <div class="bg-warning py-2 text-dark text-center">
                    شما فاکتور پرداخت نشده دارید، لطفا بررسی و پرداخت نمایید
                    <a href="/profile/factor" class="btn btn-dark py-1 font-12">مشاهده</a>
                </div>
            @endif
            @if($message_count > 0)
                <div class="bg-success text-white py-2 text-center">
                    شما پیام جدید خوانده نشده دارید
                    <a href="/profile/notifications" class="btn btn-dark py-1 font-12">مشاهده</a>
                </div>
            @endif
        @endauth
        {{--Carousel--}}

            <div id="carouselExampleControls" style="padding-right: 5%;padding-left:  5% " class="carousel slide d-none d-sm-block" data-ride="carousel">
                <div class="carousel-inner main-slider" >
                    @foreach($sliders as $slider)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}  " >
                            <a href="{{ $slider->link }}"><img src="{{ $slider->img }}"   class="d-block w-100" alt="{{ $slider->title }}"></a>
                        </div>
                    @endforeach
                </div>
                <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>

                <div id="carouselExampleIndicators" class="carousel slide d-block d-sm-none" data-ride="carousel">

                    <div class="carousel-inner">

                        @foreach($sliders_mobile as $slider)
                            <div class="carousel-item {{ $loop->first ? 'active' : '' }}  " >
                                <a href="{{ $slider->link }}"><img src="{{ $slider->img }}"   class="d-block w-100" alt="{{ $slider->title }}"></a>
                            </div>
                        @endforeach

                    </div>
                    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            <a href="/services/business-agency">
                <div class="container">
                    <div class="row m-4 p-2 shadow-sm" style="background: rgba(86, 244, 244, 0.34);">
                        <div class="col-md-6">
                            <h5 class="   text-warning" style="color: #de295a !important">
                                <img src="https://kalasakht.com/upload/png-11.png" style="width: 50px" alt="">
                                از لاله هدیه بگیرید <img src="https://usagif.com/wp-content/uploads/gift-36.gif" style="width: 40px" alt="">
                            </h5>
                            <p class="lead text-dark">
                                <strong class="text-danger">ویژه :</strong>
                                اعطای مجوز به تولیدکنندگان، خدمات‌دهندگان و فروشندگان محترم.</p>
                            <p class="text-dark">دریافت مجوز ایجاد شعب لاله در سراسر کشور.</p>
                        </div>
                    </div>
                </div>
            </a>
        {{--category--}}
            <div style="padding: 5px" >
                <div class="row ">
                    @foreach($services as $service)
                        <div class="col-md-2 col-sm-4 col-4 d-flex flex-column align-items-center text-center justify-content-center">
                            <a href="/representation/{{ $representation->slug }}/{{ $service->slug }}/{{ $callcenter->id }}" class="ml-2">
                                <img class="ml-2" src="{{ $service->icon }}" alt="{{ $service->title }}" width="74px">
                                <p class="text-dark mt-1 font-weight-bolder">{{ $service->title }}</p>
                            </a>
                        </div>
                    @endforeach
                </div>

            </div>

            <div class="container">
                <div class="row justify-content-center ">
                    نقل قول مشتریان
                </div>
                <div id="feedback-carousel" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <div class="row justify-content-center">
                                <div class="col-md-8 shadow p-3 mb-5 bg-white rounded">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p class="card-text">با توجه به قیمت، خدمات جراحی زیبایی بسیار خوبی رو ارائه دادن. حتی از توقعاتم بیشتر بود .</p>
                                            <p class="card-subtitle mb-2 text-muted">تاریخ ارسال: ۲۵ خرداد ۱۴۰۰</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item ">
                            <div class="row justify-content-center ">
                                <div class="col-md-8 shadow p-3 mb-5 bg-white rounded">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p class="card-text">از خدمت دندانپزشکی اقساطی بسیار راضی بودم ، کار پزشک حرف نداشت .</p>
                                            <p class="card-subtitle mb-2 text-muted">تاریخ ارسال: 12 مهر 1401</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <a class="carousel-control-prev" href="#feedback-carousel" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#feedback-carousel" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
@endsection

