@extends('site.master')
@section('title')
خدمات درمانی - رفاهی لاله
@endsection
@section('header')
<style>
    .active {
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
    @if(auth()->check() && auth()->user()->organization_id)
    <img src="{{ auth()->user()->organization->image }}" class="d-block w-100" height="580px">
    @else
    <div id="carouselExampleControls" style="padding-right: 5%;padding-left:  5% "
        class="carousel slide d-none d-sm-block" data-ride="carousel">
        <div class="carousel-inner main-slider">
            @foreach($sliders as $slider)
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}  ">
                <a href="{{ $slider->link }}"><img src="{{ $slider->img }}" class="d-block w-100"
                        alt="{{ $slider->title }}"></a>
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
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}  ">
                <a href="{{ $slider->link }}"><img src="{{ $slider->img }}" class="d-block w-100"
                        alt="{{ $slider->title }}"></a>
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
    @endif
    <a href="/services/business-agency">
        <div class="container">
            <div class="row m-4 p-2 shadow-sm" style="background: rgba(86, 244, 244, 0.34);">
                <div class="col-md-8">
                    <h5 class="   text-warning" style="color: #de295a !important">
                        <img src="https://kalasakht.com/upload/png-11.png" style="width: 50px" alt="">
                        از لاله هدیه بگیرید <img src="https://usagif.com/wp-content/uploads/gift-36.gif"
                            style="width: 40px" alt="">
                    </h5>
                    <p class="lead text-dark">
                        <strong class="text-danger">ویژه :</strong>
                        اعطای مجوز به تولیدکنندگان، خدمات‌دهندگان و فروشندگان محترم.
                    </p>
                    <p class="text-dark">دریافت مجوز ایجاد شعب لاله در سراسر کشور.</p>
                </div>
            </div>
        </div>
    </a>
    {{--category--}}
    <div style="padding: 5px">
        <div class="row ">
            @foreach($services as $service)
            <div
                class="col-md-2 col-sm-4 col-4 d-flex flex-column align-items-center text-center justify-content-center">
                <a href="/services/{{ $service->slug }}/{{ $callcenter->id }}" class="ml-2">
                    <img class="ml-2" src="{{ $service->icon }}" alt="{{ $service->title }}" width="74px">
                    <p class="text-dark mt-1 font-weight-bolder">{{ $service->title }}</p>
                </a>
            </div>
            @endforeach
        </div>

    </div>
    <div id="phoneModal" tabindex="-1" role="dialog" aria-labelledby="phoneModal" aria-hidden="true"
        class="modal fade phone-modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-start">
                    <i class="fas fa-mobile-alt font-18 text-orange ml-2"></i>
                    <h6 class="modal-title" id="phoneModalLable">کاربران گرامی لاله :</h6>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <img width="100%" src="https://blog.okala.com/wp-content/uploads/2022/02/luxury-3.jpg"
                                    alt="">
                            </div>
                        </div>
                    </div>
                    <h6>
                        <h6>
                            <strong>
                                روابط عمومی شبکه خدمات درمانی رفاهی لاله
                                فرارسیدن سال جدید و نوروز باستانی را خدمت همه شما هم میهنان گرامی تبریک و تهنیت عرض
                                مینماید.
                            </strong>
                            <p> اداره کل روابط عمومی سجا کل کشور </p>
                        </h6>

                </div>
                <div class="row col-12">
                    <div class="mb-1 p-1">
                        <a href="/message1403" class="btn btn-success">
                            <i class="fa fa-hand-pointer" style="margin-right: 5px "></i> متن پیام تبریک مدیرعامل شبکه
                            لاله </a>
                    </div>
                    <div class="mb-1 p-1">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">بستن</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="wrapper h-li">--}}
        {{-- <nav class="menu-wrapper">--}}
            {{-- <ul>--}}
                {{-- @foreach($services as $service)--}}
                {{-- <li class="menu-li-md">--}}
                    {{-- <a href="/services/{{ $service->slug }}/{{ $callcenter->id }}" class="ml-2">--}}
                        {{-- <img class="rounded-circle ml-2" src="{{ $service->icon }}" alt="{{ $service->title }}"
                            width="50px">--}}
                        {{-- <p class="text-dark text-center mt-1 mr-n4 font-weight-bolder">{{ $service->app_title }}
                        </p>--}}
                        {{-- </a>--}}
                    {{-- </li>--}}
                {{-- @endforeach--}}
                {{-- </ul>--}}
            {{-- </nav>--}}
        {{-- </div>--}}
    {{--Services--}}
    {{-- <div class="service-section bg-gray py-2">--}}
        {{-- <div class="container">--}}
            {{-- <div class="row">--}}
                {{-- @foreach($services as $service)--}}
                {{-- <article class="col-4 overflow-hidden pr-1 pl-1 mb-2">--}}
                    {{-- <a href="/services/{{ $service->slug }}/{{ $callcenter->id }}" class="ml-2">--}}
                        {{-- <img src="{{ $service->image_app }}" class="card-img" alt="{{ $service->title }}">--}}
                        {{-- </a>--}}
                    {{-- </article>--}}
                {{-- @endforeach--}}
                {{-- </div>--}}
            {{-- </div>--}}
        {{-- </div>--}}

    {{--Register--}}
    {{-- <div class="register-section py-4">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-5 py-5 my-auto mr-auto text-justify">
                    {{ $setting->txt_box_1 }}
                </div>
                <div class="d-none d-md-block col-md-6 pl-0 text-left">
                    <img src="{{ $setting->img_box_1 }}" class="img-fluid" alt="">
                </div>
            </div>

        </div>
    </div>--}}

    {{--section--}}
    {{-- <div class="register-section py-4">
        <div class="container-fluid">
            <div class="row">
                <div class="d-none d-md-block col-md-6 pr-0 text-right">
                    <img src="{{ $setting->img_box_2 }}" class="img-fluid" alt="">
                </div>
                <div class="col-12 col-md-5 py-5 my-auto ml-auto text-justify">
                    {{ $setting->txt_box_2 }}
                </div>
            </div>

        </div>
    </div>--}}

    {{--video--}}
    {{-- <div class="video-section py-5 bg-gray">
        <div class="container">
            <div class="row py-5">
                <div class="col-12 col-md-6 my-auto mr-auto text-justify">
                    {{ $setting->txt_video }}
                </div>
                <div class="col-12 col-md-6">
                    {!! $setting->video !!}
                </div>
            </div>
        </div>
    </div>--}}
    {{--section--}}
    {{-- <div class="video-section py-5">
        <div class="container">
            <div class="row py-5">
                <div class="col-12">
                    --}}{{-- {!! $setting->index_description !!}--}}{{--
                </div>
            </div>
        </div>
    </div>--}}
    @endsection
