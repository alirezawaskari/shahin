@if(\Auth::user()->isAbleTo('product-detail'))
    <br/>
    <h4 class="text-center">اطلاعات انبار </h4>
    <br/>
    <div class="col-12 col-md-6 mb-3">

            <label class="control-label">دسته بندی انبار</label>
            <select name="category_store_room_id" class="form-control" title="دسته بندی انبار">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($category_store_rooms as $category_store_room)
                    <option value="{{$category_store_room->id}}"
                            @if($pack_store_rooms)@if($pack_store_rooms->pivot->category_store_room_id == $category_store_room->id) selected @endif @endif>{{$category_store_room->title}}</option>
                @endforeach
            </select>
            @if($errors->has('category_store_room_id'))
                <span class="help-block sub-error"> {{$errors->first('category_store_room_id')}} </span>
            @endif
    </div>
    <div class="col-12 col-md-6 mb-3">
            <label class="control-label">انبار</label>
            <select name="store_room_id" class="form-control" title="انبار">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($store_rooms as $store_room)
                    <option value="{{$store_room->id}}"
                            @if($pack_store_rooms) @if($pack_store_rooms->pivot->store_room_id == $store_room->id) selected @endif @endif>{{$store_room->name}}</option>
                @endforeach
            </select>
            @if($errors->has('store_room_id'))
                <span class="help-block sub-error"> {{$errors->first('store_room_id')}} </span>
            @endif
        </div>
    <div class="col-12 col-md-6 mb-3">
            <label class="control-label">موجودی </label>
            <input id="stock_lbl" type="text" class="form-control"
                   name="stock"
                   placeholder="لطفا موجودی را وارد کنید"
                   value="@if($pack_store_rooms){{$pack_store_rooms->pivot->stock}}@endif">
            @if($errors->has('stock'))
                <span class="help-block sub-error"> {{$errors->first('stock')}} </span>
            @endif
        </div>


        <div class="col-12 col-md-6 mb-3">
            <label class="control-label">قیمت فروش </label>
            <input id="sales_price_lbl" type="text" class="form-control"
                   name="sales_price"
                   placeholder="لطفا قیمت فروش را وارد کنید"
                   value="@if($pack_store_rooms){{$pack_store_rooms->pivot->sales_price}}@endif">
            @if($errors->has('sales_price'))
                <span class="help-block sub-error"> {{$errors->first('sales_price')}} </span>
            @endif
        </div>
    <div class="col-12 col-md-6 mb-3">
            <label class="control-label">قیمت خرید </label>
            <input id="purchase_price_lbl" type="text" class="form-control"
                   name="purchase_price"
                   placeholder="لطفا قیمت خرید را وارد کنید"
                   value="@if($pack_store_rooms){{$pack_store_rooms->pivot->purchase_price}}@endif">
            @if($errors->has('purchase_price'))
                <span class="help-block sub-error"> {{$errors->first('purchase_price')}} </span>
            @endif
        </div>
    <div class="col-12 col-md-6 mb-3">
            <label class="control-label">واحد کل</label>
            <select name="main_unit_id" class="form-control">
                <option>لطفا انتخاب کنید..</option>
                @foreach($counting_units as $counting_unit)
                    <option value="{{$counting_unit->id}}"
                            @if($pack_store_rooms)@if($pack_store_rooms->pivot->main_unit_id == $counting_unit->id) selected @endif @endif>{{$counting_unit->title}}</option>
                @endforeach
            </select>
            @if($errors->has('main_unit_id'))
                <span class="help-block sub-error"> {{$errors->first('main_unit_id')}} </span>
            @endif
        </div>

    <div class="col-12 col-md-12 mb-3">
        <label ></label>
        <div class="col-md-3 col-sm-3 col-xs-12 ">
            <label class="control-label">مقدار کل</label>
            <input id="main_unit_value_lbl" type="text" class="form-control"
                   name="main_unit_value"
                   placeholder="لطفا مقدار کل را وارد کنید"
                   value="@if($pack_store_rooms){{$pack_store_rooms->pivot->main_unit_value}}@endif">
            @if($errors->has('main_unit_value'))
                <span class="help-block sub-error"> {{$errors->first('main_unit_value')}} </span>
            @endif
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 ">
            <label class="control-label">واحد جزء</label>
            <select name="sub_unit_id" class="form-control">
                <option value="">لطفا انتخاب کنید</option>
                @foreach($counting_units as $counting_unit)
                    <option value="{{$counting_unit->id}}"
                            @if($pack_store_rooms)@if($pack_store_rooms->pivot->sub_unit_id == $counting_unit->id) selected @endif @endif>{{$counting_unit->title}}</option>
                @endforeach
            </select>
            @if($errors->has('sub_unit_id'))
                <span class="help-block sub-error"> {{$errors->first('sub_unit_id')}} </span>
            @endif
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 ">
            <label class="control-label"> مقدار جزء </label>
            <input id="sub_unit_value_lbl" type="text" class="form-control"
                   name="sub_unit_value"
                   placeholder="لطفا مقدار جزء را وارد کنید"
                   value="@if($pack_store_rooms){{$pack_store_rooms->pivot->sub_unit_value}}@endif">
            @if($errors->has('sub_unit_value'))
                <span class="help-block sub-error"> {{$errors->first('sub_unit_value')}} </span>
            @endif
        </div>

    </div>


    <div class="col-12 col-md-12 mb-3">
        <label ></label>
        <div class="col-md-3 col-sm-3 col-xs-12 ">
            <label class="control-label">آدرس</label>
            <input id="address" type="text" class="form-control"
                   name="address"
                   placeholder="لطفا آدرس را وارد کنید"
                   value="@if($pack_store_rooms){{$pack_store_rooms->pivot->address}}@endif">
            @if($errors->has("address"))
                <span class="help-block sub-error"> {{$errors->first("address")}} </span>
            @endif
        </div>
        <div class="col-md-3 col-sm-3 col-xs-12 ">
            <label class="control-label">بارکد</label>
            <input id="barcode" type="text" class="form-control"
                   name="barcode"
                   placeholder="لطفا بارکد را وارد کنید"
                   value="@if($pack_store_rooms){{$pack_store_rooms->pivot->barcode}}@endif">
            @if($errors->has("barcode"))
                <span class="help-block sub-error"> {{$errors->first("barcode")}} </span>
            @endif
        </div>
    </div>

    <div class="col-12 col-md-12 mb-3">
        <label class="col-md-2 col-sm-2 col-xs-12 control-label"></label>
        <div class="col-md-4 col-sm-4 col-xs-12">
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="guarantee"
                           @if($pack_store_rooms && $pack_store_rooms->pivot->guarantee == 1) checked @endif
                           value="1">گارانتی
                </label>
            </div>
        </div>
    </div>


@endif