@extends('Admin.master')
@section('title')تامین کننده جدید@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>تامین کننده جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">تامین کننده جدید</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('file-fastEdit')
            <a href="{{ route('supplier.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فهرست تامین کنندگان</span>
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
            <form class="form-horizontal" role="form" method="post" action="{{route('supplier.store')}}">
                @csrf

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-3 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ old('name') }}"
                               class="form-control"
                               name="name" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>مدیر</label>
                        <input type="text" value="{{ old('manager') }}"
                               class="form-control"
                               name="manager" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>شماره تماس</label>
                        <input type="text" value="{{ old('phone') }}"
                               class="form-control"
                               name="phone" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>انتخاب کاربر تامین کننده</label>
                        <select name="user_id" class="select2">
                            @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name.' '.$user->family}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label>آدرس</label>
                        <input type="text" value="{{ old('address') }}"
                               class="form-control"
                               name="address" required>
                    </div>

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
