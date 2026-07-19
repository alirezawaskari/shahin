@extends('Admin.master')
@section('title')اقساط {{ $user['family'] }}@endsection

@section('content')
@include('Admin.totalfactoritem.installment.info')

@include('Admin.layouts.errors')

<div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ثبت توضیح جدید</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('factor.comment') }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <textarea required class="form-control" name="comment" id="" cols="30" rows="10"
                        placeholder="توضیح خود را بنویسید..."></textarea>
                    <input type="hidden" value="{{ $totalFactorItem->factor->id }}" name="factor_id">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $totalFactorItem->status_mali != 6 && $totalFactorItem->status_mali != 7 && $totalFactorItem->status_mali != 8 && $totalFactorItem->status_mali != 9 ? 'active' : '' }}"
                    id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact"
                    aria-selected="false">اقساط</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="extraPay-tab" data-toggle="tab" href="#extraPay" role="tab"
                    aria-controls="extraPay" aria-selected="false">جریمه</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $totalFactorItem->status_mali == 6 || $totalFactorItem->status_mali == 7 || $totalFactorItem->status_mali == 8 || $totalFactorItem->status_mali == 9 ? 'active' : '' }}"
                    id="contact-tab" data-toggle="tab" href="#installmentSettlement" role="tab" aria-controls="contact"
                    aria-selected="false">تسویه حساب</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="comment-tab" data-toggle="tab" href="#comment" role="tab"
                    aria-controls="comment" aria-selected="false">توضیحات</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 {{ $totalFactorItem->status_mali != 6 && $totalFactorItem->status_mali != 7 && $totalFactorItem->status_mali != 8 && $totalFactorItem->status_mali != 9 ? 'show active' : '' }}"
                id="contact" role="tabpanel" aria-labelledby="contact-tab">
                @include('Admin.totalfactoritem.installment.installmentLink')
                @include('Admin.totalfactoritem.installment.list')
            </div>
            <div class="tab-pane fade p-t-20" id="extraPay" role="tabpanel" aria-labelledby="extraPay-tab">
                <div class="card col-12 mt-3 pt-4 col-l main border border-secondary">
                    <h5 class="mb-3 card-header"><i class="fa fa-credit-card" aria-hidden="true"></i> ثبت و ارسال قبض
                        پرداخت جریمه</h5>
                    <div class="card-body">
                        <form action="{{ route('totalFactorItem.saveExtraPay') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <input type="hidden" value="{{ $item['id'] }}" name="id">
                                <input type="hidden" value="{{ $user['id'] }}" name="user_id">
                                <div class="col-12 col-md-2">
                                    <label>مبلغ (ریال)</label>
                                    <input class="form-control" name="price" type="text" oninput="separatePrices(this)"
                                        value="{{ $item['extra_pay_debt'] }}">
                                </div>

                                <div class="col-12 col-md-2">
                                    <label>انتخاب نوع پرداخت</label>
                                    <select class="form-control px-4 mb-3" name="payment_type" id="payment_type4"
                                        onchange="paymentType(4)">
                                        <option value="online">آنلاین</option>
                                        <option value="send_tax_free_card_number">ارسال شماره کارت</option>
                                        @permission('record_payment')
                                        <option value="card_reader">کارتخوان</option>
                                        <option value="card">کارت به کارت</option>
                                        <option value="cash">پول نقد</option>
                                        @endpermission
                                        <option value="wallet">کسر از کیف پول</option>
                                        <option value="credit">کسر از حواله</option>
                                        <option value="overpayment">کسر از اضافه پرداخت</option>
                                        <option value="deduction_price_difference">کسر از بستانکاری</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 mb-2 d-none" id="factor_item_price_diff4">
                                    <label>پرونده انتخابی</label>
                                    <select class="form-control" name="factor_item">
                                        @foreach ($items as $factor_item)
                                        <option value="{{ $factor_item['id'] }}">{{ $factor_item['factoritemable']->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3 mb-2" id="payment_date">
                                    <label>تاریخ پرداخت</label>
                                    <date-picker value="{{ now() }}" format="YYYY/M/D HH:mm" name="payment_date"
                                        type="datetime" display-format="jYYYY/jMM/jDD HH:mm">
                                    </date-picker>
                                </div>

                                <div class="col-12 col-md-3 mb-2 d-none" id="number_card4">
                                    <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                                    <input class="form-control" name="number_card" type="number"
                                        id="number_card_input4">
                                </div>

                                <div class="col-12 col-md-2 mb-2 d-none" id="payment_code4">
                                    <label>شماره پیگیری (4 رقم آخر)</label>
                                    <input class="form-control" name="payment_code" type="number"
                                        id="payment_code_input4">
                                </div>

                                <div class="col-12 col-md-3 mb-2 d-none" id="bill_image4">
                                    <label>رسید دریافت وجه</label>
                                    <br>
                                    <label for="bill_image_input4" id="up-label" class="btn badge-secondary"><i
                                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                    <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image"
                                        type="file" id="bill_image_input4">
                                    <div id="bill_image_input4_src" class="show"></div>
                                    <input type="hidden" id="bill_image_input4" class="path" name="url_bill_image"
                                        value="">
                                </div>

                                <div class="col-12 col-md-2 mb-2 d-none" id="password4">
                                    <label>رمز عبور کاربر</label>
                                    <input class="form-control" name="password" type="password" id="password_input4">
                                    <p>لطفا رمز عبور 6 رقمی که برای کاربر ارسال شده است را وارد کنید.</p>
                                </div>

                                <div class="col-12 col-md-2 mb-2 d-none" id="send_password4">
                                    <input type="hidden" value="{{ $user->phone }}" name="phone" class="phone">
                                    <span class="send_user_password btn btn-success-default text-white px-3"> ارسال رمز
                                        <i class="fa fa-paper-plane mr-1" aria-hidden="true"></i></span>
                                </div>

                                <div class="col-md-12">
                                    <button class="btn btn-primary px-4 mb-3 submit_btn" {{ $item['extra_pay_debt'] ? ''
                                        : 'disabled' }}>ثبت و ارسال</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-t-20 {{ $totalFactorItem->status_mali == 6 || $totalFactorItem->status_mali == 7 || $totalFactorItem->status_mali == 8 || $totalFactorItem->status_mali == 9 ? 'show active' : '' }}"
                id="installmentSettlement" role="tabpanel" aria-labelledby="installmentSettlement-tab">
                @include('Admin.totalfactoritem.installment.financialBalance')
                <div id="accordion" class="panel-group">
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#requestContractSettlement" class="accordion-toggle collapsed"
                                    data-toggle="collapse" data-parent="#accordion">درخواست تسویه حساب کاربر</a>
                            </h5>
                        </div>
                        <div id="requestContractSettlement" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="card col-12 mt-3 pt-4 col-l main border border-secondary">
                                    <h5 class="mb-3 card-header">درخواست تسویه حساب کاربر</h5>
                                    <div class="card-body">
                                        @include('Admin.totalfactoritem.installment.contractSettlement')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @permission('store-impunity')
                    @if($item['financial_balance_items']['extra_pay']>0 ||
                    $item['financial_balance_items']['unpaid_installments']>0)
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#impunity" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">ثبت بخشودگی</a>
                            </h5>
                        </div>
                        <div id="impunity" class="panel-collapse collapse">
                            <div class="panel-body">
                                @if($item['financial_balance_items']['extra_pay']>0)
                                <div class="card col-12 mt-3 pt-4 col-l main border border-secondary">
                                    <h5 class="mb-3 card-header text-dark">بخشودگی جریمه</h5>
                                    <p class="text-warning font-weight-bolder"><i class="fa fa-exclamation-triangle"
                                            aria-hidden="true"></i>
                                        در صورتی که قبلا برای این پرونده بخشودگی جریمه ثبت کرده باشید با ثبت بخشودگی
                                        جدید , بخشودگی جدید جایگزین بخشودگی قبلی خواهد گردید .
                                    </p>
                                    <div class="card-body">
                                        <p>مبلغ جریمه : {{number_format($item['financial_balance_items']['extra_pay'])
                                            }} ریال </p>
                                        <div class="container m-b-20">
                                            <div class="row">
                                                <div class="col-md-3 border" style="padding: 5px">
                                                    <form onsubmit="return formQuestionConfirm()"
                                                        action="{{route('totalFactorItem.storeImpunity')}}"
                                                        method="post">
                                                        @csrf
                                                        <input type="hidden" name="total_factor_item_id"
                                                            value="{{$item['id']}}">
                                                        <input type="hidden" name="impunity_type" value="penalty">
                                                        <label for="impunity"> مبلغ بخشودگی (ریال)</label>
                                                        <input id="impunity" name="impunity_amount" class="form-control"
                                                            type="text" pattern="\d{1,3}(,\d{3})*(\.\d+)?$" required
                                                            title="لطفا فقط عدد وارد نمایید"
                                                            oninput="separatePrices(this)"
                                                            placeholder="مبلغ بخشودگی را وارد نمایید">
                                                        <label for="description"> توضیحات</label>
                                                        <textarea class="form-control" name="description"
                                                            id="description" cols="15" rows="4"></textarea>
                                                        <br>
                                                        <input type="submit" class="btn btn-success submit-impunity"
                                                            value="ثبت ">
                                                    </form>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($item['financial_balance_items']['unpaid_installments']>0)
                                <div class="card col-12 mt-3 pt-4 col-l main border border-secondary">
                                    <h5 class="mb-3 card-header text-dark">بخشودگی اقساط</h5>
                                    <p class="text-warning font-weight-bolder"><i class="fa fa-exclamation-triangle"
                                            aria-hidden="true"></i>
                                        در صورتی که قبلا برای این پرونده بخشودگی قسط ثبت کرده باشید با ثبت بخشودگی جدید
                                        , بخشودگی جدید جایگزین بخشودگی قبلی خواهد گردید .
                                    </p>
                                    <div class="card-body">
                                        <p>مبلغ اقساط :
                                            {{number_format($item['financial_balance_items']['unpaid_installments']) }}
                                            ریال </p>
                                        <div class="container m-b-20">
                                            <div class="row">
                                                <div class="col-md-3 border" style="padding: 5px">
                                                    <form onsubmit="return formQuestionConfirm()"
                                                        action="{{route('totalFactorItem.storeImpunity')}}"
                                                        method="post">
                                                        @csrf
                                                        <input type="hidden" name="impunity_type" value="installment">
                                                        <input type="hidden" name="total_factor_item_id"
                                                            value="{{$item['id']}}">
                                                        <label for="impunity"> مبلغ بخشودگی (ریال)</label>
                                                        <input id="impunity" name="impunity_amount" class="form-control"
                                                            type="text" oninput="separatePrices(this)"
                                                            pattern="\d{1,3}(,\d{3})*(\.\d+)?$" required
                                                            title="لطفا فقط عدد وارد نمایید"
                                                            placeholder="مبلغ بخشودگی را وارد نمایید">
                                                        <label for="description"> توضیحات</label>
                                                        <textarea class="form-control" name="description"
                                                            id="description" cols="15" rows="4"></textarea>
                                                        <br>
                                                        <input type="submit" class="btn btn-success submit-impunity"
                                                            value="ثبت ">
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    @endpermission
                    @if($totalFactorItem->status_mali == 6 || $totalFactorItem->status_mali == 9)
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#saveContractSettlement" class="accordion-toggle collapsed"
                                    data-toggle="collapse" data-parent="#accordion">ثبت تسویه حساب کاربر</a>
                            </h5>
                        </div>
                        <div id="saveContractSettlement" class="panel-collapse collapse">
                            <div class="panel-body">
                                <div class="card col-12 mt-5 pt-4 col-l main border border-secondary">
                                    <h5 class="mb-3 card-header">ثبت تسویه حساب کاربر</h5>
                                    <div class="card-body">
                                        <form action="{{ route('totalFactorItem.contractSettlement') }}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
                                            <input type="hidden" name="" value="6">
                                            <input type="hidden" name="status_factor"
                                                value="{{ $totalFactorItem->total_factor_item_status_id }}">
                                            <input type="hidden" name="item[user_id]" value="{{ $user['id'] }}">
                                            <input type="hidden" name="item[center]"
                                                value="{{ auth()->user()->center_id }}">
                                            <div class="col-12 col-md-4 mb-2">
                                                <div class="card">
                                                    @if (isset($totalFactorItem->totalFactorItemContractSettlement->img_warranty_delivery_receipt))
                                                    <a href="{{ asset($totalFactorItem->totalFactorItemContractSettlement->img_warranty_delivery_receipt) }}"
                                                        target="_blank">
                                                        <img src="{{ $totalFactorItem->totalFactorItemContractSettlement->img_warranty_delivery_receipt }}"
                                                            class="card-img-top" height="150px">
                                                    </a>
                                                    @else
                                                    <div id="img_warranty_delivery_receipt_src" class="show">
                                                        <img class="card-img-top" height="150px">
                                                    </div>
                                                    <div class="card-body">
                                                        <label>تصویر رسید تحویل ضمانت</label>
                                                        <br>
                                                        <label for="img_warranty_delivery_receipt" id="up-label"
                                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                                        <input name="img_warranty_delivery_receipt" type="file"
                                                            id="img_warranty_delivery_receipt"
                                                            class="filestyle file-upload file_upload_ajax file-up"
                                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png"
                                                            required>
                                                        <input type="hidden" id="img_warranty_delivery_receipt" class="path"
                                                            name="urlimg_warranty_delivery_receipt">
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <p class="text-warning font-weight-bolder"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> تذکر : آپلود تصویر برای تسویه حساب کاربر الزامی است. </p>
                                            </div>
                                            <input type="submit" class="btn btn-success px-4 mb-3 submit_btn"
                                                value="ثبت" {{ $totalFactorItem->status_mali == 6 ? 'disabled' : '' }}>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="panel">
                        <div class="panel-heading">
                            <h5 class="panel-title">
                                <a href="#payments" class="accordion-toggle collapsed" data-toggle="collapse"
                                    data-parent="#accordion">مشاهده پرداخت های پرونده</a>
                            </h5>
                        </div>
                        <div id="payments" class="panel-collapse collapse">
                            <div class="panel-body">
                                @include('Admin.totalfactoritem.layouts.payments')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="comment" role="tabpanel" aria-labelledby="comment-tab">
                <span>توضیحات همکاران</span>
                <span class="btn btn-success py-1 px-2" data-toggle="modal" data-target="#commentModal">ثبت توضیح
                    جدید</span>
                <div class="mt-4">
                    @if(count($comments) > 0)
                    @foreach($comments as $comment)
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="panel-heading">
                            <span class="d-inline-block">{{ $comment['user'] }}</span>
                            <span class="text-black-50">{{ $comment['date'] }}</span>
                        </div>
                        <p class="mt-4">
                            {!! $comment['description'] !!}
                        </p>
                    </div>
                    @endforeach
                    @else
                    <p>هیچ توضیحی ثبت نشده</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@role('consulting_contract_affairs')
<form action="/dashboard/totalFactorItem/change-status-mali" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{ $totalFactorItem->id }}">
    <input type="hidden" name="status_mali" value="10">
    @if ($totalFactorItem->status_mali == 4)
    <button class="btn btn-danger mr-2">ارجاع به واحد حقوقی</button>
    @endif
</form>
@endrole
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/factorItem.js') }}"></script>
@endsection
