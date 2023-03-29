@extends('Admin.master')

@section('content')
<div class="page-header">
    <div>
        <h3>فهرست اقساط</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست اقساط</li>
            </ol>
        </nav>
    </div>
    <div class="btn-group" role="group">
        <div class="btn badge-success mb-3">
            جمع
            کل بدهکاری اقساط
            : {{ number_format($total_price) }}
            ریال
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered table-striped table-responsive-md" id="table-id">
                <thead>
                    <tr>
                        <th>نام</th>
                        <th>نام خانوادگی</th>
                        <th>شماره موبایل</th>
                        <th>مبلغ قسط (ریال)</th>
                        <th>سررسید قسط</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($installments as $key => $installment)
                    <tr class="active">
                        <td>{{ $installment->factoritem->factor->user->name }}</td>
                        <td>{{ $installment->factoritem->factor->user->family }}</td>
                        <td>{{ $installment->factoritem->factor->user->phone }}</td>
                        <td>{{ number_format($installment->price) }}</td>
                        <td>{{ verta($installment->date)->format('%d %B ، %Y - H:i:s') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="btn-group" role="group">
            <div class="btn mb-3">
                  جمع مبالغ صفحه
                : {{ number_format($installments->sum('price')) }}
                ریال
            </div>
        </div>
        @include('Admin.layouts.pagination')
    </div>
</div>
@stop
@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
@endsection