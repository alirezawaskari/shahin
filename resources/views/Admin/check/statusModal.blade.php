<div class="modal fade " id="statusCheckModal" tabindex="-1" role="dialog" aria-labelledby="statusCheckLabel"
     aria-hidden="true" >
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ویرایش وضعیت چک </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body letter m-3 border  bg-check" >
                <div class="container">
                    <div class="row" id="check-status-update-error-box">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12" id="check-update-status-error-box"> </div>
                    <div class="col-md-12  " id="status-check-front-image" >
                    </div>
                    <div class="col-md-12">
                        <form id="status-check-form"  >
                            <div class="form-group col-md-4  ">
                                <input type="hidden" id="check-id" name="check_id">
                                <label>وضعیت :</label>
                                <select name="status" class="form-control"  id="">
                                    {{--                                        <option value="0">در انتظار وصول</option>--}}
                                    <option value="1">وصول شده</option>
                                    <option value="2">برگشت خورده</option>
                                    <option value="3">عودت داده شده</option>
                                </select>
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
