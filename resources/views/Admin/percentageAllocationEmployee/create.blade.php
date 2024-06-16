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
            <select class="select form-control select2" onchange="selectByCategory() , checkSelectValue('service_category_id')" name="service_category[]" id="service_category_id"
                multiple>
                <option disabled> انتخاب دسته بندی</option>
                <option value="select_all">انتخاب همه</option>
                <option value="de_select_all">حذف همه</option>
                @foreach ($service_categories as $category)
                <option value="{{ $category->id }}">{{
                    $category->title }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 mb-3 mt-2 service">
            <label class="w-100">انتخاب خدمت</label>
            <select class="form-control select2" onchange="selectByService() , checkSelectValue('service_id')" name="service[]" id="service_id" multiple>
                <option disabled>انتخاب خدمات</option>
                <option value="select_all">انتخاب همه</option>
                <option value="de_select_all">حذف همه</option>
                @foreach ($services as $service)
                <option value="{{ $service->id }}">{{
                    $service->title }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-12 mb-3 mt-2 center">
            <label class="w-100">انتخاب مرکز</label>
            <select class="form-control select2" onchange="checkSelectValue('center_id')" name="center[]" id="center_id" multiple>
                <option disabled>انتخاب مرکز</option>
                <option value="select_all">انتخاب همه</option>
                <option value="de_select_all">حذف همه</option>
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
            <div class="col-12 col-md-6 mb-2 mt-2">
                <label>نقش مربوطه</label>
                <select class="form-control select2" name="role" id="role" required>
                    <option value="all">انتخاب کنید</option>
                    @foreach(\App\Models\Role::all() as $role)
                    <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-6 mb-2 mt-2">
                <label>همکار مربوطه</label>
                <select class="form-control select2" name="employee" id="users" required>
                    <option value="all">انتخاب کنید</option>
                </select>
            </div>
            <div class="col-12 col-md-6 mb-3 mt-2">
                <label class="w-100">انتخاب تاریخ شروع</label>
                <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                </date-picker>
            </div>
            <div class="col-12 col-md-6 mb-3 mt-2">
                <label class="w-100">انتخاب تاریخ پایان</label>
                <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate"></date-picker>
            </div>
            <div class="col-12 mb-3 mt-2 mr-3 card-header">
                <h5>تعیین نوع برداشت درآمد و تخصیص سود</h5>
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <input type="checkbox" name="register_price" class="checkbox_type"> برداشت از محل ثبت نام
                <input type="number" name="register_price_percent" class="form-control mt-2 d-none"
                    placeholder="درصد تخصیص داده شده از هزینه ثبت نام" min="0" max="100" step="0.01">
                <select name="register_price_factor_sender" id="register_price"
                    class="form-control mt-2 d-none factor_sender">
                    <option selected disabled>ارسال کننده فاکتور</option>
                    <option value="all">همه</option>
                    <option value="employee_factors">مرتبط با همکار</option>
                </select>
                <select name="register_price_limit_type" class="form-control mt-2 d-none limit_type">
                    <option selected disabled>انتخاب نوع محدودیت اعمال تسهیم</option>
                    <option value="null">محدودیتی ندارد</option>
                    <option value="count">براساس تعداد</option>
                    <option value="price">براساس مبلغ</option>
                </select>
                <div class="d-none mt-2" id="register_price_limit_type_count">
                    حداقل تعداد پرداختی موفق<input type="number" min="1" name="register_price_min_limit_count"
                        class="form-control mt-2">
                    حداکثر تعداد پرداختی موفق<input type="number" min="1" name="register_price_max_limit_count"
                        class="form-control mt-2">
                </div>
                <div class="d-none mt-2" id="register_price_limit_type_price">
                    حداقل جمع مبالغ پرداختی موفق<input type="text" name="register_price_min_limit_price" class="form-control mt-2"
                        oninput="separatePrices(this)">
                    حداکثر جمع مبالغ پرداختی موفق<input type="text" name="register_price_max_limit_price" class="form-control mt-2"
                        oninput="separatePrices(this)">
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <input type="checkbox" name="company_profit" class="checkbox_type"> برداشت از محل کارمزد تسهیلات
                <input type="number" name="company_profit_percent" class="form-control mt-2 d-none"
                    placeholder="درصد تخصیص داده شده از کارمزد تسهیلات" min="0" max="100" step="0.01">
                <select name="company_profit_factor_sender" id="company_profit"
                    class="form-control mt-2 d-none factor_sender">
                    <option selected disabled>ارسال کننده فاکتور</option>
                    <option value="all">همه</option>
                    <option value="employee_factors">مرتبط با همکار</option>
                </select>
                <select name="company_profit_limit_type" class="form-control mt-2 d-none limit_type">
                    <option selected disabled>انتخاب نوع محدودیت اعمال تسهیم</option>
                    <option value="null">محدودیتی ندارد</option>
                    <option value="count">براساس تعداد</option>
                    <option value="price">براساس مبلغ</option>
                </select>
                <div class="d-none mt-2" id="total_price_limit_type_count">
                    حداقل تعداد پرداختی موفق<input type="number" min="1" name="total_price_min_limit_count"
                        class="form-control mt-2">
                    حداکثر تعداد پرداختی موفق<input type="number" min="1" name="total_price_max_limit_count"
                        class="form-control mt-2">
                </div>
                <div class="d-none mt-2" id="total_price_limit_type_price">
                    حداقل جمع مبالغ پرداختی موفق<input type="text" name="total_price_min_limit_price" class="form-control mt-2"
                        oninput="separatePrices(this)">
                    حداکثر جمع مبالغ پرداختی موفق<input type="text" name="total_price_max_limit_price" class="form-control mt-2"
                        oninput="separatePrices(this)">
                </div>
            </div>
            <div class="col-12 col-md-4 mb-3 mt-2">
                <input value="all_total_price" type="radio" name="total_price" class="checkbox_type"> برداشت از جمع کل
                فاکتور
                <input value="income_total_price" type="radio" name="total_price" class="checkbox_type"> برداشت از محل
                درآمد فاکتور
                <input type="number" name="total_price_percent" class="form-control mt-2 d-none"
                    placeholder="درصد تخصیص داده شده از هزینه فاکتور" min="0" max="100" step="0.01">
                <select name="total_price_factor_sender" id="total_price"
                    class="form-control mt-2 d-none factor_sender">
                    <option selected disabled>ارسال کننده فاکتور</option>
                    <option value="all">همه</option>
                    <option value="employee_factors">مرتبط با همکار</option>
                </select>
                <select name="total_price_limit_type" class="form-control mt-2 d-none limit_type">
                    <option selected disabled>انتخاب نوع محدودیت اعمال تسهیم</option>
                    <option value="null">محدودیتی ندارد</option>
                    <option value="count">براساس تعداد</option>
                    <option value="price">براساس مبلغ</option>
                </select>
                <div class="d-none mt-2" id="total_price_limit_type_count">
                    حداقل تعداد پرداختی موفق<input type="number" min="1" name="total_price_min_limit_count"
                        class="form-control mt-2">
                    حداکثر تعداد پرداختی موفق<input type="number" min="1" name="total_price_max_limit_count"
                        class="form-control mt-2">
                </div>
                <div class="d-none mt-2" id="total_price_limit_type_price">
                    حداقل جمع مبالغ پرداختی موفق<input type="text" name="total_price_min_limit_price" class="form-control mt-2"
                        oninput="separatePrices(this)">
                    حداکثر جمع مبالغ پرداختی موفق<input type="text" name="total_price_max_limit_price" class="form-control mt-2"
                        oninput="separatePrices(this)">
                </div>
            </div>
        </div>
        <div class="col-12 mb-3 mt-2">
            <input type="submit" class="btn btn-success border-0" value="ثبت">
        </div>
    </div>
</form>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/layouts.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/percentageAllocationEmployee.js') }}"></script>
@endsection
