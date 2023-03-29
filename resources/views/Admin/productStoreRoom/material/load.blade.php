
<div class="load" style="position: relative;">
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>ردیف</th>
        <th>نام</th>
        <th>نام وارد کننده</th>
        <th>قفسه-سطر-ستون</th>
        <th>واحد اصلی</th>
        <th>تاریخ انقضا</th>
        <th>اعلام انقضا (روز)</th>
        <th>نقطه سفارش</th>
        <th>زمان سفارش (روز)</th>
        <th>موجودی اولیه</th>
        <th>موجودی</th>
        <th>انبار</th>
        <th>تاریخ ورود</th>
        <th>اقدام</th>
    </tr>
    </thead>
    <tbody>
    @foreach($products_store_room as $ind=>$product)
        <tr>
            <td>{{++$ind}}</td>
            <td>{{ $product->name }}
                ( <span>برند : </span>{{$product->brand_title}} )
            </td>
            <td>{{ $product->user_id }}</td>
            <td>{{ $product->pivot->storage_num.' - '.$product->pivot->row_num.' - '.$product->pivot->col_num }}</td>
            <td>{{$product->main_unit_title}} </td>
            <td>
                @if($product->pivot->expire_date)
                    {{ verta($product->pivot->expire_date)->format('Y/m/d') }}
                @endif
            </td>
            <td>{{ $product->pivot->expire_alert ? $product->pivot->expire_alert : 'ندارد' }}</td>
            <td>{{ $product->pivot->order_point }}</td>
            <td>{{ $product->pivot->order_day }}</td>
            <td>{{$product->pivot->initial_stock}} </td>
            <td>{{$product->pivot->stock}} </td>
            <td>{{$product->store_room_name}} </td>
            <td>{{ verta($product->pivot->created_at)->format('Y/m/d') }}</td>
            <td>
                <div data-v-15965e3b="" class="dropdown">
                    <a href="#" data-toggle="dropdown" aria-haspopup="true"
                       aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                    </a>
                    <div class="dropdown-menu">
                        @if(isset($product->pivot->id))
                            <a href="/dashboard/editStoreRoomMaterial/{{$product->pivot->id}}/{{$product->id}}">
                                <button type="button" class="dropdown-item pointer">ویرایش</button>
                            </a>
                        @endif

                        @if(isset($product->pivot->id))
                            <form class="product_form" method="post"
                                  action="{{route('productStoreRoom.destroy',['id'=> $product->id])}}">
                                @method('DELETE') @csrf
                                <input type="hidden" name="product_store_room_id" value="{{$product->pivot->id}}">
                                <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف محصول از انبار شما
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
{{$products_store_room->links()}}