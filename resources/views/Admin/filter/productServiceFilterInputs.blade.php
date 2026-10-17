<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">دسته‌بندی کالا</label>
    <select name="product_category[]" id="product_category" class="custom-select select2 form-select form-select-sm" multiple="multiple" data-placeholder="انتخاب دسته‌بندی کالا">
        @if(isset($product_categories))
            @foreach($product_categories as $category)
                <option value="{{$category['id']}}">{{$category['label']}}</option>
            @endforeach
        @endif
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">دسته‌بندی خدمت</label>
    <select name="service_category[]" id="service_category" class="custom-select select2 form-select form-select-sm" multiple="multiple" data-placeholder="انتخاب دسته‌بندی خدمت">
        @if(isset($service_categories))
            @foreach($service_categories as $service)
                <option value="{{$service['id']}}">{{$service['label']}}</option>
            @endforeach
        @endif
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">خدمت درخواستی</label>
    <select name="sub_service_category[]" id="sub_service_category" class="custom-select select2 form-select form-select-sm" multiple="multiple" data-placeholder="انتخاب خدمت درخواستی">
        @if(isset($services))
            @foreach($services as $service)
                <option value="{{$service['id']}}">{{$service['label']}}</option>
            @endforeach
        @endif
    </select>
</div>
<div class="col-12 col-md-3 mb-2">
    <label class="form-label small fw-bold text-dark mb-1">آیتم و نوع خدمت</label>
    <select name="service_items[]" id="service_items" class="custom-select select2 form-select form-select-sm" multiple="multiple" data-placeholder="انتخاب نوع خدمت">
    </select>
</div>
