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

    </div>
    <div class="card">
        <div class="card-body">
            <form id="filter_form" action="/dashboard/totalFactorItem/{{$url}}" method="get">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="name" placeholder="نام" />
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="family" placeholder="نام خانوادگی" />
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="phone" placeholder="موبایل" />
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="purchase_type">
                            <option value="all">انتخاب نوع خرید</option>
                            <option value="1">حضوری</option>
                            <option value="0">آنلاین</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date">
                        </date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="end_date"></date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select name="status_mali[]" id="select_status_mali" class="custom-select"  multiple="multiple">
                            <option value="0">
                                تایید نشده
                            </option>
                            <option value="1">
                                جاری
                            </option>
                            <option value="2">
                                اخطار اول
                            </option>
                            <option value="3">
                                اخطار دوم
                            </option>
                            <option value="4">
                                ارجاع به امورقراردادها
                            </option>
                            <option value="10">
                                ارجاع به حقوقی
                            </option>
                            <option value="5">
                                اتمام اقساط
                            </option>
                            <option value="6">
                                تسویه حساب
                            </option>
                            <option value="7">
                                در انتظار تایید تسویه حساب مالی
                            </option>
                            <option value="8">
                                در انتظار تایید تسویه حساب مدیر
                            </option>
                            <option value="9">
                                آماده تسویه حساب
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control select2" name="supporter" id="callcenter_id">
                            <option value="all">انتخاب پشتیبان</option>

                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control select2" name="center" id="center_id">
                            <option value="all">انتخاب مرکز</option>

                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control select2" name="representation" id="representation_id">
                            <option value="all">انتخاب نمایندگی</option>

                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control select2" name="organization" id="organization_id">
                            <option value="all">انتخاب سازمان</option>

                        </select>
                    </div>
                    @include('Admin.filter.productServiceFilterInputs')
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

            </form>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام</th>
                    <th> موبایل</th>
                    <th>خدمات | محصولات</th>
                    <th>کل مبلغ</th>
                    <th>پیش پرداخت</th>
                    <th>مبلغ قسط</th>
                    <th>تعداد قسط</th>
                    <th>اقساط پرداخت شده</th>
                    <th>جریمه</th>
                    <th>تاریخ شروع و پایان قرارداد</th>
                    <th>وضعیت مالی</th>
                    <th>اقدام</th>
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

            <p id="page_text" style="display: initial"></p>
            <p id="sum_price" style="display: initial"></p>
            <input type="hidden" value="{{ $url }}" id="url">
            <input type="hidden" value="true" id="report">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/totalFactorItem.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/layout/productServiceFilterInputs.js')}}"></script>
@endsection
