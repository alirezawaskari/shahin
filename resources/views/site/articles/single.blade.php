@extends('site.master')

@section('title'){{ $article->title }}@endsection

@section('description'){{ $article->description }}@endsection


@section('content')

    <div class="article">
        {{--banner--}}
        @component('site.layouts.breadcrumb',['title'=> $article->title ,'background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent
        {{--article--}}
        <div class="article-section pb-5 bg-gray">
            <div class="container">
                <div class="row">
                    <div class="card shadow w-100 bg-white article-box border-0 overflow-hidden">
                        <img src="{{ $article->old_image }}" class="card-img-top" alt="">
                        <div class="card-body">
                            <h4 class="card-title text-orange d-inline-block">{{ $article->title }}</h4>
                            <span class="float-left pt-2 text-black-50">{{ verta($article->created_at)->format('%d %B ، %Y') }}</span>
                            <p class="card-text text-black-50 my-4">{!!  $article->description !!}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
