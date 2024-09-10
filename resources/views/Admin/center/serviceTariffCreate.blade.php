@extends('Admin.master')
@section('title')ایجاد تعرفه@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد تعرفه جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="/{{$center->id}}">مرکز {{$center->title}}</a></li>
                <li class="breadcrumb-item active" aria-current="page">ایجاد تعرفه جدید</li>
            </ol>
        </nav>
    </div>
</div>
<form id="form_data">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-4 mb-3">
                    <label>عنوان</label>
                    <select class="form-control select2" name="name" id="serviceIndexTariff">
                        <option>عنوان تعرفه را تایپ کنید</option>
                        @foreach ($service_index_tariffs as $item)
                        <option value="{{$item->id}}">{{$item->title}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>انتخاب تعرفه</label>
                    <select class="form-control" name="tariff" id="tariff1">
                        @foreach ($tariffs as $tariff)
                            <option value="{{ $tariff->id }}">{{ $tariff->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>قیمت فروش مرکز (ریال)</label>
                    <input type="text" class="form-control" name="purchase_price" required
                        oninput="separatePrices(this)">
                </div>
                @permission('service_tariff_selling_price')
                <div class="col-12 col-md-4 mb-3">
                    <label>قیمت مصرف کننده (ریال)</label>
                    <input type="text" class="form-control" name="selling_price" required
                        oninput="separatePrices(this)">
                </div>
                @endpermission
                <div class="col-12 col-md-4 mb-3">
                    <label>پرداخت در چند مرحله</label>
                    <select class="form-control" name="multi_stage">
                        <option value="1" selected>1</option>
                        <option value="2">2</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>تعداد جلسات</label>
                    <input type="number" value="1" class="form-control" name="session_number" id="session_number1"
                        required min="1">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>طول دوره انجام (روز)</label>
                    <input type="number" value="1" class="form-control" name="treatment_duration"
                        id="treatment_duration1" required min="1">
                </div>
            </div>
        </div>
    </div>
    @permission('allocationEmployeeShareCenter_create')
    <div class="card" id="newallocationemployeesharetpl">
        <div class="card-body">
            <h5>تخصیص سهم همکار</h5>
            <hr>
            <div class="row">
                <div class="col-12 col-md-4 mb-3">
                    <label>انتخاب همکار</label>
                    <select class="form-control" name="employee[]">
                        <option>همکار مربوطه را انتخاب کنید</option>
                        @foreach ($employees as $item)
                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>مبلغ مورد نظر (ریال)</label>
                    <input type="text" class="form-control" name="price[]" required
                        oninput="separatePrices(this)">
                </div>
                <div class="col-12 mb-3 mb-3" id="delete1">
                </div>
            </div>
        </div>
    </div>
    <div id="newallocationemployeeshare">
        <div>
        </div>
    </div>
    <div class="col-md-12">
        <div id="addnew" class="btn badge-success mb-5">
            <a href="javascript:newAllocationEmployeeShareLink()" class="text-white"><i class="fa fa-plus"
                    aria-hidden="true"></i>
                افزودن همکار جدید </a>
        </div>
    </div>
    @endpermission
    <input type="hidden" name="center" value="{{ $center->id }}">
    <div class="col-12">
        <button id="createServiceTariff" type="button" class="btn btn-outline-success py-2 px-4">ثبت</button>
    </div>
</form>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
<script src="{{ asset('/assets/dashboard/js/serviceTariff.js?v=2.0') }}"></script>
@endsection
