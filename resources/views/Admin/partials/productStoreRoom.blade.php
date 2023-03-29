
    <div class="row">

        <div class="col-12 col-md-12 mb-3">
            <h4 class="text-center">اطلاعات انبار *</h4>
            <span class="disTable">تمامی اطلاعات انبار ضروری می باشند.</span>
        </div>

        @if($arrayName != 'material')
        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">نوع خرید</label>
            <select name="{{$arrayName}}[purchase_type]" class="form-control">
                <option value="0" @if(old("$arrayName.purchase_type") == 0) selected @endif>آنلاین</option>
                <option value="1" @if(old("$arrayName.purchase_type") == 1) selected @endif>حضوری</option>
                <option value="2" @if(old("$arrayName.purchase_type") == 2) selected @endif>هر دو</option>
            </select>
            @if($errors->has("$arrayName.purchase_type"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.purchase_type")}} </span>
            @endif
        </div>
        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">نوع پرداخت</label>
            <select name="{{$arrayName}}[pay_type]" class="form-control">
                <option value="0" @if(old("$arrayName.pay_type") == 0) selected @endif>نقدی</option>
                <option value="1" @if(old("$arrayName.pay_type") == 1) selected @endif>اقساط</option>
                <option value="2" @if(old("$arrayName.pay_type") == 2) selected @endif>هر دو</option>
            </select>
            @if($errors->has("$arrayName.pay_type"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.pay_type")}} </span>
            @endif
        </div>
        @endif

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">قیمت خرید</label>
            <input id="currency-field" data-type="currency" type="text" class="form-control"
                   name="{{$arrayName}}[purchase_price]"
                   placeholder="" value="{{old("$arrayName.purchase_price")}}">

            @if($errors->has("$arrayName.purchase_price"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.purchase_price")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">قیمت فروش</label>
            <input id="currency-field" data-type="currency" type="text" class="form-control"
                   name="{{$arrayName}}[sales_price]"
                   placeholder=""
                   value="{{old("$arrayName.sales_price")}}">
            @if($errors->has("$arrayName.sales_price"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.sales_price")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تخفیف</label>
            <input id="currency-field" data-type="currency" type="text" class="form-control"
                   name="{{$arrayName}}[discount]"
                   placeholder=""
                   value="{{old("$arrayName.discount") ? old("$arrayName.discount") : 0}}">
            @if($errors->has("$arrayName.discount"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.discount")}} </span>
            @endif
        </div>

        @if(isset($store_room_id) && $store_room_id != null)
            <input type="hidden" name="{{$arrayName}}[store_room_id]" value="{{$store_room_id}}">
            <input type="hidden" name="insertByStoreRoom" value="1">
            @if(isset($internal_use_material))
                <input type="hidden" name="internal_use_material" value="{{$internal_use_material}}">
            @endif
        @else
            <div class="col-12 col-md-3 mb-3">
                <label class="control-label">انبار</label>
                <select name="{{$arrayName}}[store_room_id]" class="form-control productPack" id="store_room" title="انبار">
                    <option value="">لطفا انتخاب کنید..</option>
                    @foreach($store_rooms as $store_room)
                        <option value="{{$store_room->id}}"
                                @if(old("$arrayName.store_room_id") == $store_room->id) selected @endif>{{$store_room->name}}</option>
                    @endforeach
                </select>
                @if($errors->has("$arrayName.store_room_id"))
                    <span class="help-block sub-error"> {{$errors->first("$arrayName.store_room_id")}} </span>
                @endif
            </div>
        @endif

        @if(isset($purchase_invoice_id) && $purchase_invoice_id != null)
            <input type="hidden" name="{{$arrayName}}[purchase_invoice_id]" value="{{$purchase_invoice_id}}">
        @endif

        <div class="col-12 col-md-3 mb-3">

            <label class="control-label">دسته بندی انبار</label>
            <select name="{{$arrayName}}[category_store_room_id]" class="form-control" title="دسته بندی انبار">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($category_store_rooms as $category_store_room)
                    <option value="{{$category_store_room->id}}"
                            @if(old("$arrayName.category_store_room_id") == $category_store_room->id) selected @endif>{{$category_store_room->title}}</option>
                @endforeach
            </select>
            @if($errors->has("$arrayName.category_store_room_id"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.category_store_room_id")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> محل نگهداری</label>
            <select name="{{$arrayName}}[storage_store_room_id]" class="form-control" title="انبار">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($storage_store_rooms as $storage_store_room)
                    <option value="{{$storage_store_room->id}}"
                            @if(old("$arrayName.storage_store_room_id") == $storage_store_room->id) selected @endif>
                        {{$storage_store_room->title}}</option>
                @endforeach
            </select>
            @if($errors->has("$arrayName.storage_store_room_id"))
                <span class="help-block sub-error"> {{$errors->first('storage_store_room_id')}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">واحد اصلی</label>
            <select name="{{$arrayName}}[main_unit_id]" class="form-control">
                <option value="">لطفا انتخاب کنید..</option>
                @foreach($counting_units as $counting_unit)
                    <option value="{{$counting_unit->id}}"
                            @if(old("$arrayName.main_unit_id") == $counting_unit->id) selected @endif>{{$counting_unit->title}}</option>
                @endforeach
            </select>
            @if($errors->has("$arrayName.main_unit_id"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.main_unit_id")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تعداد ( واحد اصلی )</label>
            <input id="main_unit_value_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[main_unit_value]"
                   placeholder=""
                   value="{{old("$arrayName.main_unit_value")}}">
            @if($errors->has("$arrayName.main_unit_value"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.main_unit_value")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">واحد فرعی</label>
            <select name="{{$arrayName}}[sub_unit_id]" class="form-control">
                <option value="">لطفا انتخاب کنید</option>
                @foreach($counting_units as $counting_unit)
                    <option value="{{$counting_unit->id}}"
                            @if(old("$arrayName.sub_unit_id") == $counting_unit->id) selected @endif>{{$counting_unit->title}}</option>
                @endforeach
            </select>
            @if($errors->has("$arrayName.sub_unit_id"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.sub_unit_id")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تعداد ( واحد فرعی )</label>
            <input id="sub_unit_value_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[sub_unit_value]"
                   placeholder=""
                   value="{{old("$arrayName.sub_unit_value")}}">
            @if($errors->has("$arrayName.sub_unit_value"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.sub_unit_value")}} </span>
            @endif
        </div>


        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">واحد اندازه گیری مصرفی</label>

            <select name="{{$arrayName}}[measurement_unit_id]" class="form-control">
                <option value="">لطفا انتخاب کنید</option>
                @foreach($counting_units_measurement as $counting_unit_measurement)
                    <option value="{{$counting_unit_measurement->id}}"
                            @if(old("$arrayName.measurement_unit_id") == $counting_unit_measurement->id) selected @endif>{{$counting_unit_measurement->title}}</option>
                @endforeach
            </select>

            @if($errors->has("$arrayName.measurement_unit_id"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.measurement_unit_id")}} </span>
            @endif
        </div>

        @if(isset($stock) && $stock == true)
        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">موجودی (تعداد)</label>
            <input id="stock_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[stock]"
                   placeholder=""
                   value="{{old("$arrayName.stock")}}">
            @if($errors->has("$arrayName.stock"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.stock")}} </span>
            @endif
        </div>
        @endif


        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> نقطه سفارش (تعداد)</label>
            <input id="order_point_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[order_point]"
                   placeholder=""
                   value="{{old("$arrayName.order_point")}}">
            @if($errors->has("$arrayName.order_point"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.order_point")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> مدت زمان سفارش (روز)</label>
            <input id="order_day_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[order_day]"
                   placeholder=""
                   value="{{old("$arrayName.order_day")}}">
            @if($errors->has("$arrayName.order_day"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.order_day")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> اعلام انقضا (تعداد روز)</label>
            <input id="expire_alert_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[expire_alert]"
                   placeholder=""
                   value="{{old("$arrayName.expire_alert")}}">
            @if($errors->has("$arrayName.expire_alert"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.expire_alert")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label"> تاریخ انقضا</label>
            <input id="expire_date_lbl" type="text" class="form-control expire_date"
                   name="{{$arrayName}}[expire_date]"
                   placeholder=""
                   value="{{old("$arrayName.expire_date")}}">
            @if($errors->has("$arrayName.expire_date"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.expire_date")}} </span>
            @endif
        </div>


        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">بارکد</label>
            <input id="barcode" type="text" class="form-control"
                   name="{{$arrayName}}[barcode]"
                   placeholder=""
                   value="{{old("$arrayName.barcode")}}">
            @if($errors->has("$arrayName.barcode"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.barcode")}} </span>
            @endif
        </div>

    </div>
    <div class="row"><br/><br/>
        <div class="col-12 col-md-12 mb-3">
            <h5>آدرس کالا در انبار</h5>
        </div>
        
        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">شماره محل نگهداری</label>
            <input id="storage_num_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[storage_num]" placeholder=""
                   value="{{old("$arrayName.storage_num")}}">
            @if($errors->has("$arrayName.storage_num"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.storage_num")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">شماره سطر </label>
            <input id="row_num_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[row_num]" placeholder=""
                   value="{{old("$arrayName.row_num")}}">
            @if($errors->has("$arrayName.row_num"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.row_num")}} </span>
            @endif
        </div>

        <div class="col-12 col-md-3 mb-3">
            <label class="control-label">شماره ستون </label>
            <input id="col_num_lbl" type="number" class="form-control"
                   name="{{$arrayName}}[col_num]" placeholder=""
                   value="{{old("$arrayName.col_num")}}">
            @if($errors->has("$arrayName.col_num"))
                <span class="help-block sub-error"> {{$errors->first("$arrayName.col_num")}} </span>
            @endif
        </div>
    </div>


