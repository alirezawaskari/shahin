@extends('Admin.master')
@section('title')فهرست تامین کنندگان@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>فهرست تامین کنندگان</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست تامین کنندگان</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            <a href="{{ route('supplier.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>تامین کننده جدید</span>
            </a>
            <a href="{{ route('purchaseInvoice.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>فاکتور جدید</span>
            </a>
            <a href="{{ route('product.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-edit text-indigo-400" aria-hidden="true"></i>
                <span>محصول جدید</span>
            </a>
            <a href="{{ route('storeRoom.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>انبار جدید</span>
            </a>
        </div>
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>نام مدیر</th>
                        <th>شماره تماس</th>
                        <th>آدرس</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($suppliers as $key => $supplier)
                        <tr class="active" id="item_{{$supplier->id}}" style="cursor: move;">
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->manager }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->address }}</td>
                            <td>
                                <a href="{{ route('supplier.edit' , [$supplier->id]) }}"
                                   class="btn btn-primary">ویرایش
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @include('Admin.layouts.pagination')
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/pagination.js"></script>
@endsection
