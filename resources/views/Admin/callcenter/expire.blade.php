@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست تسک های تعلیقی</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="/dashboard/callcenter">تسک ها</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست تسک های تعلیقی</li>
                </ol>
            </nav>
        </div>
    </div>
    <service-modal></service-modal>
    <task-expire-data></task-expire-data>
@stop
