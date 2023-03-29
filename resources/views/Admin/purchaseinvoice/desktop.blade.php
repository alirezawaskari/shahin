@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>فهرست درخواست ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست درخواست ها</li>
            </ol>
        </nav>
    </div>
    @include('Admin.purchaseinvoice.layouts')
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-md">
                <thead>
                    <tr>
                        <th>شماره فاکتور</th>
                        <th> درخواست کننده</th>
                        <th>مبلغ فاکتور (ریال)</th>
                        <th>وضعیت</th>
                        <th>اقدام</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase_invoices as $key => $purchase_invoice)
                    <tr class="active" id="item_{{$purchase_invoice->id}}">
                        <td>{{ $purchase_invoice->id }}</td>
                        <td>{{ $purchase_invoice->representation->fa_name }}</td>
                        <td>{{ number_format($purchase_invoice->total_price) }}</td>
                        <td>{{ App\Models\PurchaseInvoice::status($purchase_invoice->status) }}</td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{ route('purchaseInvoice.edit' , [$purchase_invoice->id]) }}"><button type="button" class="dropdown-item pointer">ویرایش</button></a>
                                    <a href="{{ url('dashboard/create-product-purchaseInvoice' , ['id' => $purchase_invoice->id]) }}"><button type="button" class="dropdown-item pointer">ثبت کالا</button></a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @include('Admin.layouts.pagination')
        </div>
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
@endsection
