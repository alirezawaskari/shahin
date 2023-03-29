@extends('site.master')

@section('title')
    درباره ما
@endsection

@section('content')

    <div class="help">
        {{--banner--}}
        <div class="banner position-relative">
            <div class="overlay position-absolute"></div>
            <div class="container py-4">
                <h2 class="text-orange pt-5 position-relative z-index-5 text-center">{{ $help->title }}</h2>
                <div class="row">
                    <div class="col-12 py-5">
                        {!! $help->content !!}
                    </div>
{{--                   <img src="{{ asset('img/installment.png') }}" width="100%">--}}
                </div>
            </div>
        </div>
    </div>
@endsection
