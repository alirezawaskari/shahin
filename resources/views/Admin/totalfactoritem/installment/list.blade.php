<div class="card border border-secondary">
    <h5 class="mb-3 card-header">فهرست اقساط</h5>
    <div class="card-body">
        <table class="table table-bordered table-responsive-md">
            <tr>
                <th>شماره قسط</th>
                <th>سررسید قسط</th>
                <th>تاریخ پرداخت</th>
                <th>مبلغ قسط (ریال)</th>
                <th>جریمه دیرکرد (ریال)</th>
                <th>مبلغ قابل پرداخت (ریال)</th>
                <th>وضعیت پرداخت</th>
{{--                <th>دارای چک</th>--}}
{{--                <th>عملیات</th>--}}
            </tr>
            @foreach ($installments as $installment)
                <tr class="{{ $installment['class_name'] }}">
                    <td>{{ $installment['level'] }}</td>
                    <td>{{ $installment['date'] }}</td>
                    <td>{{ $installment['pay_date'] }}</td>
                    <td>{{ $installment['price'] }}</td>
                    <td>{{ $installment['extra_pay'] }}</td>
                    <td>{{ $installment['amount_payable'] }}</td>
                    <td>
                        {!! \App\Helpers\Helper::iconIsItOrNot($installment['status']) !!}
                    </td>
                </tr>
            @endforeach
        </table>
        <p class="text-warning font-weight-bolder"><i class="fa fa-square text-danger" aria-hidden="true"></i>
            توجه : اقساطی که با رنگ قرمز در جدول نشان داده شده اند اقساطی هستند که موعد پرداخت آن ها گذشته است.</p>
        <p class="text-warning font-weight-bolder"><i class="fa fa-square text-dark" aria-hidden="true"></i>
            توجه : اقساطی که با رنگ طوسی در جدول نشان داده شده اند اقساطی هستند که با تسویه حساب پرونده بسته شده اند و
            نیاز به پرداخت آن ها نیست.</p>
    </div>
</div>
