@extends('site.master')

@section('content')

<div class="services">
    {{--banner--}}
    @component('site.layouts.breadcrumb',['title'=>'فهرست
    خدمات','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

    {{--Services--}}
    <div class="service-section py-5">
        <div class="container">
            <div class="row py-3">
                <div style="padding: 5px">
                    <div class="row ">
                        @foreach($services as $service)
                        <div
                            class="col-md-2 col-sm-4 col-4 d-flex flex-column align-items-center text-center justify-content-center">
                            <a href="/services/{{ $service->slug }}" class="ml-2">
                                <img class="ml-2" src="{{ $service->icon }}" alt="{{ $service->title }}" width="74px">
                                <p class="text-dark mt-1 font-weight-bolder">{{ $service->title }}</p>
                            </a>
                        </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection