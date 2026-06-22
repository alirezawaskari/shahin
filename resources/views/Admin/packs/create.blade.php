@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title"><h4>ثبت پک</h4></div>

            <form class="form-horizontal" method="post" action="{{route('pack.store')}}"
                  enctype="multipart/form-data">
                {{csrf_field()}}

                <div class="row">
                    <div class="col-12 col-md-12 mb-3">
                        <label></label>

                        @if($center_id != null)
                            <input class="type_good_selected" type="hidden" name="type_good_selected" value="1">

                        @elseif($center_id == null)
                            <input class="type_good_selected" type="radio" name="type_good_selected" title="انبار"
                                   value="0"><span class="lblRadioBtn font-weight-bold font16">انبار </span>

                            <input class="type_good_selected" type="radio" name="type_good_selected" title="شعبه" value="1"><span class="lblRadioBtn font-weight-bold font16">شعبه </span>
                        @endif

                    </div>
                    <hr/>

                    <div class="col-12 col-md-12 mb-3">
                        <button type="button" class="btn btn-info btn-md" data-toggle="modal"
                                data-target="#packModal">اطلاعات پک
                        </button>
                    </div>


                    <div class="col-12 col-md-12 mb-3">
                        @include('Admin.modal.pack')
                    </div>


                    <div class="col-12 col-md-12 mb-3 goodStockBox" style="display: none;">
                        @include('Admin.partials.productStoreRoom',['arrayName'=>'pack','stock'=>true])
                    </div>

                    <div class="col-12 col-md-12 mb-3 goodCenterBox" style="display: none;">
                        <h4 class="text-center">اطلاعات شعبه</h4>
                        <div class="row">
                            @if($center_id == null)
                                <div class="col-12 col-md-6 mb-3">
                                    <label>شعبه</label>
                                    <select name="pack[center_id]" class="form-control productPack" id="center">
                                        <option value="">لطفا انتخاب کنید..</option>
                                        @foreach($centers as $center)
                                            <option value="{{$center->id}}">{{$center->title}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('pack.center_id'))
                                        <span class="help-block sub-error"> {{$errors->first('pack.center_id')}} </span>
                                    @endif
                                </div>
                            @else
                                <input type="hidden" class="center_id" name="pack[center_id]" value="{{$center_id}}" >
                                <input type="hidden" name="packByCenter" value="1" >
                            @endif
                            <div class="col-12 col-md-3 mb-3">
                                <label>قیمت *</label>
                                <input type="text" name="pack[price_center]" id="currency-field" value="{{old('pack[price_center]')}}"
                                       class="form-control" data-type="currency" placeholder="لطفا قیمت را وارد کنید">
                                @if($errors->has('pack.price_center'))
                                    <span class="help-block sub-error"> {{$errors->first('pack.price_center')}} </span>
                                @endif
                            </div>
                            <div class="col-12 col-md-3 mb-3">
                                <label>موجودی *</label>
                                <input id="pack_stock_center_lbl" type="text" class="form-control"
                                       name="pack[stock_center]"
                                       placeholder="لطفا موجودی را وارد کنید" value="{{old('pack[stock_center]')}}">
                                @if($errors->has('pack.stock_center'))
                                    <span class="help-block sub-error"> {{$errors->first('pack.stock_center')}} </span>
                                @endif
                            </div>
                            <br/>
                        </div>
                    </div>


                    <div class="col-12 col-md-12 mb-3">
                        <label>انتخاب کالا *</label>
                        <div class="productsPackBox">
                            <select class="js-example-basic-single form-control productsPack" name="product_id[]" multiple="multiple">
                            </select>
                        </div>

                        @if($errors->has('product_id'))
                            <span class="help-block sub-error"> {{$errors->first('product_id')}} </span>
                        @endif
                    </div>

                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <button type="submit" class="btn btn-success">ثبت و درج پک بعدی</button>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>


@stop

@section('css')
@stop

@section('js')
    <script src="/assets/dashboard/ckeditor/ckeditor.js"></script>
    <script src="/assets/dashboard/js/site/pack/packCreate.js"></script>
@stop
