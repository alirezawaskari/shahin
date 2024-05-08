@extends('Admin.master')
@section('title')تخصیص درصد سود همکاران@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>تخصیص درصد سود همکاران</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">تخصیص درصد سود همکاران</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('percentageAllocationEmployee.all')
            <a href="{{ route('percentageAllocationEmployee.index') }}"
                class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فهرست درصد سود تخصیص داده شده </span>
            </a>
            @endpermission
        </div>
    </div>
</div>
<form class="form-horizontal" role="form" method="post" action="{{route('percentageAllocationEmployee.store')}}">
    @csrf

    @include('Admin.layouts.errors')
    <div class="card position-relative">
        <div class="col-12 mb-3 mt-2 service_category">
            <label class="w-100">انتخاب دسته بندی</label>
            <button type="button" class="btn btn-primary mb-3" onclick="selectAll('service_category_id')">انتخاب
                همه</button>
            <button type="button" class="btn btn-danger mb-3" onclick="deselectAll('service_category_id')">حذف
                همه</button>
            <select class="form-control select2" name="service_category[]" id="service_category_id"
                onchange="selectByCategory()" multiple>
                <option disabled> انتخاب دسته بندی</option>
                @foreach ($service_categories as $category)
                <option value="{{ $category->id }}">{{
                    $category->title }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card position-relative">
        <div class="col-12 mb-3 mt-2 service">
            <label class="w-100">انتخاب خدمت</label>
            <button type="button" class="btn btn-primary mb-3" onclick="selectAll('service_id')">انتخاب همه</button>
            <button type="button" class="btn btn-danger mb-3" onclick="deselectAll('service_id')">حذف همه</button>
            <select class="form-control select2" name="service[]" id="service_id" onchange="selectByService()" multiple>
                <option disabled>انتخاب خدمات</option>
                @foreach ($services as $service)
                <option value="{{ $service->id }}">{{
                    $service->title }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card position-relative">
        <div class="col-12 mb-3 mt-2 center">
            <label class="w-100">انتخاب مرکز</label>
            <button type="button" class="btn btn-primary mb-3" onclick="selectAll('center_id')">انتخاب همه</button>
            <button type="button" class="btn btn-danger mb-3" onclick="deselectAll('center_id')">حذف همه</button>
            <select class="form-control select2" name="center[]" id="center_id" multiple>
                <option disabled>انتخاب مرکز</option>
                @foreach ($centers as $center)
                <option value="{{ $center->id }}">{{
                    $center->title }}
                </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="card position-relative">
        <div class="col-12 row">
            <div class="col-12 col-md-4 mb-3 mt-2">
                <label class="w-100">انتخاب همکاران مربوطه</label>
                <select class="form-control select2" name="employee" id="employee_id">
                    <option selected disabled> انتخاب همکاران مربوطه</option>
                    @foreach ($employees as $employee)
                    <option value="{{ $employee->user->id }}">{{
                        $employee->user->name.' '.$employee->user->family}}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <label class="w-100">انتخاب تاریخ شروع</label>
                <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                </date-picker>
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <label class="w-100">انتخاب تاریخ پایان</label>
                <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate"></date-picker>
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <input type="checkbox" name="register_price" class="checkbox_type"> هزینه ثبت نام
                <input type="number" name="register_price_percent" class="form-control mt-2 d-none"
                    placeholder="درصد تخصیص داده شده از هزینه ثبت نام" min="0" max="100">
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <input type="checkbox" name="total_price" class="checkbox_type"> جمع کل فاکتور
                <input type="number" name="total_price_percent" class="form-control mt-2 d-none"
                    placeholder="درصد تخصیص داده شده از جمع کل فاکتور" min="0" max="100">
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <input type="checkbox" name="company_profit" class="checkbox_type"> سود شرکت
                <input type="number" name="company_profit_percent" class="form-control mt-2 d-none"
                    placeholder="درصد تخصیص داده شده از سود شرکت" min="0" max="100">
            </div>
            <div class="col-12 mb-3 mt-2">
                <input type="submit" class="btn btn-success border-0" value="ثبت">
            </div>
        </div>
    </div>
</form>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/percentageAllocationEmployee.js') }}"></script>
@endsection
