@if($item['prepayment']['price'])
<div class="card col-12 pt-4 col-l main border">
    <h4 class="mb-3 card-header"><i class="fa fa-credit-card" aria-hidden="true"></i> ثبت و ارسال قبض پرداخت هزینه پیش
        پرداخت</h4>
    <div class="card-body">
        <form action="{{ route('totalFactorItem.sendPayment')}}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($item['total_price'] != 0)
            <input type="hidden" value="prepayment" name="title">
            <input type="hidden" value="{{ $item['id'] }}" name="id">
            <input type="hidden" value="{{ $user['id'] }}" name="user_id">
            <input type="hidden" value="prepayment" name="type">
            <h5> مبلغ قابل پرداخت : {{number_format($item['prepayment']['price']) }} ریال ( پرداخت
                در {{$item['prepayment']['num']}} مرحله/ نوع پرداخت اقساط) </h5>
            <h5> مبلغ باقی مانده : {{number_format($item['prepayment']['debt'])}} ریال </h5>
            <div class="row mt-5 mb-4">
                <div class="col-12 col-md-2 mb-2">
                    <label>مبلغ(ریال)</label>
                    <input class="form-control" value="{{number_format($item['prepayment']['debt'])}}" name="price"
                        type="text" {{$item['prepayment']['count']==1 ? 'min=' .$item['prepayment']['debt'] : '' }}
                        {{ 'max=' .$item['prepayment']['debt'] }} oninput="separatePrices(this)">
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label>نحوه پرداخت</label>
                    <select class="form-control" name="payment_type" id="payment_type2" onchange="paymentType(2)">
                        <option value="online">آنلاین</option>
                        <option value="send_tax_free_card_number">ارسال شماره کارت</option>
                        @permission('record_payment')
                        <option value="card_reader">کارتخوان</option>
                        <option value="card">کارت به کارت</option>
                        <option value="cash">پول نقد</option>
                        <option value="account">واریز به حساب</option>
                        @endpermission
                        <option value="credit">کسر از حواله</option>
                        <option value="wallet">کسر از کیف پول</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2" id="payment_date">
                    <label>تاریخ پرداخت</label>
                    <date-picker value="{{ now() }}" format="YYYY/M/D HH:mm"
                            name="payment_date" type="datetime" display-format="jYYYY/jMM/jDD HH:mm">
                    </date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="number_card2">
                    <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                    <input class="form-control" name="number_card" type="number" id="number_card_input2">
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="payment_code2">
                    <label>شماره پیگیری (4 رقم آخر)</label>
                    <input class="form-control" name="payment_code" type="number" id="payment_code_input2">
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="bill_image2">
                    <label>رسید دریافت وجه</label>
                    <br>
                    <label for="bill_image_input2" id="up-label" class="btn badge-secondary"><i
                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                    <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image" type="file"
                        id="bill_image_input2">
                    <div id="bill_image_input2_src" class="show"></div>
                    <input type="hidden" id="bill_image_input2_path" class="path" name="url_bill_image" value="">
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="sheba_number2">
                    <label>شماره شبا</label>
                    <input class="form-control" name="sheba_number" type="text" id="sheba_number_input2">
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="password2">
                    <label>رمز عبور کاربر</label>
                    <input class="form-control" name="password" type="password" id="password_input2">
                    <p>لطفا رمز عبور 6 رقمی که برای کاربر ارسال شده است را وارد کنید.</p>
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="send_password2">
                    <input type="hidden" value="{{ $user->phone }}" name="phone" class="phone">
                    <span class="send_user_password btn btn-success-default text-white px-3"> ارسال رمز <i
                            class="fa fa-paper-plane mr-1" aria-hidden="true"></i></span>
                </div>
            </div>
            @endif
            <input type="submit" class="btn btn-success px-4 mb-3 submit_btn" value="ارسال فاکتور "
                {{$item['prepayment']['debt']==0 ? 'disabled' : '' }}>
        </form>
    </div>
</div>
@endif
