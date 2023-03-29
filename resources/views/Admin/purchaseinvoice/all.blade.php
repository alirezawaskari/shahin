@extends('Admin.master')
@section('title'){{ $title }}@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>{{ $title }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('purchase-invoice-detail')
            <a href="{{ route('purchaseInvoice.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>ثبت فاکتور جدید</span>
            </a>
            @endpermission
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form  id="filter_form" method="get" role="form" action="/dashboard/contact/{{$url}}">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="number" class="form-control" name="id" placeholder="شماره فاکتور" min="1" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="supplier">
                        <option value="all">انتخاب تامین کننده</option>
                        @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">وضعیت</option>
                        <option value="0">جدید</option>
                        <option value="1">منتظر تایید مالی</option>
                        <option value="2">منتظر تایید مدیر</option>
                        <option value="3">منتظر تایید مدیر مالی عاملیت</option>
                        <option value="4">منتظر تایید  مدیر عاملیت</option>
                        <option value="5">آماده ارسال</option>
                        <option value="6">کالا ارسال شده</option>
                        <option value="7">گزارش خرابی کالا</option>
                        <option value="8">اتمام کار</option>
                        <option value="9">منتظر واریز وجه</option>
                        <option value="10">رد شده</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                 name="end_date"></date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>

                </div>
            </div>
        </form>
    </div>
</div>
<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
            <thead>
                <tr>
                    <th>#</th>
                    <th>شماره فاکتور</th>
                    <th>نام تامین کننده</th>
                    <th>نام درخواست کننده</th>
                    <th>نمایندگی</th>
                    <th>مبلغ فاکتور (ریال)</th>
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
            <div role="status" class="spinner-border text-primary">
            </div>
            <br>
            <span>در حال بارگذاری...</span>
        </div>
        <p id="page_text"></p>
        <input type="hidden" value="{{ $url }}" id="url">
        <input type="hidden" value="{{$delete}}" id="delete">
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/purchaseInvoice.js"></script>
@endsection
