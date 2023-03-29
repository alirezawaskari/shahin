<div
    class="col-12 mt-3 card-body border {{ $item['financial_balance_items']['financial_balance'] > 0 ? 'border-danger' : 'border-success' }}">
    <h5 class="mb-3 card-header">تراز مالی پرونده جهت تسویه حساب</h5>
    <table class="table table-bordered table-striped table-responsive-md">
        <tr>
            <th>نام حساب</th>
            <th> بدهکار (ریال)</th>
            <th> بستانکار (ریال)</th>
        </tr>
        <tr>
            <td>پیش پرداخت</td>
            <td>{{ $item['financial_balance_items']['prepayment_debt'] > 0 ?
                number_format($item['financial_balance_items']['prepayment_debt']) : 0 }}</td>
            <td>{{ $item['financial_balance_items']['prepayment_debt'] < 0 ?
                    number_format($item['financial_balance_items']['prepayment_debt']*-1) : 0 }}</td>
        </tr>
        <tr>
            <td>اقساط</td>
            <td>{{ $item['financial_balance_items']['unpaid_installments'] > 0 ?
                number_format($item['financial_balance_items']['unpaid_installments']) : 0 }}</td>
            <td>{{ $item['financial_balance_items']['unpaid_installments'] < 0 ?
                    number_format($item['financial_balance_items']['unpaid_installments']*-1) : 0 }}</td>
        </tr>
        <tr>
            <td>اختلاف حساب</td>
            <td>{{ $item['financial_balance_items']['price_difference_debt'] > 0 ?
                number_format($item['financial_balance_items']['price_difference_debt']) : 0 }}</td>
            <td>{{ $item['financial_balance_items']['price_difference_debt'] < 0 ?
                    number_format($item['financial_balance_items']['price_difference_debt']*-1) : 0 }}</td>
        </tr>
        <tr>
            <td>جریمه</td>
            <td>{{ $item['financial_balance_items']['extra_pay'] > 0 ?
                number_format($item['financial_balance_items']['extra_pay']) : 0 }}</td>
            <td>{{ $item['financial_balance_items']['extra_pay'] < 0 ?
                    number_format($item['financial_balance_items']['extra_pay']*-1) : 0 }}</td>
        </tr>
        <tr>
            <td>جمع کل</td>
            <td>{{ number_format($item['financial_balance_items']['total_debt']) }}</td>
            <td>{{ number_format($item['financial_balance_items']['total_credit']*-1) }}</td>
        </tr>
    </table>
    <div class="badge {{ $item['financial_balance_items']['financial_balance'] > 0 ? 'badge-danger' : 'badge-success' }} font-size-13 mb-3 p-2">
         تراز مالی پرونده  ({{ $item['financial_balance_items']['financial_balance'] > 0 ? ' بدهکاری کل : '
        :' بستانکاری کل : ' }} {{
        number_format(abs($item['financial_balance_items']['financial_balance'])).' ریال ' }})</div>
    <p class="text-warning font-weight-bolder"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>
        جمع مبلغ با جریمه باقی مانده پرونده محاسبه شده است.
    </p>
</div>
