@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست درصد سود تخصیص داده شده </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست درصد سود تخصیص داده شده </li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('percentageAllocationEmployee.create') }}">جدید</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="/dashboard/percentageAllocationEmployee/filter" method="get">
                <div class="row">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <select class="form-control" name="user">
                        <option value="all">همکاران</option>
                        @foreach($employees as $employee)
                            <option value="{{$employee->user->id}}">{{ $employee->user->name }} - {{ $employee->user->family }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select name="type" class="custom-select select2" id="type">
                        <option value="all">انتخاب نوع تسهیم</option>
                        @foreach(\App\Models\PercentageAllocationEmployee::TYPE as $key => $type)
                            <option value="{{ $key }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                    <div class="col-12 col-md-3 mb-2">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" name="excel" id="excel">
                            <label class="custom-control-label" for="excel">خروجی اکسل</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button id="do_filter" class="btn btn-outline-primary">
                            فیلتر کن
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card position-relative">
            <div class="card-body table-responsive" style="padding-bottom: 120px">
                <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>نام خانوادگی</th>
                        <th>موبایل</th>
                        <th>نوع تسهیم</th>
                        <th>خدمت | مرکز</th>
                        <th>درصد</th>
                        <th>تاریخ شروع</th>
                        <th>تاریخ پایان</th>
                        <th>ثبت کننده</th>
                        <th>تاریخ ثبت</th>
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

                <input type="hidden" value="{{$delete}}" id="delete">
                <div id="pagination-box">
                    <ul id="pagination-demo" class="pagination"></ul>
                </div>
            </div>
        </div>
 @stop

@section('js')
    <script src="/assets/dashboard/js/percentageAllowcationEmployee.js?v=1.0"></script>
@endsection
