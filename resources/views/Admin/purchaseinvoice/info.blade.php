<div class="col-12">
    <div class="row">
        <div class="col-12 card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <h5> تامین کننده : {{ $purchaseInvoice->supplier ? $purchaseInvoice->supplier->name : 'بدون تامین کننده' }}</h5>
                                <h5> درخواست کننده : {{ $purchaseInvoice->user ? $purchaseInvoice->user->name.' '.$purchaseInvoice->user->family : 'ثبت نشده'}}</h5>
                                <h5> وضعیت فاکتور : {{ $status }}</h5>
                            </div>
                            <div class="col-12 col-md-6">
                                <h5> جمع کل فاکتور  : {{ number_format($purchaseInvoice->total_price) }} ریال</h5>
                                <h5> مبلغ پرداخت شده : {{ number_format($financial_factors['sum_paid_factors']) }} ریال</h5>
                                <h5> {{ $purchaseInvoice->total_price-$financial_factors['sum_paid_factors'] > 0 ? 'باقی مانده : '. number_format($purchaseInvoice->total_price-$financial_factors['sum_paid_factors']).' ریال' : 'مبلغ اضافه واریز : '.number_format(($purchaseInvoice->total_price-$financial_factors['sum_paid_factors'])*-1).' ریال'}}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

