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
    <div>
        @if(!is_null($item['financial_balance_items']['impunity_installment']) || !is_null($item['financial_balance_items']['impunity_penalty']))
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info text-center"> بخشودگی </div>
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">مبلغ بخشودگی (ریال)</th>
                            <th scope="col">توضیح</th>
                            <th scope="col">نوع</th>
                            <th scope="col">ثبت کننده</th>
                            <th scope="col">تاریخ</th>
                            <th scope="col">اقدام</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(!is_null($item['financial_balance_items']['impunity_installment']))
                            <tr>
                                <td>{{number_format($item['financial_balance_items']['impunity_installment']->amount)}}</td>
                                <td>{{$item['financial_balance_items']['impunity_installment']->description}}</td>
                                <td><span class="badge badge-info">اقساط</span></td>
                                <td>{{$item['financial_balance_items']['impunity_installment']->creator->name.' '.$item['financial_balance_items']['impunity_installment']->creator->family}}</td>
                                <td>{{ verta($item['financial_balance_items']['impunity_installment']->created_at)->format('%d %B ، %Y - H:i:s')}}</td>
                                <td>
                                    @permission('delete-impunity')
                                    <form  onsubmit="return formQuestionConfirm()" method="POST" action="{{route('totalFactorItem.destroyImpunity',[$item['financial_balance_items']['impunity_installment']->id])}}">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <div class="form-group">
                                            <input type="submit" class="btn btn-sm btn-warning" value="حذف">                                        </div>
                                    </form>
                                    @endpermission
                                </td>
                            </tr>
                        @endif
                        @if(!is_null($item['financial_balance_items']['impunity_penalty']))

                            <tr>
                                <td>{{number_format($item['financial_balance_items']['impunity_penalty']->amount)}}</td>
                                <td>{{$item['financial_balance_items']['impunity_penalty']->description}}</td>
                                <td><span class="badge badge-info">جریمه</span></td>
                                <td>{{$item['financial_balance_items']['impunity_penalty']->creator->name.' '.$item['financial_balance_items']['impunity_penalty']->creator->family}}</td>
                                <td>{{ verta($item['financial_balance_items']['impunity_penalty']->created_at)->format('%d %B ، %Y - H:i:s')}}</td>
                                <td>
                                    @permission('delete-impunity')
                                    <form onsubmit="return formQuestionConfirm()" method="POST" action="{{route('totalFactorItem.destroyImpunity',[$item['financial_balance_items']['impunity_penalty']->id])}}">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <div class="form-group">
                                            <input type="submit" class="btn btn-sm btn-warning" value="حذف">
                                        </div>
                                    </form>
                                    @endpermission
                                </td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
    <div
        class="badge {{ $item['financial_balance_items']['financial_balance'] > 0 ? 'badge-danger' : 'badge-success' }} font-size-13 mb-3 p-2">
        تراز مالی پرونده ({{ $item['financial_balance_items']['financial_balance'] > 0 ? ' بدهکاری کل با جریمه : '
        :' بستانکاری کل با جریمه : ' }} {{
        number_format(abs($item['financial_balance_items']['financial_balance'])).' ریال ' }})
    </div>
    <div
    class="badge {{ $item['financial_balance_items']['financial_balance_without_extra_pay'] > 0 ? 'badge-danger' : 'badge-success' }} font-size-13 mb-3 p-2">
    تراز مالی پرونده ({{ $item['financial_balance_items']['financial_balance_without_extra_pay'] > 0 ? ' بدهکاری کل بدون جریمه : '
    :' بستانکاری کل بدون جریمه : ' }} {{
    number_format(abs($item['financial_balance_items']['financial_balance_without_extra_pay'])).' ریال ' }})
</div>
</div>
