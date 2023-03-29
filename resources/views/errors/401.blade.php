@extends('errors::illustrated-layout')

@section('code', '401')
@section('title', __('Unauthorized'))

@section('image')
<div style="background-image: url({{ asset('assets/errors/access_denied.jpg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
</div>
@endsection

@section('message', __('.شما اجازه ورود به این صفحه را ندارید'))
