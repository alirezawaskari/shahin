<div class="col-md-6">
    <table id="datatable-keytable" class="table table-striped">
        <thead>
        <tr>
            <th>انتخاب</th>
            <th>فروشنده</th>
            <th>ویژگی</th>
        </tr>
        </thead>
        <tbody>
        @foreach($attributes as $item)
            <tr>
                <td>
                    <div class="checkbox">
                        <label><input name="product_attribute_id[]" class="product_attribute_id" type="checkbox" value="{{$item->id}}"></label>
                    </div>
                </td>
                <td>{{$item->seller_type}}  {{$item->seller}}</td>
                <td>{{$item->attribute_title}} {{$item->value}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<br/> <br/>
<div class="col-12 col-md-12 mb-3">
    <button type="submit" class="btn btn-success saveCouponItem"> ثبت و افزودن محصول بعدی </button>
</div>

