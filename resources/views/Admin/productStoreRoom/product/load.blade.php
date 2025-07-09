<div class="load" style="position: relative;">
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ردیف</th>
        <th>نام</th>
        <th>نام وارد کننده</th>
        <th>نوع</th>
        <th>قفسه-سطر-ستون</th>
        <th>واحد اصلی</th>
        <th>تاریخ انقضا</th>
        <th>اعلام انقضا (روز)</th>
        <th>نقطه سفارش</th>
        <th>زمان سفارش (روز)</th>
        <th>انبار</th>
        <th>تاریخ ورود</th>
        <th>اقدام</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products as $ind=>$item)
        <tr {{ $item->expire_date && $item->expire_date < Carbon\Carbon::now() ? 'class=table-danger' : '' }}>
            <td>{{ ++$ind  }}</td>
            <td>{{ $item->name}}
                ( <span>برند : </span>{{$item->brand_title}} )
            </td>
            <td>{{ $item->user_id }}</td>
            <td>
                @if(isset($item->category_store_room_title)) {{ $item->category_store_room_title }} @endif
            </td>
            <td>{{ $item->pivot->storage_num.' - '.$item->pivot->row_num.' - '.$item->pivot->col_num }}</td>
            <td>{{ $item->main_unit_title }}</td>
            <td>{{ verta($item->pivot->created_at)->format('Y/m/d')  }}</td>
            <td>{{ $item->pivot->expire_alert ? $item->pivot->expire_alert : 'ندارد' }}</td>
            <td>{{ $item->pivot->order_point }}</td>
            <td>{{ $item->pivot->order_day }}</td>
            <td>{{ $item->store_room_name }}</td>
            <td>{{ verta($item->pivot->created_at)->format('Y/m/d') }}</td>
            <td>
                <div data-v-15965e3b="" class="dropdown">
                    <a href="#" data-toggle="dropdown" aria-haspopup="true"
                       aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu">
                        @if(isset($item->pivot->id))
                            @if($item->internal_use == 1)
                                <a href="/dashboard/editStoreRoomMaterial/{{$item->pivot->id}}/{{$item->id}}">
                                    <button type="button" class="dropdown-item pointer">ویرایش</button>
                                </a>
                            @else
                                <a href="/dashboard/editStoreRoomGood/{{$item->pivot->id}}/{{$item->id}}">
                                    <button type="button" class="dropdown-item pointer">ویرایش</button>
                                </a>
                            @endif
                            @if($item->type == 1)
                                <a href="/dashboard/products/visit/{{$item->id}}">
                                    <button type="button" class="dropdown-item pointer">مشاهده</button>
                                </a>
                            @endif
                        @endif


                        @if(\App\Models\Attribute::attributeIsPriceByLastCat($item) > 0)
                            <a href="/dashboard/product/pricesList/{{$item->id}}">
                                <button type="button" class="dropdown-item pointer">تنوع کالا
                                </button>
                            </a>
                        @else
                            <a href="/dashboard/product/pricesListWithoutAttribute/{{$item->id}}">
                                <button type="button" class="dropdown-item pointer">تنوع کالا
                                </button>
                            </a>
                        @endif

                        @if(isset($item->pivot->id))
                            <form class="product_form" method="post"
                                  action="{{route('productStoreRoom.destroy',['id'=>$item->id])}}">
                                @method('DELETE')@csrf
                                <input type="hidden" name="product_store_room_id" value="{{$item->pivot->id}}">
                                <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف کالا از انبار شما
                                </button>
                            </form>
                        @endif

                    </div>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
{{$products->links()}}
