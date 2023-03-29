@extends('Admin.master')
@section('title')جزئیات گزارش مالی {{ $user->family }}@endsection

@section('content')
    <div class="page-header">
        <div>
            <h3>جزئیات گزارش مالی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">جزئیات گزارش مالی</li>
                </ol>
            </nav>
        </div>
        <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
                @permission('user_performance_detail')
                <a href="{{ route('employee.performanceDetail', [$user->id]) }}"
                    class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>کلیات گزارش مالی</span>
                </a>
                @endpermission
            <a href="{{ route('employee.financial.report' , [$user->employee->id]) }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>عملکرد ماهانه</span>
            </a>
        </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="/dashboard/employee-wallet-filter/{{$user->id}}/{{$key}}" method="get">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="name" placeholder="نام"/>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="family" placeholder="نام خانوادگی"/>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="phone" placeholder="شماره موبایل" />
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                        </date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                     name="endDate"></date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="type">
                            <option value="all">انتخاب نوع تراکنش</option>
                            <option value="0">
                               برداشت از کیف پول
                            </option>
                            <option value="1">
                               واریز به کیف پول
                            </option>
                            <option value="2">
                              واریز به حساب بانکی
                            </option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button id="do_filter"  class="btn btn-outline-primary">
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
                    <th>نام و نام خانوادگی</th>
                    <th> موبایل</th>
                    <th>مبلغ کل(ریال)</th>
                    <th>نوع تراکنش</th>
                    <th>تاریخ و ساعت ثبت</th>
                    <th>توضیحات</th>
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
            <input type="hidden" value="complete" id="type">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
{{--    <wallet-data :per_user="{{ $user->id }}" :per_key="{{ json_encode($key) }}"></wallet-data>--}}
@stop
@section('js')
    <script src="/assets/dashboard/js/employeeWalletTransaction.js?v=1.1"></script>
@endsection
