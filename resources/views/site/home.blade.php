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
                    <h6 class="modal-title" id="phoneModalLable">همراهان همیشگی لاله :</h6>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <img width="100%" src="https://dl.grapik.ir/shop/wp-content/uploads/2024/03/%D8%AF%D8%A7%D9%86%D9%84%D9%88%D8%AF-%D9%88%DA%A9%D8%AA%D9%88%D8%B1-%D8%B1%D9%88%D8%B2-%D9%85%D8%A7%D8%AF%D8%B1.webp">
                            </div>
                        </div>
                    </div>
                    <h6 class="text-center">
                        <strong>
                            کاشکی می‌شد بهت بگم؛ چقدر صدات‌و دوست دارم
                            چقدر مث بچگی‌هام؛ لالایی‌هات‌و دوست دارم
                            میلاد با سعادت حضرت فاطمه زهرا(س) و بزرگداشت روز مادر به همه شما اعضاء خانواده عزیز و بزرگ لاله تبریک و تهنیت باد
                            به مناسبت روز مادر جشنواره ویژه عقد قرارداد سامانه مطب و مغازه دیجیتال
                            اینجا مراجعه کنید👇
                        </strong>
                        <br>
                    </h6>
                </div>
                <div class="row col-12">
                    <div class="col-12 mb-3">
                        <a href="/services/Permanent-employment" class="w-100 btn btn-info">
                            ثبت نام سامانه مطب و مغازه دیجیتال
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
