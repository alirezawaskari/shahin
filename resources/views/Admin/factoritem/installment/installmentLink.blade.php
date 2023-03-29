<div class="card col-12 mt-3 pt-4 col-l main border border-secondary">
    <h5 class="mb-3 card-header"><i class="fa fa-credit-card" aria-hidden="true"></i> ثبت و ارسال قبض پرداخت اقساط</h5>
    <div class="card-body">
        <form action="{{ url('dashboard/save-installments-paid') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <input type="hidden" value="{{ $item['id'] }}" name="id">
                <input type="hidden" value="{{ $user['id'] }}" name="user_id">
                <input type="hidden" value="{{ verta($factorItem->approval_date) }}" name="approval_date">
                <div class="col-12 col-md-2">
                    <label>مبلغ (ریال)</label>
                    <input class="form-control" name="price[]" type="text"
                        value="{{ number_format($expire_installments->sum('price')) }}" oninput="separatePrices(this)">
                </div>
                <div class="col-12 col-md-2">
                    <label>انتخاب نوع پرداخت</label>
                    <select class="form-control px-4 mb-3" name="payment_type[]" id="payment_type3"
                        onchange="paymentType(3)">
                        <option value="online">آنلاین</option>
                        @permission('record_payment')
                        <option value="card_reader">کارتخوان</option>
                        <option value="card">کارت به کارت</option>
                        <option value="cash">پول نقد</option>
                        @endpermission
                        <option value="wallet">کسر از کیف پول</option>
                        <option value="credit">کسر از حواله</option>
                        <option value="deduction_price_difference">کسر از بستانکاری</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2" id="payment_date">
                    <label>تاریخ پرداخت</label>
                    <date-picker value="{{ now() }}" format="YYYY/M/D HH:mm"
                            name="payment_date[]" type="datetime" display-format="jYYYY/jMM/jDD HH:mm">
                    </date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="number_card3">
                    <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                    <input class="form-control" name="number_card[]" type="number" id="number_card_input3">
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="payment_code3">
                    <label>شماره پیگیری (4 رقم آخر)</label>
                    <input class="form-control" name="payment_code[]" type="number" id="payment_code_input3">
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="bill_image3">
                    <label>رسید دریافت وجه</label>
                    <br>
                    <label for="bill_image_input3" id="up-label" class="btn badge-secondary"><i
                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                    <input class="form-control file-up" name="bill_image[]" type="file" id="bill_image_input3"
                        accept=".jpg,.jpeg,.png">
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="password3">
                    <label>رمز عبور کاربر</label>
                    <input class="form-control" name="password" type="number" id="password_input3">
                    <p>لطفا رمز عبور 6 رقمی که برای کاربر ارسال شده است را وارد کنید.</p>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-primary px-4 mb-3 submit_btn" {{ $factorItem->status_mali == 9
                        ? 'disabled' : ''}}>ثبت و ارسال</button>
                </div>
            </div>
        </form>
    </div>
</div>
