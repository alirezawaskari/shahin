@extends('Admin.master')
@section('title')فهرست تراکنش ها@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست تراکنش ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست تراکنش ها</li>
            </ol>
        </nav>
    </div>
</div>
@role('organ-manager')
<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card overflow-hidden">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center m-b-10">
                    <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                        <i class="ti-server"></i>
                    </div>
                    <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($credits) }}</h2>
                </div>
                <p>اعتبار کل سازمان(ریال)</p>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-lg-6 col-md-12">
        <div class="card overflow-hidden">
            <div class="card-body text-center">
                <div class="d-flex align-items-center justify-content-center m-b-10">
                    <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                        <i class="ti-server"></i>
                    </div>
                    <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($wallets) }}</h2>
                </div>
                <p>اعتبار باقیمانده سازمان(ریال)</p>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
</div>
@endrole
@permission('factor|factor-detail')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6 mb-4 mb-md-0">
                <payment-pie :values="{{ json_encode($values) }}" :labels="{{ json_encode($labels) }}"></payment-pie>
            </div>
            <div class="col-12 col-md-6 mb-4 mb-md-0">
                <day-payment-pie :values="{{ json_encode($day_values) }}" :labels="{{ json_encode($labels) }}">
                </day-payment-pie>
            </div>
        </div>
    </div>
</div>
@endrole
<form id="filter_form" method="get" action="/dashboard/factors">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-3 mb-2"><input type="text" name="name" placeholder="نام" class="form-control">
                </div>
                <div class="col-12 col-md-3 mb-2"><input type="text" name="family" placeholder="نام خانوادگی"
                        class="form-control"></div>
                <div class="col-12 col-md-3 mb-2"><input type="text" name="phone" placeholder="موبایل"
                        class="form-control"></div>
                <div class="col-12 col-md-3 mb-2"><input type="text" name="resnumber" placeholder="شناسه پرداخت"
                        class="form-control"></div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                    </date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate"></date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="status" class="custom-select  select2">
                        <option value="all">انتخاب وضعیت تراکنش</option>
                        <option value="1">پرداخت شده</option>
                        <option value="2">عودت پرداخت</option>
                        <option value="0">در انتظار پرداخت</option>
                        <option value="3">منقضی شده</option>
                        <option value="5">واریز شده</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="title" class="custom-select select2" id="title">
                        <option value="all">انتخاب موضوع دریافت وجه</option>
                        <option value="register">هزینه ثبت نام</option>
                        <option value="installment">قسط</option>
                        <option value="prepayment">پیش دریافت اقساط</option>
                        <option value="extra_pay">جریمه</option>
                        <option value="price_difference">اختلاف حساب</option>
                        <option value="factor">فروش نقد</option>
                        <option value="registeration_debt">اختلاف هزینه ثبت نام</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="title_pay" class="custom-select select2" id="title-pay">
                        <option value="all">انتخاب موضوع پرداخت وجه</option>
                        <option value="employee_payment">حقوق ودستمزد</option>
                        <option value="prepayment">عودت پیش دریافت اقساط</option>
                        <option value="factor">عودت فروش نقد</option>
                        <option value="price_difference">اختلاف حساب</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="type[]" id="factor_type" class="custom-select" multiple="multiple">
                        <option value="online">آنلاین</option>
                        <option value="card">کارت به کارت</option>
                        <option value="card_reader">کارتخوان</option>
                        <option value="cash">پول نقد</option>
                        <option value="check">چک</option>
                        <option value="wallet">کسر از کیف پول</option>
                        <option value="credit">کسر از حواله</option>
                        <option value="overpayment">کسر از اضافه پرداخت</option>
                        <option value="deduction_price_difference">کسر از بستانکاری</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="type_pay" class="custom-select select2">
                        <option value="all">انتخاب نوع پرداخت وجه</option>
                        <option value="card">کارت به کارت</option>
                        <option value="account">واریز به حساب</option>
                        <option value="cash">پول نقد</option>
                        <option value="increase_credit">افزایش اعتبار</option>
                        <option value="check">چک</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="user" class="custom-select select2">
                        <option value="all">انتخاب فروشنده</option>
                        <option value="site">سایت</option>
                        @foreach($callcenters as $item)
                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="old_callcenter" class="custom-select select2">
                        <option value="all">انتخاب فروشنده غیرفعال</option>
                        @foreach($old_callcenters as $item)
                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="province" class="custom-select" id="province_id">
                        <option value="all">انتخاب استان</option>
                        @foreach($provinces as $province)
                        <option value="{{$province->id}}">{{$province->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="city" class="custom-select select2" id="city_id">
                        <option value="all">انتخاب شهر</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="representation" class="custom-select select2">
                        <option value="all">انتخاب نمایندگی</option>
                        <option value="center">نمایندگی مرکزی تهران</option>
                        @foreach($representations as $item)
                        <option value="{{$item->id}}">{{$item->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="organization" class="custom-select select2">
                        <option value="all">انتخاب سازمان</option>
                        @foreach($organizations as $item)
                        <option value="{{$item->id}}">{{$item->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                {{-- -----------------------}}
                @include('Admin.filter.productServiceFilterInputs')
                {{-- -----------------------}}
                @include('Admin.filter.businessSelect')
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                        <label class="custom-control-label" for="exel">خروجی اکسل</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
                </div>
            </div>
        </div>
    </div>
</form>
@if($title !== 'deleted_list')
    @permission('transfer_to_business')
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <span class="ml-3">عملیات گروهی:</span>
                @include('Admin.businessTransfer.bulkAction', [
                    'entity' => 'factor',
                    'recordSelector' => '.factor-business-transfer-record'
                ])
            </div>
        </div>
    @endpermission
@endif
<form id="form-factor-action">
    @if($delete )
    {{-- <div class="card">--}}
        {{-- <div class="card-body">--}}
            {{-- <div class="row">--}}
                {{-- <div class="col-12"></div>--}}
                {{-- <div class="col-12 col-md-3 mb-2"><select class="custom-select" id="delete-transfer-select" --}}
                        {{-- name="type">--}}
                        {{-- <option value="">انتخاب روش عملیات</option>--}}
                        {{-- <option value="delete">حذف انتخاب شده ها</option>--}}
                        {{-- </select>--}}
                    {{-- </div>--}}
                {{-- <div class="col-12 col-md-2 mb-2" id="callcenters-box" style="display: none;">--}}
                    {{-- <select class="form-control">--}}
                        {{-- <option value="">کالسنتر مقصد</option>--}}
                        {{-- @foreach($callcenters as $item)--}}
                        {{-- <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>--}}
                        {{-- @endforeach--}}
                        {{-- </select></div>--}}
                {{-- <div class="col-12 col-md-3 mb-2">--}}
                    {{-- <button type="button" class="btn btn-outline-primary py-2 px-4" id="do-action">اجرا
                        کن</button>--}}
                    {{-- </div>--}}
                {{-- </div>--}}
            {{-- </div>--}}
        {{-- </div>--}}
    @endif
    <div class="card position-relative">

        <div class="card-body table-responsive ">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json"
                   data-title="{{ $title }}"
                   data-business-transfer-enabled="{{ $title !== 'deleted_list' && auth()->user()->hasPermission('transfer_to_business') ? 1 : 0 }}">
                <thead>
                    <tr>
                        <th>#</th>
                        @if($title !== 'deleted_list')
                            @permission('transfer_to_business')
                                <th><input type="checkbox" class="business-transfer-select-all" aria-label="انتخاب همه"></th>
                            @endpermission
                        @endif
                        <th>نام</th>
                        <th>موبایل</th>
                        <th>فروشنده</th>
                        <th>مبلغ</th>
                        <th>عاملیت</th>
                        <th>موضوع</th>
                        <th>نوع پرداخت</th>
                        <th>تاریخ</th>
                        <th>وضعیت</th>
                        <th>اقدام</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center"
                id="loading">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال بارگذاری...</span>
            </div>
            <p id="page_text"></p>
            <input type="hidden" value="filter" id="url">
            <input type="hidden" value="complete" id="type">
            <input type="hidden" value="{{ $delete }}" id="delete">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>

    </div>
</form>
<!-- Modal -->
@include('Admin.factor.factorShowModal')
@stop
@section('js')
<script src="/assets/dashboard/js/layouts.js"></script>
<script src="/assets/dashboard/js/factor.js?v=2.1"></script>
<script src="/assets/dashboard/js/factorModal.js"></script>
<script src="/assets/dashboard/js/layout/productServiceFilterInputs.js"></script>
@endsection
