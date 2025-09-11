<div class="card-body p-1 p-md-4">
    <div class="col-12 mb-3">
        <div class="row">
            <div class="col-6 col-md-10">
                <h5>
                    قرارداد همکاری
                </h5>
            </div>
            <div class="col-6 col-md-2">
                @if ($employee->confirm)
                <h5 class="btn btn-success float-left">قرارداد تایید شده</h5>
                @endif
            </div>
        </div>
        <hr>
        <div id="printableAreaContract" class="bg-white">
            <div class="border-contract pr-1 pr-md-5 pb-4 mb-4">
                <div class="row text-center">
                    <div class="col-12 col-md-3 mt-4">
                        <p> تاریخ : {{ verta($employee->contract_start_date)->format('Y/m/d') }} </p>
                        <p> شماره قرارداد : {{ $employee->contract_number }} </p>
                    </div>
                    <div class="col-12 col-md-6 mt-4 text-center">
                        <h3> خدمات رفاهی لاله</h3>
                        <h5>قرارداد کاری بین همکار و شرکت</h5>
                    </div>
                    <div class="col-12 col-md-3 mt-3">
                        <img src="{{ asset('assets/dashboard/images/laleh_icon.png') }}" width="150px">
                    </div>
                </div>
            </div>
            <div class="border-contract page-break-first p-md-6" style="text-align: justify;">
                {!! $employee->contract !!}
            </div>
        </div>
        @if (isset($confirmable) && $confirmable)
        <div class="col-12 card mt-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">فرم امضا الکترونیکی قرارداد :</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 alert alert-success">
                        <strong>
                            مرحله اول :
                        </strong>
                        مطالعه و تایید قرارداد
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <p>با تایید این گزینه مفاد قرارداد فوق را مطالعه مینمایم و اقرار می کنم تایید اینجانب به
                                منزله امضا
                                دیجیتالی مورد قبول اینجانب می باشد.</p>
                            <div class="form-check">
                                <input style="cursor: pointer" class="form-check-input" type="checkbox"
                                    id="confirm-contract-check" name="confirm_contract_check" required>
                                <label style="cursor: pointer" class="form-check-label mr-3"
                                    for="confirm-contract-check">
                                    قرارداد را تایید میکنم.
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 alert alert-success" id="step-two">
                        <strong>
                            مرحله دوم :
                        </strong>
                        وارد کردن کد تایید پیامک شده به همکار
                    </div>
                    <div id="step-two-box" style="position: relative ;width: 100%">
                        <div class="alert alert-primary" role="alert">
                            لطفا بر روی دکمه ارسال کد کلیک کرده و کد دریافت شده از طریق پیامک به شماره تماس {{
                            auth()->user()->phone }} را
                            در قسمت کد دریافتی وارد کنید.
                        </div>
                        <div id="step-two-cover"
                            style="position: absolute;top:0;width: 100%;height: 100%;z-index: 9999;background-color: rgba(0, 0, 0, 0.5);">
                        </div>
                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <input type="number" min="123456" max="999999" class="form-control"
                                    id="verification_code" name="verification_code"
                                    placeholder="لطفا کد دریافتی را وارد کنید">
                            </div>
                            <div class="col-md-4 text-center">
                                <button class="send_confirm_contract_code btn btn-success px-3 w-100"> درخواست کد <i
                                        class="fa fa-paper-plane mr-1" aria-hidden="true"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 text-center"
                id="loading">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال اعتبار سنجی کد...</span>
            </div>
        </div>
        @endif
    </div>
</div>
