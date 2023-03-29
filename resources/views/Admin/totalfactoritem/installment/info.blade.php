<div class="page-header">
    <a href="{{ url()->previous() }}" class="btn btn-primary">بازگشت</a>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            <a href="/dashboard/totalFactorItem/{{$totalFactorItem->id}}"
                class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>بازدید پرونده</span>
            </a>
            @permission('file-report')
            <a href="/dashboard/totalFactorItem/{{$totalFactorItem->id}}/installment"
                class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>مشاهده اقساط</span>
            </a>
            @endpermission
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-4">
                <table class="table table-light border-right font-weight-bold">
                    <tr>
                        <td>شماره پرونده مالی :</td>
                        <td>{{ $item['file_number_mali'] }}</td>
                    </tr>
                    <tr>
                        <td>
                            نام :
                        </td>
                        <td>{{ $user['name'] }} {{ $user['family'] }}</td>
                    </tr>
                    <tr>
                        <td>شماره تماس :</td>
                        <td>{{ $user['phone'] }} </td>
                    </tr>
                    <tr>
                        <td>وضعیت مالی :</td>
                        <td>{{ $item['status'] }}</td>
                    </tr>
                    <tr>
                        <td>تاریخ شروع قرارداد :</td>
                        <td>{{ $item['start_date'] }}</td>
                    </tr>
                    <tr>
                        <td>تاریخ پایان قرارداد :</td>
                        <td>{{ $item['end_date'] }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-12 col-md-3">
                <table class="table table-light border-right font-weight-bold">
                    <tr>
                        <td>مبلغ تسهیلات :</td>
                        <td> {{ number_format($item['loan']) }} ریال</td>
                    </tr>
                    <tr>
                        <td>تعداد کل اقساط :</td>
                        <td>{{ $item['installments_num'] }}</td>
                    </tr>
                    <tr>
                        <td>تعداد اقساط پرداخت شده :</td>
                        <td>{{ $item['installments_num_pay'] }}</td>
                    </tr>
                    <tr>
                        <td>تعداد اقساط پرداخت نشده :</td>
                        <td>{{ $item['installments_num'] - $item['installments_num_pay'] }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-12 col-md-3">
                <table class="table table-light border-right font-weight-bold">
                    <tr>
                        <td>
                            {{$item['impunity_installment_amount']>0||$item['impunity_penalty_amount']>0?'بدهکاری کل (بااحتساب بخشودگی ):':'بدهکاری کل'}}
                             </td>
                        <td>{{ number_format($item['installments_debt']) }} ریال</td>
                    </tr>
                    <tr>
                        <td>
                            {{$item['impunity_installment_amount']>0?'بدهکاری اقساط (با‌‌احتساب بخشودگی):':'بدهکاری اقساط'}}
                        </td>
                        <td>{{ number_format($expire_installments->sum('price')-$item['impunity_installment_amount']-$item['credit_amount']) }} ریال </td>
                    </tr>
                    <tr>
                        <td> جریمه کل :</td>
                        <td>{{ $item['extra_pay'] }}</td>
                    </tr>
                    <tr>
                        <td>
                            {{$item['impunity_penalty_amount']>0?'جریمه باقیمانده (با احتساب بخشودگی):':'جریمه باقیمانده'}}
                        </td>
                        <td>{{ $item['extra_pay_debt'] }} ریال </td>
                    </tr>
                    <tr>
                        <td> مبلغ اضافه پرداخت :</td>
                        <td> {{ number_format($item['credit_amount']) }} ریال</td>
                    </tr>
                    <tr>
                        <td>بخشودگی جریمه :</td>
                        <td>{{number_format($item['impunity_penalty_amount'])}}ریال</td>
                    </tr>
                    <tr>
                        <td>بخشودگی قسط :</td>
                        <td>{{number_format($item['impunity_installment_amount'])}}ریال</td>
                    </tr>
                </table>
            </div>
            <div class="col-12 col-md-2 float-left">
                <img src="{{ $user['avatar'] }}" width="75%">
                @include('Admin.user.vipAlert')
            </div>
        </div>
    </div>
</div>
