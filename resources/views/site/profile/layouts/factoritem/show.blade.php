@extends('site.master')

@section('content')
<div class="col-12 mt-4 mb-4">
    <div class="tab-content" id="nav-tabContent">
        <div class="container">
            <h5>کاربر گرامی</h5>
            <p>تمامی اطلاعات مربوط به پرونده شما در این صفحه قابل مشاهده می باشد چنانچه درخواست تسویه حساب دارید از
                قسمت تسویه حساب پیام خود را ارسال کنید در اسرع وقت پاسخگو شما هستیم.</p>
            <div id="accordion">
                @if($item['type'] == 1 && $totalFactorItem)
                @foreach ($items as $key => $factor_item)
                @if (!is_null($factor_item['centers']) || !is_null($factor_item['forms']))
                @foreach ($factor_item['factorItems'] as $factorItem)
                <div class="card">
                    <div class="card-header">
                        <a class="card-link" data-toggle="collapse" href="#collapseOne">
                            فاکتور
                        </a>
                    </div>
                    <div id="collapseOne" class="collapse show" data-parent="#accordion">
                        <div class="col-12">
                            @if(!is_null($item['centers']) || !is_null($item['forms']))
                            <div class="row p-3 position-relative">
                                @if(!is_null($item['forms']))
                                @foreach($item['forms'] as $form)
                                @if (in_array('site' , $form->data_display_level))
                                @include('Admin.factoritem.layouts.formItem')
                                @endif
                                @endforeach
                                @endif
                            </div>
                            @else
                            <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                <div>
                                    @if(!is_null($item['service_item']))
                                    <span>{{ $item['service']->title . '-' . $item['service_item']->title }}</span>
                                    @else
                                    <span>{{ $item['service']->title }}</span>
                                    @endif
                                </div>
                                <i class="fa fa-check font-18 text-success"></i>
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-12 col-md-3 mb-2">
                                    <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                                    <p class="form-control">{{number_format($item['total_price'])}} </p>
                                </div>
                                @if($item['type'] == 1)
                                <div class="col-12 col-md-3 mb-2">
                                    <label>تعداد اقساط</label>
                                    <p class="form-control">{{$item['installments_num']}}</p>
                                </div>
                                <div class="col-12 col-md-3 mb-2">
                                    <label>پیش پرداخت(ریال)</label>
                                    <p class="form-control">{{number_format($item['prepayment']['price'])}}</p>
                                </div>
                                <div class="col-12 col-md-4 mb-3">
                                    <label>نوع ضمانت</label>
                                    <br>
                                    چک <input type="radio" name="guarantee_type" value="check" {{
                                        $item['guarantee_type']=='check' ? 'checked' : '' }}>
                                    سفته <input type="radio" name="guarantee_type" value="promissorynote" {{
                                        $item['guarantee_type']=='promissorynote' ? 'checked' : '' }}>
                                </div>
                                @endif
                                <div class="col-12 col-md-12 mb-3">
                                    <label> نوع پرداخت</label>
                                    <br>
                                    نقد <input type="radio" name="type" value="0" {{ $item['type']=='0' ? 'checked' : ''
                                        }}>
                                    اقساط<input type="radio" name="type" value="1" {{ $item['type']=='1' ? 'checked'
                                        : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($item['service']->session)
                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-toggle="collapse" href="#sessions">
                            جلسات
                        </a>
                    </div>
                    <div id="sessions" class="collapse" data-parent="#accordion">
                        <div class="card-body">
                            <div class="card-body">
                                <div class="tab-content" id="myTabContent">
                                    @foreach($item['factorItems'] as $factoritem)
                                    <div class="col-12 col-md-12 mb-3">
                                        جلسات {{ $factoritem->factor->user->name.'
                                        '.$factoritem->factor->user->family}}
                                    </div>
                                    <div class="row col-md-12">
                                        <table class="table table-bordered table-striped table-responsive-md"
                                            style="font-family: font;">
                                            <tr>
                                                <th>تاریخ و ساعت</th>
                                                <th>شماره دندان</th>
                                                <th>توضیحات</th>
                                                <th>پزشک</th>
                                                <th>دستیار</th>
                                                <th>مبلغ (ریال)</th>
                                            </tr>
                                            @foreach ($factoritem->sessions->where('status', '!=', false) as $session)
                                            <tr>
                                                <td>{{ verta($session->date)->format('%d %B ، %Y - H:i:s') }}
                                                </td>
                                                <td>
                                                    @foreach ($item['service_tariffs'] as $service_tariff)
                                                    {{ $session->factor_item_service_tariff_id ==
                                                    $service_tariff->pivot->id
                                                    ? $service_tariff->pivot->value : '' }}
                                                    @endforeach
                                                </td>
                                                <td>{{ $session->description }}</td>
                                                <td>
                                                    @if (isset($session->agent))
                                                    {{ $session->agent->name }}
                                                    {{ $session->agent->family }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (isset($session->assistant))
                                                    {{ $session->assistant->name }}
                                                    {{ $session->assistant->family }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ number_format($session->price) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                        <div class="col-12 mb-2">
                                            <div class="btn badge-success mb-3 p-2">
                                                جمع مبلغ جلسات
                                                :
                                                {{ number_format($factoritem->sessions->where('status' , '!=' ,
                                                false)->sum('price')) }}
                                                ریال
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endforeach
                @endif
                @endforeach
                @else
                <div class="card">
                    <div class="card-header">
                        <a class="card-link" data-toggle="collapse" href="#collapseOne">
                            فاکتور
                        </a>
                    </div>
                    <div id="collapseOne" class="collapse show" data-parent="#accordion">
                        <div class="col-12">
                            @if(!is_null($item['centers']) || !is_null($item['forms']))
                            <div class="row p-3 position-relative">
                                @if(!is_null($item['forms']))
                                @foreach($item['forms'] as $form)
                                @if (in_array('site' , $form->data_display_level))
                                @include('Admin.factoritem.layouts.formItem')
                                @endif
                                @endforeach
                                @endif
                            </div>
                            @else
                            <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                <div>
                                    @if(!is_null($item['service_item']))
                                    <span>{{ $item['service']->title . '-' . $item['service_item']->title }}</span>
                                    @else
                                    <span>{{ $item['service']->title }}</span>
                                    @endif
                                </div>
                                <i class="fa fa-check font-18 text-success"></i>
                            </div>
                            @endif
                            <div class="row">
                                <div class="col-12 col-md-3 mb-2">
                                    <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                                    <p class="form-control">{{number_format($item['total_price'])}} </p>
                                </div>
                                @if($item['type'] == 1)
                                <div class="col-12 col-md-3 mb-2">
                                    <label>تعداد اقساط</label>
                                    <p class="form-control">{{$item['installments_num']}}</p>
                                </div>
                                <div class="col-12 col-md-3 mb-2">
                                    <label>پیش پرداخت(ریال)</label>
                                    <p class="form-control">{{number_format($item['prepayment']['price'])}}</p>
                                </div>
                                <div class="col-12 col-md-4 mb-3">
                                    <label>نوع ضمانت</label>
                                    <br>
                                    چک <input type="radio" name="guarantee_type" value="check" {{
                                        $item['guarantee_type']=='check' ? 'checked' : '' }}>
                                    سفته <input type="radio" name="guarantee_type" value="promissorynote" {{
                                        $item['guarantee_type']=='promissorynote' ? 'checked' : '' }}>
                                </div>
                                @endif
                                <div class="col-12 col-md-12 mb-3">
                                    <label> نوع پرداخت</label>
                                    <br>
                                    نقد <input type="radio" name="type" value="0" {{ $item['type']=='0' ? 'checked' : ''
                                        }}>
                                    اقساط<input type="radio" name="type" value="1" {{ $item['type']=='1' ? 'checked'
                                        : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if ($item['service']->session)
                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-toggle="collapse" href="#sessions">
                            جلسات
                        </a>
                    </div>
                    <div id="sessions" class="collapse" data-parent="#accordion">
                        <div class="card-body">
                            <div class="card-body">
                                <div class="tab-content" id="myTabContent">
                                    @foreach($item['factorItems'] as $factoritem)
                                    <div class="col-12 col-md-12 mb-3">
                                        جلسات {{ $factoritem->factor->user->name.'
                                        '.$factoritem->factor->user->family}}
                                    </div>
                                    <div class="row col-md-12">
                                        <table class="table table-bordered table-striped table-responsive-md"
                                            style="font-family: font;">
                                            <tr>
                                                <th>تاریخ و ساعت</th>
                                                <th>شماره دندان</th>
                                                <th>توضیحات</th>
                                                <th>پزشک</th>
                                                <th>دستیار</th>
                                                <th>مبلغ (ریال)</th>
                                            </tr>
                                            @foreach ($factoritem->sessions->where('status', '!=', false) as $session)
                                            <tr>
                                                <td>{{ verta($session->date)->format('%d %B ، %Y - H:i:s') }}
                                                </td>
                                                <td>
                                                    @foreach ($item['service_tariffs'] as $service_tariff)
                                                    {{ $session->factor_item_service_tariff_id ==
                                                    $service_tariff->pivot->id
                                                    ? $service_tariff->pivot->value : '' }}
                                                    @endforeach
                                                </td>
                                                <td>{{ $session->description }}</td>
                                                <td>
                                                    @if (isset($session->agent))
                                                    {{ $session->agent->name }}
                                                    {{ $session->agent->family }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (isset($session->assistant))
                                                    {{ $session->assistant->name }}
                                                    {{ $session->assistant->family }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ number_format($session->price) }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </table>
                                        <div class="col-12 mb-2">
                                            <div class="btn badge-success mb-3 p-2">
                                                جمع مبلغ جلسات
                                                :
                                                {{ number_format($factoritem->sessions->where('status' , '!=' ,
                                                false)->sum('price')) }}
                                                ریال
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @endif
                @if($item['type'] == 1 && $totalFactorItem)
                @if($item['confirm'])
                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-toggle="collapse" href="#collapseTwo">
                            اقساط - وضعیت مالی پرونده : {{ $item['status_mali_text'] }}
                        </a>
                    </div>
                    <div id="collapseTwo" class="collapse" data-parent="#accordion">
                        <div class="card-body">
                            <div class="card-body">
                                <div class="tab-content" id="myTabContent">
                                    <h5 class="mb-3"> جزئیات اقساط پرداخت نشده</h5>
                                    <hr>
                                    <table class="table table-bordered table-striped table-responsive-md"
                                        style="font-family: font;">
                                        <tr>
                                            <th>شماره قسط</th>
                                            <th>سررسید قسط</th>
                                            <th>جریمه دیرکرد (ریال)</th>
                                            <th>مبلغ قابل پرداخت (ریال)</th>
                                        </tr>
                                        @foreach($expire_installments as $expire_installment)
                                        <tr>
                                            <td>{{ $expire_installment->level }}</td>
                                            <td>{{ verta($expire_installment->date)->format('%d %B ، %Y - H:i:s') }}
                                            </td>
                                            <td>{{ (\Carbon\Carbon::now()->diffInDays($expire_installment->date)) > 3 ?
                                                number_format((\Carbon\Carbon::now()->diffInDays($expire_installment->date))*$expire_installment->price*0.06)
                                                : 0 }}</td>
                                            <td>{{ number_format($expire_installment->price +
                                                \Carbon\Carbon::now()->diffInDays($expire_installment->date)*$expire_installment->price*0.06
                                                )}}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                    <h5 class="mb-3">جزئیات اقساط پرداخت شده</h5>
                                    <hr>
                                    <table class="table table-bordered table-striped table-responsive-md"
                                        style="font-family: font;">
                                        <tr>
                                            <th>شماره قسط</th>
                                            <th>سررسید قسط</th>
                                            <th>تاریخ پرداخت</th>
                                            <th>مبلغ قسط</th>
                                        </tr>
                                        @foreach($paid_installments as $paid_installment)
                                        <tr>
                                            <td>{{ $paid_installment->level }}</td>
                                            <td>{{ verta( $paid_installment->date)->format('%d %B ، %Y - H:i:s') }}</td>
                                            <td>{{ verta( $paid_installment->pay_date)->format('%d %B ، %Y - H:i:s') }}
                                            </td>
                                            <td>{{ number_format($paid_installment->price) }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-toggle="collapse" href="#collapseThree">
                            درخواست تسویه حساب
                        </a>
                    </div>
                    <div id="collapseThree" class="collapse" data-parent="#accordion">
                        <div class="col-12">
                            <div class="card-body">
                                <div class="tab-content" id="myTabContent">
                                    @if($item['status_mali'] != 6 && $item['status_mali'] != 7 && $item['status_mali']
                                    != 8 && $item['status_mali'] != 9)
                                    <form class="form-horizontal" role="form" method="post"
                                        action="{{ route('site.factorItem.requestContractSettlement') }}">
                                        @csrf
                                        <div class="row modal-body">
                                            <input type="hidden" name="item[id]" value="{{ $totalFactorItem->id }}">
                                            <div class="col-12 col-md-8 card-body">
                                                <textarea required class="form-control"
                                                    name="description_request_contract_settlement" id="" cols="30"
                                                    rows="10"
                                                    placeholder="توضیح خود را بنویسید...">{{ $totalFactorItem->totalFactorItemContractSettlement ? $totalFactorItem->totalFactorItemContractSettlement->description_request_contract_settlement : '' }}</textarea>
                                            </div>
                                            <div class="col-12 col-md-4 card-body">
                                                @if(isset($totalFactorItem->totalFactorItemContractSettlement->img_request_contract_settlement))
                                                <a href="{{ asset($totalFactorItem->totalFactorItemContractSettlement->img_request_contract_settlement) }}"
                                                    target="_blank">
                                                    <img src="{{ $totalFactorItem->totalFactorItemContractSettlement->img_request_contract_settlement }}"
                                                        class="card-img-top" height="150px">
                                                </a>
                                                @else
                                                <div id="request_contract_settlement_src" class="show">
                                                    <img class="card-img-top" height="150px">
                                                </div>
                                                <div class="card-body">
                                                    <label>تصویر درخواست تسویه حساب کاربر</label>
                                                    <br>
                                                    <label for="request_contract_settlement" id="up-label"
                                                        class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                            aria-hidden="true"></i> انتخاب تصویر </label>
                                                    <input name="request_contract_settlement" type="file"
                                                        id="request_contract_settlement"
                                                        class="filestyle file-upload file_upload_ajax file-up"
                                                        data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                                    <input type="hidden" id="request_contract_settlement" class="path"
                                                        name="urlrequest_contract_settlement">
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                        @if(!in_array($totalFactorItem->status_mali , [6,7,8,9]))
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-success px-4 mb-3 submit_btn">ثبت
                                                درخواست</button>
                                        </div>
                                        @else
                                        <p class="text-warning font-weight-bolder"><i class="fa fa-exclamation-triangle"
                                                aria-hidden="true"></i> قبلا برای این پرونده درخواست تسویه حساب ثبت شده
                                            است. </p>
                                        @endif
                                    </form>
                                    @else
                                    <h5>قبلا درخواست تسویه حساب برای این پرونده ثبت شده است.</h5>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @if ($totalFactorItem->total_factor_item_status_id == 18 || $item['confirm'] )
                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-toggle="collapse" href="#collapseContract">
                            قرارداد
                        </a>
                    </div>
                    <div id="collapseContract" class="collapse" data-parent="#accordion">
                        <div class="card-body">
                            @if($totalFactorItem->factorItems->where('factoritemable_id' , 124)->count())
                            @include('Admin.totalfactoritem.layouts.contractLoanCash')
                            @elseif ($totalFactorItem->factorItems->whereIn('factoritemable_id' , [17,198])->count())
                            @include('Admin.totalfactoritem.layouts.contractGhorfeOnline')
                            @else
                            @include('Admin.totalfactoritem.layouts.contract')
                            @endif
                            <hr>
                            @if($agents_contract_file)
                            <h6>فایل قرارداد خدمات دهنده</h6>
                            @foreach ($agents_contract_file as $contract_file)
                            <a href="{{ $contract_file }}" class="btn btn-primary py-1 px-2" target="_blank">مشاهده</a>
                            @endforeach
                            @endif
                        </div>
                        @if($totalFactorItem->total_factor_item_status_id == 18)
                        @include('site.profile.layouts.factoritem.confirmContract')
                        @endif
                    </div>
                </div>
                @endif
                @elseif ($factorItem->whereIn('factoritemable_id' , [17,198])->count())
                @if ($item['status_factor'] == 18 || $item['confirm'] )
                <div class="card">
                    <div class="card-header">
                        <a class="collapsed card-link" data-toggle="collapse" href="#collapseContract">
                            قرارداد
                        </a>
                    </div>
                    <div id="collapseContract" class="collapse" data-parent="#accordion">
                        <div class="card-body">
                            @if ($factorItem->whereIn('factoritemable_id' , [17,198])->count())
                            @include('Admin.factoritem.layouts.contractGhorfeOnline')
                            @endif
                            <hr>
                        </div>
                        @if($item['status_factor'] == 18)
                        @include('site.profile.layouts.factoritem.confirmContract')
                        @endif
                    </div>
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@if(session()->get('success')=='success')
<span id="success"></span>
@elseif(session()->get('error')=='success')
<span id="error"></span>
@endif
@stop
@section('js')
<script>
    $('#loading').css('display', 'none');
    $('#confirm-contract-check').prop('checked', false);
    $('#confirm-contract-check').change(function () {
        if (this.checked) {
            $('#step-two-cover').slideUp();
        } else {
            $('#step-two-cover').slideDown();
        }
    });
    $("body").on('click','.send_confirm_contract_code',function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var confirm_contract_check = $('#confirm-contract-check').prop('checked');
    var total_factor_item = $('#total_factor_item').val();
    var factor_item = $('#factor_item').val();
    $.ajax({
        url: "/factorItem/sendConfirmContractCode",
        type: 'post',
        data: {
            "_token": token,
            "confirm_contract_check": confirm_contract_check,
            "total_factor_item": total_factor_item,
            "factor_item": factor_item,
        },
        success: function (response) {
            swal(response['message'], "", response['status'])
        }
    });
    });
    //verification confim contract
    $("body").on('keyup','#verification_code',function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var verification_code = $('#verification_code').val();
    var total_factor_item = $('#total_factor_item').val();
    var factor_item = $('#factor_item').val();

    if(verification_code.length == 6)
    {
        $('#loading').css('display', 'block');
        $.ajax({
        url: "/factorItem/verificationConfirmContractCode",
        type: 'post',
        data: {
            "_token": token,
            "verification_code": verification_code,
            "total_factor_item": total_factor_item,
            "factor_item": factor_item,
        },
        success: function (response) {
            swal(response['message'], "", response['status']);
            if(response['status'] == 'success')
            {
                location.reload();
            }
            $('#loading').css('display', 'none');
        }
    });
    }
    });

    //verification confim factor
    $("body").on('keyup','#verification_factor_confirm_code',function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var verification_code = $('#verification_factor_confirm_code').val();
    var total_factor_item = $('#total_factor_item').val();
    var factor_item = $('#factor_item').val();

    if(verification_code.length == 6)
    {
        $('#loading').css('display', 'block');
        $.ajax({
        url: "/factorItem/verificationConfirmContractCodeFactor",
        type: 'post',
        data: {
            "_token": token,
            "verification_code": verification_code,
            "total_factor_item": total_factor_item,
            "factor_item": factor_item,
        },
        success: function (response) {
            swal(response['message'], "", response['status']);
            if(response['status'] == 'success')
            {
                location.reload();
            }
            $('#loading').css('display', 'none');
        }
    });
    }
    });
</script>
<script src="{{asset('assets/dashboard/js/site/adminJs.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/factorItem.js?v=1.3') }}"></script>
@endsection
