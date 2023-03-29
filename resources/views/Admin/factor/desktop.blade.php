@extends('Admin.master')

@section('content')
    <div class="page-header">
        <div>
            <h3>میزکار تراکنش ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">میزکار تراکنش ها</li>
                </ol>
            </nav>
        </div>
    </div>
    <form id="filter_form" method="get" action="/dashboard/factor-desktop">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2"><input type="text" name="name" placeholder="نام"
                                                             class="form-control"></div>
                    <div class="col-12 col-md-3 mb-2"><input type="text" name="family" placeholder="نام خانوادگی"
                                                             class="form-control"></div>
                    <div class="col-12 col-md-3 mb-2"><input type="text" name="phone" placeholder="موبایل"
                                                             class="form-control"></div>
                    <div class="col-12 col-md-3 mb-2"><input type="text" name="resnumber" placeholder="شناسه پرداخت"
                                                             class="form-control"></div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                        </date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                     name="endDate"></date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="status" class="custom-select">
                            <option value="all">انتخاب وضعیت تراکنش</option>
                            <option value="1">پرداخت شده</option>
                            <option value="2">عودت پرداخت</option>
                            <option value="0">در انتظار پرداخت</option>
                            <option value="3">منقضی شده</option>
                            <option value="5">واریز شده</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="title" class="custom-select" id="title">
                            <option value="all">انتخاب موضوع دریافت وجه</option>
                            <option value="register">هزینه ثبت نام</option>
                            <option value="installment">قسط</option>
                            <option value="prepayment">پیش دریافت اقساط</option>
                            <option value="extra_pay">جریمه</option>
                            <option value="price_difference">اختلاف حساب</option>
                            <option value="factor">فروش نقد</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="title_pay" class="custom-select" id="title-pay">
                            <option value="all">انتخاب موضوع پرداخت وجه</option>
                            <option value="employee_payment">حقوق ودستمزد</option>
                            <option value="prepayment">عودت پیش دریافت اقساط</option>
                            <option value="factor">عودت فروش نقد</option>
                            <option value="price_difference">اختلاف حساب</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="type" class="custom-select">
                            <option value="all">انتخاب نوع دریافت وجه</option>
                            <option value="online">آنلاین</option>
                            <option value="card">کارت به کارت</option>
                            <option value="card_reader">کارتخوان</option>
                            <option value="cash">پول نقد</option>
                            <option value="wallet">کسر از کیف پول</option>
                            <option value="credit">کسر از حواله</option>
                            <option value="overpayment">کسر از اضافه پرداخت</option>
                            <option value="deduction_price_difference">کسر از بستانکاری</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="type_pay" class="custom-select">
                            <option value="all">انتخاب نوع پرداخت وجه</option>
                            <option value="card">کارت به کارت</option>
                            <option value="account">واریز به حساب</option>
                            <option value="cash">پول نقد</option>
                            <option value="increase_credit">افزایش اعتبار</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="user" class="custom-select">
                            <option value="all">انتخاب فروشنده</option>
                            @foreach($callcenters as $item)
                                <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="old_callcenter" class="custom-select">
                            <option value="all">انتخاب فروشنده غیرفعال</option>
                            @foreach($old_callcenters as $item)
                                <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="representation" class="custom-select">
                            <option value="all">انتخاب نمایندگی</option>
                            <option value="center">نمایندگی مرکزی تهران</option>
                            @foreach($representations as $item)
                                <option value="{{$item->id}}">{{$item->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="organization" class="custom-select">
                            <option value="all">انتخاب سازمان</option>
                            @foreach($organizations as $item)
                                <option value="{{$item->id}}">{{$item->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    {{--                    -----------------------}}
                    @include('Admin.filter.productServiceFilterInputs')
                    {{--                    -----------------------}}
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
            </div>
        </div>
    </form>
    <form id="form-factor-action">
        <div class="card position-relative">

            <div class="card-body table-responsive">
                <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>موبایل</th>
                        <th>فروشنده</th>
                        <th>مبلغ</th>
                        <th>عاملیت</th>
                        <th>موضوع</th>
                        <th>نوع پرداخت</th>
                        <th>تاریخ</th>
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
                <input type="hidden" value="filter" id="url">
                <input type="hidden" value="complete" id="type">
                <div id="pagination-box">
                    <ul id="pagination-demo" class="pagination"></ul>
                </div>
            </div>

        </div>
    </form>
    <!-- Modal -->
    <div class="modal fade bd-example-modal-lg" id="factorShowModal" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body modal-body-factor-show">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
                </div>
            </div>
        </div>
    </div>
{{--    <factor-data :per_detail="{{ json_encode($detail) }}" :per_desktop="{{ json_encode($desktop) }}"></factor-data>--}}
@stop
@section('js')
    <script src="/assets/dashboard/js/layouts.js"></script>
    <script src="/assets/dashboard/js/factor.js"></script>
    <script src="/assets/dashboard/js/layout/productServiceFilterInputs.js"></script>
@endsection
