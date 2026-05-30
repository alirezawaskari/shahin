@extends('Admin.master')
@section('title'){{ $title }}@endsection

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <div>
            <h3>{{ $title }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group mt-2 mt-md-0" role="group">
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
    <form id="filter_form" action="/dashboard/factorItem/{{$url}}" method="get">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-6 col-lg-3 mb-2"><input type="text" name="name" placeholder="نام"
                            class="form-control"></div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2"><input type="text" name="family" placeholder="نام خانوادگی"
                            class="form-control"></div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2"><input type="text" name="phone" placeholder="موبایل"
                            class="form-control"></div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2"><input type="text" name="service_tariff"
                            placeholder="نام تعرفه (مثال : ایمپلنت)" class="form-control"></div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <select name="has_session" id="has_session" class="custom-select">
                            <option value="all">انتخاب وضعیت جلسه </option>
                            <option value="1">دارد</option>
                            <option value="0">ندارد</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                        </date-picker>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate">
                        </date-picker>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <date-picker placeholder="تاریخ مراجعه" locale="fa,en" format="YYYY-M-D" name="date">
                        </date-picker>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <select name="status[]" id="select_status" class="custom-select" multiple="multiple">
                            <option value="0">جدید</option>
                            <option value="16">ارجاع به کارشناسی</option>
                            <option value="17">منتظر مشاوره مرکز</option>
                            <option value="4">منتظر تکمیل مدارک</option>
                            <option value="14">منتظر تایید پشتیبان</option>
                            <option value="3">تعلیق پشتیبان</option>
                            <option value="2"> مشاوره شد</option>
                            <option value="9">منتظر تایید امور تسهیلات</option>
                            <option value="5">منتظرتایید مالی</option>
                            <option value="13">منتظر تایید امور قراردادها</option>
                            <option value="8">منتظر تایید مدیر</option>
                            <option value="10">آماده اجرا</option>
                            <option value="6">شروع کار</option>
                            <option value="7">اتمام کار</option>
                            <option value="15">پرونده مختومه</option>
                            <option value="11">منتظرتایید مالی نمایندگی</option>
                            <option value="18">منتظر تایید قرارداد توسط کاربر</option>
                            <option value="20">منتظر تایید فاکتور توسط کاربر</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <select name="type" class="custom-select" id="type">
                            <option value="all">انتخاب نوع پرداخت</option>
                            <option value="0">نقد</option>
                            <option value="1">اقساط</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <select name="purchase_type" class="custom-select" id="purchase_type">
                            <option value="all">انتخاب نوع خرید</option>
                            <option value="0">آنلاین</option>
                            <option value="1">حضوری</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2"><select name="user" class="custom-select select2">
                            <option value="all">انتخاب پشتیبان</option>
                            @foreach($callcenters as $item)
                            <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2"><select name="center" class="custom-select select2">
                            <option value="all">انتخاب مرکز</option>
                            @foreach($centers as $center)
                            <option value="{{$center->id}}">{{$center->title}}</option>
                            @endforeach
                        </select></div>

                    @if(!auth()->user()->admin_representation)
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <select name="representation" class="custom-select select2">
                            <option value="all">انتخاب نمایندگی</option>
                            <option value="center_representation">نمایندگی کل</option>
                            @foreach($representations as $representation)
                            <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-12 col-sm-6 col-lg-3 mb-2"><select name="organization" class="custom-select select2
">
                            <option value="all">انتخاب سازمان</option>
                            @foreach($organizations as $organization)
                            <option value="{{$organization->id}}">{{$organization->fa_name}}</option>
                            @endforeach
                        </select></div>
                    @include('Admin.filter.productServiceFilterInputs')
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <select class="form-control" name="has_payment">
                            <option value="all">انتخاب پرداختی پرونده</option>
                            <option value="true">دارد</option>
                            <option value="false">ندارد</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                            <label class="custom-control-label" for="exel">خروجی اکسل</label>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-3 mb-2">
                        <button type="button" class="btn btn-outline-primary btn-block py-2" id="do_filter">فیلتر کن</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form id="transfer_form">
        <div class="card">
            <div class="card-body">
                <div class="row transfer_message_box">
                </div>
                <div class="row">
                    <div class="col-12 col-sm-6 col-lg-2 mb-2">
                        <select id="transfer" name="type" class="form-control">
                            <option value="select">بر حسب انتخاب</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2 mb-2">
                        <select class="custom-select select2" name="transfer_type" id="transfer_type">
                            <option value=" ">ارجاع به</option>
                                @permission('factor_item_referral_to_representation')
                                <option value="representation">نمایندگی</option>
                                @endpermission
                                @permission('factor_item_referral_to_supporter')
                                <option value="supporter">پشتیبان</option>
                                @endpermission
                                @permission('factor_item_referral_to_center')
                                <option value="center">مرکز</option>
                                @endpermission
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2 mb-2 d-none" id="representation_list_box">
                        <select class="custom-select select2" name="selected_representation">
                            <option value="center_representation">نمایندگی کل</option>
                            @foreach($representations as $representation)
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2 mb-2 d-none" id="supporter_list_box">
                        <select class="custom-select select2" name="selected_supporter">
                            <option value=" ">انتخاب پشتیبان</option>
                            @foreach($supporter_users as $supporter_user)
                                <option value="{{$supporter_user->id}}">{{$supporter_user->name.' '.$supporter_user->family}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2 mb-2 d-none" id="center_list_box">
                        <select class="custom-select select2" name="selected_center">
                            <option value=" ">انتخاب مرکز</option>
                            @foreach($centers as $center)
                                <option value="{{$center->id}}">{{$center->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-2 mb-2">
                        <button id="do_transfer" type="button" class="btn btn-outline-primary btn-block py-2">انتقال</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card position-relative">
            <div class="card-body ">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="datatable-json">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><input type="checkbox" id="transfer_checkbox"></th>
                            <th>نام</th>
                            <th>موبایل</th>
                            <th>تاریخ ثبت</th>
                            <th>تاریخ مراجعه</th>
                            <th>پشتیبان - شماره پرونده</th>
                            <th>نوع پرداخت - مبلغ</th>
                            <th>خدمات</th>
							<th>نمایندگی</th>
                            <th>وضعیت اجرایی</th>
                            <th>پرداختی</th>
                            <th>اعتبار (ریال)</th>
                            <th>اقدام</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
</div>

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
                <div id="pagination-box">
                    <ul id="pagination-demo" class="pagination"></ul>
                </div>
            </div>
        </div>
    </form>

@include('Admin.callcenter.sendLink')

<div class="modal" id="sendLinkModal">
    <div class="modal-dialog modal-dialog modal-lg modal-dialog-centered">
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
<script src="{{asset('assets/dashboard/js/factorItem.js?v=1.3')}}"></script>
<script src="{{asset('assets/dashboard/js/sendLink.js')}}"></script>
<script src="{{asset('assets/dashboard/js/layout/productServiceFilterInputs.js')}}"></script>
<script>
    const csrfToken = '{{ csrf_token() }}';
</script>
@endsection

