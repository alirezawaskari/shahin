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
            <form role="form" id="filter_form" action="/dashboard/session/{{$url}}" method="get">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="name" placeholder="نام" />
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="family" placeholder="نام خانوادگی" />
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="phone" placeholder="شماره موبایل" />
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date">
                        </date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="end_date"></date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control select2" id="employee" name="employee">
                            <option value="all">انتخاب همکار</option>
                            @foreach($admin_users as $item)
                                <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control select2" name="center">
                            <option value="all">انتخاب مرکز</option>
                            @foreach($centers as $center)
                                <option value="{{$center->id}}">{{$center->title}}</option>
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
                        <button id="do_filter" class="btn btn-primary">
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
                    <th>شماره موبایل</th>
                    <th>خدمت - مسئول</th>
                    <th>
                        آخرین توضیح ثبت شده
                        <span class="alert text-warning">
                            (
                            <i class="fa fa-question-circle" aria-hidden="true"></i>

                            جهت مشاهده تمام گزارش های ثبت شده برای کاربر بر روی آیکون
                            <i class="fa fa-list text-dark" aria-hidden="true"></i> کلیک نمایید .
                            )
                    </span></th>
                    <th>حضور کاربر</th>
                    <th>ثبت کننده</th>
                    <th>تایید کننده</th>
                    <th>تاریخ و ساعت</th>
                    <th>حضور</th>
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
            <input type="hidden" value="{{ $delete }}" id="delete">
            <input type="hidden" value="{{ $type }}" id="type">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="container mt-2 modal-content card">
                <div class="modal-header card-header">
                    <h5 class="modal-title " id="exampleModalLongTitle">توضیحات ثبت شده برای کاربر :  <span class="text-dark user-fullname"></span> </h5>
                </div>
                <div class="modal-body table-responsive card-body width-auto" >
                    <table class="vuetable table table-bordered table-striped table-responsive-md w-100" id="datatable-history">
                        <thead>
                        <tr>
                            <th scope="col">کالسنتر</th>
                            <th scope="col">ثبت کننده</th>
                            <th scope="col">تاریخ ثبت</th>
                            <th scope="col">گزارشها</th>
                        </tr>
                        </thead>
                        <tbody class="histories-table-body">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">خروج</button>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="/assets/dashboard/js/layouts.js"></script>
    <script src="/assets/dashboard/js/session.js"></script>
@endsection
