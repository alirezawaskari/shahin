@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست مواد اولیه</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست مواد اولیه
                    <span>{{$store_room_title}}</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div class="heading-elements">
            <div class="heading-btn-group ltr mb-3">
                @permission('supplier')
                <a href="{{ route('supplier.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فهرست تامین کنندگان</span>
                </a>
                @endpermission
                @permission('store-room')
                <a href="{{ route('countingUnit.index') }}"
                class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>فهرست واحدها</span>
                </a>
                @endpermission
                @permission('product' | 'product-detail')
                @if(isset($create_link))
                    <a href="{{$create_link}}" class="btn btn-link btn-float has-text text-size-small rtl">
                        <i class="fa fa-edit text-indigo-400" aria-hidden="true"></i>
                        <span>ایجاد محصول جدید</span>
                    </a>
                @endif
                @endpermission
                @permission('store-room')
                <a href="{{ route('storeRoom.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>فهرست انبار</span>
                </a>
                @endpermission
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="get" role="form"
                  action="{{$page_link}}">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="name" placeholder="نام محصول" />
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="storage">
                            <option value="all">محل نگهداری</option>
                            <option value="1">
                                قفسه
                            </option>
                            <option value="2">
                                یخچال
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="main_unit">
                            <option value="all">واحد بسته بندی</option>
                            @foreach($main_units as $main_unit)
                                <option value="{{$main_unit->id}}">{{ $main_unit->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="category_store_room_id">
                            <option value="all">نوع بسته بندی</option>
                            @foreach($category_store_rooms as $category)
                                <option value="{{$category->id}}">{{ $category->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="انقضا از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date"></date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="انقضا تا تاریخ" locale="fa,en" format="YYYY-M-D" name="end_date"></date-picker>
                    </div>

                    <div class="col-12 col-md-2 mb-2 mt-3">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="expiredProducts" name="expired_products" value="1">
                                <label class="custom-control-label" for="expiredProducts">محصولات منقضی شده</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-2 mb-2 mt-3">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="expiringProducts" name="expiring_products" value="1">
                                <label class="custom-control-label" for="expiringProducts">محصولات درحال انقضا</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-2 mb-2 mt-3">
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="finishingProducts" name="finishing_products" value="1">
                                <label class="custom-control-label" for="finishingProducts">محصولات روبه اتمام</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                            <label class="custom-control-label" for="exel">خروجی اکسل</label>
                        </div>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <button id="filter" type="submit" class="btn btn-primary">
                            فیلتر کن
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            @if (count($products_store_room) > 0)
                <section class="dataPagination">
                    @include('Admin.productStoreRoom.material.load')
                </section>
            @endif
        </div>

    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>

    <script src="{{ asset('assets/dashboard/js/site/productStoreRoom/productFilter.js') }}"></script>
@stop
