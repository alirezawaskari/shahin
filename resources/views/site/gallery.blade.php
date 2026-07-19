@extends('site.master')

@section('title')
    گالری تصاویر
@endsection

@section('content')

    <div class="gallery">
        {{-- banner --}}
        @component('site.layouts.breadcrumb',['title'=>'گالری تصاویر','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        {{-- contract --}}
        <div class="gallery-section py-5 bg-gray">
            <div class="container">

                <ul class="button-group filters-button-group nav justify-content-center nav-pills nav-fill filter-box mb-3">
                    <li class="nav-item mx-2 pb-2 active" data-filter="*">نمایش همه
                    </li>
                    @foreach($gallerycategories as $cate)
                        <li class="nav-item mx-2 pb-2" data-filter=".gallery{{ $cate->id }}">
                            {{ $cate->title }}
                        </li>
                    @endforeach
                </ul>

                <div class="grid demo-gallery row" id="lightgallery">

                    @include('site.gallerydata')

                </div>

            </div>
            {{-- <div class="text-center d-flex justify-content-center align-items-center">
                 <a class="ajax-load btn btn-orange text-white font-16 py-1 px-3 mt-5">تصاویر بیشتر</a>
             </div>--}}
        </div>

    </div>

@endsection