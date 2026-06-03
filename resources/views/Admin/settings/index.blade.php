@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="page-header">
        <div>
            <h3>تنظیمات</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تنظیمات</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-8 ">
                        <form action="{{ route('settings.updateCompanyInfo') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="form-row border">
                                <div class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">اطلاعات حساب مالیاتی</div>
                                <div class="col-md-4 form-group">
                                    <label for="shaba_number">شماره شبا (IR0000000000000000)</label>
                                    <input type="text" name="shaba_number" class="form-control"   value="{{$company_info['shaba_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="card_number">شماره کارت</label>
                                    <input type="text" name="card_number" class="form-control"   value="{{$company_info['card_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="card_holder">نام کامل صاحب حساب</label>
                                    <input type="text" name="card_holder" class="form-control"   value="{{$company_info['card_holder'] ?? ''}}">
                                </div>
                                <hr>
                                <div class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">اطلاعات حساب معمولی</div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_shaba_number">شماره شبا (IR0000000000000000)</label>
                                    <input type="text" name="tax_free_shaba_number" class="form-control"   value="{{$company_info['tax_free_shaba_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_card_number">شماره کارت</label>
                                    <input type="text" name="tax_free_card_number" class="form-control"   value="{{$company_info['tax_free_card_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_card_holder">نام کامل صاحب حساب</label>
                                    <input type="text" name="tax_free_card_holder" class="form-control"   value="{{$company_info['tax_free_card_holder'] ?? ''}}">
                                </div>
                                <div class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">اطلاعات سایت</div>
                                <div class="col-md-12 form-group">
                                    <label for="fullName">آدرس</label>
                                    <input type="text" name="address" class="form-control" value="{{$company_info['address'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="fullName">تلفن</label>
                                    <input type="text" name="phone" class="form-control"   value="{{$company_info['phone'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="fullName">ایمیل</label>
                                    <input type="text" name="email" class="form-control  "   value="{{$company_info['email'] ?? ''}}">
                                </div>

                                <hr>

                                <div class="form-group col-md-12 ">
                                    <button type="submit" class="btn btn-success">ثبت </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-8 ">
                        <form action="{{route('settings.updateExpireDates')}}" method="post">
                            @csrf
                            @method('PATCH')
                            <div class="form-row border  ">
                                    <div class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">موعد تمدید دامنه ها</div>
                                <div class="col-md-2 form-group">
                                    <label for="fullName">lalecard.ir</label>
                                    <input type="text" name="domain_expire_date_lalecard_ir" class="form-control date" value="{{$expire_dates['domain_expire_date_lalecard.ir'] ?? ''}}">
                                </div>
                                <div class="col-md-2 form-group">
                                    <label for="fullName">lalecard.com</label>
                                    <input type="text" name="domain_expire_date_lalecard_com" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalecard.com'] ?? ''}}">
                                </div>
                             <div class="col-md-2 form-group">
                                    <label for="fullName">ghorfe-online.ir</label>
                                    <input type="text" name="domain_expire_date_ghorfe_online_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_ghorfe-online.ir'] ?? ''}}">
                                </div>
                             <div class="col-md-2 form-group">
                                    <label for="fullName">lalehkart.ir</label>
                                    <input type="text" name="domain_expire_date_lalehkart_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalehkart.ir'] ?? ''}}">
                                </div>
                             <div class="col-md-2 form-group">
                                    <label for="fullName">lalekart.ir</label>
                                    <input type="text" name="domain_expire_date_lalekart_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalekart.ir'] ?? ''}}">
                                </div>
                             <div class="col-md-2 form-group">
                                    <label for="fullName">lalemarket.ir</label>
                                    <input type="text" name="domain_expire_date_lalemarket_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalemarket.ir'] ?? ''}}">
                                </div>
                             <div class="col-md-2 form-group">
                                    <label for="fullName">qorfe-online.ir</label>
                                    <input type="text" name="domain_expire_date_qorfe_online_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_qorfe-online.ir'] ?? ''}}">
                                </div>
                             <div class="col-md-2 form-group">
                                    <label for="fullName">lalebank.com</label>
                                    <input type="text" name="domain_expire_date_lalebank_com" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalebank.com'] ?? ''}}">
                                </div>
                                <div class="card-header col-md-12 border   text-center border-right-0 border-left-0  ">موعد تمدید سرورها</div>
                                <div class="col-md-2 form-group">
                                    <label for="fullName">سرور مجازی رادکام</label>
                                    <input type="text" name="vps_expire_date" class="form-control date"  value="{{$expire_dates['vps_expire_date'] ?? ''}}">
                                </div>
                                <div class="card-header col-md-12 border   text-center border-right-0 border-left-0  ">موعد تمدید پنل ها</div>
                                <div class="col-md-2 form-group">
                                    <label for="fullName">پلسک</label>
                                    <input type="text" name="plesk_expire_date" class="form-control date"  value="{{$expire_dates['plesk_expire_date'] ?? ''}}">
                                </div>
                            <div class="form-group col-md-12 ">
                                <button type="submit" class="btn btn-success">ثبت </button>
                            </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-8 ">
                        <form action="{{route('settings.update.sms.setting')}}" method="post">
                            @csrf
                            <div class="form-row border">
                                <div class="card-header col-md-12 border  border-right-0 border-left-0  text-center ">اطلاعات سایت</div>
                                <div class="col-md-12 form-group p-3">
                                    <label for="fullName">پنل پیامکی</label>
                                    <select name="sms_panel" class="form-control select2">
                                        <option value="iranSms"
                                            {{ old('sms_panel', $company_info['sms_panel'] ?? '') == 'iranSms' ? 'selected' : '' }}>
                                            ایران اس ام اس
                                        </option>

                                        <option value="neginSms"
                                            {{ old('sms_panel', $company_info['sms_panel'] ?? '') == 'neginSms' ? 'selected' : '' }}>
                                            نگین اس ام اس
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12 ">
                                    <button type="submit" class="btn btn-success">ثبت </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop
@section('js')
    <script src="/assets/script/ckeditor/ckeditor.js"></script>
    <script>
    CKEDITOR.replace('content',ck_uploader);
    $(document).ready(function () {
        $(".date").pDatepicker({
        initialValueType: "gregorian",
        format: "YYYY/MM/DD",
        onSelect: "year",
        timePicker: {
        enabled: true,
        meridiem: {
        enabled: true
        }
        }
        });
    });
    </script>
@stop
