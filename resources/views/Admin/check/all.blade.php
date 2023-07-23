@extends('Admin.master')
@section('title')فهرست چک ها@endsection

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست چکها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست چک ها</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="/dashboard/check/filter" method="get">
                <div class="row">
                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="applicant_name" placeholder="نام متقاضی"/>
                    </div>
                     <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="applicant_family" placeholder="نام خانوادگی متقاضی"/>
                    </div>
                     <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="applicant_phone" placeholder="موبایل متقاضی"/>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="exporter_name" placeholder="نام صادر کننده"/>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="exporter_family" placeholder="نام خانوادگی صادر کننده"/>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="exporter_phone" placeholder="موبایل صادر کننده"/>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="pay_to_name" placeholder="نام صادر شده در وجه"/>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="pay_to_family" placeholder="نام خانوادگی صادر شده در وجه"/>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="pay_to_family" placeholder=" موبایل صادر شده در وجه"/>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="serial_code" placeholder="شماره سریال" />
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date">
                        </date-picker>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                     name="end_date"></date-picker>
                    </div>

                    <div class="col-12 col-md-4 mb-2">
                        <select class="form-control select2" name="title">
                            <option value="all">انتخاب موضوع چک</option>
                            <option value="installment">
                               قسط
                            </option>
                            <option value="guaranty">
                              ضمانت
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <select class="form-control select2" name="status">
                            <option value="all">انتخاب وضعیت چک</option>
                            <option value="0">
                               در انتظار وصول
                            </option>
                            <option value="1">
                               وصول شده
                            </option>
                            <option value="2">
                                برگشت خورده
                            </option>
                            <option value="3">
                              عودت داده شده
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" name="excel" id="excel">
                            <label class="custom-control-label" for="excel">خروجی اکسل</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button id="do_filter"  class="btn btn-primary">
                            فیلتر کن
                        </button>
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
                    <th scope="col" >#</th>
                    <th scope="col">مبلغ (ریال)</th>
                    <th scope="col">تاریخ وصول </th>
                    <th scope="col">متقاضی </th>
                    <th scope="col">صادر کننده </th>
                    <th scope="col">در وجه </th>
                    <th scope="col">شماره سریال </th>
                    <th scope="col">موضوع </th>
                    <th scope="col">تصاویر</th>
                    <th scope="col">توضیحات </th>
                    <th scope="col">در اختیار</th>
                    <th scope="col">وضعیت </th>
                    <th scope="col">عملیات </th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>

            <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال بارگذاری...</span>
            </div>
            <p id="page_text"></p>
            <input type="hidden" value="{{$can_update_status_check}}" id="can-update-status-check">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
    {{--status check modal --}}
    @include('Admin.check.statusModal')
@stop

@section('js')
    <script src="/assets/dashboard/js/layouts.js"></script>
    <script src="/assets/dashboard/js/check.js?v=1.0"></script>
@endsection
