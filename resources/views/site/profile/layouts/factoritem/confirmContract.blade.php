<div class="col-11 card mb-3">
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
                    <p>با تایید این گزینه مفاد قرارداد فوق را مطالعه مینمایم و اقرار می کنم تایید اینجانب به منزله امضا
                        دیجیتالی مورد قبول اینجانب می باشد.</p>
                    <div class="form-check">
                        <input style="cursor: pointer" class="form-check-input" type="checkbox"
                            id="confirm-contract-check" name="confirm_contract_check" required>
                        <label style="cursor: pointer" class="form-check-label mr-3" for="confirm-contract-check">
                              قرارداد را تایید میکنم.
                        </label>
                    </div>
                </div>
            </div>
            <div class="col-md-12 alert alert-success" id="step-two">
                <strong>
                    مرحله دوم :
                </strong>
                وارد کردن کد تایید پیامک شده به خدمات گیرنده
            </div>
            <div class="row" id="step-two-box" style="position: relative ;width: 100%">

                <div class="col-md-12">
                    <div class="alert alert-primary" role="alert">
                        لطفا بر روی دکمه ارسال کد کلیک کرده و کد دریافت شده از طریق پیامک به شماره تماس {{ $factorItem->factor->user->phone }}  را
                        در قسمت کد دریافتی وارد کنید.
                    </div>
                </div>
                <div class="" id="step-two-cover"
                    style="position: absolute;top:0;left: -13px;width: 100%;height: 100%;z-index: 9999;background-color: rgba(0, 0, 0, 0.5);">
                </div>
                <div class="col-12 row">
                    <div class="col-12 col-md-5 mb-3">
                        <input type="number" min="123456" max="999999" class="form-control" id="verification_code"
                            name="verification_code" placeholder="لطفا کد دریافتی را وارد کنید">
                    </div>
                    <input type="hidden" id="total_factor_item" value="{{ $factorItem->totalFactorItem ? $factorItem->totalFactorItem->id : null}}">
                    <input type="hidden" id="factor_item" value="{{ $factorItem->id}}">

                    <div class="col-12 col-md-4">
                        <button class="send_confirm_contract_code btn btn-success px-3"> درخواست کد <i
                                class="fa fa-paper-plane mr-1" aria-hidden="true"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 text-center" id="loading">
        <div role="status" class="spinner-border text-primary">
        </div>
        <br>
        <span>در حال اعتبار سنجی کد...</span>
    </div>
</div>

