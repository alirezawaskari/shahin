@permission('purchase-invoice-detail')
<div id="payment">
    <div class="row">
        <div class="col-md-2">
            <div id="addnew" class="btn badge-success mb-5">
                <a href="javascript:new_payment_div()" class="text-white"><i class="fa fa-plus" aria-hidden="true"></i>
                    افزودن قسط جدید</a>
            </div>
        </div>
        <div class="col-12">
            <div id="newpayment">
                <div>
                </div>
            </div>
            <div id="newpaymenttpl" class="d-none">
                <table class="table table-bordered table-striped table-responsive-md">
                    <tr>
                        <th>مبلغ قسط (ریال)</th>
                        <th>سررسید قسط</th>
                        <th>حذف</th>
                    </tr>
                    <tr>
                        <td>
                            <input class="form-control" name="payments[price][]" type="text" min="0" value=""
                                oninput="separatePrices(this)">
                        </td>
                        <td>
                            <input type="text" name="payments[date][]" class="form-control date">
                        </td>
                        <td id="delete">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    @if($installments->count() > 0)
    <table class="table table-bordered table-striped table-responsive-md">
        <tr>
            <th>شماره قسط</th>
            <th>سررسید قسط</th>
            <th>تاریخ پرداخت</th>
            <th>مبلغ قسط (ریال)</th>
            <th>وضعیت پرداخت</th>
            <th>اقدام</th>
        </tr>
        @foreach($installments as $key => $installment)
        <tr>
            <td>{{$key+1}}</td>
            <td>{{ verta($installment->date)->format('%d %B ، %Y - H:i') }}</td>
            <td>{{ $installment->pay_date ? verta($installment->pay_date)->format('%d %B ، %Y - H:i') : 'پرداخت نشده' }}
            </td>
            <td>{{ number_format($installment->price) }}</td>
            <td>
                {!! \App\Helpers\Helper::iconIsItOrNot($installment->status) !!}
            </td>
            <td>
                <div data-v-15965e3b="" class="dropdown">
                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                        class="btn btn-dark btn-floating btn-icon btn-sm">
                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item pointer"
                            href="{{ url('dashboard/purchase-invoice-installment-delete/'.$installment->id) }}">حذف</a>
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
    </table>
    @endif
</div>
@endpermission
