@extends('site.representation.master')
@section('title')
{{ $representation->fa_name }}
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

    {{--category--}}
    <div style="padding: 5px">
        <div class="row ">
            @foreach($services as $service)
            <div
                class="col-md-2 col-sm-4 col-4 d-flex flex-column align-items-center text-center justify-content-center">
                <a href="/{{ $representation->slug }}/{{ $service->slug }}/{{ $callcenter->id }}" class="ml-2">
                    <img class="ml-2" src="{{ $service->icon }}" alt="{{ $service->title }}" width="74px">
                    <p class="text-dark mt-1 font-weight-bolder">{{ $service->title }}</p>
                </a>
            </div>
            @foreach($service->activeServiceItems($service) as $service_item)
            <div
                class="col-md-2 col-sm-4 col-4 d-flex flex-column align-items-center text-center justify-content-center">
                <a href="/{{ $representation->slug }}/serviceItem/{{ $service_item->slug }}/{{ $callcenter->id }}" class="ml-2">
                    <img class="ml-2" src="{{ $service_item->icon }}" alt="{{ $service_item->title }}" width="74px">
                    <p class="text-dark mt-1 font-weight-bolder">{{ $service_item->title }}</p>
                </a>
            </div>
            @endforeach
            @endforeach
        </div>

    </div>
    @endsection
