
@foreach($lists as $index=>$list)
    @if((isset($list['stock']) &&  $list['stock'] != []) || ( isset($list['attributes']) && $list['attributes'] != []) )

        <div class="disInline col-12 col-md-12 mb-4">

        <label>مقدار درخواستی برای <span>{{$list['name']}}   @if(isset($list['unit'])){{$list['unit']}} @endif</span>
        </label>

        @if($list['type'] == 0 || $list['internal_use'] == 1)
            <div class="disInline col-12 col-md-1 mb-4">
                <select class="form-control" name="products_number[{{$index}}][number]">
                    @for($i=1 ; $i <=$list['stock'] ; $i++)
                        <option value="{{$i}}" @if(isset($list['number']) && $list['number'] != 0)
                        @if($list['number'] == $i) selected @endif
                                @endif>{{$i}}</option>
                    @endfor
                </select>
            </div>

        @elseif($list['type'] == 1 || $list['internal_use'] == 0)
            <div class="row">
                <div class="col-12 col-md-12 mb-6">
                    @foreach($list['attributes'] as $ind=>$attributes)
                        <div class="col-md-3 mb-4 custom-control disInline custom-checkbox">
                            <input data-title="{{$attributes['stock']}}" type="checkbox"
                                   @if(isset($attributes['number']) && $attributes['number'] != 0) checked @endif
                                   class="custom-control-input" name="productAttributeSelect[]" id="customCheck{{$ind}}"
                                   value="{{$ind}}">
                            <label class="custom-control-label"
                                   for="customCheck{{$ind}}">{{$attributes['value']}} </label>

                            <select class="col-md-3 form-control disInline" name="product_attribute_ids[{{$ind}}][number]">
                                @for($i=1 ; $i <=$attributes['stock'] ; $i++)
                                    <option value="{{$i}}" @if(isset($attributes['number']) && $attributes['number'] != 0)
                                    @if($attributes['number'] == $i) selected @endif
                                            @endif>{{$i}}</option>
                                @endfor
                            </select>

                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    @endif
@endforeach

