@extends('site.master')

@section('content')

    <div class="services">
        {{--banner--}}
        @component('site.layouts.breadcrumb',['title'=>'آرشیو خدمات','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        {{--Services--}}
        <div class="service-section py-5">
            <div class="container">
                <div class="row py-3">
                    {{--<div class="col-12 pb-5 d-flex justify-content-center">
                        <form action="" class="d-inline-block">
                            <input class="search" type="text" required placeholder="می خواهم عضو خانواده لاله شوم">
                            <button class="send pt-1" type="submit">
                                <i class="fa fa-search"></i>
                            </button>
                        </form>
                    </div>--}}
                    @foreach($services as $service)
                         <article class="col-12 col-sm-6 col-md-4 mb-3 overflow-hidden">
                            <div class="card bg-dark text-white position-relative rounded-2x overflow-hidden">
                                 <img src="{{ $service->image_app }}" class="card-img" alt="{{ $service->title }}">
                                 <div class="card-img-overlay text-center position-absolute">
                                 <h5 class="card-title pt-2">{{ $service->title }}</h5>
                                 <a href="/services/{{ $service->slug }}" class="btn btn-orange font-12 text-white mt-4">همین حالا ثبت نام کنید</a>
                                 </div>
                             </div>
                         </article>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
@endsection
