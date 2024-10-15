@extends('Admin.master')

@section('content')
    <div class="page-header">
        <div>
            <h3>حسابرسی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">حسابرسی کاربران</li>
                </ol>
            </nav>
        </div>
    </div>
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item col-md-4 text-center">
            <a class="nav-link show active" id="cashDebtorsList-tab" data-toggle="tab" href="#cashDebtorsList" role="tab"
                aria-controls="cashDebtorsList" aria-selected="false">فهرست بدهکارن نقدی</a>
        </li>
        <li class="nav-item col-md-4 text-center">
            <a class="nav-link show" id="installmentDebtorsList-tab" data-toggle="tab" href="#installmentDebtorsList" role="tab"
                aria-controls="installmentDebtorsList" aria-selected="false">فهرست بدهکارن اقساطی</a>
        </li>
        <li class="nav-item col-md-4 text-center">
            <a class="nav-link show" id="creditDebtList-tab" data-toggle="tab" href="#creditDebtList" role="tab"
                aria-controls="creditDebtList" aria-selected="false">فهرست کاربران دارای اعتبار</a>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade p-t-20 active show" id="cashDebtorsList" role="tabpanel" aria-labelledby="cashDebtorsList-tab">
           @include('Admin.accounting.cashDebtorsList')
        </div>
        <div class="tab-pane fade p-t-20" id="installmentDebtorsList" role="tabpanel" aria-labelledby="installmentDebtorsList-tab">
            @include('Admin.accounting.installmentDebtorsList')
        </div>
        <div class="tab-pane fade p-t-20" id="creditDebtList" role="tabpanel" aria-labelledby="creditDebtList-tab">
            @include('Admin.accounting.creditDebtList')
        </div>
    </div>
@stop

@section('js')
    <script src="/assets/dashboard/js/cashDebtors.js?v=1.0"></script>
    <script src="/assets/dashboard/js/installmentDebtors.js?v=1.0"></script>
    <script src="/assets/dashboard/js/creditDebt.js?v=1.0"></script>
    <script src="/assets/dashboard/js/layouts.js?v=1.0"></script>
@endsection
