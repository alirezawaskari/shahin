<div class="col-12 col-md-12 mb-3">
    <h4 class="text-center">اطلاعات شعبه </h4>
</div>

    @if($center_id == null)
        <div class="col-12 col-md-6 mb-3">
            <label >شعبه</label>
                <select name="center_id" class="form-control">
                    <option value="">لطفا انتخاب کنید..</option>
                    @foreach($centers as $center)
                        <option value="{{ $center->id }}"
                                @if($pack_centers && $pack_centers->pivot)
                                @if($center->id == $pack_centers->pivot->center_id) selected @endif
                                @endif>{{ $center->title }}</option>
                    @endforeach
                </select>
                @if($errors->has('center_id'))
                    <span class="help-block sub-error"> {{$errors->first('center_id')}} </span>
                @endif
        </div>
        <br/>
    @endif

    @if($center_id != null)
        <input name="center_id" value="{{$center_id}}" type="hidden">
        <input name="packByCenter" value="1" type="hidden">
    @endif

    <div class="col-12 col-md-3 mb-3">
        <label >قیمت *</label>
            <input id="price_lbl" type="text" class="form-control" name="price"
                   placeholder="لطفا قیمت را وارد کنید"
                   value="@if($pack_centers && $pack_centers->pivot)  {{$pack_centers->pivot->price}} @endif">
            @if($errors->has('price'))
                <span class="help-block sub-error"> {{$errors->first('price')}} </span>
            @endif
    </div>
    <div class="col-12 col-md-3 mb-3">
        <label >موجودی *</label>
            <input id="stock_lbl" type="text" class="form-control"
                   name="stock"
                   placeholder="لطفا موجودی را وارد کنید"
                   value="@if($pack_centers && $pack_centers->pivot)  {{$pack_centers->pivot->stock}} @endif">
            @if($errors->has('stock'))
                <span class="help-block sub-error"> {{$errors->first('stock')}} </span>
            @endif

    </div>
