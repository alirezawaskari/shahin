@extends('Admin.master')
@section('title')محاسبه اقساط@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>محاسبه اقساط</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">محاسبه اقساط</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link show active" id="total-price-tab" data-toggle="tab" href="#total_price" role="tab"
                    aria-controls="total_price" aria-selected="false">جمع کل</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="prepayment-price-tab" data-toggle="tab" href="#prepayment_price" role="tab"
                    aria-controls="prepayment_price" aria-selected="false">پیش پرداخت</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="installment-price-tab" data-toggle="tab" href="#installment_price"
                    role="tab" aria-controls="installment_price" aria-selected="false">مبلغ قسط</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="total_price" role="tabpanel"
                aria-labelledby="total-price-tab">
                <div class="card calculate">
                    <div class="card-body">
                        <div class="col-12 border-bottom-white mb-4">
                            <form id="calculate1">
                                <input type="hidden" name="type" value="calculateByTotalPrice">
                                <div class="row">
                                    <div class="col-12 col-md-3">
                                        <label>طرح مورد نظر</label>
                                        <select class="form-control" name="financial_plans_type" required>
                                            <option value="all">انتخاب طرح مورد نظر</option>
                                            @foreach($financial_plans_types as $financial_plans_type)
                                            <option value="{{ $financial_plans_type->id }}">{{
                                                $financial_plans_type->title
                                                }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>خدمت</label>
                                        <select class="form-control" name="service" required id="service1">
                                            <option value="none">انتخاب خدمت</option>
                                            @foreach(\App\Models\Service::all() as $service)
                                            @if($service->percent > 0)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endif @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>دسته بندی کالا</label>
                                        <select class="form-control" name="product_category" id="product_category1">
                                            <option value="none">انتخاب دسته بندی کالا</option>
                                            @foreach($product_categories as $product_category)
                                            @if($product_category->percent > 0)
                                            <option value="{{ $product_category->id }}">{{ $product_category->title }}
                                            </option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>جمع کل (ریال)</label>
                                        <input type="text" name="total_price" class="form-control classname"
                                            oninput="separatePrices(this)">
                                    </div>
                                </div>
                                <div class="col-12 mt-5">
                                    <button class="btn btn-success px-4 mb-3" onclick="calculate('1')">محاسبه</button>
                                </div>
                            </form>
                        </div>

                        <div class="col-12 card text-dark p-4 result-calculate">
                            <div class="row">
                                <div class="col-12 col-md-2 text-center">
                                    <label class="font-weight-800 border-bottom pb-3"> جمع کل(ریال)</label>
                                    <h4 id="totalPrice1" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ پیش پرداخت (ریال)</label>
                                    <h4 id="prepayment1" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ قسط (ریال)</label>
                                    <h4 id="installmentsPrice1" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">تعداد قسط</label>
                                    <h4 id="installmentsNum1" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">جمع تسهیلات (ریال)</label>
                                    <h4 id="loan1" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ ضمانت (ریال)</label>
                                    <h4 id="guarantee1" class="mt-2"></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="prepayment_price" role="tabpanel"
                aria-labelledby="prepayment-price-tab">
                <div class="card calculate">
                    <div class="card-body">
                        <div class="col-12 border-bottom-white mb-4">
                            <form id="calculate2">
                                <input type="hidden" name="type" value="calculateByPrepayment">
                                <div class="row">
                                    <div class="col-12 col-md-3">
                                        <label>طرح مورد نظر</label>
                                        <select class="form-control" name="financial_plans_type" required>
                                            <option value="all">انتخاب طرح مورد نظر</option>
                                            @foreach($financial_plans_types as $financial_plans_type)
                                            <option value="{{ $financial_plans_type->id }}">{{
                                                $financial_plans_type->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>خدمت</label>
                                        <select class="form-control" name="service" id="service2">
                                            <option value="none">انتخاب خدمت</option>
                                            @foreach(\App\Models\Service::all() as $service)
                                            @if($service->percent > 0)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endif @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>دسته بندی کالا</label>
                                        <select class="form-control" name="product_category" id="product_category2">
                                            <option value="none">انتخاب دسته بندی کالا</option>
                                            @foreach($product_categories as $product_category)
                                            @if($product_category->percent > 0)
                                            <option value="{{ $product_category->id }}">{{ $product_category->title
                                                }}
                                            </option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>پیش پرداخت (ریال)</label>
                                        <input type="text" name="prepayment" class="form-control classname"
                                            oninput="separatePrices(this)">
                                    </div>
                                </div>
                                <div class="col-12 mt-5">
                                    <button class="btn btn-success px-4 mb-3" onclick="calculate('2')">محاسبه</button>
                                </div>
                            </form>
                        </div>

                        <div class="col-12 card text-dark p-4 result-calculate">
                            <div class="row">
                                <div class="col-12 col-md-2 text-center">
                                    <label class="font-weight-800 border-bottom pb-3">جمع کل (ریال)</label>
                                    <h4 id="totalPrice2" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ پیش پرداخت (ریال)</label>
                                    <h4 id="prepayment2" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ قسط (ریال)</label>
                                    <h4 id="installmentsPrice2" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">تعداد قسط</label>
                                    <h4 id="installmentsNum2" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">جمع تسهیلات (ریال)</label>
                                    <h4 id="loan2" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ ضمانت (ریال)</label>
                                    <h4 id="guarantee2" class="mt-2"></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="installment_price" role="tabpanel"
                aria-labelledby="installment-price-tab">
                <div class="card calculate">
                    <div class="card-body">
                        <div class="col-12 border-bottom-white mb-4">
                            <form id="calculate3">
                                <input type="hidden" name="type" value="calculateByInstallment">
                                <div class="row">
                                    <div class="col-12 col-md-3">
                                        <label>طرح مورد نظر</label>
                                        <select class="form-control" name="financial_plans_type" required>
                                            <option value="all">انتخاب طرح مورد نظر</option>
                                            @foreach($financial_plans_types as $financial_plans_type)
                                            <option value="{{ $financial_plans_type->id }}">{{
                                                $financial_plans_type->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>خدمت</label>
                                        <select class="form-control" name="service" id="service3">
                                            <option value="none">انتخاب خدمت</option>
                                            @foreach(\App\Models\Service::all() as $service)
                                            @if($service->percent > 0)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <label>دسته بندی کالا</label>
                                        <select class="form-control" name="product_category" id="product_category3">
                                            <option value="none">انتخاب دسته بندی کالا</option>
                                            @foreach($product_categories as $product_category)
                                            @if($product_category->percent > 0)
                                            <option value="{{ $product_category->id }}">{{ $product_category->title }}
                                            </option>
                                            @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <label>مبلغ قسط (ریال)</label>
                                        <input type="text" name="installments_price"
                                            class="form-control classname" oninput="separatePrices(this)">
                                    </div>
                                    <div class="col-12 col-md-1">
                                        <label>تعداد اقساط</label>
                                        <input type="number" name="installments_num"
                                            class="form-control classname" min="1" id="installmentsNum">
                                    </div>
                                </div>
                                <div class="col-12 mt-5">
                                    <button class="btn btn-success px-4 mb-3" onclick="calculate('3')">محاسبه</button>
                                </div>
                            </form>
                        </div>

                        <div class="col-12 card text-dark p-4 result-calculate">
                            <div class="row">
                                <div class="col-12 col-md-2 text-center">
                                    <label class="font-weight-800 border-bottom pb-3">جمع کل (ریال)</label>
                                    <h4 id="totalPrice3" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ پیش پرداخت (ریال)</label>
                                    <h4 id="prepayment3" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ قسط (ریال)</label>
                                    <h4 id="installmentsPrice3" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">تعداد قسط</label>
                                    <h4 id="installmentsNum3" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">جمع تسهیلات (ریال)</label>
                                    <h4 id="loan3" class="mt-2"></h4>
                                </div>
                                <div class="col-12 col-md-2 text-center border-left-2">
                                    <label class="font-weight-800 border-bottom pb-3">مبلغ ضمانت (ریال)</label>
                                    <h4 id="guarantee3" class="mt-2"></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/installmentCalculate.js') }}"></script>
@endsection
