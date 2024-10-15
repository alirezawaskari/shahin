@extends('Admin.master')
@section('title')ایجاد فاکتور خرید@endsection

@section('content')
    <div class="page-header">
        <div>
            <h3>فاکتور جدید</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فاکتور جدید</li>
                </ol>
            </nav>
        </div>
        @include('Admin.purchaseinvoice.layouts')
    </div>
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('purchaseInvoice.store')}}" enctype="multipart/form-data">
                @csrf

                @include('Admin.layouts.errors')
                <input name="user_id" type="hidden" value="{{ auth()->user()->id }}">
                <div class="row mb-3">
                @permission('purchase-invoice')
                <input name="supplier_id" type="hidden" value="{{ auth()->user()->supplier->id }}">
                @endpermission
                @permission('purchase-invoice-detail')
                <div class="col-12 col-md-2 mb-3">
                        <label>تامین کننده</label>
                        <select class="form-control" name="supplier_id">
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endpermission
                    <div class="col-12 col-md-2 mb-3">
                        <label>نمایندگی درخواست کننده</label>
                        <select class="form-control" name="representation_id">
                            <option value="">نمایندگی کل</option>
                            @foreach($representations as $representation)
                                <option value="{{ $representation->id }}">{{ $representation->fa_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 row mt-3">
                        <div class="col-12 mb-3">
                            <label>لیست درخواست کالا</label>
                            <textarea type="text" class="form-control" name="list"></textarea>
                        </div>
                    </div>
                </div>
                <p class="text-warning">راهنمایی : همکار گرامی لیست درخواست خود را در قسمت لیست درخواست کالا وارد نموده و بر روی ثبت کلیک کنید.</p>
                <div class="col-12">
                    <input type="submit" class="btn btn-success border-0" value="ثبت">
                </div>
            </form>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/layouts.js') }}"></script>
@endsection
