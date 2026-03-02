@extends('site.representation.master')

@section('content')

    <div class="articles">
        {{--banner--}}
        @component('site.layouts.breadcrumb',['title'=>'آرشیو مقالات','background'=>asset('/assets/site/images/article_header.jpg')]) @endcomponent

        {{--Services--}}
        <div class="service-section py-5 bg-gray">
            <div class="container">
                <div class="row py-3">

                    @foreach($articles as $article)
                        <article class="col-12 col-sm-6 col-md-4 mb-3 ">
                            <div class="card article shadow">
                                <a href=""><img src="{{ $article->old_image }}" class="card-img-top" alt=""></a>
                                <div class="card-body">
                                    <a href="" class="text-dark"><h5>{{ $article->title }}</h5></a>
                                    <p class="card-text text-black-50">{{ Str::limit($article->demo , 50) }}</p>
                                    <a href="{{ $article->path() }}" class="btn btn-orange text-white rounded-pill float-left font-12 py-2 px-4">ادامه
                                        مطلب</a>
                                    <span class="font-12 text-black-50 float-right pt-2">{{ verta($article->created_at)->format('%d %B ، %Y') }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach

                </div>

            </div>

            <div class="d-flex justify-content-center align-items-center">
                {{ $articles->render() }}
            </div>

        </div>


    </div>
@endsection
