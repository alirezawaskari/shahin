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
            @permission('callcenter-create')
            <a data-toggle="modal" href="#sendLinkModal" class="dropdown-item fast-service">ثبت خدمات سریع</a>
            @endpermission
            @permission('get-spread-sms')
            <a class="dropdown-item" href="/get-sms">گرفتن پیامک</a>
            <a class="dropdown-item" href="/spread-sms">پخش پیامک</a>
            @endpermission
        </div>
    </div>
</div>
@csrf
<form id="filter_form" action="/dashboard/callcenter/{{$url}}" method="get">
    <div class="card">
        <div class="card-body">
            <div class="row">
                @include('Admin.callcenter.partials.filter-fields')
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                        <label class="custom-control-label" for="exel">خروجی اکسل</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
                    @if($url === 'filter')
                        @include('Admin.callcenter.partials.sms-to-filter-button')
                    @endif
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
            <div class="row"
                 data-business-transfer-scope
                 data-transfer-entity="callcenter"
                 data-record-selector=".callcenter-business-transfer-record">
                <div class="col-12 col-md-2 mb-2">
                    <select id="transfer" name="type" class="form-control">
                        <option value="default">پیش فرض</option>
                        <option value="date">بر حسب تاریخ</option>
                        <option value="select">بر حسب انتخاب</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <select class="custom-select select2" id="transfer_type" data-business-transfer-type-select>
                        <option value=" ">ارجاع به</option>
                        <option value="callcenter">کالسنتر</option>
                        @permission('transfer_to_business')
                        <option value="business">کسب و کار</option>
                        @endpermission
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="destination_box">
                    <select class="custom-select select2" name="startUser">
                        <option value="all"> کالسنتر مبدا</option>
                        @foreach($callcenters as $item)
                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="source_box">
                    <select class="custom-select select2" name="endUser">
                        <option value="all"> کالسنتر مقصد</option>
                        @foreach($callcenters as $item)
                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="transfer_start_date_box">
                    <date-picker placeholder="از تاریخ" class="transfer_start_date" locale="fa,en" format="YYYY-M-D"
                        name="startDate">
                    </date-picker>
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="transfer_end_date_box">
                    <date-picker class="transfer_end_date" placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                        name="endDate"></date-picker>
                </div>

                <div class="col-12 col-md-2 mb-2 d-none" id="count_box"><input type="text" placeholder="تعداد" name="count"
                        class="form-control"></div>
                @include('Admin.businessTransfer.destinationBox', ['boxClass' => 'col-12 col-md-2 mb-2'])
                <div class="col-12 col-md-2 mb-2">
                    <button id="do_transfer" type="button" class="btn btn-outline-primary py-2 px-4">انتقال</button>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <button class="btn btn-danger" id="multi_delete">
                        حذف
                        (<span id="checkboxCount"></span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body table-responsive">
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><input type="checkbox" id="transfer_checkbox" class="business-transfer-select-all"></th>
                        <th>نام</th>
                        <th>استان</th>
                        <th>شهر</th>
                        <th>موبایل</th>
                        <th>متن پیامک</th>
                        <th>کالسنتر</th>
                        <th>نمایندگی</th>
                        <th>نوع درخواست</th>
                        <th>موضوع درخواست</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
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
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
</form>
<!-- change status modal -->
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div class="modal-title">
                    <div class="container">
                        <div class="row font-weight-bold" id="exampleModalLabel">
                        </div>
                        <hr>
                    </div>
                </div>
                <ul id="myTab" role="tablist" class="nav nav-tabs">
                    <li class="nav-item"><a id="status-tab" data-toggle="tab" href="#status" role="tab"
                            aria-controls="status" aria-selected="true" class="nav-link show active">گزارش تماس</a>
                    </li>
                    <li class="nav-item"><a id="send-link-factor-tab" data-toggle="tab" href="#sendLinkFactor"
                            role="tab" aria-controls="sendLinkFactor" aria-selected="false" class="nav-link show">ارسال
                            فاکتور</a>
                    </li>

                    <li class="nav-item"><a id="transfers-tab" data-toggle="tab" href="#transfers" role="tab"
                            aria-controls="transfers" aria-selected="false" class="nav-link show">انتقال</a>
                    </li>
                    <li class="nav-item"><a id="detail-tab" data-toggle="tab" href="#detail" role="tab"
                            aria-controls="detail" aria-selected="false" class="nav-link show">جزئیات</a>
                    </li>
                    @permission('close-task|followed-task')
                    <li class="nav-item"><a id="change-status-task-tab" data-toggle="tab" href="#changeStatusTask" role="tab"
                            aria-controls="colseTask" aria-selected="false" class="nav-link show">تغییر وضعیت درخواست
                            </a>
                    </li>
                    @endpermission
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div id="status" role="tabpanel" aria-labelledby="status-tab"
                        class="tab-pane fade p-t-20 active show">
                        <div class="tab">
                            <div class="row">
                                <button class="tablinks col-12 col-md-6" onclick="openTab(event, 'noCall')">ثبت
                                    خودکار</button>

                                <button id="defaultOpen" class="tablinks col-12 col-md-6"
                                    onclick="openTab(event, 'callDetails')">ثبت توضیح دستی</button>
                            </div>
                        </div>
                        <div id="noCall" class="tabcontent">
                            <form id="modal-status-form-noCall">
                                <div class="row">
                                    <div class="modal_status_message_box col-12 col-md-12 "></div>
                                    <div class="col-12 mb-3">
                                        <select id="reason-remember" name="reason" class="form-control">
                                            <option value="6"> عدم ارتباط ( پاسخ ندادن - خاموش
                                                بودن - دردسترس نبودن - مشغول بودن )</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3"><textarea id="remember-description" cols="30" rows="5"
                                            placeholder="توضیح مختصر" name="description"
                                            class="form-control"></textarea>
                                    </div>
                                    <input type="hidden" class="task" name="task" value="">
                                    <div class="col-12 mb-3">
                                        <input value="ثبت" class="btn btn-success border-0 btn-block"
                                            id="submit-status-noCall">
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div id="callDetails" class="tabcontent">
                            <form id="modal-status-form">
                                <div class="row">
                                    <div class="modal_status_message_box col-12 col-md-12 "></div>
                                    <div class="col-12 mb-3"><textarea id="remember-description" cols="30" rows="5"
                                            placeholder="توضیح مختصر" name="description"
                                            class="form-control"></textarea>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3"><label>انتخاب تاریخ یادآوری</label>
                                        <date-picker placeholder="انتخاب تاریخ" locale="fa,en" format="YYYY-M-D HH:mm"
                                            name="date" type="datetime">
                                        </date-picker>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <label>انتقال به همکار (درصورت نیاز به انتقال درخواست ، همکار مورد نظر را انتخاب
                                            کنید.)</label>
                                        <select name="callcenter" class="form-control">
                                            <option value="">انتخاب همکار</option>
                                            @foreach($callcenters as $item)
                                            <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="reason" value="10">
                                <input type="hidden" class="task" name="task" value="">
                                <label> ثروتمند/توانمند </label>
                                <input type="checkbox" name="vip" id="custom-check-vip" class="mr-2 ml-2"> <label>
                                    فقیر/کم توان </label>
                                <input type="checkbox" name="sos" id="custom-check-sos" class="mr-2">
                                <input value="ثبت" class="btn btn-success border-0 btn-block" id="submit-status">
                            </form>
                        </div>
                    </div>
                    <div id="sendLinkFactor" role="tabpanel" aria-labelledby="send-link-factor-tab"
                        class="tab-pane fade p-t-20">
                        @include('Admin.callcenter.sendLink')
                    </div>
                    <div id="transfers" role="tabpanel" aria-labelledby="transfers-tab" class="tab-pane fade p-t-20">
                        <form id="modal-transfer-form">
                            <div class="row">
                                <div class="modal_transfer_message_box col-12 col-md-12 mb-12"></div>
                                <div class="col-12 mb-3"><label>انتخاب همکار</label>
                                    <select name="callcenter" class="form-control" id="transfer_callcenter">
                                        <option value="">انتخاب همکار</option>
                                        @foreach($callcenters as $item)
                                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" class="task" name="task" value="">
                                <input value="ثبت" id="submit-transfer" class="btn btn-success border-0 btn-block">
                            </div>
                        </form>
                    </div>
                    <div id="detail" role="tabpanel" aria-labelledby="detail-tab" class="tab-pane fade p-t-20">
                        <p class="mb-3 text-dark">تاریخچه</p>
                        <table class="table table-bordered table-striped table-responsive-md mb-5">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">آپدیت کننده</th>
                                    <th scope="col">پیگیری کننده</th>
                                    <th scope="col">تاریخ</th>
                                    <th scope="col">توضیحات</th>
                                </tr>
                            </thead>
                            <tbody class="history-tbody">
                                <tr>
                                    <th>حدیثه صفری</th>
                                    <td>حدیثه صفری</td>
                                    <td>29 دی ، 1400 - 11:57</td>
                                    <td>لینک ارسال گردید و در انتظار پرداخت قرار دارد</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="mb-3 text-dark">فاکتورها</p>
                        <table class="table table-bordered table-striped table-responsive-md mb-5">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">شماره</th>
                                    <th scope="col">مبلغ</th>
                                    <th scope="col">نوع</th>
                                    <th scope="col">نمایندگی</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">تاریخ</th>
                                    <th scope="col">آیتم ها</th>
                                </tr>
                            </thead>
                            <tbody class="factors-tbody">
                                <tr>
                                    <th>3624072995</th>
                                    <td>1,180,000 ریال</td>
                                    <td>پرداخت آنلاین</td>
                                    <td>مستاجرین و شرکای تجاریی تهران</td>
                                    <td>منقضی شده</td>
                                    <td>24 آذر ، 1400 - 11:29</td>
                                    <td>[
                                        "مد و پوشاک",
                                        "مد و پوشاک"
                                        ]
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div id="changeStatusTask" role="tabpanel" aria-labelledby="change-status-task-tab" class="tab-pane fade p-t-20">
                        <div class="tab">
                            <div class="row">
                                @permission('close-task')
                                <button class="tablinks col-12 col-md-6" onclick="openTab(event, 'closeTask')">بستن درخواست</button>
                                @endpermission
                                @permission('followed-task')
                                <button id="changeStatusTaskDefaultOpen" class="tablinks col-12 col-md-6"
                                    onclick="openTab(event, 'followedTask')">اقدام شده</button>
                                @endpermission
                            </div>
                        </div>
                        @permission('close-task')
                        <div id="closeTask" class="tabcontent">
                            <form id="modal-close-task-form">
                                <div class="row">
                                    <div class="modal_close_task_message_box col-12 col-md-12 "></div>
                                    <div class="col-12 mb-3"><textarea id="close-task-description" cols="30" rows="5"
                                            placeholder="دلیل بستن درخواست" name="description"
                                            class="form-control"></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input style="cursor: pointer" type="checkbox" name="sms_close_task"
                                                class="form-check-input" id="sms-close-task">
                                            <label style="cursor: pointer" class="form-check-label"
                                                for="sms-close-task">پیامک
                                                بسته شدن
                                                درخواست و محرومیت 15 روزه از خدمات لاله به کابر ارسال گردد.</label>
                                        </div>
                                        <hr>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="close_task_type"
                                                id="flexRadioDefault1" value="this_task" checked>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                                بستن همین درخواست
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="close_task_type"
                                                id="flexRadioDefault2" value="all_user_task">
                                            <label class="form-check-label" for="flexRadioDefault2">
                                                بستن تمام درخواست های باز این کاربر
                                            </label>
                                        </div>
                                        <p class="text-warning font-weight-bolder mt-4"><i
                                                class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                                            همکار گرامی : درصورتی که میخواهید تمام درخواست های باز این کاربر بسته شود
                                            گزینه مربوطه را انتخاب کنید.
                                        </p>
                                    </div>
                                    <input type="hidden" class="task" name="task" value="">
                                    <input value="ثبت" class="btn btn-success border-0 btn-block"
                                        id="submit-close-task">
                                </div>
                            </form>
                        </div>
                        @endpermission
                        @permission('followed-task')
                        <div id="followedTask" class="tabcontent">
                            <form id="modal-followed-task-form">
                                <div class="row">
                                    <div class="modal_followed_task_message_box col-12 col-md-12 "></div>
                                    <div class="col-12 mb-3"><textarea id="followed-task-description" cols="30" rows="5"
                                            placeholder="توضیحات مربوط به اقدام انجام شده" name="description"
                                            class="form-control"></textarea>
                                    </div>
                                    <input type="hidden" class="task" name="task" value="">
                                    <input value="ثبت" class="btn btn-success border-0 btn-block"
                                        id="submit-followed-task">
                                </div>
                            </form>
                        </div>
                        @endpermission
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- send link modal -->
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
<!-- show history modal -->
<div class="modal" id="historyModal" data-backdrop="static" class="mt-5">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div class="text-center">
                    <h2 class="modal-title ">
                        <i class="fa fa-history" aria-hidden="true"></i>
                        تاریخچه کاربر
                    </h2>
                </div>
                <button type="button" class="closeHistoryBtn close" data-dismiss="modal" aria-hidden="true">×
                </button>
            </div>

            <ul class="nav nav-tabs" id="myTab" role="tablist">

                <li class="nav-item  flex-fill text-center">
                    <a class="nav-link active" href="#factor-items" data-toggle="tab" role="tab"
                        aria-controls="factor-items" aria-selected="true">
                        <h5>
                            <i class="fa fa-credit-card-alt" aria-hidden="true"></i>
                            فاکتورها
                        </h5>
                    </a>
                </li>
                <li class="nav-item  flex-fill text-center">
                    <a class="nav-link" href="#description-items" data-toggle="tab" role="tab"
                        aria-controls="description-items" aria-selected="true">
                        <h5>
                            <i class="fa fa-list" aria-hidden="true"></i>
                            توضیحات
                        </h5>
                    </a>
                </li>

            </ul>

            <div class="modal-body">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane" id="description-items" role="tabpanel" aria-labelledby="firstData-tab">
                        <div class="col-12 mb-4">
                            <table class="vuetable table table-bordered table-striped table-responsive-md w-100"
                                id="datatable-history">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>کالسنتر</th>
                                        <th>ثبت کننده</th>
                                        <th>تاریخ</th>
                                        <th>توضیحات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card tab-pane fade show active " id="factor-items" role="tabpanel"
                        aria-labelledby="firstData-tab">
                        <div class="card-body">
                            <div class="modal-body">
                                <div class="col-12 mb-4">
                                    <table class="vuetable table table-bordered table-striped table-responsive-md w-100"
                                        id="data-table-factors">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>مبلغ</th>
                                                <th>نوع</th>
                                                <th>نمایندگی</th>
                                                <th>وضعیت</th>
                                                <th>تاریخ</th>
                                                <th>ایجاد کننده</th>
                                                <th>آیتم ها</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" data-dismiss="modal" class="closeHistoryBtn btn btn-danger">بستن</a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Main list only: the other lists (desktop / paid / suspended / opt-outs) carry extra base
     conditions the SMS audience does not replicate, so "the filter result" would not match. --}}
@if($url === 'filter')
    @include('Admin.callcenter.partials.sms-to-filter-modal')
@endif
@stop
@section('js')
<script>
    var csrfToken = '{{ csrf_token() }}';
</script>
<script src="{{asset('assets/dashboard/js/layouts.js?v=1.0')}}"></script>
<script src="{{asset('assets/dashboard/js/callcenter.js?v=2.0')}}"></script>
<script src="{{asset('assets/dashboard/js/sendLink.js?v=1.9')}}"></script>
<script src="{{asset('assets/dashboard/js/layout/productServiceFilterInputs.js')}}"></script>
<script src="{{asset('assets/dashboard/js/requestFilterSms.js?v=1.0')}}"></script>
@endsection
