@extends('Admin.master')

@section('css')
@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title"><h4>ثبت برند</h4></div>
            @if(isset($category))
                @include('Admin.site.brand.brandForm',['category'=>$category])
            @else
                @include('Admin.site.brand.brandForm')
            @endif

        </div>
    </div>
@stop
