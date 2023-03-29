@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>هزینه</th>
                    <th>درصد پیش پرداخت</th>
                    <th>پیش پرداخت</th>
                    <th>تسهیلات</th>
                    <th>کارمزد</th>
                    <th>مالیات ارزش افزوده</th>
                    <th>کل پرداختی</th>
                    <th>مبلغ قسط</th>
                    <th>تعداد قسط</th>

                </tr>
                </thead>
                <tbody>
                @foreach($installment_details as $item)
                    <tr>
                        <td>{{number_format($item['cost'])}} </td>
                        <td>{{number_format($item['prepaymentـpercentage'])}} </td>
                        <td>{{number_format($item['prepayment'])}} </td>
                        <td>{{number_format($item['facilities'])}} </td>
                        <td>{{number_format($item['wage'])}} </td>
                        <td>{{number_format($item['valueـaddedـtax'])}} </td>
                        <td>{{number_format($item['total_facility_payment'])}} </td>
                        <td>{{number_format($item['installment_price'])}} </td>
                        <td>{{number_format($item['installment_count'])}} </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    </div>
@stop
@section('js')
@stop
