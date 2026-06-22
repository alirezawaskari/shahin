<!-- transferProductSellerModal -->
<div id="productSellers{{$product_id}}" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">

        <!-- Modal content-->
        <div id="{{$product_id}}" class="modal-content content1">
            <div class="modal-header">
                <h4 class="modal-title">فروشندگان کالا</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body modal-lg">
                <div class="row">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr><th>انبار / شعبه</th>
                                @if($product_type != 2)<th>موجودی </th>@endif<th>قیمت </th><th>اقدام</th></tr>
                        </thead>
                        <tbody>
                        @if($sellers)
                             @foreach($sellers as $item)
                            <tr>
                                <td>{{$item['seller_title']}}
                                @if(isset($item['attribute_id']))( {{$item['attribute_id']}} : @endif
                                @if(isset($item['attribute_item_id'])) {{$item['attribute_item_id']}} ) @endif
                                </td>
                                @if($product_type != 2)<td>@if(isset($item['stock'])){{$item['stock'] }} @endif </td>@endif
                                <td>@if(isset($item['price'])){{number_format($item['price']) }} @endif</td>
                                <td>
                                    <div data-v-15965e3b="" class="dropdown">
                                        <a href="#" data-toggle="dropdown" aria-haspopup="true"  aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                        </a>
                                        <div class="dropdown-menu">
                                            <a href="{{$item['link'] }}" >
                                                <button type="button" class="dropdown-item pointer">ویرایش</button>
                                            </a>
                                        </div>
                                    </div>

                            </tr>
                        @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
            </div>
        </div>

    </div>
</div>
