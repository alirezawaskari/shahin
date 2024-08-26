@extends('Admin.master')
@section('title')ویرایش تعرفه@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ویرایش تعرفه</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item"><a href="/{{$center->id}}">مرکز {{$center->title}}</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش تعرفه</li>
            </ol>
        </nav>
    </div>
</div>
<form id="form_data">
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label>عنوان</label>
                    <input class="form-control" value="{{$serviceTariff->name}}" readonly>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label>تعرفه</label>
                    <input class="form-control" value="{{$serviceTariff->tariff->title}}" readonly>
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>قیمت فروش مرکز (ریال)</label>
                    <input type="text" class="form-control" name="purchase_price" required
                        value="{{number_format($serviceTariff->purchase_price)}}" oninput="separatePrices(this)">
                </div>
                @permission('service_tariff_selling_price')
                <div class="col-12 col-md-4 mb-3">
                    <label>قیمت مصرف کننده (ریال)</label>
                    <input type="text" class="form-control" name="price" required
                        value="{{number_format($serviceTariff->price)}}" oninput="separatePrices(this)">
                </div>
                @endpermission
                <div class="col-12 col-md-4 mb-3">
                    <label>تعداد جلسات</label>
                    <input type="number" value="{{$serviceTariff->session_number}}" class="form-control" name="session_number" id="session_number1"
                        required min="1">
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label>طول دوره انجام (روز)</label>
                    <input type="number" value="{{$serviceTariff->treatment_duration}}" class="form-control" name="treatment_duration"
                        id="treatment_duration1" required min="1">
                </div>
            </div>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body table-responsive">
            <h5>فهرست همکاران تخصیص داده شده</h5>
            <hr>
            <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                    <tr>
                        <th>نام و نام خانوادگی</th>
                        <th>مبلغ سهم (ریال)</th>
                        <th>ثبت کننده</th>
                        <th>تاریخ ثبت</th>
                        <th>اقدام</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allocation_employee_share_centers as $key => $item)
                    <tr>
                        <td>{{ $item->user->name.' '.$item->user->family }}</td>
                        <td>{{ number_format($item->amount) }}</td>
                        <td>{{ $item->agent->name.' '.$item->agent->family }}</td>
                        <td>{{ verta($item->created_at)->format('Y/m/d') }}</td>
                        <td>
                            <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a>
                                <button data-id="{{ $item->id }}" type="button" class="allocation_employee_delete dropdown-item pointer">حذف</button>
                            </a>
                        </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @permission('allocationEmployeeShareCenter_create')
    <div class="card" id="newallocationemployeesharetpl">
        <div class="card-body">
            <h5>تخصیص سهم همکار جدید</h5>
            <hr>
            <div class="row">
                <div class="col-12 col-md-6 mb-3">
                    <label>انتخاب همکار</label>
                    <select class="form-control" name="employee[]">
                        <option>همکار مربوطه را انتخاب کنید</option>
                        @foreach ($employees as $item)
                        <option value="{{$item->id}}">{{$item->name.' '.$item->family}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-3">
                    <label>مبلغ مورد نظر (ریال)</label>
                    <input type="text" class="form-control" name="price[]"
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
        <button id="updateServiceTariff" type="button" class="btn btn-outline-success py-2 px-4">ثبت</button>
    </div>
</form>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
<script src="{{ asset('/assets/dashboard/js/serviceTariff.js?v=2.1') }}"></script>
@endsection
