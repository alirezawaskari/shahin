<div class="panel-body">
    <div class="card col-12 mt-2 pt-4 col-l main border">
        @if($item['total_price'] != 0)
            <div class="row">
                @if ($item['type'] == 1 && $item['confirm'])
                    <div class="col-12 col-md-3 mb-3">
                        <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                        <p class="form-control">
                            {{ number_format($item['total_price'] +
                            (isset($item['price_difference']['total']) ?
                            $item['price_difference']['total'] : 0)) }} </p>
                    </div>
                    <div class="col-12 col-md-3 mb-3" data-toggle="tooltip" data-placement="bottom"
                         title="مبلغ کل اعتباری که کاربر از مجموعه دریافت  کرده است.">
                        <label>مبلغ کل اعتبار پرونده(ریال)</label>
                        <p class="form-control border-success">
                            {{ number_format($item['total_price']) }} </p>
                    </div>
                @else
                    <div class="col-12 col-md-3 mb-3">
                        <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                        <p class="form-control">
                            {{ number_format($item['total_price']) }} </p>
                    </div>
                @endif
                <div class="col-12 col-md-3 mb-2">
                    <label>مبلغ تخفیف</label>
                    <span class="form-control">{{ number_format($item['discount']) }}</span>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>مبلغ اختلاف حساب</label>
                    <p class="form-control">
                        {{ isset($item['price_difference']['total']) ?
                        number_format($item['price_difference']['total']) : 0 }}
                    </p>
                </div>

                @if ($item['type'] == 1)
                    <div class="col-12 col-md-3 mb-2">
                        <label>تعداد اقساط</label>
                        <p class="form-control">
                            {{ $item['installments_num'] }}
                        </p>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>مبلغ اقساط(ریال)</label>
                        <p class="form-control">
                            {{ number_format($item['installments_price']) }}
                        </p>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>پیش پرداخت(ریال)</label>
                        <p class="form-control">
                            {{ number_format($item['prepayment']['price']) }}
                        </p>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>تعداد مراحل پرداخت پیش پرداخت</label>
                            <p class="form-control">
                                {{ isset($item['prepayment']['num']) ? $item['prepayment']['num'] : 1 }}
                            </p>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <label>درصد جریمه روزانه</label>
                        <p class="form-control">
                            {{ isset($item['percent_fines']) ? $item['percent_fines'] : 2 }}
                        </p>
                    </div>
                    @endif
                    <div class="col-12 mb-3">
                        <label>نوع ضمانت:</label>
                        @switch( $item['guarantee_type'])
                            @case( 'check')
                            چک
                            @break
                            @case( 'certificate')
                            گواهی کسراز حقوق
                            @break
                            @case( 'promissorynote')
                           سفته
                            @break
                        @endswitch
                        <br>
                    </div>
                @endif
            </div>
    </div>
</div>
