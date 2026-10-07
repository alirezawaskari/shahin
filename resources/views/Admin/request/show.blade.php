@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>مشاهده درخواست</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('request.index') }}">درخواست ها</a></li>
                    <li class="breadcrumb-item active" aria-current="page">مشاهده درخواست #{{ $request_rec->id }}</li>
                </ol>
            </nav>
        </div>
    </div>

    @php
        $requestSmsList = isset($sms_messages) ? $sms_messages : $request_rec->smsMessages()->with(['senderNumber', 'group', 'template'])->latest()->get();
    @endphp

    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="requestTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="details-tab" data-toggle="tab" href="#requestDetails" role="tab" aria-controls="requestDetails" aria-selected="true">
                        <i class="fa fa-list me-1"></i> جزئیات درخواست
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="sms-messages-tab" data-toggle="tab" href="#requestSmsMessages" role="tab" aria-controls="requestSmsMessages" aria-selected="false">
                        <i class="fa fa-envelope-o me-1"></i> پیامهای دریافتی و ارسالی کاربر ({{ $requestSmsList->count() }})
                    </a>
                </li>
            </ul>

            <div class="tab-content pt-3" id="requestTabContent">
                <!-- Tab 1: Request Details -->
                <div class="tab-pane fade show active" id="requestDetails" role="tabpanel" aria-labelledby="details-tab">
                    <div class="table-responsive">
                        <span>وضعیت درخواست :</span>
                        <span>{{$request_rec::STATUS[$request_rec->status]}} </span>

                        <table class="table table-bordered table-striped mt-4">
                            <thead>
                            <tr>
                                <th>کالا</th>
                                <th>تعداد</th>
                                <th>موجودی</th>
                                <th> برند</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($request_rec->requestItems as $item)
                                <tr>
                                    <td>{{$item->name}}
                                        @if($request_rec->type == 1)-
                                            @if($item->pivot->product_attribute_id != null)
                                                {{\App\Models\ProductAttribute::findStoreRoomName($item->pivot->product_attribute_id)}}
                                                - <span>ویژگی :</span>
                                                {{\App\Models\ProductAttribute::find($item->pivot->product_attribute_id)->value}}
                                            @endif
                                        @endif
                                    </td>
                                    <td>@if(isset($item->pivot)) {{$item->pivot->number}}@endif</td>
                                    <td> {{ $item->stock }} </td>
                                    <td>{{ $item->brand }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                    @if($request_rec->status == 1)
                        <p>همکار گرامی : ارجاع به انباردار به منظور تایید و ثبت درخواست از جانب شما می باشد.</p>
                        <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/2">
                            <button type="button" class="btn btn-success">ارجاع به انباردار</button>
                        </a>
                    @endif
                    @if($request_rec->status == 2)
                        <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/1">
                            <button type="button" class="btn btn-info">پیش نویس</button>
                        </a>
                    @endif
                    @if($request_rec->status == 2)
                        @role('superadmin')
                        <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/3" title="تایید"
                           class="btn btn-success curpon">تایید</a>

                        <a title="رد" class="btn btn-danger curpon colorWhite"
                           data-toggle="modal" data-target="#rejectedStatusRequest{{$request_rec->id}}">رد</a>

                        @endrole
                        @if($request_rec->storeRoom->warehouse_keeper->id == auth()->user()->id)
                            <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/3" title="تایید"
                               class="badge badge-success">تایید</a>

                            <a title="رد" class="btn btn-danger curpon colorWhite"
                               data-toggle="modal" data-target="#rejectedStatusRequest{{$request_rec->id}}">رد</a>
                        @endif
                        <!-- rejectedStatusRequest -->
                        <div id="rejectedStatusRequest{{$request_rec->id}}" class="modal fade" role="dialog">
                            <div class="modal-dialog">
                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title"> پیام برای رد درخواست</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <label>پیام *</label>
                                        <textarea id="{{$request_rec->id}}" class="form-control rejectMessage"
                                                  name="message" rows="8" cols="5"
                                                  title="لطفا پیام را وارد کنید " required>{{old('message')}}</textarea>
                                        <br/>
                                        <button id="{{$request_rec->id}}" type="submit"
                                                class="btn btn-sm btn-success rejectedStatusRequest">ارسال
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @endif

                    @if($request_rec->status == 3)
                        <a href="/dashboard/changeStatusRequest/{{$request_rec->id}}/5" title="ارسال "
                           class="btn btn-success">ارسال </a>
                    @endif
                </div>

                <!-- Tab 2: User SMS Messages tied to Request -->
                <div class="tab-pane fade" id="requestSmsMessages" role="tabpanel" aria-labelledby="sms-messages-tab">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover text-center align-middle">
                            <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">جهت</th>
                                <th scope="col">نوع سرویس</th>
                                <th scope="col">فرستنده</th>
                                <th scope="col">گیرنده</th>
                                <th scope="col" class="text-right" style="min-width: 200px;">متن پیام</th>
                                <th scope="col">وضعیت</th>
                                <th scope="col">تاریخ و ساعت</th>
                                <th scope="col">شناسه رهگیری</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($requestSmsList as $msg)
                                <tr>
                                    <td>{{ $msg->id }}</td>
                                    <td>
                                        @if($msg->direction === 'inbound')
                                            <span class="badge badge-info px-2 py-1"><i class="fa fa-arrow-down me-1"></i> دریافتی</span>
                                        @else
                                            <span class="badge badge-success px-2 py-1"><i class="fa fa-arrow-up me-1"></i> ارسالی</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($msg->service_type === 'otp')
                                            <span class="badge badge-warning text-dark">OTP</span>
                                        @elseif($msg->service_type === 'advertising')
                                            <span class="badge badge-primary">تبلیغاتی</span>
                                        @else
                                            <span class="badge badge-secondary">عادی</span>
                                        @endif
                                    </td>
                                    <td style="direction: ltr;">{{ $msg->sender_phone ?: ($msg->senderNumber?->number ?: 'سیستم') }}</td>
                                    <td style="direction: ltr;">{{ $msg->recipient_phone ?: '-' }}</td>
                                    <td class="text-right" style="direction: rtl;">{{ $msg->content }}</td>
                                    <td>
                                        @if($msg->status === 'delivered' || $msg->delivery_status === 'delivered')
                                            <span class="badge badge-success px-2 py-1"><i class="fa fa-check me-1"></i> تحویل شد</span>
                                        @elseif($msg->status === 'sent')
                                            <span class="badge badge-info px-2 py-1"><i class="fa fa-paper-plane me-1"></i> ارسال شد</span>
                                        @elseif($msg->status === 'failed')
                                            <span class="badge badge-danger px-2 py-1" title="{{ $msg->error_reason }}"><i class="fa fa-times me-1"></i> ناموفق</span>
                                        @else
                                            <span class="badge badge-warning text-dark px-2 py-1">{{ $msg->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ verta($msg->created_at)->format('Y/m/d H:i') }}</td>
                                    <td style="direction: ltr; font-family: monospace;">{{ $msg->tracking_id ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-4 text-muted">
                                        <i class="fa fa-envelope-o fa-2x mb-2 text-muted"></i>
                                        <p class="mb-0">هیچ پیامک ارسالی یا دریافتی برای این درخواست ثبت نشده است.</p>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
@section('js')
@stop
