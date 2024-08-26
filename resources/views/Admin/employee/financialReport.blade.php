@extends('Admin.master')
@section('title')عملکرد ماهانه {{ $user->family }}@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>عملکرد ماهانه</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">عملکرد ماهانه</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            <a href="{{ route('employee.wallet', [$user->id , 'key' => 'all']) }}"
                class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>جزئیات گزارش مالی</span>
            </a>
            @permission('user_performance_detail')
            <a href="{{ route('employee.performanceDetail', [$user->id]) }}"
                class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>کلیات گزارش مالی</span>
            </a>
            @endpermission
            @if ($user->id == auth()->user()->id)
            <a href="#" class="btn btn-link btn-float has-text text-size-small rtl" data-toggle="modal"
                data-target="#requestModal">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>واریز به حساب</span>
            </a>
            @endif
        </div>
    </div>
</div>
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ثبت درخواست واریز به حساب</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('ticket.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <textarea name="content" placeholder="لطفا درخواست خود را همراه مبلغ مورد نظر وارد کنید"
                        class="form-control"></textarea>
                    <input type="hidden" name="title" value="درخواست واریز وجه از کیف پول به حساب">
                    <input type="hidden" name="card_id" value="2">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>
<wallets-suspension-data></wallets-suspension-data>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-5">
                <h5> نام و نام خانوادگی : {{ $user->name.' '.$user->family }} </h5>
                <h5> عملکرد کل : {{ number_format($item['total_income']) }} ریال (بدون کسر مالیات)</h5>
                <h5> مبلغ دریافت شده : {{ number_format($item['amount_received']) }} ریال</h5>
                <h5> {{$item['credit_amount'] >= 0 ? 'مبلغ بستانکاری : '.number_format($item['credit_amount']).'ریال (بدون کسر مالیات)' :
                    'مبلغ اضافه دریافت بدون کسر مالیات : '.number_format($item['credit_amount']*(-1)).'ریال'}}</h5>
                <h5> مبلغ مالیات : {{ number_format($item['total_tax']) }} ریال</h5>
                <h5> مبلغ قابل دریافت : {{ $item['amount_payable'] >= 0 ? number_format($item['amount_payable']) : 0 }}
                    ریال</h5>
            </div>
            <div class="col-12 col-md-5">
                <h5> تاریخ شروع همکاری : {{ verta($employee->contract_start_date)->format('Y/m/d') }}</h5>
                <h5> تاریخ انقضاء قرارداد : {{ verta($employee->contract_expiration_date)->format('Y/m/d') }}</h5>
                <h5> درصد مالیات : {{ $employee->tax ? $employee->tax.'درصد' : 'ندارد (توسط شرکت پرداخت می شود.)'}}
                </h5>
                <h5> درصد حسن انجام کار : {{ $employee->blocked_percentage ? $employee->blocked_percentage.'درصد' :
                    'ندارد'}}</h5>
                <h5> سپرده مقصد (شماره شبا) : {{ $item['shaba_number'] }}</h5>
            </div>
            <div class="col-12 col-md-2 float-left">
                <img src="{{ $user->avatar }}" width="85%">
            </div>
        </div>
    </div>
</div>
<div class="card position-relative">
    <div class="card-body">
        <h5>گزارش مالی ماهانه </h5>
        <hr>
        <table class="table table-bordered table-striped" id="table-id">
            <tr>
                <th>سال</th>
                <th>ماه</th>
                <th>تعداد پرونده انجام شده</th>
                <th>مبلغ عملکرد(ریال)</th>
                <th>مبلغ مالیات(ریال)</th>
                <th>مبلغ دریافت شده(ریال)</th>
                <th>باقی مانده | اضافه دریافت (ریال)</th>
                <th>اقدام</th>
            </tr>
            @foreach ($items as $key => $item)
            <tr>
                <td>{{ $item['year'] }}</td>
                <td>{{ $item['month'] }}</td>
                <td>{{ $item['item_count'] }}</td>
                <td>{{ number_format($item['center_income']) }}</td>
                <td>{{ number_format($item['center_income'] * 0.1) }}</td>
                <td>{{ number_format($item['amount_received']) }}</td>
                <td>{{ number_format($item['center_income'] - $item['amount_received'] - $item['user_tax']) }}</td>
                <td>
                    <div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a
                                href="{{ route('employee.wallet', [ $user->id , 'key' => $item['created_at']]) }}"><button
                                    type="button" class="dropdown-item pointer">جزئیات</button></a>
                            @permission('user_performance_detail')
                            <form method="get" action="{{ route('employee.performanceDetail', [ $user->id]) }}">
                                <input type="hidden" name="key" value="{{ $item['created_at'] }}">
                                <button type="submit" id="filter" class="dropdown-item pointer">کلیات</button>
                            </form>
                            @endpermission
                            @permission('employee-detail')
                            <a data-toggle="modal" data-target="#paymentModal">
                                <input type="button" class="dropdown-item pointer payment"
                                    id="{{ $item['created_at'] }}" value="واریز وجه">
                            </a>
                            @endpermission
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        @include('Admin.layouts.pagination')
    </div>
</div>
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ثبت پرداختی جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('WalletTransaction.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="employee_id" value="{{ $user->employee->id }}">
                <input type="hidden" name="month" id="month">
                <div class="col-12 mt-5 mb-4">
                    <div class="col-12 mb-2">
                        <label>مبلغ واریزی</label>
                        <input class="form-control" value="0" name="price" type="text" oninput="separatePrices(this)">
                    </div>
                    <div class="col-12 mb-2">
                        <label>نحوه پرداخت</label>
                        <select class="form-control" name="payment_type" id="payment_type1" onchange="paymentType(1)">
                            <option value="card">کارت به کارت</option>
                            <option value="account">واریز به حساب</option>
                            <option value="cash">پول نقد</option>
                        </select>
                    </div>
                    <div class="col-12 mb-2" id="payment_date">
                        <label>تاریخ پرداخت</label>
                        <date-picker value="{{ now() }}" format="YYYY/M/D HH:mm"
                                name="payment_date" type="datetime" display-format="jYYYY/jMM/jDD HH:mm">
                        </date-picker>
                    </div>
                    <div class="col-12 mb-2" id="number_card1">
                        <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                        <input class="form-control" name="number_card" type="number" id="number_card_input1">
                    </div>
                    <div class="col-12 mb-2" id="payment_code1">
                        <label>شماره پیگیری (4 رقم آخر)</label>
                        <input class="form-control" name="payment_code" type="number" id="payment_code_input1">
                    </div>
                    <div class="col-12 mb-2 d-none" id="sheba_number1">
                        <label>شماره شبا</label>
                        <input class="form-control" name="sheba_number" type="text" id="sheba_number_input1">
                    </div>
                    <div class="col-12 col-md-3 mb-2" id="bill_image1">
                        <label>رسید دریافت وجه</label>
                        <br>
                        <label for="bill_image_input1" id="up-label" class="btn badge-secondary"><i
                                class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                        <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image" type="file"
                            id="bill_image_input1">
                        <div id="bill_image_input1_src" class="show"></div>
                        <input type="hidden" id="bill_image_input1" class="path" name="url_bill_image" value="">
                    </div>

                    <div class="col-12 mb-2 mt-4">
                        <input type="submit" class="btn btn-success mb-3 submit_btn" value="ثبت فاکتور">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script>
    $(".payment").click(function () {
        $('#month').val(this.id);
    });
</script>
@endsection
