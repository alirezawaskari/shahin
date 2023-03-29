<div class="row">
    <div class="col-12 col-md-6 mb-3">
        <label for="name_lbl">نام کالا <span class="required">*</span> :</label>
        <input id="name_lbl" type="text" class="form-control good_name" name="good[name]"
               placeholder="ماهیت کالا + برند + مدل" value="{{old('good.name')}}">
        <p>مثال : گوشی موبایل سامسونگ سری A</p>
        @if($errors->has('good.name'))
            <span class="help-block sub-error"> {{$errors->first('good.name')}} </span>
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3">
        <label for="en_name_lbl">نام انگلیسی کالا </label>
        <input id="en_name_lbl" type="text" class="form-control text-left" name="good[en_name]"
               placeholder="Brand + Model + Nature of the Product" value="{{old('good.en_name')}}">
        @if($errors->has('good.en_name'))
            <span class="help-block sub-error"> {{$errors->first('good.en_name')}} </span>
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3 tagBox">
        <label for="name_lbl">تگ <span class="required">*</span> :</label>
        <select class="selectMultiTag form-control" name="tag[]" multiple="multiple">
            @foreach($tags as $tag)
                <option value="{{$tag->name}}">{{$tag->name}}</option>
            @endforeach
        </select>
        @if($errors->has('tag'))
            <span class="help-block sub-error"> {{$errors->first('tag')}} </span>
        @endif
    </div>

    <div class="col-12 col-md-6 mb-3 brandsBox">
        <label for="name_lbl">برند <span class="required">*</span> :</label>

        @if($errors->has('good.brand_id'))
            <span class="help-block sub-error"> {{$errors->first('good.brand_id')}} </span>
        @endif
    </div>

    @if($center_id =='')
    <div class="col-12 col-md-12 mb-3"><br/><br/>
        {{--checkBox--}}
        <div class="col-md-3 col-sm-3 col-xs-6">
            <div class="checkbox">
                <label>
                    <input class="good_is_sale" type="checkbox" name="good[is_sale]"
                           checked value="1">جهت عرضه در سایت
                </label>
                @if($errors->has('good.is_sale'))<span
                        class="help-block sub-error">{{$errors->first('good.is_sale')}}</span>@endif
            </div>
        </div>
        @if(isset($internal_use) && $internal_use == true)
        <div class="col-md-3 col-sm-3 col-xs-6">
            <div class="checkbox">
                <label class="internal_use">
                    <input type="checkbox" name="good[internal_use]"
                           @if(old('good.internal_use') == 1) checked @endif value="1">استفاده داخلی
                </label>
                @if($errors->has('good.internal_use'))<span
                        class="help-block sub-error">{{$errors->first('good.internal_use')}}</span>@endif
            </div>
        </div>
        <br/><br/>
            @endif
    </div>
    @endif
    <div class="col-12 col-md-6 mb-3">
        <label for="length_lbl">ابعاد بسته بندی (سانتی متر) <span class="required">*</span> :</label>

        <br/>
        <input id="length_lbl" type="number" min="1" class="form-control disInline good_length" style="width: 20%;" name="good[length]"
               placeholder="طول" value="{{old('good.length')}}">

        <input id="width_lbl" type="number" min="1" class="form-control disInline good_width" style="width: 20%;" name="good[width]"
               placeholder="عرض" value="{{old('good.width')}}">

        <input id="height_lbl" type="number" min="1" class="form-control disInline good_height" style="width: 20%;" name="good[height]"
               placeholder="ارتفاع" value="{{old('good.height')}}">
    </div>

    <div class="col-12 col-md-4 mb-3">
        <label for="weight_lbl">وزن بسته بندی (گرم) <span class="required">*</span> :</label>
        <input id="weight_lbl" type="number" class="form-control good_weight" name="good[weight]"
               placeholder="لطفا وزن را وارد کنید" value="{{old('good.weight')}}">
    </div>

    <div class="col-12 col-md-6 mb-3">
        @if($errors->has('good.length'))
            <span class="help-block sub-error"> {{$errors->first('good.length')}} </span>
        @endif
        <br/>
        @if($errors->has('good.width'))
            <span class="help-block sub-error"> {{$errors->first('good.width')}} </span>
        @endif
            <br/>
        @if($errors->has('good.height'))
            <span class="help-block sub-error"> {{$errors->first('good.height')}} </span>
        @endif
            <br/>
        @if($errors->has('good.weight'))
            <span class="help-block sub-error"> {{$errors->first('good.weight')}} </span>
        @endif
    </div>


    <br/><br/>
    <div class="col-12 col-md-12 mb-3">
        <label for="description_lbl">شرح کالا :</label>
        <textarea id="description_lbl" class="form-control" title="شرح کالا" rows="3" cols="5"
                  placeholder="شرح کالا"
                  name="good[description]">{{old('good.description')}}</textarea>
        @if($errors->has('good.description'))
            <span class="help-block sub-error"> {{$errors->first('good.description')}} </span>
        @endif
    </div>
    <br/><br/>


    <br/><br/>
    {{--review--}}
    <input class="counter_review_strength" value="1" type="hidden">
    <div class="col-12 col-md-6 mb-3">
        <div class="input-group mrg10">
            <input type="text" name="reviewstrength[0][text]" class="form-control" placeholder="نقاط قوت">
            <span class="input-group-btn addInputStrength"><button type="button"
                                                                   class="btn btn-info">+</button></span>
        </div>
        <div id="reviewstrength">

        </div>
    </div>

    <div class="col-12 col-md-6 mb-3">
        <div class="input-group mrg10">
            <input type="text" name="reviewWeakness[0][text]" class="form-control" placeholder="نقاط ضعف">
            <span class="input-group-btn addInputWeakness"><button type="button"
                                                                   class="btn btn-info">+</button></span>
        </div>
        <div id="reviewWeakness">

        </div>
    </div>


</div>
