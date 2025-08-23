@extends('Admin.master')
@section('title')پرونده {{ $user['family'] }}@endsection
@section('content')
@include('Admin.totalfactoritem.layouts.info')
<div class="modal fade" id="linkModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ارسال لینک</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('totalFactorItem.sendInitialLink') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row p-4">
                    <input type="hidden" value="{{ $item['id'] }}" name="id">
                    <input type="hidden" value="{{ $user['id'] }}" name="user_id">
                    <input type="hidden" value="{{ verta($item['approval_date']) }}" name="approval_date">
                    <div class="col-12 col-md-6">
                        <label>مبلغ (ریال)</label>
                        <input class="form-control px-4 mb-3" name="price" type="text"
                            value="{{ number_format($expire_installments->sum('price')) }}"
                            oninput="separatePrices(this)">
                    </div>
                    <div class="col-12 col-md-6">
                        <label>انتخاب نوع پرداخت</label>
                        <select class="form-control px-4 mb-3" name="payment_type" id="payment_type3"
                            onchange="paymentType(3)">
                            <option value="send_tax_free_card_number">ارسال شماره کارت</option>
                            <option value="online">آنلاین</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-primary px-4 mb-3 submit_btn">ثبت و ارسال</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row page-header">
    <div class="{{$totalFactorItem->guarantor_checked ? 'col-lg-3' : 'col-lg-4'}} col-md-12">
        <div class="file-box card overflow-hidden">
            <div class="card-body text-center">
                <h5 class="title-box">پیش پرداخت</h5>
                <div class="m-b-10">
                    <p> مبلغ کل : {{number_format($item['prepayment']['price']) }}</p>
                    <p> مبلغ پرداخت شده : {{number_format($item['prepayment']['price'] - $item['prepayment']['debt']) }}
                    </p>
                    <p> مبلغ باقی مانده : {{number_format($item['prepayment']['debt']) }}</p>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="{{$totalFactorItem->guarantor_checked ? 'col-lg-3' : 'col-lg-4'}} col-md-12">
        <div class="file-box card overflow-hidden">
            <div class="card-body text-center">
                <h5 class="title-box">مدارک هویتی وام گیرنده</h5>
                <div class="m-b-10">
                    <p>تصویر کارت ملی <i class="fa {{ isset($factor->user->imgmeli) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    <p>تصویر شناسنامه <i
                            class="fa {{ isset($factor->user->imgshenasname) ? 'fa-check' : 'fa-close' }}"></i></p>
                    <p> تصویر ثنا <i class="fa {{ isset($factor->user->imgturnover) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @if ($totalFactorItem->guarantor_checked)
    <div class="col-lg-3 col-md-12">
        <div class="file-box card overflow-hidden">
            <div class="card-body text-center">
                <h5 class="title-box">مدارک هویتی ضامن</h5>
                <div class="m-b-10">
                    <div class="m-b-10">
                        <p> تصویر کارت ملی <i
                                class="fa {{ isset($item['contract_values']['imgmelicardguarantor']) ? 'fa-check' : 'fa-close' }}"></i>
                        </p>
                        <p> تصویر شناسنامه <i
                                class="fa {{ isset($item['contract_values']['imgshenasnameguarantor']) ? 'fa-check' : 'fa-close' }}"></i>
                        </p>
                        <p> تصویر ثنا <i
                                class="fa {{ isset($item['contract_values']['imgauthentication']) ? 'fa-check' : 'fa-close' }}"></i>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    <div class="{{$totalFactorItem->guarantor_checked ? 'col-lg-3' : 'col-lg-4'}} col-md-12">
        <div class="file-box card overflow-hidden">
            <div class="card-body text-center">
                <h5 class="title-box">مدارک مالی</h5>
                <div class="m-b-10">
                    @if ($item['guarantee_type'] == 'check')
                    <p> تصویر استعلام چک <i
                            class="fa {{ isset($item['contract_values']['imginquiry']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    <p> تصویر چک <i
                            class="fa {{ isset($item['contract_values']['imgcheck']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    <p> تصویر معدل کارکرد سه ماهه <i
                            class="fa {{ isset($item['contract_values']['imgValidation']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    @elseif($item['guarantee_type'] == 'certificate')
                    <p>تصویر گواهی کسر از حقوق<i
                            class="fa {{ isset($item['contract_values']['imgcertificate']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    <p>تصویر گواهی اشتغال به کار <i
                            class="fa {{ isset($item['contract_values']['imgcertificateemployment']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    <p>تصویر ثنا <i
                            class="fa {{ isset($item['contract_values']['imginquiry']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    @else
                    <p>تصویر سفته<i
                            class="fa {{ isset($item['contract_values']['imgpromissorynote']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    <p> تصویر معدل کارکرد سه ماهه <i
                            class="fa {{ isset($item['contract_values']['imgValidation']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    <p>تصویر ثنا <i
                            class="fa {{ isset($item['contract_values']['imgauthentication']) ? 'fa-check' : 'fa-close' }}"></i>
                    </p>
                    @endif
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">

        @permission('file-show')

        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link show active" id="contact-tab" data-toggle="tab" href="#factor" role="tab"
                    aria-controls="factor" aria-selected="false">پرونده</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="basic-document-tab" data-toggle="tab" href="#basic-document" role="tab"
                    aria-controls="basic-document" aria-selected="true">مدارک اولیه و پشتیبانی</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="consultation-referral-tab" data-toggle="tab" href="#consultationReferral"
                    role="tab" aria-controls="home" aria-selected="false">ثبت مشاوره و ارجاع</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                    aria-controls="contact" aria-selected="false">توضیحات</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="return-payment-tab" data-toggle="tab" href="#returnPayment" role="tab"
                    aria-controls="returnPayment" aria-selected="false">عودت وجه</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="factor" role="tabpanel" aria-labelledby="contact-tab">
                <div id="accordion" class="panel-group">
                    @include('Admin.totalfactoritem.layouts.file.factor')
                    @include('Admin.totalfactoritem.layouts.file.session')
                    @include('Admin.totalfactoritem.layouts.file.check')
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#payments" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">پرداخت های انجام شده</a>
                            </h5>
                        </div>
                        <div id="payments" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.totalfactoritem.layouts.payments')
                            </div>
                        </div>
                    </div>
                    @if ($totalFactorItem->total_price != 0)
                    @if(isset($item['registeration_debt']))
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#prepaymentLink" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">ثبت و ارسال قبض پرداخت هزینه ثبت نام</a>
                            </h5>
                        </div>
                        <div id="prepaymentLink" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.totalfactoritem.layouts.registerationLink')
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#prepaymentLink" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">ثبت و ارسال قبض پرداخت هزینه پیش پرداخت</a>
                            </h5>
                        </div>
                        <div id="prepaymentLink" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.totalfactoritem.layouts.prepaymentLink')
                            </div>
                        </div>
                    </div>
                    @if(isset($item['price_difference']['total']))
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#priceDiffLink" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">ثبت و ارسال قبض پرداخت اختلاف حساب</a>
                            </h5>
                        </div>
                        <div id="priceDiffLink" class="panel-collapse collapse">
                            <div class="panel-body">
                                <ul class="nav nav-tabs mt-4 border-bottom-0" id="myTab" role="tablist">
                                    @foreach ($items as $key => $factorItem)
                                    <li class="nav-item">
                                        <a class="nav-link show {{ $key == 0 ? 'active' : ''}}"
                                            id="priceDiffLink-{{ $key }}-tab" data-toggle="tab"
                                            href="#priceDiffLink-{{ $key }}" role="tab"
                                            aria-controls="priceDiffLink-{{ $key }}" aria-selected="false">{{
                                            $factorItem['factoritemable']->title }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content border p-4" id="myTabContent">
                                    @foreach ($items as $key => $factorItem)
                                    <div class="tab-pane fade p-t-20 {{ $key == 0 ? 'show active' : ''}} "
                                        id="priceDiffLink-{{ $key }}" role="tabpanel"
                                        aria-labelledby="priceDiffLink-{{ $key }}-tab">
                                        @include('Admin.factoritem.layouts.file.priceDiffLink')
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#installments" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">اقساط</a>
                            </h5>
                        </div>
                        <div id="installments" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12 col-md-4">
                                                <h5> وضعیت مالی : {{ $item['status_mali'] }}</h5>
                                                <h5>تاریخ شروع قرارداد : {{ $item['start_date'] }} </h5>
                                                <h5>تاریخ پایان قرارداد : {{ $item['end_date'] }}</h5>
                                                <h5> مبلغ تسهیلات : {{ number_format($item['loan']) }}
                                                    ریال</h5>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <h5> تعداد کل اقساط : {{ $item['installments_num'] }}
                                                </h5>
                                                <h5> تعداد اقساط پرداخت شده :
                                                    {{ $item['installments_num_pay'] }}</h5>
                                                <h5> تعداد اقساط پرداخت نشده :
                                                    {{ $item['installments_num'] - $item['installments_num_pay'] }}
                                                </h5>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <h5> مبلغ بدهکاری کل
                                                    : {{ number_format($item['installments_debt']) }}
                                                    ریال</h5>
                                                <h5> مبلغ بدهکاری اقساط :
                                                    {{ number_format($expire_installments->sum('price')) }}
                                                    ریال</h5>
                                                <h5> جریمه کل : {{ $item['extra_pay'] }}</h5>
                                                <h5> جریمه باقیمانده : {{ $item['extra_pay_debt'] }}
                                                    ریال
                                                </h5>
                                            </div>
                                            <div class="card col-md-12" style="margin-top: 20px">
                                                @include('Admin.totalfactoritem.installment.list')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="panel">
                        <div class="panel-heading" id="orginal-document-panel">
                            <h5 class="panel-title">
                                <a href="#orginal-documents" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">مدارک پرونده</a>
                            </h5>
                        </div>
                        <div id="orginal-documents" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.totalfactoritem.show.orginalDocument')
                            </div>
                        </div>
                    </div>
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#contract" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">قرارداد</a>
                            </h5>
                        </div>
                        <div id="contract" class="panel-collapse collapse">
                            <div class="panel-body">
                                @if($totalFactorItem->factorItems->where('factoritemable_id' , 124)->count())
                                @include('Admin.totalfactoritem.layouts.contractLoanCash')
                                @elseif ($totalFactorItem->factorItems->where('factoritemable_id' , 17)->count())
                                @include('Admin.totalfactoritem.layouts.contractGhorfeOnline')
                                @else
                                @include('Admin.totalfactoritem.layouts.contract')
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#printForm" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">فرم ثبت نام</a>
                            </h5>
                        </div>
                        <div id="printForm" class="panel-collapse collapse">
                            <div class="panel-body">
                                <ul class="nav nav-tabs mt-4 border-bottom-0" id="myTab" role="tablist">
                                    @foreach ($items as $key => $factorItem)
                                    <li class="nav-item">
                                        <a class="nav-link show {{ $key == 0 ? 'active' : ''}}"
                                            id="printForm-{{ $key }}-tab" data-toggle="tab" href="#printForm-{{ $key }}"
                                            role="tab" aria-controls="printForm-{{ $key }}" aria-selected="false">{{
                                            $factorItem['factoritemable']->title }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                                <div class="tab-content border p-4" id="myTabContent">
                                    @foreach ($items as $key => $factorItem)
                                    <div class="tab-pane fade p-t-20 {{ $key == 0 ? 'show active' : ''}} "
                                        id="printForm-{{ $key }}" role="tabpanel"
                                        aria-labelledby="printForm-{{ $key }}-tab">
                                        @include('Admin.factoritem.layouts.print')
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('Admin.totalfactoritem.show.confirmationForm')
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="basic-document" role="tabpanel" aria-labelledby="basic-document-tab">
                @include('Admin.totalfactoritem.show.basicDocument')
            </div>
            <div class="tab-pane fade p-t-20" id="consultationReferral" role="tabpanel"
                aria-labelledby="consultation-referral-tab">
                @foreach ($items as $key => $factor_item)
                <ul class="nav nav-tabs mt-4 border-bottom-0" id="myTab" role="tablist">
                    @foreach ($factor_item['factorItems'] as $count => $factorItem)
                    <li class="nav-item">
                        <a class="nav-link show {{ $count == 0 ? 'active' : ''}}"
                            onclick="consultationReferralAppend('consultation_referral_collection_{{$count}}')"
                            id="consultation-referral-{{ $count }}-tab" data-toggle="tab"
                            href="#consultation_referral_collection_{{ $count }}" role="tab"
                            aria-controls="consultation_referral_collection_{{ $count }}" aria-selected="false">پرونده
                            {{$factorItem->factor->user->name . ' '.$factorItem->factor->user->family}}</a>
                    </li>
                    @endforeach
                </ul>
                <div class="tab-content border p-4" id="myTabContent">
                    @foreach ($factor_item['factorItems'] as $count => $factorItem)
                    <div class="tab-pane fade p-t-20 {{ $count == 0 ? 'show active' : ''}} "
                        id="consultation_referral_collection_{{ $count }}" role="tabpanel"
                        aria-labelledby="consultationReferral-{{ $count }}-tab">
                        @include('Admin.totalfactoritem.show.determineCentering')
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @include('Admin.factoritem.layouts.histories')
            <div class="tab-pane fade p-t-20" id="returnPayment" role="tabpanel" aria-labelledby="return-payment-tab">
                @include('Admin.totalfactoritem.layouts.file.returnPayment')
            </div>
        </div>
        @endpermission
    </div>
</div>
<div class="row col-12 col-md-6">
    <span class="btn btn-warning text-white mr-2" data-toggle="modal" data-target="#suspensionModal">یادآوری
        پیگیری</span>
    <a data-toggle="modal" data-target="#closeFileModal" class="btn btn-dark mr-2 text-white">پرونده مختومه</a>
    @if (in_array(trim($item['status']) , [0,3,4,14]) )
    <form action="/dashboard/totalFactorItem/change-status" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="9">
        <button class="btn btn-danger mr-2">ارجاع به امورتسهیلات</button>
    </form>
    @endif
    @if (in_array(trim($item['status']) , [9,8]) )
    <form action="/dashboard/totalFactorItem/change-status" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="14">
        <button class="btn btn-danger mr-2">ارجاع به پشتیبان</button>
    </form>
    @endif
    @if (in_array(trim($item['status']) , [9,5]) )
    <form action="/dashboard/totalFactorItem/change-status" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="8">
        <button class="btn btn-success mr-2">ارجاع به مدیرعامل</button>
    </form>
    @endif
</div>
@stop
@section('js')

<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/factorItem.js?v=2.1') }}"></script>
<script src="/assets/dashboard/js/factorModal.js"></script>
<script>
    $('body').on('click','#orginal-document-panel',function (){
        job();
        guarantorChecked();
    })
</script>
@endsection
