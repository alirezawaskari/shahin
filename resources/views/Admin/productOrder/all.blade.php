@extends('Admin.master')
@section('title')مدیریت سفارشات کالا@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>مدیریت سفارشات کالا</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">سفارشات کالا</li>
            </ol>
        </nav>
    </div>
</div>

<form id="filter_form" method="get" action="/dashboard/productOrder/filter">
    @csrf
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" name="resnumber" placeholder="شماره سفارش" class="form-control">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" name="name" placeholder="نام" class="form-control">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" name="family" placeholder="نام خانوادگی" class="form-control">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" name="phone" placeholder="موبایل" class="form-control">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" name="tracking_code" placeholder="کد رهگیری مرسوله" class="form-control">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="shipping_status" class="custom-select">
                        <option value="all">انتخاب وضعیت سفارش</option>
                        @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="product_category" class="custom-select select2">
                        <option value="all">انتخاب دسته بندی کالا</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate"></date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate"></date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
            <thead>
                <tr>
                    <th>#</th>
                    <th>شماره سفارش</th>
                    <th>خریدار</th>
                    <th>موبایل</th>
                    <th>کالاها</th>
                    <th>تعداد</th>
                    <th>مبلغ سفارش (ریال)</th>
                    <th>هزینه ارسال (ریال)</th>
                    <th>مبلغ کل (ریال)</th>
                    <th>آدرس ارسال</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center"
            id="loading">
            <div role="status" class="spinner-border text-primary"></div>
            <br>
            <span>در حال بارگذاری...</span>
        </div>
        <p id="page_text"></p>
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="{{ asset('assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/productOrder.js?v=1.0') }}"></script>
@endsection
