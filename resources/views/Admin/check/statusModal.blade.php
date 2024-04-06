<div class="modal fade " id="statusCheckModal" tabindex="-1" role="dialog" aria-labelledby="statusCheckLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ویرایش وضعیت چک </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body letter m-3 border  bg-check">
                <div class="container">
                    <div class="row" id="check-status-update-error-box">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" id="check-update-status-error-box"> </div>
                    <div class="col-md-12  " id="status-check-front-image">
                    </div>
                    <div class="col-md-12">
                        <form id="status-check-form">
                            <div class="form-group col-md-4  ">
                                <input type="hidden" id="check-id" name="check_id">
                                <label>وضعیت :</label>
                                <select name="status" class="form-control" id="check-change-status">
                                    {{-- <option value="0">در انتظار وصول</option>--}}
                                    <option value="1">وصول شده</option>
                                    <option value="2">برگشت خورده</option>
                                    <option value="3">عودت داده شده</option>
                                    <option value="11">پرداخت به دیگری</option>
                                </select>
                            </div>
                            <div class="form-group col-md-4 " id="payment-date-box">
                                <label>تاریخ وصول :</label>
                                    <input name="payment_date" id="check-payment-date" class="form-control check-date"
                                required>
                            </div>
                            <div class="form-group col-md-12 row " id="receiver-box" style="display: none">
                                <div class="col-md-12 text-center card-title ">
                                    اطلاعات تحویل گیرنده
                                    <hr>
                                </div>
                                <div class="  form-group col-md-4">
                                    <label>شماره موبایل تحویل گیرنده (پرداخت شده به) </label>
                                    <input id="check-receiver-phone" name="receiver_phone"
                                        oninput="searchUser(this.value,'receiver')" class="form-control" placeholder=""
                                        value="" required>
                                    <p class="text-danger text-center receiver_error_user"></p>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>نام</label>
                                    <input class="form-control my-2 receiver_name" type="text" id="receiver_name"
                                        name="receiver_name" required placeholder="نام" value=""
                                        pattern="^[\u0600-\u06FF\s]+$" title="لطفا با حروف فارسی وارد نمایید">
                                    @if ($errors->has('name'))
                                    <span class="d-block text-red text-left">{{ $errors->first('name')}}</span>
                                    @endif
                                    <input type="hidden" class="receiver_id" id="receiver-id" name="receiver_id">
                                </div>

                                <div class="form-group col-md-4">
                                    <label>نام خانوادگی</label>
                                    <input class="form-control my-2 receiver_family" type="text" id="receiver-family"
                                        name="receiver_family" required placeholder="نام خانوادگی" value=""
                                        pattern="^[\u0600-\u06FF\s]+$" title="لطفا با حروف فارسی وارد نمایید">
                                    @if ($errors->has('family'))
                                    <span class="d-block text-red text-left">{{ $errors->first('family')}}</span>
                                    @endif
                                </div>
                                <div class="form-group col-md-4">
                                    <label>جنسیت</label>
                                    <div class="form-check">
                                        <input type="radio" name="receiver_gender" id="receiver-female-gender" value="1"
                                            class="form-check-input receiver_gender" style="cursor: pointer;">
                                        <label for="receiver-female-gender" class="form-check-label ">
                                            خانم
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" name="receiver_gender" id="receiver-male-gender"
                                            checked="checked" value="0" class="form-check-input receiver_gender"
                                            style="cursor: pointer;">
                                        <label for="receiver-male-gender" class="form-check-label">
                                            آقا
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <span class="btn btn-success m-2" id="update-check-status-btn">ثبت</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">خروج</button>
            </div>
        </div>
    </div>
</div>
