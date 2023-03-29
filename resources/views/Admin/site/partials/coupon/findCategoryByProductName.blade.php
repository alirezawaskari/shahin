<div class="col-12 col-md-6 mb-3"><label>محصولات</label>
<select class="form-control productSelect" name="products_ids[]" title="product_id">
    <option value=""></option>
    @foreach($products as $product)
        <option value="{{$product->id}}">{{$product->name}}</option>
    @endforeach
</select>
</div>