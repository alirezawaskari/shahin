@extends('Admin.master')
@section('title')سفارش {{ $factor->resnumber }}@endsection

@php
    $shipped = $factor->is_shipped;
    $snapshot = $factor->factorAddress;
    $products_price = 0;
@endphp

@section('content')
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <div>
        <h3>
            سفارش {{ $factor->resnumber }}
            @if($shipped)
            <span class="badge badge-success">{{ \App\Models\Factor::SHIPPING_STATUS[\App\Models\Factor::SHIPPING_SHIPPED] }}</span>
            @else
            <span class="badge badge-warning">{{ \App\Models\Factor::SHIPPING_STATUS[\App\Models\Factor::SHIPPING_PENDING] }}</span>
            @endif
        </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('productOrder.index') }}">سفارشات کالا</a></li>
                <li class="breadcrumb-item active" aria-current="page">جزئیات سفارش</li>
            </ol>
        </nav>
    </div>
    <a href="/factor/{{ $factor->resnumber }}" target="_blank" class="btn btn-outline-primary mt-2 mt-md-0">مشاهده فاکتور</a>
</div>

@if($shipped)
<div class="alert alert-success">
    این سفارش در تاریخ {{ verta($factor->shipped_at)->format('Y/m/d H:i') }} ارسال شده و بسته شده است؛ آدرس، هزینه ارسال و کد رهگیری آن دیگر قابل تغییر نیست.
</div>
@endif

@include('Admin.layouts.errors')

<div class="row">
    {{-- ------------------------------ اطلاعات کاربر ------------------------------ --}}
    <div class="col-12 col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">اطلاعات خریدار</h5>
                <table class="table table-bordered mb-0">
                    <tr>
                        <th class="w-50">نام و نام خانوادگی</th>
                        <td>{{ trim(optional($factor->user)->name . ' ' . optional($factor->user)->family) ?: 'ثبت نشده' }}</td>
                    </tr>
                    <tr>
                        <th>موبایل</th>
                        <td>{{ optional($factor->user)->phone ?: 'ثبت نشده' }}</td>
                    </tr>
                    <tr>
                        <th>کد ملی</th>
                        <td>{{ optional($factor->user)->melicode ?: 'ثبت نشده' }}</td>
                    </tr>
                    <tr>
                        <th>پروفایل کاربر</th>
                        <td>
                            @if($factor->user)
                            <a href="/dashboard/user/{{ $factor->user->id }}/edit" target="_blank">مشاهده پروفایل</a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- ------------------------------ اطلاعات سفارش ------------------------------ --}}
    <div class="col-12 col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">اطلاعات سفارش</h5>
                <table class="table table-bordered mb-0">
                    <tr>
                        <th class="w-50">شماره سفارش</th>
                        <td>{{ $factor->resnumber }}</td>
                    </tr>
                    <tr>
                        <th>تاریخ ثبت سفارش</th>
                        <td>{{ verta($factor->created_at)->format('Y/m/d H:i') }}</td>
                    </tr>
                    <tr>
                        <th>وضعیت سفارش</th>
                        <td>{{ \App\Models\Factor::SHIPPING_STATUS[(int) $factor->shipping_status] }}</td>
                    </tr>
                    <tr>
                        <th>تاریخ ارسال</th>
                        <td>{{ $factor->shipped_at ? verta($factor->shipped_at)->format('Y/m/d H:i') : 'ارسال نشده' }}</td>
                    </tr>
                    <tr>
                        <th>کد رهگیری مرسوله</th>
                        <td>{{ $factor->tracking_code ?: 'ثبت نشده' }}</td>
                    </tr>
                    <tr>
                        <th>ارسال کننده</th>
                        <td>{{ $factor->shippedBy ? trim($factor->shippedBy->name . ' ' . $factor->shippedBy->family) : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ------------------------------ کالاهای سفارش ------------------------------ --}}
<div class="card mb-3">
    <div class="card-body table-responsive">
        <h5 class="mb-3">کالاهای سفارش</h5>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>تصویر</th>
                    <th>نام کالا</th>
                    <th>دسته بندی</th>
                    <th>تنوع</th>
                    <th>کد کالا</th>
                    <th>تعداد</th>
                    <th>قیمت واحد (ریال)</th>
                    <th>قیمت کل (ریال)</th>
                </tr>
            </thead>
            <tbody>
                @php $row = 0; @endphp
                @foreach($factor->factorItems as $factor_item)
                @foreach($factor_item->products as $product_row)
                @php
                    $row++;
                    $row_price = (int) $product_row->price * max((int) $product_row->count, 1);
                    $products_price = $products_price + $row_price;
                    //AttributeItem::getAttributeItem() dereferences find() blindly, and rows
                    //saved without a variant carry attribute_item_id = 0 - resolve it safely here
                    $variant = $product_row->attribute_item_id
                        ? optional(\App\Models\AttributeItem::find($product_row->attribute_item_id))->title
                        : null;
                @endphp
                <tr>
                    <td>{{ $row }}</td>
                    <td>
                        @if($product_row->product && $product_row->product->image)
                        <img src="{{ $product_row->product->image }}" alt="{{ $product_row->product->name }}" height="60">
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ optional($product_row->product)->name ?: 'حذف شده' }}</td>
                    <td>{{ optional($factor_item->factoritemable)->title ?: '-' }}</td>
                    <td>{{ $variant ?: '-' }}</td>
                    <td>{{ optional($product_row->product)->id ?: '-' }}</td>
                    <td>{{ $product_row->count }}</td>
                    <td>{{ number_format($product_row->price) }}</td>
                    <td>{{ number_format($row_price) }}</td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-12 col-md-6">
                <table class="table table-bordered mb-0">
                    <tr>
                        <th class="w-50">جمع کالاها (ریال)</th>
                        <td>{{ number_format($products_price) }}</td>
                    </tr>
                    <tr>
                        <th>مبلغ پرداخت شده سفارش (ریال)</th>
                        <td>{{ number_format($factor->price) }}</td>
                    </tr>
                    <tr>
                        <th>هزینه ارسال (ریال)</th>
                        <td>{{ number_format($factor->shipping_cost) }}</td>
                    </tr>
                    <tr class="font-weight-bold">
                        <th>مبلغ کل سفارش (ریال)</th>
                        <td>{{ number_format($factor->order_total_price) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ------------------------------ آدرس ارسال سفارش ------------------------------ --}}
<div class="card mb-3">
    <div class="card-body">
        <h5 class="mb-3">آدرس ارسال سفارش</h5>

        @if($snapshot)
        <div class="alert alert-info mb-4">
            <div><strong>گیرنده:</strong> {{ $snapshot->recipient ?: '-' }} @if($snapshot->recipient_mobile) - {{ $snapshot->recipient_mobile }} @endif</div>
            <div><strong>آدرس:</strong> {{ $snapshot->full_address }}</div>
            <small class="text-muted">این آدرس روی خود سفارش ذخیره شده است و با تغییر یا حذف آدرس های کاربر تغییر نمی کند.</small>
        </div>
        @else
        <div class="alert alert-warning mb-4">برای این سفارش هنوز آدرس ارسال ثبت نشده است.</div>
        @endif

        @if(!$shipped)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">آدرس های ثبت شده کاربر</h6>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-bs-toggle="modal"
                data-target="#addAddressModal" data-bs-target="#addAddressModal">افزودن آدرس جدید</button>
        </div>

        <form method="post" action="{{ route('productOrder.setAddress', [$factor->resnumber]) }}">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>انتخاب</th>
                            <th>گیرنده</th>
                            <th>موبایل گیرنده</th>
                            <th>آدرس</th>
                            <th>کدپستی</th>
                            <th>اقدام</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($addresses as $address)
                        <tr>
                            <td>
                                <input type="radio" name="address_id" value="{{ $address->id }}"
                                    {{ (int) $factor->address_id === (int) $address->id ? 'checked' : '' }}>
                            </td>
                            <td>{{ trim($address->recipient_name . ' ' . $address->recipient_family) }}</td>
                            <td>{{ $address->recipient_mobile }}</td>
                            <td>
                                {{ optional($address->province)->name }} - {{ optional($address->city)->name }} -
                                {{ $address->address }}
                                @if($address->plaque) - پلاک {{ $address->plaque }} @endif
                                @if($address->unit) - واحد {{ $address->unit }} @endif
                            </td>
                            <td>{{ $address->postal_code }}</td>
                            <td class="text-nowrap">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal"
                                    data-bs-toggle="modal" data-target="#editAddressModal{{ $address->id }}"
                                    data-bs-target="#editAddressModal{{ $address->id }}">ویرایش</button>
                                <button type="button" class="btn btn-outline-danger btn-sm address-delete"
                                    data-url="{{ route('productOrder.destroyAddress', [$factor->resnumber, $address->id]) }}">حذف</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">این کاربر هیچ آدرسی ثبت نکرده است.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(count($addresses))
            <button type="submit" class="btn btn-primary">ثبت آدرس انتخاب شده برای این سفارش</button>
            @endif
        </form>
        @endif
    </div>
</div>

{{-- ------------------------------ هزینه ارسال و ارسال سفارش ------------------------------ --}}
<div class="row">
    <div class="col-12 col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">هزینه ارسال</h5>
                @if($shipped)
                <p class="mb-0">{{ number_format($factor->shipping_cost) }} ریال</p>
                @else
                <form method="post" action="{{ route('productOrder.shippingCost', [$factor->resnumber]) }}">
                    @csrf
                    <div class="form-group">
                        <label for="shipping_cost">هزینه ارسال (ریال)</label>
                        <input type="text" class="form-control" id="shipping_cost" name="shipping_cost"
                            value="{{ number_format($factor->shipping_cost) }}" oninput="separatePrices(this)">
                    </div>
                    <button type="submit" class="btn btn-primary">ثبت هزینه ارسال</button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="mb-3">ارسال سفارش</h5>
                @if($shipped)
                <p class="mb-1">تاریخ ارسال: {{ verta($factor->shipped_at)->format('Y/m/d H:i') }}</p>
                <p class="mb-0">کد رهگیری: {{ $factor->tracking_code ?: 'ثبت نشده' }}</p>
                @else
                <form method="post" action="{{ route('productOrder.ship', [$factor->resnumber]) }}" id="ship_form">
                    @csrf
                    <div class="form-group">
                        <label for="tracking_code">کد رهگیری مرسوله (اختیاری)</label>
                        <input type="text" class="form-control" id="tracking_code" name="tracking_code"
                            value="{{ $factor->tracking_code }}">
                    </div>
                    @if(!$snapshot)
                    <div class="alert alert-warning">تا زمانی که آدرس ارسال ثبت نشود امکان ارسال سفارش وجود ندارد.</div>
                    @endif
                    <button type="submit" class="btn btn-success" id="ship_button" {{ $snapshot ? '' : 'disabled' }}>
                        ارسال سفارش
                    </button>
                    <small class="d-block text-muted mt-2">با ارسال سفارش، وضعیت آن به «ارسال شده» تغییر کرده و سفارش بسته می شود.</small>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$shipped)
{{-- ------------------------------ مودال افزودن آدرس ------------------------------ --}}
<div class="modal fade" id="addAddressModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن آدرس جدید</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('productOrder.storeAddress', [$factor->resnumber]) }}">
                @csrf
                <div class="modal-body">
                    @include('Admin.productOrder.addressForm', ['address' => null, 'provinces' => $provinces, 'areas' => $areas])
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ثبت آدرس و انتخاب برای سفارش</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ------------------------------ مودال های ویرایش آدرس ------------------------------ --}}
@foreach($addresses as $address)
<div class="modal fade" id="editAddressModal{{ $address->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ویرایش آدرس</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post" action="{{ route('productOrder.updateAddress', [$factor->resnumber, $address->id]) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    @include('Admin.productOrder.addressForm', ['address' => $address, 'provinces' => $provinces, 'areas' => $areas])
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif
@stop

@section('js')
{{-- layouts.js provides separatePrices() used by the shipping cost input --}}
<script src="{{ asset('assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/productOrderShow.js?v=1.0') }}"></script>
@endsection
