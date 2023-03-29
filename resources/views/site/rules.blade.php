@extends('site.master')

@section('title')
    قوانین
@endsection

@section('content')

    <div class="article">
        {{--banner--}}
        @component('site.layouts.breadcrumb',['title'=>'قوانین سایت','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        {{--rules--}}
		@if (! auth()->check() && ! \Illuminate\Support\Facades\Cookie::has('phone_newsletter'))
        @endif
        <div class="article-section pb-5 bg-gray">
            <div class="container">
                <div class="row">
                    <div class="card w-100 shadow bg-white article-box overflow-hidden border-0">
                        <div class="card-body">
                            {!! $site_rules['body'] !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
