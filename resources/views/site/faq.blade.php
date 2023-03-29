@extends('site.master')

@section('title')
    سوالات متداول
@endsection

@section('content')

    <div class="contract">
        {{-- banner --}}
        @component('site.layouts.breadcrumb',['title'=>'سوالات متداول','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        {{-- faq --}}
        <div class="contract-section py-5 bg-gray">
            <div class="container">

                    <div class="accordion" id="accordionExample">
                        @foreach($faqs as $key => $faq)
                            <div class="card mb-3">
                                <div class="card-header" id="headingOne{{ $key }}" data-toggle="collapse" data-target="#collapseOne{{ $key }}" aria-expanded="true" aria-controls="collapseOne{{ $key }}">
                                    <h2 class="mb-0">
                                        <span class="font-14 text-orange">
                                            {{ $faq->title }}
                                        </span>
                                    </h2>
                                </div>

                                <div id="collapseOne{{ $key }}" class="collapse" aria-labelledby="headingOne{{ $key }}" data-parent="#accordionExample">
                                    <div class="card-body">
                                        {{ $faq->text }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

            </div>
        </div>

    </div>
@endsection
