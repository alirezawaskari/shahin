<div class="card col-12 mt-2 pt-4 col-l main border border-success">
    <div class="card-header row col-12 col-md-12 mb-3">
        <div class="col-md-11">
            <h4 class="mb-3"><i class="fa fa-credit-card" aria-hidden="true"></i> ثبت واریزی فاکتور</h4>
        </div>
        <div class="col-md-1">
            <span class="badge badge-{{$status_class}}">{{$status}}</span>
        </div>
    </div>
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post"
            action="{{ route('purchaseInvoice.savePayment' , [$purchaseInvoice->id ])}}"
            enctype="multipart/form-data">
            @csrf

            @include('Admin.layouts.errors')
            <div class="row col-12">
                <div class="col-12 col-md-2 mb-3">
                    <label>مبلغ واریزی (ریال)</label>
                    <input class="form-control" name="price" type="text" min="0"
                        value="{{ number_format($purchaseInvoice->prepayment) }}" oninput="separatePrices(this)"
                        required>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label>نحوه واریز وجه</label>
                    <select class="form-control" name="payment_type" id="payment_type2" onchange="paymentType(2)">
                        <option value="card">کارت به کارت</option>
                        <option value="account">واریز به حساب</option>
                        <option value="card_reader">کارت به کارت</option>
                        <option value="cash">پول نقد</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2" id="payment_date">
                    <label>تاریخ واریزی</label>
                    <input type="text" name="payment_date" class="form-control date mb-2" onkeydown="return false">
                </div>
                <div class="col-12 col-md-3 mb-2" id="number_card2">
                    <label>شماره کارت (4 رقم آخر کارت مقصد)</label>
                    <input class="form-control" name="number_card" type="number" id="number_card_input2">
                </div>
                <div class="col-12 col-md-2 mb-2" id="payment_code2">
                    <label>شماره پیگیری (4 رقم آخر)</label>
                    <input class="form-control" name="payment_code" type="number" id="payment_code_input2">
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="sheba_number2">
                    <label>شماره شبا</label>
                    <input class="form-control" name="sheba_number" type="text" id="sheba_number_input2">
                </div>
                <div class="col-12 col-md-3 mb-2" id="bill_image2">
                    <label>رسید دریافت وجه</label>
                    <br>
                    <label for="bill_image_input2" id="up-label" class="btn badge-secondary"><i
                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                    <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image" type="file"
                        id="bill_image_input2">
                    <div id="bill_image_input2_src" class="show"></div>
                    <input type="hidden" id="bill_image_input2" class="path" name="url_bill_image" value="">
                </div>
            </div>
            <div class="col-12">
                @role('superadmin|mali|superadmin-representaion|mali-representaion')
                <input type="submit" class="btn btn-success border-0" value="ثبت">
                @endrole
            </div>
        </form>
    </div>
</div>
<div class="card col-12 mt-2 pt-4 col-l main border border-danger">
    <div class="card-header row col-12 col-md-12 mb-3">
        <div class="col-md-11">
            <h4 class="mb-3"><i class="fa fa-credit-card" aria-hidden="true"></i> ثبت عودت واریزی از تامین کننده</h4>
        </div>
    </div>
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post"
            action="{{ route('purchaseInvoice.returnPayment' , [$purchaseInvoice->id ])}}"
            enctype="multipart/form-data">
            @csrf

            @include('Admin.layouts.errors')
            <div class="row col-12">
                <div class="col-12 col-md-2 mb-3">
                    <label>مبلغ واریزشده (ریال)</label>
                    <input class="form-control" name="price" type="text" min="0"
                        value="{{ number_format($purchaseInvoice->prepayment) }}" oninput="separatePrices(this)"
                        required>
                </div>
                <div class="col-12 col-md-2 mb-2">
                    <label>نحوه واریز</label>
                    <select class="form-control" name="payment_type" id="payment_type3" onchange="paymentType(3)">
                        <option value="card">کارت به کارت</option>
                        <option value="account">واریز به حساب</option>
                        <option value="card_reader">کارت به کارت</option>
                        <option value="cash">پول نقد</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2" id="payment_date">
                    <label>تاریخ واریز</label>
                    <input type="text" name="payment_date" class="form-control date mb-2" onkeydown="return false">
                </div>
                <div class="col-12 col-md-3 mb-2" id="number_card3">
                    <label>شماره کارت (4 رقم آخر کارت مقصد)</label>
                    <input class="form-control" name="number_card" type="number" id="number_card_input3">
                </div>
                <div class="col-12 col-md-2 mb-2" id="payment_code3">
                    <label>شماره پیگیری (4 رقم آخر)</label>
                    <input class="form-control" name="payment_code" type="number" id="payment_code_input3">
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="sheba_number3">
                    <label>شماره شبا</label>
                    <input class="form-control" name="sheba_number" type="text" id="sheba_number_input3">
                </div>
                <div class="col-12 col-md-3 mb-2" id="bill_image3">
                    <label>رسید دریافت وجه</label>
                    <br>
                    <label for="bill_image_input3" id="up-label" class="btn badge-secondary"><i
                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                    <input class="filestyle file-upload file_upload_ajax file-up" name="bill_image" type="file"
                        id="bill_image_input3">
                    <div id="bill_image_input3_src" class="show"></div>
                    <input type="hidden" id="bill_image_input3" class="path" name="url_bill_image" value="">
                </div>
            </div>
            <div class="col-12">
                @role('superadmin|mali|superadmin-representaion|mali-representaion')
                <input type="submit" class="btn btn-success border-0" value="ثبت">
                @endrole
            </div>
        </form>
    </div>
</div>
<div class="card col-12 mt-2 pt-4 col-l main border">
    <h4 class="mb-3 card-header"><i class="fa fa-money" aria-hidden="true"></i> واریزی های انجام شده </h4>
    <div class="card-body">
        <table class="table table-bordered table-striped table-responsive-md">
            <tr>
                <th>شماره قبض</th>
                <th>مبلغ واریز شده</th>
                <th>نحوه واریز</th>
                <th>تاریخ و ساعت واریز</th>
                <th>وضعیت</th>
                <th>اقدام</th>
            </tr>
            @foreach($purchase_invoice_factors as $purchase_invoice_factor)
            <tr>
                <td>{{ $purchase_invoice_factor->factor->resnumber }}</td>
                <td>{{ number_format($purchase_invoice_factor->factor->price) }} ریال</td>
                <td>{{ $purchase_invoice_factor->factor::TYPE[$purchase_invoice_factor->factor->type] }}</td>
                <td>{{ verta($purchase_invoice_factor->factor->created_at)->format('%d %B ، %Y - H:i:s') }}</td>
                <td>
                    @if($purchase_invoice_factor->factor->status == 4)
                    <span class="badge badge-success">{{ $purchase_invoice_factor->factor::status($purchase_invoice_factor->factor->status)
                        }}</span>
                    @elseif($purchase_invoice_factor->factor->status == 5)
                    <span class="badge badge-danger">{{ $purchase_invoice_factor->factor::status($purchase_invoice_factor->factor->status)
                    }}</span>
                    @endif
                </td>
                <td>
                    <div data-v-15965e3b="" class="dropdown">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="btn btn-dark btn-floating btn-icon btn-sm text-white">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a>
                                <button data-id="{{ $purchase_invoice_factor->factor->resnumber }}" type="button" class="factor-show dropdown-item pointer"  data-toggle="modal"
                                        data-target="#factorShowModal">مشاهده قبض</button>
                            </a>
                            @permission('factor-delete')
                                <form class="delete_form" method="post"
                                    action="{{ route('factor.destroy', $purchase_invoice_factor->factor->resnumber) }}">
                                    @method('DELETE')
                                    @csrf
                                    <input type="button" class="dropdown-item pointer delete_submit"
                                        id="{{ $purchase_invoice_factor->factor->id }}" value="حذف">
                                </form>
                            @endpermission
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
        <div class="col-12 mb-2">
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                مجموع تراز مالی واریزی :
                {{ number_format($financial_factors['sum_paid_factors']) }}
                ریال
            </div>
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                مجموع واریزی :
                {{ number_format($financial_factors['paid_factors']) }}
                ریال
            </div>
            <div class="badge badge-primary font-size-13 mb-3 p-2">
                مجموع عودت واریزی :
                {{ number_format($financial_factors['return_payment_factors']) }}
                ریال
            </div>
        </div>
    </div>
</div>
@include('Admin.factor.factorShowModal')
