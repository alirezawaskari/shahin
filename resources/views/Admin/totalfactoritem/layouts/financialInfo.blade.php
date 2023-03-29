<div class="col-12">
    <div class="row">
        <div class="col-12 col-md-4 card alert-danger mb-0">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <ul class="list-unstyled">
                            <li>
                                <h5>بدهکاری کل : {{$item['total_debt']['debt'] > 0 ?
                                    number_format($item['total_debt']['debt']) : 0 }} ریال</h5>
                                <ul>
                                    <li> هزینه ثبت نام {{ ($item['total_debt']['registeration_debt'] > 0 ?
                                        number_format($item['total_debt']['registeration_debt']) : 0)}} ریال</li>
                                    @if($item['confirm'])
                                        <li> اقساط : {{$item['total_debt']['expire_installments_price'] > 0 ? number_format($item['total_debt']['expire_installments_price']) : 0}}
                                            ریال</li>
                                        <li> اختلاف حساب : {{$item['total_debt']['price_difference_debt'] > 0 ?
                                        number_format($item['total_debt']['price_difference_debt']) : 0 }} ریال</li>
                                    @endif
                                    <li> پیش پرداخت {{ ($item['total_debt']['prepayment_debt'] > 0 ?
                                        number_format($item['total_debt']['prepayment_debt']) : 0)}} ریال</li>
                                </ul>
                            </li>
                        </ul>
                        <p class="text-warning font-weight-bolder" data-toggle="tooltip" data-placement="bottom" title="حداقل مبلغی که کاربر هم اکنون موظف به پرداخت آن می باشد."><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
                            مبلغ استفاده شده بیش از اعتبار کاربر :
                            {{number_format($item['total_debt']['extra_work'])}} ریال</p>
                        <p class="text-dark d-md-none d-lg-none">توجه : بدهکاری کل ، براساس هزینه فاکتور پرونده محاسبه
                            می شود که پرداختی های کاربر از آن کسر شده است. </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 card alert-success mb-0">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <ul class="list-unstyled">
                            <li>
                                <h5>اعتبار باقی مانده : {{number_format($item['credit'])}} ریال</h5>
                                <ul>
                                    <li>موجودی کیف پول : {{number_format($user->wallet)}} ریال</li>
                                    <li>موجودی حواله : {{number_format($user->credit)}} ریال</li>
                                </ul>
                            </li>
                        </ul>
                        <p class="text-dark d-md-none d-lg-none">توجه : اعتبار کاربر ، حداکثر مبلغی است که درحال حاضر
                            می‌توانید به کاربر خدمات یا کالا ارائه دهید.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4 card alert-light mb-0">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <ul class="list-unstyled">
                            <li>
                                <h5>بستانکاری کل : {{number_format($item['total_debt']['debt'] < 0 ?
                                        $item['total_debt']['debt']*-1 : 0)}} ریال</h5>
                                <ul>
                                    <li> پیش پرداخت : {{ ($item['total_debt']['prepayment_debt'] < 0 ?
                                                    number_format($item['total_debt']['prepayment_debt']*-1) : 0)}}
                                        ریال</li>
                                    @if($item['confirm'])
                                        <li> اقساط : {{$item['total_debt']['expire_installments_price'] < 0 ? number_format($item['total_debt']['expire_installments_price']*-1) : 0}}
                                            ریال</li>
                                        <li> اختلاف حساب : {{$item['total_debt']['price_difference_debt'] < 0 ?
                                                    number_format($item['total_debt']['price_difference_debt']*-1) : 0
                                                    }} ریال</li>
                                    @endif
                                </ul>
                            </li>
                        </ul>
                        <p class="text-dark d-md-none d-lg-none">توجه : بستانکاری کل ، براساس کل پرداختی های کاربر
                            محاسبه می شود که هزینه فاکتور پرونده از آن کسر شده است. </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-4 alert-danger">
            <p class="text-dark d-none d-md-block d-lg-block">توجه : بدهکاری کل ، براساس هزینه فاکتور پرونده محاسبه می
                شود که پرداختی های کاربر از آن کسر شده است. </p>
        </div>

        <div class="col-md-4 alert-success">
            <p class="text-dark d-none d-md-block d-lg-block">توجه : اعتبار کاربر ، حداکثر مبلغی است که درحال حاضر
                می‌توانید به کاربر خدمات یا کالا ارائه دهید.</p>
        </div>

        <div class="col-md-4 alert-light">
            <p class="text-dark d-none d-md-block d-lg-block">توجه : بستانکاری کل ، براساس کل پرداختی های کاربر محاسبه
                می شود که هزینه فاکتور پرونده از آن کسر شده است. </p>
        </div>
    </div>
</div>
