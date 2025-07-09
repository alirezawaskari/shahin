<!-- transferProductSellerModal -->
<div id="similarProducts{{$product_id}}" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div id="{{$product_id}}" class="modal-content content1">
            <div class="modal-header">
                <h4 class="modal-title">کالا ها مشابه</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body modal-lg">
                @if(count($similar_products_available) > 0)
                <div class="row similarProductsList" @if($product->message == null) style="display: block;" @else style="display: none;" @endif>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th>شناسه</th><th>کالا</th><th>اقدام</th></tr>
                        </thead>
                        <tbody>
                            @foreach($similar_products_available as $product)
                                <tr>
                                    <td>{{$product->id}}</td>
                                    <td>{{$product->name}}</td>
                                    <td><a id="{{$product_id}}" data-title="{{$product->id}}"
                                           class="btn btn-sm btn-success assignSimilarProduct">مشابه به این کالا</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                    <p>کالا مشابهی وجود ندارد</p>
                @endif

                <div class="row confirmSimilarProducts" @if($product_status == 7) style="display: block;" @else style="display: none;"  @endif>
                    <div class="col-12 col-md-12 mb-2 confirmSimilarProductsMsg">
                        {!! $message !!}
                    </div>
                    <div class="col-12 col-md-12 mb-2">
                        <button type="button" class="btn btn-sm btn-warning backSimilarProductsList" >برگشت به لیست کالا ها مشابه</button>
                        <button type="button" class="btn btn-sm btn-info" data-dismiss="modal">تایید</button>
                    </div>

                </div>
            </div>
            <div class="modal-footer"></div>
        </div>

    </div>
</div>
