@extends('errors::illustrated-layout')

@section('code', '404')
@section('title', __('صفحه مورد نظر شما یافت نشد!'))

@section('image')
<div style="background-image: url({{ asset('/svg/404.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection
@section('message', __('صفحه مورد نظر شما یافت نشد!'))
<script>
    var currentURL = window.location.href;
    var parts = currentURL.split("/");
    var section = parts[3];
    if(section != 'dashboard'){
        window.location.replace("/");
    }
</script>
