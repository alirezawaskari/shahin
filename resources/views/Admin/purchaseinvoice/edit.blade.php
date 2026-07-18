@extends('Admin.master')
@section('title')ویرایش فاکتور خرید@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ویرایش فاکتور</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش فاکتور</li>
            </ol>
        </nav>
    </div>
    @include('Admin.purchaseinvoice.layouts')
</div>
<div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ثبت گزارش خرابی کالا</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('purchaseInvoice.report' , [$purchaseInvoice->id ]) }}" method="POST">
                <div class="modal-body">
                    @csrf
                    <textarea required class="form-control" name="report" id="" cols="30" rows="10"
                        placeholder="گزارش خود را بنویسید..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ثبت</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('Admin.purchaseinvoice.info')
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $purchaseInvoice->status != 9 ? 'show active' : 'show'}}" id="factor-tab"
                    data-toggle="tab" href="#factor" role="tab" aria-controls="factor" aria-selected="false">جزئیات
                    فاکتور</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $purchaseInvoice->status == 9 ? 'show active' : 'show'}}" id="payment-factor-tab"
                    data-toggle="tab" href="#payment-factor" role="tab" aria-controls="payment-factor"
                    aria-selected="false">پرداخت فاکتور</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="contact-tab" data-toggle="tab" href="#contact" role="tab"
                    aria-controls="contact" aria-selected="false">گزارشات</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 {{ $purchaseInvoice->status != 9 ? 'active show' : ''}}" id="factor"
                role="tabpanel" aria-labelledby="factor-tab">
                @if($purchaseInvoice->status == 5)
                <div class="card border">
                    <div class="card-body">
                        <div class="card-header row col-12 col-md-12 mb-3">
                            <div class="col-md-11">
                                <h4>ارسال کالا</h4>
                            </div>
                            <div class="col-md-1">
                                <span class="badge badge-{{$status_class}}">{{$status}}</span>
                            </div>
                        </div>
                        <form class="form-horizontal" role="form" method="post"
                            action="{{route('purchaseInvoice.sendProduct' , [$purchaseInvoice->id ])}}"
                            enctype="multipart/form-data">
                            @csrf

                            @include('Admin.layouts.errors')
                            <div class="col-12 col-md-4 mb-2">
                                <div class="card">
                                    {{-- truthiness, not isset(): post_image is an accessor returning ''
                                         when there is no image, and isset('') is true - which hid the
                                         upload widget in the @else below. --}}
                                    @if($purchaseInvoice->post_image)
                                    <a href="{{ asset($purchaseInvoice->post_image) }}" target="_blank">
                                        <img src="{{ $purchaseInvoice->post_image }}" class="card-img-top"
                                            height="150px">
                                    </a>
                                    @if($purchaseInvoice->mediaIsLegacy('post_image'))
                                    <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                    @endif
                                    @else
                                    <div id="post_image_src" class="show">
                                        <img class="card-img-top" height="150px">
                                    </div>
                                    <div class="card-body">
                                        <label>تصویر رسید ارسال کالا</label>
                                        <br>
                                        <label for="post_image" id="up-label" class="btn badge-secondary"><i
                                                class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب
                                            تصویر </label>
                                        <input name="post_image" type="file" id="post_image"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png" required>
                                        <input type="hidden" id="post_image_path" class="path" name="url_post_image">
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if(!$purchaseInvoice->pay_postage)
                            <div class="col-12 col-md-2 mb-3">
                                <label> هزینه ارسال کالا(ریال)</label>
                                <input class="form-control" name="postage" type="text" min="0"
                                    value="{{ number_format($purchaseInvoice->postage) }}"
                                    oninput="separatePrices(this)" required>
                            </div>
                            <p class="text-warning">توجه : هزینه ارسال کالا به عهده خریدار می باشد بنابراین
                                مبلغ هزینه
                                ارسال کالا را وارد کنید.</p>
                            @endif
                            <div class="col-12">
                                <input type="submit" class="btn btn-success border-0" value="ثبت">
                            </div>
                        </form>
                    </div>
                </div>
                @endif
                @include('Admin.purchaseinvoice.factor')
                <div class="col-12 row">
                    {{-- truthiness, not isset(): see the note above. --}}
                    @if($purchaseInvoice->post_image)
                    <div class="card col-12 col-md-3 p-2">
                        <a href="{{ asset($purchaseInvoice->post_image) }}" target="_blank">
                            <img src="{{ $purchaseInvoice->post_image }}" class="card-img-top" height="150px">
                        </a>
                        @if($purchaseInvoice->mediaIsLegacy('post_image'))
                        <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                        @endif
                        <div class="card-body text-center">
                            <h5>تصویر رسید ارسال کالا</h5>
                        </div>
                    </div>
                    @endif
                    @if(isset($purchaseInvoice->payment_receipt))
                    <div class="card col-12 col-md-3 p-2">
                        <a href="{{ asset($purchaseInvoice->payment_receipt) }}" target="_blank">
                            <img src="{{ $purchaseInvoice->payment_receipt }}" class="card-img-top" height="150px">
                        </a>
                        <div class="card-body text-center">
                            <h5>تصویر رسید پرداخت وجه</h5>
                        </div>
                    </div>
                    @endif
                </div>
                @include('Admin.purchaseinvoice.confirm')
            </div>
            <div class="tab-pane fade p-t-20 {{ $purchaseInvoice->status == 9 ? 'active show' : ''}}"
                id="payment-factor" role="tabpanel" aria-labelledby="payment-factor-tab">
                @include('Admin.purchaseinvoice.payments')
            </div>
            <div class="tab-pane fade p-t-20" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                <span>گزارشات فاکتور</span>
                <span class="btn btn-success py-1 px-2" data-toggle="modal" data-target="#commentModal"> ثبت گزارش خرابی جدید</span>
                <div class="mt-4">
                    @if(count($reports) > 0)
                    @foreach($reports as $item)
                    <div class="bg-light rounded p-3 mb-3">
                        <div class="panel-heading">
                            <span class="d-inline-block">{{ $item->user->name .' '.$item->user->family }}</span>
                            <span class="text-black-50">{{ verta($item->created_at)->format('%d %B ، %Y - H:i:s')
                                }}</span>
                        </div>
                        <p class="mt-4">
                            {!! $item->report !!}
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
@if(isset($purchaseInvoice->values))
<div class="card">
    <div class="card-body">
        <h4>تاییدیه های فاکتور</h4>
        <hr>
        <div class="row">
            @foreach($purchaseInvoice->values as $key => $value)
            <div class="card col-12 col-md-3 p-2">
                <a href="{{ App\Models\User::getUser($value)->imgsignature }}" target="_blank">
                    <img src="{{ App\Models\User::getUser($value)->imgsignature }}" class="card-img-top" height="150px">
                </a>
                <div class="card-body text-center">
                    <h5>{{ 'تائیدیه '.App\Models\Role::where('name' , $key)->first()->display_name.' : '.
                        App\Models\User::getUser($value)->name.' '.App\Models\User::getUser($value)->family
                        }}</h5>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
@stop
@section('js')
<script src="{{ asset('assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/purchaseInvoice.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/factorModal.js?v=1.0') }}"></script>
@endsection
