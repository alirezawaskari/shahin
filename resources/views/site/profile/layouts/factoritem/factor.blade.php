@extends('site.master')

@section('content')
<style>
    @media print {
  .noprint{
    display:none;
  }
}
</style>
<div class="text-center">
    <button class="noprint btn btn-danger py-1 px-2 mt-4" onclick="printSection()"><i class="fa fa-file-pdf-o"></i>
ذخیره به صورت PDF</button>
    <button onclick="window.print();" class="noprint btn btn-secondary py-1 px-2 mt-4"><i class="fa fa-print" aria-hidden="true"></i>
پرینت قرارداد</button>
</div>
<div class="p-3">
    <div id="pdf-section" class="section position-relative">
        <div class="overlay position-absolute"></div>
        <div class="container col-10">
            <div class="row">
                <div class="col-12 my-auto">
                    @if($item['type'] == 1 && $totalFactorItem)
                    <div class="card" style="background-color: #fafafa;">
                        <div class="col-12">
                            @if(!is_null($item['centers']) || !is_null($item['forms']))
                            <div class="row p-3 position-relative">
                                @if(!is_null($item['forms']))
                                @foreach($item['forms'] as $form)
                                @if (in_array('show_factor_site' , $form->data_display_level))
                                @include('Admin.factoritem.layouts.formItem')
                                @endif
                                @endforeach
                                @endif
                            </div>
                            @if($item['status_factor'] == 20)
                            <div class="alert alert-danger">
                                <p>کاربر گرامی توجه داشته باشید : </p>
                                <p>* در صورتی که به مورد یا مواردی از فاکتور صادره اعتراض داشته یا نیاز به
                                    تغییر آن موارد دارید از کلید درخواست اصلاح برای آن مورد استفاده نمائید.</p>
                                <p>* در صورت رد و اعتراض به کل فاکتور صادره صرفا یکبار میتوانید از دکمه درخواست اصلاح
                                    تمام
                                    فاکتور استفاده نمائید
                                    خاطر نشان میدارد شماره و نشانی شعبه صادر کننده فاکتور در پیامک برای شما ارسال شده از
                                    طریق ارتباط با شعبه یاد شده درخواست اصلاح فاکتور را هماهنگ نمائید پس از یکبار
                                    اصلاحیه یا انقضای مدت زمان اعتبار فاکتور و عدم تائید از سوی شما در زمان مقرر هزینه
                                    نام نویسی یا مشاوره / معاینه / پرداخت شده صفر میگردد و نیاز به ثبت درخواست و واریز
                                    هزینه مجدد و اجرای کامل روند نوبت دهی یا مشاوره حضوری یا آنلاین دارد.</p>
                            </div>
                            <span class="btn btn-danger py-1 px-2 text-white mt-4" data-toggle="modal"
                                data-target="#rejectFactorModal">درخواست اصلاح فاکتور</span>
                            @include('site.profile.layouts.factoritem.confirmFactor')
                            @endif
                            @else
                            <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                <div>
                                    @if(!is_null($item['service_item']))
                                    <span>{{ $item['service']->title . '-' . $item['service_item']->title }}</span>
                                    @else
                                    <span>{{ $item['service']->title }}</span>
                                    @endif
                                </div>
                                <i class="fa fa-check font-18 text-success"></i>
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="card" style="background-color: #fafafa;">
                        <div class="col-12">
                            @if(!is_null($item['centers']) || !is_null($item['forms']))
                            <div class="row p-3 position-relative">
                                @if(!is_null($item['forms']))
                                @foreach($item['forms'] as $form)
                                @if (in_array('show_factor_site' , $form->data_display_level))
                                @include('Admin.factoritem.layouts.formItem')
                                @endif
                                @endforeach
                                @endif
                            </div>
                            @if($item['status_factor'] == 20)
                            <div class="alert alert-danger">
                                <p>کاربر گرامی توجه داشته باشید : </p>
                                <p>* در صورتی که به مورد یا مواردی از فاکتور صادره اعتراض داشته یا نیاز به
                                    تغییر آن موارد دارید از کلید درخواست اصلاح برای آن مورد استفاده نمائید.</p>
                                <p>* در صورت رد و اعتراض به کل فاکتور صادره صرفا یکبار میتوانید از دکمه درخواست اصلاح
                                    تمام
                                    فاکتور استفاده نمائید
                                    خاطر نشان میدارد شماره و نشانی شعبه صادر کننده فاکتور در پیامک برای شما ارسال شده از
                                    طریق ارتباط با شعبه یاد شده درخواست اصلاح فاکتور را هماهنگ نمائید پس از یکبار
                                    اصلاحیه یا انقضای مدت زمان اعتبار فاکتور و عدم تائید از سوی شما در زمان مقرر هزینه
                                    نام نویسی یا مشاوره / معاینه / پرداخت شده صفر میگردد و نیاز به ثبت درخواست و واریز
                                    هزینه مجدد و اجرای کامل روند نوبت دهی یا مشاوره حضوری یا آنلاین دارد.</p>
                            </div>
                            <span class="btn btn-danger py-1 px-2 text-white mt-4" data-toggle="modal"
                                data-target="#rejectFactorModal">درخواست اصلاح فاکتور</span>
                            @include('site.profile.layouts.factoritem.confirmFactor')
                            @endif
                            @else
                            <div class="bg-light p-3 font-14 d-flex align-items-center justify-content-between">
                                <div>
                                    @if(!is_null($item['service_item']))
                                    <span>{{ $item['service']->title . '-' . $item['service_item']->title }}</span>
                                    @else
                                    <span>{{ $item['service']->title }}</span>
                                    @endif
                                </div>
                                <i class="fa fa-check font-18 text-success"></i>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@if(session()->get('success')=='success')
<span id="contact_success"></span>
@endif
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
    var total_factor_item = $('#total_factor_item').val();
    var factor_item = $('#factor_item').val();
    $.ajax({
        url: "/factorItem/sendConfirmContractCode",
        type: 'post',
        data: {
            "_token": token,
            "confirm_contract_check": confirm_contract_check,
            "total_factor_item": total_factor_item,
            "factor_item": factor_item,
        },
        success: function (response) {
            swal(response['message'], "", response['status'])
        }
    });
    });
    //verification confim factor
    $("body").on('keyup','#verification_factor_confirm_code',function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var verification_code = $('#verification_factor_confirm_code').val();
    var total_factor_item = $('#total_factor_item').val();
    var factor_item = $('#factor_item').val();

    if(verification_code.length == 6)
    {
        $('#loading').css('display', 'block');
        $.ajax({
        url: "/factorItem/verificationConfirmContractCodeFactor",
        type: 'post',
        data: {
            "_token": token,
            "verification_code": verification_code,
            "total_factor_item": total_factor_item,
            "factor_item": factor_item,
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function printSection() {
    const element = document.getElementById('pdf-section');
    html2pdf()
        .from(element)
        .save('contract.pdf');
}
</script>
<script src="{{asset('assets/dashboard/js/site/adminJs.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/factorItem.js?v=1.3') }}"></script>
@endsection
