
    <div class="panel-body">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <h5> وضعیت مالی : {{ $item['status_mali'] }}</h5>
                        <h5>تاریخ شروع قرارداد : {{ $item['start_date'] }} </h5>
                        <h5>تاریخ پایان قرارداد : {{ $item['end_date'] }}</h5>
                        <h5> مبلغ تسهیلات : {{ number_format($item['loan']) }}
                            ریال</h5>
                    </div>
                    <div class="col-12 col-md-4">
                        <h5> مبلغ قسط :
                            {{ number_format($item['installments_price']) }} ریال
                        </h5>
                        <h5> تعداد کل اقساط : {{ $item['installments_num'] }}
                        </h5>
                        <h5> تعداد اقساط پرداخت شده :
                            {{ $item['installments_num_pay'] }}</h5>
                        <h5> تعداد اقساط پرداخت نشده :
                            {{ $item['installments_num'] - $item['installments_num_pay'] }}
                        </h5>
                    </div>
                    <div class="col-12 col-md-4">
                        <h5> مبلغ بدهکاری کل : {{ number_format($item['installments_debt']) }}
                            ریال</h5>
                        <h5> مبلغ بدهکاری اقساط :
                            {{ number_format($expire_installments->sum('price')) }}
                            ریال</h5>
                        <h5> جریمه کل : {{ $item['extra_pay'] }}</h5>
                        <h5> جریمه باقیمانده : {{ $item['extra_pay_debt'] }} ریال
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

