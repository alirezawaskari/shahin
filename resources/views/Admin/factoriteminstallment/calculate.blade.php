@extends('Admin.master')

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
                <a class="nav-link show active" id="total-price-tab" data-toggle="tab" href="#total_price" role="tab" aria-controls="total_price" aria-selected="false">جمع کل</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="prepayment-price-tab" data-toggle="tab" href="#prepayment_price" role="tab" aria-controls="prepayment_price" aria-selected="false">پیش پرداخت</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="installment-price-tab" data-toggle="tab" href="#installment_price" role="tab" aria-controls="installment_price" aria-selected="false">مبلغ قسط</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="total_price" role="tabpanel" aria-labelledby="total-price-tab">
                <div class="card calculate">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <form id="calculate1">
                                    <input type="hidden" name="type" value="calculateByTotalPrice">
                                    <div class="col-12 col-md-10">
                                        <label>خدمت</label>
                                        <select class="form-control" name="service" required id="service1">
                                            <option value="">انتخاب خدمت</option>
                                            @foreach(\App\Models\Service::all() as $service)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-10 mt-3">
                                        <label>جمع کل (ریال)</label>
                                        <input type="text" name="total_price" id="totalPrice1" class="form-control classname" oninput="separatePrices(this)">
                                    </div>
                                    <div class="col-12 mt-5">
                                        <button class="btn btn-success px-4 mb-3" onclick="calculate('1')">محاسبه</button>
                                    </div>
                                </form>
                            </div>

                            <div class="row col-12 col-md-6">
                                <div class="col-12 col-md-5">
                                    <label>مبلغ قسط (ریال)</label>
                                    <p id="installmentsPrice1" class="form-control"></p>
                                </div>
                                <div class="col-12 col-md-5">
                                    <label>تعداد قسط</label>
                                    <p id="installmentsNum1" class="form-control"></p>
                                </div>
                                <div class="col-12 col-md-5 mt-3">
                                    <label>مبلغ پیش پرداخت (ریال)</label>
                                    <p id="prepayment1" class="form-control"></p>
                                </div>

                                <div class="col-12 col-md-5 mt-3">
                                    <label>جمع تسهیلات (ریال)</label>
                                    <p id="loan1" class="form-control"></p>
                                </div>
								
								<div class="col-12 col-md-10 mt-3">
                                    <label>مبلغ ضمانت (ریال)</label>
                                    <p id="guarantee1" class="form-control"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="prepayment_price" role="tabpanel" aria-labelledby="prepayment-price-tab">
                <div class="card calculate">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <form id="calculate2">
                                    <input type="hidden" name="type" value="calculateByPrepayment">
                                    <div class="col-12 col-md-10 mb-3">
                                        <label>خدمت</label>
                                        <select class="form-control" name="service" required id="service2">
                                            <option value="">انتخاب خدمت</option>
                                            @foreach(\App\Models\Service::all() as $service)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-10 mt-3">
                                        <label>پیش پرداخت (ریال)</label>
                                        <input type="text" name="prepayment" id="prepayment2" class="form-control classname" oninput="separatePrices(this)">
                                    </div>
                                    <div class="col-12 mt-5">
                                        <button class="btn btn-success px-4 mb-3" onclick="calculate('2')">محاسبه</button>
                                    </div>
                                </form>
                            </div>

                            <div class="row col-12 col-md-6">
                                <div class="col-12 col-md-5">
                                    <label>جمع کل (ریال)</label>
                                    <p id="totalPrice2" class="form-control"></p>
                                </div>
								
								 <div class="col-12 col-md-5">
                                    <label>جمع تسهیلات (ریال)</label>
                                    <p id="loan2" class="form-control"></p>
                                 </div>
                                <div class="col-12 col-md-5">
                                    <label>مبلغ قسط (ریال)</label>
                                    <p id="installmentsPrice2" class="form-control"></p>
                                </div>
                                <div class="col-12 col-md-5">
                                    <label>تعداد قسط</label>
                                    <p id="installmentsNum2" class="form-control"></p>
                                </div>
								
								<div class="col-12 col-md-10 mt-3">
                                    <label>مبلغ ضمانت (ریال)</label>
                                    <p id="guarantee2" class="form-control"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade p-t-20" id="installment_price" role="tabpanel" aria-labelledby="installment-price-tab">
                <div class="card calculate">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <form id="calculate3">
                                    <input type="hidden" name="type" value="calculateByInstallment">
                                    <div class="col-12 col-md-10 mb-3">
                                        <label>خدمت</label>
                                        <select class="form-control" name="service" id="service3">
                                            <option value="">انتخاب خدمت</option>
                                            @foreach(\App\Models\Service::all() as $service)
                                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-10 mt-3">
                                        <label>مبلغ قسط (ریال)</label>
                                        <input type="text" name="installments_price" id="installmentsPrice3" class="form-control classname" oninput="separatePrices(this)">
                                    </div>
                                    <div class="col-12 col-md-10 mt-3">
                                        <label>تعداد اقساط</label>
                                        <input type="number" name="installments_num" id="installmentsNum3" class="form-control classname">
                                    </div>
                                    <div class="col-12 mt-5">
                                        <button class="btn btn-success px-4 mb-3" onclick="calculate('3')">محاسبه</button>
                                    </div>
                                </form>
                            </div>

                            <div class="row col-12 col-md-6">
                                <div class="col-12 col-md-10">
                                    <label>جمع کل (ریال)</label>
                                    <p id="totalPrice3" class="form-control"></p>
                                </div>
                                <div class="col-12 col-md-10">
                                    <label>مبلغ پیش پرداخت (ریال)</label>
                                    <p id="prepayment3" class="form-control"></p>
                                </div>

                                <div class="col-12 col-md-10">
                                    <label>جمع تسهیلات (ریال)</label>
                                    <p id="loan3" class="form-control"></p>
                                </div>
								
								<div class="col-12 col-md-10 mt-3">
                                    <label>مبلغ ضمانت (ریال)</label>
                                    <p id="guarantee3" class="form-control"></p>
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
<script src="{{ asset('/assets/dashboard/js/factorItem.js') }}"></script>
@endsection