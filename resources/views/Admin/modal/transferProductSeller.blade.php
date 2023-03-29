<!-- materialTitleProductModal -->
<div id="transferProductSeller{{$product_id}}" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div id="{{$product_id}}" class="modal-content content1">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">تایید عملیات فروش</h4>
            </div>
            <div class="modal-body modal-lg">
                <div class="row">
                    <div class="col-md-3 col-sm-3 col-xs-3">
                        <img src="{{\App\Helpers\Helper::product($product_id,'image')}}" class="border-radius5"
                             alt="{{\App\Helpers\Helper::product($product_id,'title')}}" width="120px">
                    </div>
                    <div class="col-md-8 col-sm-8 col-xs-8">
                        <h5>{{\App\Helpers\Helper::product($product_id,'name')}}</h5>
                    </div>
                </div>
                <br/>
                <div class="row">
                    <div class="col-md-4 col-sm-4 col-xs-4">
                        <p><span>گروه :</span>
                            <span>{{\App\Helpers\Helper::rootCategoryByProductId($product_id,'root_category_id')}}</span>
                        </p>
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-4">
                        <p>
                            <span>تعداد تنوع فعال :</span>
                            <span> {{\App\Models\ProductAttribute::productAttributeActive($product_id)}}</span>
                        </p>
                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-4">
                        <p>
                            <span>کمترین قیمت روی سایت :</span>
                            <span> {{number_format(\App\Models\ProductAttribute::minPriceProductAttributeActive($item->id))}}</span>
                            <span>ریال</span>
                        </p>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="{{$product_id}}" class="btn btn-default centerSellToo">ادامه</button>
            </div>
        </div>

        <div id="{{$product_id}}" class="modal-content content2" style="display: none;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">تایید عملیات فروش</h4>
            </div>
            <div class="modal-body modal-lg">
                <br/><br/>
                <h3 class="text-center">فروشنده گرامی ، </h3><br/>
                <p class="text-center">اکنون شما هم فروشنده این کالا هستید.</p>
                <br/><br/>
            </div>
            <div class="modal-footer">
                <a href="/dashboard/products/1" id="{{$product_id}}" class="btn btn-default centerVarietyGood">درج تنوع برای این کالا</a>
            </div>

        </div>

    </div>
</div>
