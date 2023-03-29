<div class="col-12 col-md-3 mb-3"><select name="product_category" id="product_category"
                                          class="custom-select">
        <option value="all">دسته بندی کالا</option>
        @foreach($product_categories as $category)
            <option value="{{$category['id']}}">{{$category['label']}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-3"><select name="service_category" id="service_category"
                                          class="custom-select select2">
        <option value="all">انتخاب دسته بندی خدمت</option>
        @foreach($service_categories as $service)
            <option value="{{$service['id']}}">{{$service['label']}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-3">
    <select name="sub_service_category" id="sub_service_category" class="custom-select select2">
        <option value="all">انتخاب خدمت درخواستی</option>
        @foreach($services as $service)
            <option value="{{$service['id']}}">{{$service['label']}}</option>
        @endforeach
    </select>
</div>
<div class="col-12 col-md-3 mb-3">
    <select name="service_items[]" id="service_items" class="custom-select" multiple="multiple">
    </select>
</div>
