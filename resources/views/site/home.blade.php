@extends('site.master')
@section('title')
شبکه خدمات رفاهی ، درمانی و اقساطی لاله
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
    {{-- <a href="/services/business-agency">
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
    </a> --}}
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
    {{-- <div id="phoneModal" tabindex="-1" role="dialog" aria-labelledby="phoneModal" aria-hidden="true"
        class="modal fade phone-modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-start">
                    <i class="fas fa-mobile-alt font-18 text-orange ml-2"></i>
                    <h6 class="modal-title" id="phoneModalLable">کاربران گرامی :</h6>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <img width="100%" src="data:image/png;base64,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" alt="">
                            </div>
                        </div>
                    </div>
                    <h6 class="text-center">
                        <strong>
                            با توجه به مسائل پیش رو درصورت بروز هرگونه مشکلی و دردسترس نبودن شماره تماس شرکت ، برای تماس با کارشناسان لاله با شماره تماس های زیر ارتباط برقرار فرمایید.
                        </strong>
                        <strong>
                            <a href="tel:09366342145">09366342145</a>
                            و
                            <a href="tel:09018224812">09018224812</a>
                        </strong>
                        <br>
                    </h6>
                </div>
                <div class="row col-12">
                    <div class="col-12 mb-3">
                        <a href="/contact" class="w-100 btn btn-info">
                            برای ارتباط باما روی لینک زیر کلیک کنید
                        </a>
                    </div>
                </div>
                <div class="mb-1 p-1" style="margin-right: auto">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">بستن</button>
                </div>
            </div>
        </div>
    </div> --}}
    @endsection
