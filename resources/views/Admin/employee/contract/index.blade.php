@extends('Admin.master')
@section('title')قرارداد همکاری@endsection

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
    <div class="card-body p-1 p-md-4">
        @if (in_array($employee->contract_status , [1,2,3]))
        @include('Admin.employee.contract.content')
        @include('Admin.totalfactoritem.layouts.contract.histories')
        @endif
    </div>
</div>
@stop
@section('js')
<script>
    $('#loading').css('display', 'none');
    $('#confirm-contract-check').prop('checked', false);
    $('#confirm-contract-check').change(function () {
        if (this.checked) {
            $('#step-two-cover').slideUp();
        } else {
            $('#step-two-cover').slideDown();
        }
    });
    $("body").on('click','.send_confirm_contract_code',function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var confirm_contract_check = $('#confirm-contract-check').prop('checked');
    $.ajax({
        url: "/factorItem/sendConfirmContractCode",
        type: 'post',
        data: {
            "_token": token,
            "confirm_contract_check": confirm_contract_check,
        },
        success: function (response) {
            swal(response['message'], "", response['status'])
        }
    });
    });
    $("body").bind('keyup','#verification_code',function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var verification_code = $('#verification_code').val();

    if(verification_code.length == 6)
    {
        $('#loading').css('display', 'block');
        $.ajax({
        url: "/factorItem/verificationConfirmContractCodeEmployee",
        type: 'post',
        data: {
            "_token": token,
            "verification_code": verification_code,
        },
        success: function (response) {
            swal(response['message'], "", response['status']);
            if(response['status'] == 'success')
            {
                location.reload();
            }
            $('#loading').css('display', 'none');
        }
    });
    }
});
</script>
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@endsection
