@extends('Admin.master')
@section('title')رزومه کاری@endsection

@section('content')

<div class="col-12 card">
    <div class="card-body">
        <h5 class="card-title">اطلاعات کاربری من</h5>
        <hr>
        <div class="row">
            <div class="col-12 col-md-10">
                <h5> نام : {{ $employee->user->name.' '. $employee->user->family }}</h5>
                <h5> شماره تماس : {{ $employee->user->phone }}</h5>
                <h5> استان : {{ $employee->user->province ? $employee->user->province->name : 'ثبت نشده' }}</h5>
                <h5> نمایندگی : {{ $employee->user->admin_representation ?
                    $employee->user->admin_representation->fa_name : 'م تهران' }}</h5>
            </div>
            <div class="col-12 col-md-2">
                <div class="row">
                    <div class="col-12 float-left">
                        <img src="{{ $employee->user->avatar }}" width="100%">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @include('Admin.employee.resume.form')
    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@endsection
