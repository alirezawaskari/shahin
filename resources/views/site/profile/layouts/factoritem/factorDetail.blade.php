<div class="col-12 p-3">
    <a href="" class="noprint btn btn-secondary py-1 px-2 mt-4"><i class="fa fa-print"
            aria-hidden="true"></i>
        پرینت قرارداد</a>
    <div id="pdf-section">
        @if(!is_null($item['centers']) || !is_null($item['forms']))
        <div class="row p-3 position-relative">
            @if(!is_null($item['forms']))
            @foreach($item['forms'] as $form)
            @if (in_array('show_factor_site' , $form->data_display_level))
            @include('Admin.factoritem.layouts.formItem')
            @endif
            @endforeach
            @endif
        </div>
        @else
        <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
            <div>
                @if(!is_null($item['service_item']))
                <span>{{ $item['service']->title . '-' . $item['service_item']->title }}</span>
                @else
                <span>{{ $item['service']->title }}</span>
                @endif
            </div>
            <i class="fa fa-check font-18 text-success"></i>
        </div>
        @endif
        @php($factorItem['factor_histories'] = $item['factor_histories'])
        @include('Admin.factoritem.layouts.file.factorHistories')
        <hr>
        <div class="row">
            <div class="col-12 col-md-3 mb-2">
                <label>جمع فاکتور با اعمال تخفیف(ریال)</label>
                <p class="form-control">{{number_format($item['total_price'])}} </p>
            </div>
            @if($item['type'] == 1)
            <div class="col-12 col-md-3 mb-2">
                <label>تعداد اقساط</label>
                <p class="form-control">{{$item['installments_num']}}</p>
            </div>
            <div class="col-12 col-md-3 mb-2">
                <label>پیش پرداخت(ریال)</label>
                <p class="form-control">{{number_format($item['prepayment']['price'])}}</p>
            </div>
            <div class="col-12 col-md-4 mb-3">
                <label>نوع ضمانت</label>
                <br>
                چک <input type="radio" name="guarantee_type" value="check" {{ $item['guarantee_type']=='check'
                    ? 'checked' : '' }}>
                سفته <input type="radio" name="guarantee_type" value="promissorynote" {{
                    $item['guarantee_type']=='promissorynote' ? 'checked' : '' }}>
            </div>
            @endif
            <div class="col-12 col-md-12 mb-3">
                <label> نوع پرداخت</label>
                <br>
                نقد <input type="radio" name="type" value="0" {{ $item['type']=='0' ? 'checked' : '' }}>
                اقساط<input type="radio" name="type" value="1" {{ $item['type']=='1' ? 'checked' : '' }}>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function printSection() {
    const element = document.getElementById('pdf-section');
    html2pdf()
        .from(element)
        .save('contract.pdf');
}
</script>