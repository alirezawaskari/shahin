<!-- attributeItemRequest -->
<div id="attributeItemRequest{{$attribute->id}}" class="modal fade attributeItemRequest" role="dialog">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div id="{{$attribute->id}}" class="modal-content content1">
            <div class="modal-header">
                <h4 class="modal-title"> درخواست {{$attribute->title}} جدید </h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body modal-lg">
                <div class="row">
                    <input class="hidden attribute_id" value="{{$attribute->id}}" title="attribute_id">
                    <div class="col-md-12 mb-4">
                        <label for="title_lbl"> نام  {{$attribute->title}} درخواستی </label>
                        <input id="title" type="text" class="form-control attribute_item_title" value="{{old('title')}}"
                               placeholder=" نام {{$attribute->title}} درخواستی خود را وارد نمایید" name="title">
                    </div>
                    @if($attribute->type == 'color')
                        <div class="col-12 col-md-12 mb-3">
                            <label>پالت رنگ </label>
                            <input type="color" class="form-control"
                                   placeholder="لطفا کد رنگ را وارد کنید" name="color_palette">
                            @if($errors->has('color_palette'))
                                <span class="help-block sub-error"> {{$errors->first('color_palette')}} </span>
                            @endif

                        </div>
                    @endif
                    <div class="col-md-12 mb-2">
                        <button type="button" class="btn btn-info" data-dismiss="modal">بستن</button>
                        <button type="button" class="btn btn-success addRequestAttributeItem">ارسال</button>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>
