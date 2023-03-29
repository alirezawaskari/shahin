@if(\Auth::user()->isAbleTo('product-detail'))
    <input name="product_store_room_id" value="{{$store_rooms_rec->pivot->id}}" type="hidden">
    <div class="row">
        <div class="col-12 col-md-12 mb-3">
            <h4 class="text-center">ویرایش اطلاعات انبار * </h4>
        </div>
        @if($type != 0)
        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">نوع خرید</label>
            <select name="purchase_type" class="form-control">
                <option value="0"
                        @if($store_rooms_rec)@if($store_rooms_rec->pivot->purchase_type === 0) selected @endif @endif >
                    آنلاین
                </option>
                <option value="1"
                        @if($store_rooms_rec)@if($store_rooms_rec->pivot->purchase_type === 1) selected @endif @endif>
                    حضوری
                </option>
                <option value="2"
                        @if($store_rooms_rec)@if($store_rooms_rec->pivot->purchase_type === 2) selected @endif @endif>هر
                    دو
                </option>
            </select>
            @if($errors->has("purchase_type"))
                <span class="help-block sub-error"> {{$errors->first("purchase_type")}} </span>
            @endif
        </div>
        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">نوع پرداخت</label>
            <select name="pay_type" class="form-control">
                <option value="0" @if($store_rooms_rec)@if($store_rooms_rec->pivot->pay_type === 0) selected @endif @endif>نقدی
                </option>
                <option value="1" @if($store_rooms_rec)@if($store_rooms_rec->pivot->pay_type === 1) selected @endif @endif>اقساط
                </option>
                <option value="2" @if($store_rooms_rec)@if($store_rooms_rec->pivot->pay_type === 2) selected @endif @endif>هر دو
                </option>
            </select>
            @if($errors->has("pay_type"))
                <span class="help-block sub-error"> {{$errors->first("pay_type")}} </span>
            @endif
        </div>
        @endif

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">قیمت خرید</label>
            <input type="text" name="purchase_price" id="currency-field"
                   value="@if($store_rooms_rec){{number_format($store_rooms_rec->pivot->purchase_price)}}@endif"
                   class="form-control" data-type="currency" placeholder="لطفا قیمت خرید را وارد کنید">
            @if($errors->has("purchase_price"))
                <span class="help-block sub-error"> {{$errors->first("purchase_price")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">قیمت فروش</label>
            <input type="text" name="sales_price" id="currency-field"
                   value="@if($store_rooms_rec){{number_format($store_rooms_rec->pivot->sales_price)}}@endif"
                   class="form-control" data-type="currency" placeholder="لطفا قیمت فروش را وارد کنید">
            @if($errors->has("sales_price"))
                <span class="help-block sub-error"> {{$errors->first("sales_price")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تخفیف</label>
            <input type="text" name="discount" id="currency-field"
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->discount}}@endif"
                   class="form-control" data-type="currency" placeholder="لطفا تخفیف را وارد کنید">
            @if($errors->has("discount"))
                <span class="help-block sub-error"> {{$errors->first("discount")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">انبار</label>
            <select name="store_room_id" class="form-control" title="انبار">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($store_rooms as $store_room)
                    <option value="{{$store_room->id}}"
                            @if($store_rooms_rec) @if($store_rooms_rec->pivot->store_room_id == $store_room->id) selected @endif @endif>{{$store_room->name}}</option>
                @endforeach
            </select>
            @if($errors->has("store_room_id"))
                <span class="help-block sub-error"> {{$errors->first("store_room_id")}} </span>
            @endif
        </div>


        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">دسته بندی انبار</label>
            <select name="category_store_room_id" class="form-control" title="دسته بندی انبار">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($category_store_rooms as $category_store_room)
                    <option value="{{$category_store_room->id}}"
                            @if($store_rooms_rec)@if($store_rooms_rec->pivot->category_store_room_id == $category_store_room->id) selected @endif @endif>{{$category_store_room->title}}</option>
                @endforeach
            </select>
            @if($errors->has("category_store_room_id"))
                <span class="help-block sub-error"> {{$errors->first("category_store_room_id")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> محل نگهداری</label>
            <select name="storage_store_room_id" class="form-control" title="انبار">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($storage_store_rooms as $storage_store_room)
                    <option value="{{$storage_store_room->id}}"
                            @if($store_rooms_rec)
                            @if($store_rooms_rec->pivot->storage_store_room_id == $storage_store_room->id) selected @endif
                            @endif>
                        {{$storage_store_room->title}}</option>
                @endforeach
            </select>
            @if($errors->has("storage"))
                <span class="help-block sub-error"> {{$errors->first('storage')}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">واحد اصلی</label>
            <select name="main_unit_id" class="form-control">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($counting_units as $counting_unit)
                    <option value="{{$counting_unit->id}}"
                            @if($store_rooms_rec)@if($store_rooms_rec->pivot->main_unit_id == $counting_unit->id) selected @endif @endif>{{$counting_unit->title}}</option>
                @endforeach
            </select>
            @if($errors->has("main_unit_id"))
                <span class="help-block sub-error"> {{$errors->first("main_unit_id")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تعداد ( واحد اصلی )</label>
            <input id="main_unit_value_lbl" type="number" class="form-control"
                   name="main_unit_value" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->main_unit_value}}@endif">
            @if($errors->has("main_unit_value"))
                <span class="help-block sub-error"> {{$errors->first("main_unit_value")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">واحد فرعی</label>
            <select name="sub_unit_id" class="form-control">
                <option value="">لطفا انتخاب کنید</option>
                @foreach($counting_units as $counting_unit)
                    <option value="{{$counting_unit->id}}"
                            @if($store_rooms_rec)@if($store_rooms_rec->pivot->sub_unit_id == $counting_unit->id) selected @endif @endif>{{$counting_unit->title}}</option>
                @endforeach
            </select>
            @if($errors->has("sub_unit_id"))
                <span class="help-block sub-error"> {{$errors->first("sub_unit_id")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تعداد ( واحد فرعی )</label>
            <input id="sub_unit_value_lbl" type="number" class="form-control"
                   name="sub_unit_value" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->sub_unit_value}}@endif">
            @if($errors->has("sub_unit_value"))
                <span class="help-block sub-error"> {{$errors->first("sub_unit_value")}} </span>
            @endif
        </div>


        <div class="col-12 col-md-3 mb-3">

            <label class="control-label">واحد اندازه گیری مصرفی</label>
            <select name="measurement_unit_id" class="form-control">
                <option value="">لطفا انتخاب کنید</option>
                @foreach($counting_units_measurement as $counting_unit_measurement)
                    <option value="{{$counting_unit_measurement->id}}"
                            @if($store_rooms_rec) @if($store_rooms_rec->pivot->measurement_unit_id == $counting_unit_measurement->id) selected @endif @endif
                    >{{$counting_unit_measurement->title}}</option>
                @endforeach
            </select>

            @if($errors->has("measurement_unit_id"))
                <span class="help-block sub-error"> {{$errors->first("measurement_unit_id")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">موجودی (تعداد)</label>
            <input id="stock_lbl" type="number" class="form-control"
                   name="stock" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->stock}}@endif">
            @if($errors->has("stock"))
                <span class="help-block sub-error"> {{$errors->first("stock")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> نقطه سفارش (تعداد)</label>
            <input id="order_point_lbl" type="number" class="form-control"
                   name="order_point" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->order_point}}@endif">
            @if($errors->has("order_point"))
                <span class="help-block sub-error"> {{$errors->first("order_point")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> مدت زمان سفارش (روز)</label>
            <input id="order_day_lbl" type="number" class="form-control"
                   name="order_day" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->order_day}}@endif">
            @if($errors->has("order_day"))
                <span class="help-block sub-error"> {{$errors->first("order_day")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تاریخ انقضا</label>
            <input id="expire_date_lbl" type="text" class="form-control expire_date"
                   name="expire_date" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->expire_date}}@endif">
            @if($errors->has("expire_date"))
                <span class="help-block sub-error"> {{$errors->first("expire_date")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> اعلام انقضا (تعداد روز)</label>
            <input id="expire_alert_lbl" type="number" class="form-control"
                   name="expire_alert"
                   placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->expire_alert}}@endif">
            @if($errors->has("expire_alert"))
                <span class="help-block sub-error"> {{$errors->first("expire_alert")}} </span>
            @endif
        </div>


        <div class="col-12 col-md-3 mb-3">

            <label class="control-label">بارکد</label>
            <input id="barcode" type="text" class="form-control"
                   name="barcode" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->barcode}}@endif">
            @if($errors->has("barcode"))
                <span class="help-block sub-error"> {{$errors->first("barcode")}} </span>
            @endif
        </div>
    </div>



    <div class="row"><br/><br/>
        <div class="col-12 col-md-12 mb-3">
            <h5>آدرس کالا در انبار</h5>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <label class="control-label">شماره محل نگهداری</label>
            <input id="storage_num_lbl" type="number" class="form-control"
                   name="storage_num" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->storage_num}}@endif">
            @if($errors->has("storage_num"))
                <span class="help-block sub-error"> {{$errors->first("storage_num")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
            <label class="control-label">شماره سطر </label>
            <input id="row_num_lbl" type="number" class="form-control"
                   name="row_num" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->row_num}}@endif">
            @if($errors->has("row_num"))
                <span class="help-block sub-error"> {{$errors->first("row_num")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-4 mb-3">
            <label class="control-label">شماره ستون </label>
            <input id="col_num_lbl" type="number" class="form-control"
                   name="col_num" placeholder=""
                   value="@if($store_rooms_rec){{$store_rooms_rec->pivot->col_num}}@endif">
            @if($errors->has("col_num"))
                <span class="help-block sub-error"> {{$errors->first("col_num")}} </span>
            @endif
        </div>


    </div>

@endif
