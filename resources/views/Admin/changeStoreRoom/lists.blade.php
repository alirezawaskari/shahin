@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست خروج از انبار</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">خروج از انبار</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="get" action="{{route('changeStoreRoom.index')}}">
                <div class="row">
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <input class="form-control" name="name"
                               value="@if(isset($queries['name']) &&
                           $queries['name'] != null) {{$queries['name']}} @endif" placeholder="نام تایید کننده ">
                    </div>
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <input class="form-control" name="family"
                               value="@if(isset($queries['family']) &&
                           $queries['family'] != null) {{$queries['family']}} @endif" placeholder=" نام خانوادگی تایید کننده">
                    </div>
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <input class="form-control" name="product_name"
                               value="@if(isset($queries['product_name']) &&
                           $queries['product_name'] != null) {{$queries['product_name']}} @endif" placeholder="نام محصول">
                    </div>
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <select title="type" name="type" class="custom-select">
                            <option @if(isset($queries['type'])) @if($queries['type'] === "all") selected
                                    @endif @endif value="all">نوع
                            </option>
                            <option @if(isset($queries['type'])) @if($queries['type'] === 0) selected
                                    @endif @endif value="0">خروج
                            </option>
                            <option @if(isset($queries['type'])) @if($queries['type'] === 1) selected
                                    @endif @endif value="1">ورود
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <select title="store_room_id" name="store_room_id" class="custom-select">
                            <option @if(isset($queries['store_room_id'])) @if($queries['store_room_id'] == "all") selected
                                    @endif @endif value="all">انبار
                            </option>
                            @foreach($store_rooms as $store_room)
                                <option @if(isset($queries['store_room_id'])) @if($queries['store_room_id'] == $store_room->id) selected
                                        @endif @endif value="{{$store_room->id}}">{{$store_room->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-3 disInline">
                        <select title="product_type" name="product_type" class="custom-select">
                            <option @if(isset($queries['product_type'])) @if($queries['product_type'] == "all") selected
                                    @endif @endif value="all">نوع کالا
                            </option>
                            <option @if(isset($queries['product_type'])) @if($queries['product_type'] == "0") selected
                                    @endif @endif value="0">مواد اولیه
                            </option>
                            <option @if(isset($queries['product_type'])) @if($queries['product_type'] == "1") selected
                                    @endif @endif value="1">کالا
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date"></date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="end_date"></date-picker>
                    </div>
                    <div class="col-12 col-md-2 mb-3 disInline">
                        <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if (count($change_store_room) > 0)
                <section class="dataPagination">
                    @include('Admin.changeStoreRoom.load')
                </section>
            @endif
        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/changeStoreRoom/changeList.js') }}"></script>
@stop