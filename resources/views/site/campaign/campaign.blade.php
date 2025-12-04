@extends('site.master')

@section('title'){{$campaign->name}}@endsection
@section('content')
<div class="login">
    <div class="login-section bg-gray py-5">
        <div class="container">
            <div class="login-box bg-white shadow col-12 rounded-20px py-5 px-md-5 " id="register_box">
                <div class="row">
                    <div class="col-12 px-md-5 d-flex flex-column">
                        <h5 class="text-center mb-3">{{$campaign->subject}}</h5>
                        <img src="{{ asset($campaign->image) }}" alt="{{ $campaign->subject }}"
                            class="img-fluid w-100 mb-3" />
                        {!! $campaign->content !!}
                        @foreach($errors->all() as $error)
                        <div class="alert alert-danger" role="alert">
                            {{ $error }}
                        </div>
                        @endforeach
                        @if(isset($campaign->form))
                        @include($campaign->form)
                        @endif
                        @if(count($campaign->campaign_collaboration) > 0)
                        <div class="col-12  mx-auto">
                            <h3 class="text-center mb-4">مشارکت‌کنندگان این پوییش</h3>
                            <div class="row justify-content-center">
                                @foreach($campaign->campaign_collaboration as $campaign_collaboration)
                                <div class="col-12 col-md-6 mb-4 d-flex justify-content-center">
                                    <div class="card shadow-sm h-100 w-100 text-center">
                                        <div class="card-body">
                                            <a target="_blank" class="text-black-50"
                                                href="{{$campaign_collaboration->url}}">
                                                <h4 class="card-title mb-3">{{ $campaign_collaboration->name }}</h4>
                                                <img src="{{ asset($campaign_collaboration->logo) }}"
                                                    alt="{{ $campaign_collaboration->name }}" class="img-fluid mb-3"
                                                    style="max-height: 150px; object-fit: contain;">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@if(session()->get('contact_success')=='success')
<span id="contact_success"></span>
@elseif(session()->get('contact_error')=='success')
<span id="contact_error"></span>
@endif
@endsection
@section('js')
<script src="/assets/site/js/fix-number.js?v=1"></script>
<script src="/assets/site/js/register-campaign.js?v=1"></script>
@endsection