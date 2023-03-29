@extends('Admin.master')

@section('content')
    <div class="page-header">
        <div>
            <h3>درخواست ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">درخواست ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <input type="button" class="btn btn-primary" value="درخواست واریز به حساب" data-toggle="modal" data-target="#requestModal">
        </div>
    </div>
    <div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ثبت درخواست واریز به حساب</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('service/request-create') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <textarea name="description" placeholder="لطفا درخواست خود را همراه مبلغ مورد نظر وارد کنید" class="form-control"></textarea>
                        <input type="hidden" name="unit_id" value="1">
                        <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">ثبت</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <wallets-suspension-data></wallets-suspension-data>
    <div class="card">
       <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-10">
                <h5> عملکرد کل : {{ $item['total_income'] }} ریال</h5>
                <h5> مبلغ دریافت شده : {{ $item['amount_received'] }} ریال</h5>
                <h5> مبلغ بستانکاری  : {{ $item['credit_amount'] }} ریال</h5>
                <h5> مبلغ قابل برداشت  : {{ $item['amount_payable'] }} ریال</h5>
                <h5> سپرده مقصد (شماره شبا) : {{ $item['shaba_number'] }}</h5>
            </div>
            <div class="col-12 col-md-2 float-left">
                <img src="{{ auth()->user()->avatar }}" width="85%">
            </div>
        </div>
    </div>
    </div>
    <wallet-data></wallet-data>
@stop
