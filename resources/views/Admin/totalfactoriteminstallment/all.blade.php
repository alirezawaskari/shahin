@extends('Admin.master')
@section('title')فهرست اقساط بدهکار@endsection

@section('content')
    <div class="page-header">
        <div>
            <h3>فهرست اقساط بدهکار</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست اقساط بدهکار</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form role="form" id="form">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="name" placeholder="نام"/>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="family" placeholder="نام خانوادگی"/>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="phone" placeholder="موبایل"/>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date">
                        </date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                     name="end_date"></date-picker>
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
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام</th>
                    <th>نام خانوادگی</th>
                    <th> موبایل</th>
                    <th>مبلغ (ریال)</th>
                    <th>سر رسید قسط</th>
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

            <p id="page_text"></p>
            <input type="hidden" value="filter" id="url">
            <input type="hidden" value="false" id="report">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
    <script src="{{asset('assets/dashboard/js/totalFactorItemInstallment.js')}}"></script>
    <script src="/assets/dashboard/js/layout/productServiceFilterInputs.js"></script>
@endsection
