<!-- materialTitleProductModal -->
<div id="materialTitleProductModal" class="modal fade" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">ایجاد عنوان جدید</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="col-12 col-md-12 mb-3">
                    <label>عنوان *</label>
                    <input id="{{$arrayName}}_lbl" type="text" class="form-control" name="{{$arrayName}}[name]"
                           placeholder="لطفا عنوان را وارد کنید" value="{{old("$arrayName.name")}}">
                    @if($errors->has("$arrayName.name"))
                        <span class="help-block sub-error"> {{$errors->first("$arrayName.name")}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-12 mb-3">
                    <label>نام فارسی برند </label>
                    <input id="{{$arrayName}}_lbl" type="text" class="form-control" name="{{$arrayName}}[brand_name_fa]"
                           placeholder="لطفا نام فارسی برند را وارد کنید" value="{{old("$arrayName.brand_name_fa")}}">
                    @if($errors->has("$arrayName.brand_name_fa"))
                        <span class="help-block sub-error"> {{$errors->first("$arrayName.brand_name_fa")}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-12 mb-3">
                    <label>نام انگلیسی برند </label>
                    <input id="{{$arrayName}}_lbl" type="text" class="form-control" name="{{$arrayName}}[brand_name_en]"
                           placeholder="لطفا نام انگلیسی برند را وارد کنید" value="{{old("$arrayName.brand_name_en")}}">
                    @if($errors->has("$arrayName.brand_name_en"))
                        <span class="help-block sub-error"> {{$errors->first("$arrayName.brand_name_en")}} </span>
                    @endif
                </div>
              <br/><br/><br/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">ثبت</button>
            </div>
        </div>

    </div>
</div>
