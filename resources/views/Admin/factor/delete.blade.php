@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست تراکنش های پاک شده</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست تراکنش های پاک شده</li>
            </ol>
        </nav>
    </div>
</div>
<form id="filter_form" method="get" action="/dashboard/factor/deleted/filter">
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

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                    </date-picker>
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

    <div class="card-body table-responsive ">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json" data-title="{{ $title }}">
            <thead>
                <tr>
                    <th>#</th>
                    <th scope="col">نام</th>
                    <th scope="col">شماره موبایل</th>
                    <th scope="col">مبلغ (ریال)</th>
                    <th scope="col">کاربر حذف کننده</th>
                    <th scope="col">تاریخ حذف</th>
                    <th scope="col">اقدام</th>
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
        <input type="hidden" value="filterDeletedList" id="url">
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>

</div>
@stop
@section('js')
<script src="/assets/dashboard/js/factor.js"></script>
@endsection
