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
    <div class="btn-group" role="group">
        <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            اقدامات
        </button>
        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" x-placement="bottom-start"
            style="position: absolute; will-change: transform; top: 0px; left: 0px; transform: translate3d(0px, 35px, 0px);">
            <a data-toggle="modal" href="#sendLinkModal" class="dropdown-item">تشکیل پرونده</a>
        </div>
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
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate"></date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select name="status[]" id="select_status" class="custom-select" multiple="multiple">
                        <option value="0">جدید</option>
                        <option value="4">منتظر تکمیل مدارک</option>
                        <option value="14">منتظر تایید پشتیبان</option>
                        <option value="3">تعلیق پشتیبان</option>
                        <option value="2"> مشاوره</option>
                        <option value="9">منتظر تایید امور تسهیلات</option>
                        <option value="5">منتظرتایید مالی</option>
                        <option value="13">منتظر تایید امور قراردادها</option>
                        <option value="8">منتظر تایید مدیر</option>
                        <option value="10">آماده اجرا</option>
                        <option value="6">شروع کار</option>
                        <option value="7">اتمام کار</option>
                        <option value="15">پرونده مختومه</option>
                        <option value="11">منتظرتایید مالی نمایندگی</option>
                        <option value="12">منتظر تایید مدیر نمایندگی</option>
                    </select>
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
                @if(!auth()->user()->admin_representation)
                <div class="col-12 col-md-3 mb-2">
                    <select name="representation" class="custom-select select2">
                        <option value="all">انتخاب نمایندگی</option>
                        @foreach($representations as $representation)
                        <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control select2" name="organization" id="organization_id">
                        <option value="all">انتخاب سازمان</option>

                    </select>
                </div>
                @include('Admin.filter.productServiceFilterInputs')

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="has_payment">
                        <option value="all">انتخاب پرداختی پرونده</option>
                        <option value="true">دارد</option>
                        <option value="false">ندارد</option>
                    </select>
                </div>

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
                    <th>جمع کل (ریال)</th>
                    <th>اعتبار (ریال)</th>
                    <th>خدمات | محصولات</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت اجرایی</th>
                    <th>وضعیت مالی</th>
                    <th>اقدام شده</th>
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

        <p id="page_text" style="display: initial"></p>
        <p id="sum_price" style="display: initial"></p>
        <input type="hidden" value="{{ $url }}" id="url">
        <input type="hidden" value="false" id="report">
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>

@include('Admin.callcenter.sendLink')

<div class="modal" id="sendLinkModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    <span>ثبت سریع کاربر</span>
                </h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="container"></div>
            <div class="modal-body bg-dark-bright">
                <div id="status" role="tabpanel" aria-labelledby="status-tab" class="tab-pane fade p-t-20 active show">
                    <div class="row">
                        <div class="col-12">
                            @include('Admin.callcenter.sendLink')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
<script src="{{asset('assets/dashboard/js/totalFactorItem.js')}}"></script>
<script src="{{asset('assets/dashboard/js/sendLink.js')}}"></script>
<script src="{{asset('assets/dashboard/js/layout/productServiceFilterInputs.js')}}"></script>
@endsection
