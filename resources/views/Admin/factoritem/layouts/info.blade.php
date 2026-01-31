<div class="row page-header">
    <div class="col-12 col-md-8">
        <span class="btn btn-warning text-white mr-2" data-toggle="modal" data-target="#suspensionModal">یادآوری
            پیگیری</span>
        <a data-toggle="modal" data-target="#closeFileModal" class="btn btn-dark mr-2 text-white">پرونده مختومه</a>
        @if (in_array(trim($factorItem['status_factor']) , [14,17]) )
        <form action="{{ route('factorItem.changeStatus') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
            <input type="hidden" name="status_factor" value="18">
            <button class="btn btn-secondary mr-2">ارجاع به کاربر جهت تایید قرارداد</button>
        </form>
        @endif
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            <a onclick="printDiv('printableArea')" class="btn btn-link btn-float has-text text-size-small rtl p-1">
                <i class="fa fa-print" aria-hidden="true"></i>
                <span>پرینت فرم</span>
            </a>
            @permission('file-fastEdit')
            <a href="/dashboard/factorItem/{{$factorItem->resnumber}}/fastEdit"
                class="btn btn-link btn-float has-text text-size-small rtl p-1">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>ثبت سریع پرونده</span>
            </a>
            @endpermission
            <a href="{{ $factorItem->type ? '/dashboard/totalFactorItem/'.$item['id'] : '/dashboard/factorItem/'.$factorItem->resnumber }}"
                class="btn btn-link btn-float has-text text-size-small rtl p-1">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>امور اداری و مالی پرونده</span>
            </a>
            <a href="/dashboard/factorItem/{{$factorItem->resnumber}}/edit"
                class="btn btn-link btn-float has-text text-size-small rtl p-1">
                <i class="fa fa-edit text-indigo-400" aria-hidden="true"></i>
                <span>امور اجرایی پرونده</span>
            </a>
        </div>
    </div>
</div>
<div class="col-12">
    <div class="row">
        <div class="col-12 card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-10">
                        <h5 class="card-title">اطلاعات کاربر</h5>
                        <div class="row">
                            <div class="col-12 col-md-4">
                                <h5> نام : {{ $user['name'].' '.$user['family'] }}</h5>
                                <h5> شماره تماس : {{ $user['phone'] }}</h5>
                                <h5> استان : {{ $user->province ? $user->province->name : 'ثبت نشده' }}</h5>
                                <h5> نمایندگی : {{ $factorItem->representation ?
                                    $factorItem->representation->representation->fa_name : 'م تهران' }}</h5>
                                @if($factorItem['supervisor_id'])
                                <h5> سرپرست : {{ $factorItem->supervisor->factor->user->name.'
                                    '.$factorItem->supervisor->factor->user->family}}</h5>
                                @endif
                            </div>
                            <div class="col-12 col-md-5">
                                <h5> نوع خدمت | دسته بندی : {{ $factorItem['factoritemable']->title }}</h5>
                                <h5> وضعیت اجرایی پرونده : {{ $factorItem['status_factor_text'] }}</h5>
                                <h5> شماره پرونده اجرایی : {{ $factorItem['file_number'] }}</h5>
                                <h5> مرکز : {{ $factorItem['center'] ? $factorItem['center']->title : 'بدون مرکز' }}
                                </h5>
                                <h5> نوع پرداخت : {{ $factorItem::TYPE[$factorItem->type] }} - نوع دریافت خدمت | کالا :
                                    {{
                                    $factorItem::PurchaseTYPE[$factorItem->purchase_type] }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="row">
                            <div class="col-12 float-left">
                                <img src="{{ $user['avatar'] }}" width="100%">
                                @include('Admin.user.vipAlert')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if ($factorItem['type'])
<h5 class="card-title"><i data-toggle="modal" data-target="#helpModal" class="fa fa-question-circle"
        style="font-size:38px;"></i>وضعیت مالی پرونده ( اقساطی {{ ($item['confirm'] ? '-
    تایید شده' : '- منتظر تایید') }} - <a class="text-primary">{{ $factorItem['financial_plans_type'] }}</a>)</h5>
@else
<h5 class="card-title"><i data-toggle="modal" data-target="#helpModal" class="fa fa-question-circle"
        style="font-size:38px;"></i>وضعیت مالی پرونده (نقدی - <a class="text-primary">{{
        $factorItem['financial_plans_type'] }}</a>)</h5>
@endif

@if($factorItem['type'])
@include('Admin.totalfactoritem.layouts.financialInfo')
@else
@include('Admin.factoritem.layouts.financialInfo')
@endif

<div id="closeFileModal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" class="modal fade show"
    style="padding-right: 17px;" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">درخواست مختومه شدن پرونده</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="status" role="tabpanel" aria-labelledby="status-tab" class="tab-pane fade p-t-20 active show">
                    <div class="row">
                        <div class="col-12">
                            <form action="/dashboard/factorItem/close" method="POST" enctype="multipart/form-data">
                                @csrf
                                @include('Admin.layouts.errors')
                                <input type="hidden" name="id" value="{{ $factorItem['id'] }}">
                                <input type="hidden" name="status" value="15">
                                <textarea required class="form-control" name="comment" id="" cols="30" rows="5"
                                    placeholder="همکار گرامی لطفا دلیل مختومه شدن پرونده را ذکر کنید..."></textarea>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">ثبت</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="suspensionModal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" class="modal fade show"
    style="padding-right: 17px;" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">یادآوری پیگیری</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <ul id="myTab" role="tablist" class="nav nav-tabs">
                    <li class="nav-item">
                        <a id="remember-tab" data-toggle="tab" href="#remember" role="tab" aria-controls="remember"
                            aria-selected="false" class="nav-link show active">یادآوری</a>
                    </li>
                    <li class="nav-item">
                        <a id="detail-tab" data-toggle="tab" href="#detail" role="tab" aria-controls="detail"
                            aria-selected="false" class="nav-link show">جزئیات</a>
                    </li>
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div id="remember" role="tabpanel" aria-labelledby="remember-tab"
                        class="tab-pane fade p-t-20 active show">
                        <div class="row">
                            <div class="col-12"></div>
                            <div class="col-12">
                                <form class="form-horizontal" role="form" method="post"
                                    action="{{ $factorItem->type ? route('totalFactorItem.suspension') : route('factorItem.suspension') }}">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="type" value="supporter">
                                        <input type="hidden" name="status_factor" value="3">
                                        <input type="hidden" name="id"
                                            value="{{ $factorItem->type ? $item['id'] : $factorItem->id }}">
                                        <div class="row">
                                            <div class="col-12 col-md-6 mb-2">
                                                <label>تاریخ یادآوری</label>
                                                <date-picker placeholder="انتخاب تاریخ" format="YYYY/M/D HH:mm"
                                                    name="date" type="datetime" display-format="jYYYY/jMM/jDD HH:mm">
                                                </date-picker>
                                            </div>
                                            <div class="col-12 col-md-6 mb-2">
                                                <label>انتخاب دلیل</label>
                                                <select class="form-control" name="reason">
                                                    <option value="1">پاسخ ندادند مجددا تماس گرفته شود.</option>
                                                    <option value="2">سایر</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <textarea required class="form-control" name="description" id=""
                                                    cols="30" rows="10"
                                                    placeholder="توضیح خود را بنویسید..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="detail" role="tabpanel" aria-labelledby="detail-tab" class="tab-pane fade p-t-20">
                        <table class="table table-bordered table-striped table-responsive-md mb-5">
                            <thead>
                                <tr>
                                    <th scope="col">آپدیت کننده</th>
                                    <th scope="col">تاریخ</th>
                                    <th scope="col">توضیحات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($factorItem['histories'] as $history)
                                <tr>
                                    <td>{{ $history->user->name . ' ' . $history->user->family }}</td>
                                    <td>{{ verta($history->created_at)->format('Y/m/d') }}</td>
                                    <td>{{ $history->description }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="helpModal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" class="modal fade show"
    style="padding-right: 17px;" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">راهنمایی</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="status" role="tabpanel" aria-labelledby="status-tab" class="tab-pane fade p-t-20 active show">
                    <div class="row">
                        <div class="col-12 p-4 alert-warning">
                            <p> اعتبار کاربر : شامل مجموع حواله ، موجودی کیف پول کاربر و مبلغ پرداختی پرونده با کسر
                                خدمات ارائه شده به کاربر می باشد.</p>
                            <p>بدهکاری ضمنی : شامل خدمتی که به کاربر ارائه شده اما واریزی انجام نشده است. لازم به ذکر می
                                باشد که کاربر موظف به پرداخت مبلغ در اولین حضور در مرکز می باشد.</p>
                            <p>بدهکاری کل : شامل مبلغی که کاربر موظف به پرداخت آن در چند مرحله می باشد.( مانند : هزینه
                                پیش پرداخت در پرونده اقساطی - قسط عقب افتاده - اختلاف حساب - پرداخت هزینه در پرونده های
                                نقدی)</p>
                            <p>توجه : کاربر موظف است بدهکاری ضمنی پرونده را در اسرع وقت با هر یک از روش های پرداخت موجود
                                پرداخت نماید. در غیر اینصورت امکان ارائه خدمت برای کاربر وجود ندارد.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
