@permission('file-report')
<div class="card border col-12 mt-5 pt-4 col-l main">
    <h4 class="mb-3 card-header"><i class="fas fa-calculator"></i> مبالغ و محاسبه اقساط (پرونده : {{ $factorItem['factoritemable']->title }})</h4>
    <form action="{{ route('factorItem.calculate')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" value="{{ $factorItem['id'] }}" name="id">
        @if($factorItem['total_price'] != 0)
        <div class="row">
            @if ($factorItem['type'] == 1 && $item['confirm'])
            <div class="col-12 col-md-3 mb-3">
                <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                <p class="form-control border-success">
                                    {{ number_format($factorItem['total_price']) }} </p>
            </div>
            <div class="col-12 col-md-3 mb-3" data-toggle="tooltip" data-placement="bottom"
                title="مبلغ کل اعتباری که کاربر از مجموعه دریافت  کرده است.">
                <label>مبلغ کل اعتبار پرونده(ریال)</label>
                <p class="form-control border-success">
                    {{ number_format($factorItem['total_price']) }} </p>
            </div>
            @else
            <div class="col-12 col-md-3 mb-3">
                <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                <input class="form-control" value="{{ number_format($factorItem['total_price'] +
                                    (isset($factorItem['price_difference']['total']) ?
                                    $factorItem['price_difference']['total'] : 0)) }}" name="totalPrice"
                                    oninput="separatePrices(this)">
            </div>
            @endif
            <div class="col-12 col-md-3 mb-3" data-toggle="tooltip" data-placement="bottom"
                title=" درصورت گذاشتن  ' - (منفی)' این مبلغ از جمع کل فاکتور کسر می شود.">
                <label>مبلغ تخفیف(ریال)</label>
                <input class="form-control" value="{{number_format($factorItem['discount'])}}" name="discount"
                    type="text" oninput="separatePrices(this)">
            </div>
            <div class="col-12 col-md-3 mb-3">
                <label>مبلغ اختلاف حساب</label>
                <p class="form-control">
                    {{ isset($factorItem['price_difference']['total']) ?
                    number_format($factorItem['price_difference']['total']) : 0 }}
                </p>
            </div>
            @if($factorItem['type'] == 1)
            <div class="col-12 col-md-3 mb-3">
                <label>تعداد اقساط</label>
                <input class="form-control" value="{{$factorItem['installments_num']}}" name="installments_num"
                    type="number" min="1" max="{{$factorItem['installments_num']}}">
            </div>

            <div class="col-12 col-md-3 mb-3">
                <label>مبلغ اقساط</label>
                <p class="form-control">{{$factorItem['installments_price']}}</p>
            </div>

            <div class="col-12 col-md-3 mb-3">
                <label>پیش پرداخت(ریال)</label>
                <input class="form-control" value="{{number_format($factorItem['prepayment']['price'])}}"
                    name="prepayment" type="text" min="{{ $factorItem['prepayment']['price'] }}"
                    oninput="separatePrices(this)">
            </div>
            <div class="col-12 col-md-3 mb-3">
                <label>تعداد مراحل پرداخت پیش پرداخت</label>
                <input class="form-control"
                    value="{{ isset($factorItem['prepayment']['num']) ? $factorItem['prepayment']['num'] : 1}}"
                    name="prepayment_num" type="number"
                    max="{{ $factorItem['type'] == 0 ? $factorItem['prepayment']['num'] : '3' }}" min="1">
            </div>
            <div class="col-12 col-md-4 mb-3">
                <label>نوع ضمانت</label>
                <br>
                @foreach ($item['guarantee_types_list'] as $key => $guarantee_type)
                    {{ $item[ 'total_factor_item' ]::GuaranteeTYPE[$guarantee_type] }} <input type="radio" name="guarantee_type" value="{{ $key }}" {{
                        $item['guarantee_type']==$key ? 'checked' : '' }}>
                @endforeach
            </div>
            @endif
            <div class="col-12 col-md-12 mb-3">
                <label> نوع پرداخت</label>
                <br>
                نقد <input type="radio" name="type" value="0" {{ $factorItem['type']=='0' ? 'checked' : '' }}>
                اقساط<input type="radio" name="type" value="1" {{ $factorItem['type']=='1' ? 'checked' : '' }}>
            </div>
        </div>
        @endif

        <input type="submit" class="btn btn-success px-4 mb-3" value="ثبت" {{ $item['confirm'] ? 'disabled' : '' }}>
    </form>
</div>
@include('Admin.factoritem.layouts.file.changeFinancialPlansType')

@if($factorItem['type'])
<div class="card col-12 mt-3 pt-4 col-l main border bg-light">
    <h4 class="mb-3 card-header"><i class="fa fa-credit-card" aria-hidden="true"></i> اطلاعات مالی پرونده کل
    </h4>
    <input type="hidden" name="id" value="{{ $item['id'] }}">
    <div class="row">
        <div class="col-12 col-md-3 mb-2">
            <label>جمع کل با اعمال تخفیف(ریال)</label>
            <p class="form-control">
                {{ number_format($item['total_price'] +
                (isset($item['price_difference']['total']) ?
                $item['price_difference']['total'] : 0)) }} </p>
        </div>
        @if ($item['confirm'])
        <div class="col-12 col-md-3 mb-2" data-toggle="tooltip" data-placement="bottom"
            title="مبلغ کل اعتباری که کاربر از مجموعه دریافت  کرده است.">
            <label>مبلغ کل اعتبار پرونده(ریال)</label>
            <p class="form-control border-success">
                {{ number_format($item['total_price']) }} </p>
        </div>
        @endif
        <div class="col-12 col-md-3 mb-2">
            <label>مبلغ تخفیف</label>
            <p class="form-control">{{ number_format($item['discount']) }}</p>
        </div>
        <div class="col-12 col-md-3 mb-2">
            <label>مبلغ اختلاف حساب</label>
            <p class="form-control">
                {{ isset($item['price_difference']['total']) ?
                number_format($item['price_difference']['total']) : 0 }}
            </p>
        </div>

        <div class="col-12 col-md-3 mb-2">
            <label>تعداد اقساط</label>
            <p class="form-control">{{ $item['installments_num'] }}</p>
        </div>

        <div class="col-12 col-md-3 mb-2">
            <label>پیش پرداخت(ریال)</label>
            <p class="form-control">{{ number_format($item['prepayment']['price']) }}</p>
        </div>
        <div class="col-12 col-md-3 mb-2">
            <label>تعداد مراحل پرداخت پیش پرداخت</label>
            <p class="form-control">{{ isset($item['prepayment']['num']) ? $item['prepayment']['num'] : 1 }}</p>
        </div>
        <div class="col-12 col-md-3 mb-2">
            <label>درصد جریمه روزانه</label>
            <p class="form-control">{{ isset($item['percent_fines']) ? $item['percent_fines'] : 2 }}</p>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <label>نوع ضمانت</label>
            <br>
            چک <input type="radio" name="guarantee_type" value="check" {{ $item['guarantee_type']=='check' ? 'checked'
                : '' }}>
            سفته <input type="radio" name="guarantee_type" value="promissorynote" {{
                $item['guarantee_type']=='promissorynote' ? 'checked' : '' }}>
        </div>
    </div>
    </form>
</div>
<div class="card col-12 mt-5 pt-4 col-l main">
    <h4>اختلاف حساب</h4>
    <hr>
    <div class="row">
        <div class="col-12 col-md-4 mb-2">
            <label>مبلغ اختلاف حساب از تسهیلات(ریال)</label>
            <p class="form-control">{{ number_format($item['price_difference']['total']) }}</p>
        </div>
        <div class="col-12 col-md-3 mb-2">
            <label>باقی مانده مبلغ (ریال)</label>
            <p class="form-control">{{ number_format($item['price_difference']['debt']) }}</p>
        </div>
    </div>
</div>
@endif
@endpermission
