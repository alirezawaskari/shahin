@if ($item['has_payment'])
<div class="card col-12 mt-2 pt-4 col-l main border border-danger">
    <h5 class="mb-3 card-header"><i class="fa fa-credit-card" aria-hidden="true"></i> ثبت قبض عودت وجه</h5>
    <form action="{{ route('factorItem.returnPayment') }}" method="POST" enctype="multipart/form-data">
        @csrf
            <input type="hidden" value="{{ $item['id'] }}" name="id">
            <input type="hidden" value="{{ $user['id'] }}" name="user_id">
            <input type="hidden" value="factor" name="title">
            <div class="row mt-5 mb-4">
                <div class="col-12 col-md-2 mb-2">
                    <label>مبلغ عودت</label>
                    <input class="form-control" value="0"
                        name="price" type="text" oninput="separatePrices(this)">
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label>نحوه پرداخت</label>
                    <select class="form-control" name="payment_type" id="payment_type3" onchange="paymentType(3)">
                        <option value="increase_credit">افزایش اعتبار کاربر</option>
                        <option value="account">واریز به حساب</option>
                        <option value="card">کارت به کارت</option>
                        <option value="cash">پول نقد</option>
                        <option value="check">چک</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2" id="payment_date">
                    <label>تاریخ پرداخت</label>
                    <date-picker value="{{ now() }}" format="YYYY/M/D HH:mm"
                            name="payment_date" type="datetime" display-format="jYYYY/jMM/jDD HH:mm">
                    </date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2" id="number_card3">
                    <label>شماره کارت (4 رقم آخر کارت پرداخت کننده)</label>
                    <input class="form-control" name="number_card" type="number" id="number_card_input3">
                </div>
                <div class="col-12 col-md-2 mb-2" id="payment_code3">
                    <label>شماره پیگیری (4 رقم آخر)</label>
                    <input class="form-control" name="payment_code" type="number" id="payment_code_input3">
                </div>
				<div class="col-12 col-md-2 mb-2 d-none" id="sheba_number3">
                    <label>شماره شبا</label>
                    <input class="form-control" name="sheba_number" type="text" id="sheba_number_input3">
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="bill_image3">
                    <label>رسید دریافت وجه</label>
                    <br>
                    <label for="bill_image_input3" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                    <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image" type="file" id="bill_image_input3">
                    <input type="hidden" id="bill_image_input3_path" class="path" name="url_bill_image">
                </div>
                <div class="col-12 col-md-2 mb-2 d-none" id="bill_image_src3">
                    <img id="bill_image_input3_src" height="150px">
                </div>
            </div>
        <input type="submit" class="btn btn-success px-4 mb-3 submit_btn" value="ثبت فاکتور">
    </form>
</div>
@else
<div class="alert alert-info alert-with-border">
    <p> پرداختی برای این پرونده ثبت نشده است.</p>
</div>
@endif



