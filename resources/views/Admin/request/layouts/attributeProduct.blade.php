@foreach($result as $list)
    @foreach($list as $index=>$product)

        <div class="disInline col-12 col-md-12 mb-4">
            <label class="col-md-4 disInline"> <span>{{$product['name']}}</span> </label>

            @foreach($product['attributes'] as $ind=>$attributes)
                <div class="col-md-3 custom-control custom-checkbox disInline">
                    <input data-title="{{$attributes['stock']}}" type="checkbox"
                           class="custom-control-input" name="productAttributeSelect[]" id="customCheck{{$ind}}"
                           value="{{$ind}}">
                    <label class="custom-control-label" for="customCheck{{$ind}}">{{$attributes['value']}} </label>

                    <select class="col-md-3 form-control disInline" name="attribute_id[{{$ind}}][number]">
                        @for($i=1 ; $i <=$attributes['stock'] ; $i++)
                            <option value="{{$i}}" @if(isset($list['number']) && $list['number'] != 0)
                            @if($list['number'] == $i) selected @endif
                                    @endif>{{$i}}</option>
                        @endfor
                    </select>

                </div>
            @endforeach

        </div>
    @endforeach
@endforeach