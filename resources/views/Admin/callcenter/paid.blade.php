@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست تسک های پرداخت شده</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="/dashboard/callcenter">تسک ها</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست تسک های پرداخت شده</li>
                </ol>
            </nav>
        </div>
    </div>
    <service-modal></service-modal>
    <task-paid-data></task-paid-data>
@stop
