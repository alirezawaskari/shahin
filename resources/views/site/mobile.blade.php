@extends('site.master')
@section('title')
خدمات درمانی - رفاهی لاله
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
        @if(auth()->check() && auth()->user()->level == 'organ')
            <img src="{{ auth()->user()->organization->image }}" class="d-block w-100" height="580px">
        @else
            <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                @foreach($sliders as $slider)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <a href="{{ $slider->link }}"><img src="{{ $slider->img }}" class="d-block w-100" alt="{{ $slider->title }}"></a>
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
        @endif
        {{--category--}}
        <div class="wrapper h-li">
            <nav class="menu-wrapper">
                <ul class="menu">
                    @foreach($services as $service)
                        <li>
                            <a href="/services/{{ $service->slug }}/{{ $callcenter->id }}" class="ml-2">
                                <img class="rounded-circle ml-2" src="{{ $service->icon }}" alt="{{ $service->title }}" width="50px">
								<p class="text-dark text-center mt-1 mr-n1 font-weight-bolder">{{ $service->app_title }}</p>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>
        </div>
        {{--Services--}}
        <div class="service-section bg-gray py-2">
            <div class="container">
                <div class="row">
                    @foreach($services as $service)
                        <article class="col-4 overflow-hidden pr-1 pl-1 mb-1">
                            <a href="/services/{{ $service->slug }}/{{ $callcenter->id }}" class="ml-2">
                                <img src="{{ $service->image_app }}" class="card-img" alt="{{ $service->title }}">
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
		</div>
</div>
@endsection
