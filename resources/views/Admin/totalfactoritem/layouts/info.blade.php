<div class="row page-header">
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
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            <a onclick="printDiv('printableAreaContract')"
                class="btn btn-link btn-float has-text text-size-small rtl p-1">
                <i class="fa fa-print" aria-hidden="true"></i>
                <span>پرینت قرارداد</span>
            </a>
            <a data-toggle="modal" data-target="#requestContractSettlementModal"
                class="btn btn-link btn-float has-text text-size-small rtl p-1">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>درخواست تسویه حساب</span>
            </a>
            @permission('file-report')
            <a href="/dashboard/totalFactorItem/{{ $totalFactorItem->id }}/installment"
                class="btn btn-link btn-float has-text text-size-small rtl p-1">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>مشاهده اقساط</span>
            </a>
            @endpermission
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-5">
                <h5> نام : {{ $user['name'] . ' ' . $user['family'] }}</h5>
                <h5> شماره تماس : {{ $user['phone'] }}</h5>
                <h5> استان : {{ $user->province ? $user->province->name : 'ثبت نشده' }}</h5>
                <h5> نوع خدمت | دسته بندی : {{ $item['factoritemable'] }}</h5>
                <h5> تاریخ تشکیل پرونده: {{ verta($item['approval_date'])->format('Y/m/d') }} </h5>
            </div>
            <div class="col-12 col-md-5">
                <h5> نوع پرداخت : اقساط</h5>
                <h5> شماره پرونده مالی : {{ $item['file_number_mali'] ? $item['file_number_mali'] : 'ندارد' }}</h5>
                <h5> وضعیت مالی پرونده : {{ $item['status_mali'] }}</h5>
                <h5> وضعیت اداری پرونده : {{ $item['status_text'] }}</h5>
            </div>

            <div class="col-12 col-md-2 float-left" style="position: relative">
                <img src="{{ $user['avatar'] }}" width="75%">
                @include('Admin.user.vipAlert')
            </div>

        </div>
    </div>
</div>
<h5 class="card-title"><i data-toggle="modal" data-target="#helpModal" class="fa fa-question-circle"
    style="font-size:38px;"></i>وضعیت مالی پرونده ( اقساطی {{ ($item['confirm'] ? '-
تایید شده' : '- منتظر تایید') }})</h5>
@include('Admin.totalfactoritem.layouts.financialInfo')

<div id="requestContractSettlementModal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel"
    class="modal fade show" style="padding-right: 17px;" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">درخواست تسویه حساب پرونده</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                        aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="status" role="tabpanel" aria-labelledby="status-tab" class="tab-pane fade p-t-20 active show">
                    <div class="row">
                        <div class="col-12">
                            @include('Admin.totalfactoritem.installment.contractSettlement')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
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
                            <form action="/dashboard/totalFactorItem/close" method="POST" enctype="multipart/form-data">
                                @csrf
                                @include('Admin.layouts.errors')
                                <input type="hidden" name="id" value="{{ $item['id'] }}">
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
                    <li class="nav-item"><a id="remember-tab" data-toggle="tab" href="#remember" role="tab"
                            aria-controls="remember" aria-selected="false" class="nav-link show active">یادآوری</a></li>
                    <li class="nav-item"><a id="SuspensionOfExecution-tab" data-toggle="tab" href="#SuspensionOfExecution" role="tab"
                                            aria-controls="SuspensionOfExecution" aria-selected="false" class="nav-link">تعلیق اجرا</a></li>
                    <li class="nav-item"><a id="detail-tab" data-toggle="tab" href="#detail" role="tab"
                            aria-controls="detail" aria-selected="false" class="nav-link show">جزئیات</a>
                    </li>
                </ul>
                <div id="myTabContent" class="tab-content">
                    <div id="remember" role="tabpanel" aria-labelledby="remember-tab"
                        class="tab-pane fade p-t-20 active show">
                        <div class="row">
                            <div class="col-12"></div>
                            <div class="col-12">
                                <form class="form-horizontal" role="form" method="post"
                                    action="{{ route('totalFactorItem.suspension') }}">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="type" value="supporter">
                                        <input type="hidden" name="status_factor" value="3">
                                        <input type="hidden" name="id" value="{{ $item['id'] }}">
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
                    <div id="SuspensionOfExecution" role="SuspensionOfExecution" aria-labelledby="SuspensionOfExecution-tab"
                        class="tab-pane fade p-t-20">
                        <div class="row">
                            <div class="col-12"></div>
                            <div class="col-12">
                                <form class="form-horizontal" role="form" method="post"
                                    action="{{ route('totalFactorItem.suspension') }}">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="hidden" name="type" value="execution">
                                        <input type="hidden" name="id" value="{{ $item['id'] }}">
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
                                @foreach ($histories as $history)
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
