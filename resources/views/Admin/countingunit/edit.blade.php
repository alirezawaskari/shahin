@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>ویرایش واحد</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش واحد </li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('file-fastEdit')
            <a href="{{ route('supplier.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>تامین کننده جدید</span>
            </a>
            @endpermission
            <a href="{{ route('purchaseInvoice.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>فاکتور جدید</span>
            </a>
            <a href="{{ route('product.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-edit text-indigo-400" aria-hidden="true"></i>
                <span>محصول جدید</span>
            </a>
            <a href="{{ route('storeRoom.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فهرست انبار</span>
            </a>
        </div>
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post"
                  action="{{route('countingUnit.update' , [$countingUnit->id ])}}">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ $countingUnit->title }}"
                               class="form-control"
                               name="title" required>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <label>نوع</label>
                        <select class="form-control" name="type">
                            <option value="packing" {{ $countingUnit->type == 'packing' ? 'selected' : '' }}>
                                واحد اصلی
                            </option>
                            <option value="counting" {{ $countingUnit->type == 'counting' ? 'selected' : '' }}>
                                واحد فرعی
                            </option>
                            <option value="measurement" {{ $countingUnit->type == 'measurement' ? 'selected' : '' }}>
                                واحد اندازه گیری مصرفی
                            </option>
                        </select>
                    </div>
                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
