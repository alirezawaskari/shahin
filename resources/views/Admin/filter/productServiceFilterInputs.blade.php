<div class="col-12 col-md-3 mb-3"><select name="product_category[]" id="product_category"
                                          class="custom-select" multiple="multiple">
        @foreach($product_categories as $category)
            <option value="{{$category['id']}}">{{$category['label']}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-3"><select name="service_category[]" id="service_category"
                                          class="custom-select select2" multiple="multiple">
        @foreach($service_categories as $service)
            <option value="{{$service['id']}}">{{$service['label']}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-3">
    <select name="sub_service_category[]" id="sub_service_category" class="custom-select select2" multiple="multiple">
        @foreach($services as $service)
            <option value="{{$service['id']}}">{{$service['label']}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-3">
    <select name="service_items[]" id="service_items" class="custom-select" multiple="multiple">
    </select>
</div>
