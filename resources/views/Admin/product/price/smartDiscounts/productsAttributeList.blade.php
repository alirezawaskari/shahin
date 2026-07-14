<div id="load" style="position: relative;">
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>انتخاب</th>
            <th>عکس</th>
            <th>کد کالا</th>
            <th> عنوان و کد تنوع کالا</th>
            <th>گروه کالا</th>
            <th> قیمت کالا <span class="font10 colorLGray disBlock">ریال</span></th>
            <th>تعداد بازدید</th>
            <th>تعداد فروش 7 روز گذشته</th>
        </tr>
        </thead>
        <tbody>
        @foreach($product_attributes as $item)
            <tr>
                <td>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input product_attribute_id" name="product_attribute_id[]"
                               id="product_attribute_id{{$item->id}}" value="{{$item->id}}" title="product_attribute_id">
                        <label class="custom-control-label" for="product_attribute_id{{$item->id}}"></label>
                    </div>
                </td>
                <td>
                    @if($item->product->image)
                        <img src="{{$item->product->image}}" alt="{{$item->product->title}}" width="60px"
                             class="border-radius5">
                    @endif
                </td>
                <td><span class="colorBlue">{{$item->product_id}}</span></td>
                <td class="lineHeight17">
                    <span class="disBlock"> {{$item->product->name}}</span>
                    <span> | {{$item->product->weight}} گرم </span>
                    <span> | {{ $item->guarantee}} </span>
                    <span class="laleP">{{$item->id}}</span>
                </td>
                <td>{{ $item->category_title }}</td>
                <td>{{ number_format($item->price) }}</td>
                <td>{{$item->number_visits}}</td>
                <td>0</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $product_attributes->links() }}