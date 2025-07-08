<!-- transferProductSellerModal -->
<div id="insertValidation{{$field}}" class="modal fade insertValidationModal" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div id="{{$product_id}}" class="modal-content content1">
            <div class="modal-header">
                <h4 class="modal-title">درج خطا برای مقادیر کالا</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body modal-lg">
                <div class="row">
                    <input class="hidden field" title="field" name="field" value="{{$field}}">
                    <div class="col-12 col-md-10 mb-3">
                        @if(isset($validationable_id))
                            <?php
                             $product_validation=\App\Models\ProductValidation::where('product_id',$product_id)->
                            where('field',$field)->
                            where('productvalidationable_id',$validationable_id)->
                            where('productvalidationable_type',$validationable_type)->first()
                            ?>
                        @else
                            <?php
                            $product_validation = \App\Models\ProductValidation::where('product_id',$product_id)->
                            where('field',$field)->first()
                             ?>
                        @endif
                        <label for="en_name_lbl">متن پیغام : </label>
                        <select id="{{$field}}" class="form-control validation_id" name="validation_id" title="validation_id">
                            <option value=" "></option>
                            @foreach($validations as $validation)
                                <option value="{{$validation->id}}"
                                  @if(isset($product_validation)) @if($product_validation->validation_id == $validation->id) selected @endif @endif
                                >{{$validation->title}} </option>
                           @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-3">

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="{{$field}}" class="btn btn-success insertValidation">ثبت</button>
            </div>
        </div>


    </div>
</div>
