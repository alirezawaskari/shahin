@extends('Admin.master')

@section('content')
<div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ثبت کالا جدید</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="col-md-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_content">
                            <br/>
                                    <div class="row form-group">
                                    <label class="col-md-4 col-sm-4 col-xs-12">
                                        <input class="btn btn-default type_selected" type="radio" name="type" value="0" id="type">مواد
                                        اولیه
                                        مصرفی
                                    </label>
                                    <label class="col-md-4 col-sm-4 col-xs-12">
                                        <input class="btn btn-default type_selected" type="radio" name="type" value="1">کالا
                                    </label>
                                </div>
                                <hr/>
                                <br/>
                                <div class="row form-group">
                                    {{--title--}}
                                    <div class="col-md-4 col-sm-4 col-xs-12">
                                        <input id="name" type="text" class="form-control" name="name"
                                               placeholder="لطفا عنوان را وارد کنید"
                                               value="{{old('material.name')}}">
                                        @if($errors->has('material.name'))
                                            <span
                                                class="help-block sub-error"> {{$errors->first('material.name')}} </span>
                                        @endif
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-12">
                                        <input id="brand_title" type="text" class="form-control" name="brand_title"
                                               placeholder="لطفا برند را به زبان فارسی وارد کنید">
                                    </div>
                                    <div class="col-md-4 col-sm-4 col-xs-12">
                                        <input id="brand_title_en" type="text" class="form-control" name="brand_title_en"
                                               placeholder="لطفا برند را به زبان انگلیسی وارد کنید">
                                        </div>
                                    </div>
                                </div>


                                <div class="goodForm" style="display: none;">
                                    <div>
                                        <br/>
                                        <h4 class="text-center">اطلاعات کالا </h4>
                                        <br/>
                                        <div class="row form-group">
                                            {{--title--}}
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12">نام *</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <input id="name_lbl" type="text" class="form-control"
                                                       name="good[name]"
                                                       placeholder="لطفا عنوان را وارد کنید"
                                                       value="{{old('good.name')}}">
                                                @if($errors->has('good.name'))
                                                    <span
                                                        class="help-block sub-error"> {{$errors->first('good.name')}} </span>
                                                @endif
                                            </div>
                                        </div>
                                        <br/>
                                        <div class="row form-group">
                                            {{--checkBox--}}
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12"></label>
                                            <div class="col-md-3 col-sm-3 col-xs-3">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="good[isShow]"
                                                               @if(old('good.isShow') == 1) checked
                                                               @endif value="1">نمایش در سایت
                                                    </label>
                                                    @if($errors->has('good.isShow'))<span
                                                        class="help-block sub-error">{{$errors->first('good.isShow')}}</span>@endif
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-xs-3">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="good[is_sale]"
                                                               @if(old('good.is_sale') == 1) checked
                                                               @endif value="1">جهت عرضه در سایت
                                                    </label>
                                                    @if($errors->has('good.is_sale'))<span
                                                        class="help-block sub-error">{{$errors->first('good.is_sale')}}</span>@endif
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-sm-3 col-xs-3">
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="good[internal_use]"
                                                               @if(old('good.internal_use') == 1) checked
                                                               @endif value="1">استفاده داخلی
                                                    </label>
                                                    @if($errors->has('good.internal_use'))<span
                                                        class="help-block sub-error">{{$errors->first('good.internal_use')}}</span>@endif
                                                </div>
                                            </div>
                                        </div>
                                        <br/> <br/>
                                        {{--category--}}
                                        <div class="row form-group">
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12">دسته بندی
                                                *</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <select class="form-control parent_id" name="good[parent_id]">
                                                    <option value="">لطفا انتخاب کنید</option>
                                                    @foreach($parents as $parent)
                                                        <option value="{{$parent->id}}">{{$parent->title}}</option>
                                                    @endforeach
                                                </select>
                                                @if($errors->has('good.parent_id'))<span
                                                    class="help-block sub-error">{{$errors->first('good.parent_id')}}</span>@endif
                                            </div>
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12"></label>
                                            <div class="col-md-4 col-sm-4 col-xs-12 fieldsBox">
                                                @if($errors->has('good.child_id'))<span
                                                    class="help-block sub-error">{{$errors->first('good.child_id')}}</span>@endif
                                            </div>
                                        </div>
                                        {{--category--}}
                                        <div class="row form-group">
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12"></label>
                                            <div class="col-md-4 col-sm-4 col-xs-12 thirdBox">
                                                @if($errors->has('good.third_id'))<span
                                                    class="help-block sub-error">{{$errors->first('good.third_id')}}</span>@endif
                                            </div>
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12"></label>
                                            <div class="col-md-4 col-sm-4 col-xs-12 fourthBox">
                                                @if($errors->has('good.fourth_id'))<span
                                                    class="help-block sub-error">{{$errors->first('good.fourth_id')}}</span>@endif
                                            </div>

                                        </div>
                                        <br/> <br/>

                                        {{--pay , purchase type--}}
                                        <div class="row form-group">
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12">نوع خرید
                                                *</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <select name="good[purchase_type]" class="form-control">
                                                    <option value="0"
                                                            @if(old('good.purchase_type') == 0) selected @endif>
                                                        آنلاین
                                                    </option>
                                                    <option value="1"
                                                            @if(old('good.purchase_type') == 1) selected @endif>
                                                        حضوری
                                                    </option>
                                                </select>
                                                @if($errors->has('good.purchase_type'))
                                                    <span
                                                        class="help-block sub-error"> {{$errors->first('good.purchase_type')}} </span>
                                                @endif
                                            </div>
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12">نوع پرداخت
                                                *</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <select name="good[pay_type]" class="form-control">
                                                    <option value="0"
                                                            @if(old('good.pay_type') == 0) selected @endif>نقدی
                                                    </option>
                                                    <option value="1"
                                                            @if(old('good.pay_type') == 1) selected @endif>اقساط
                                                    </option>
                                                </select>
                                                @if($errors->has('good.pay_type'))
                                                    <span
                                                        class="help-block sub-error"> {{$errors->first('good.pay_type')}} </span>
                                                @endif
                                            </div>
                                        </div>
                                        <br/><br/>

                                        <br/><br/>
                                        <div class="row goodBox" style="display: none;">
                                            <label class="control-label col-md-2 col-sm-2 col-xs-12">عکس اصلی
                                                *</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <input id="image_lbl" type="file" class="form-control"
                                                       name="good[image]">
                                                @if($errors->has('good.image'))
                                                    <span
                                                        class="help-block sub-error"> {{$errors->first('good.image')}} </span>
                                                @endif
                                            </div>

                                            <label class="control-label col-md-2 col-sm-2 col-xs-12">آیکون</label>
                                            <div class="col-md-4 col-sm-4 col-xs-12">
                                                <input id="icon_lbl" type="file" class="form-control"
                                                       name="good[icon]">
                                                @if($errors->has('good.icon'))
                                                    <span
                                                        class="help-block sub-error"> {{$errors->first('good.icon')}} </span>
                                                @endif
                                            </div>
                                        </div>
                                        <br/>
                                        <div class="row goodBox" style="display: none;">
                                            <label
                                                class="control-label col-md-2 col-sm-2 col-xs-12">توضیحات </label>
                                            <div class="col-md-10 col-sm-10 col-xs-12">
                            <textarea class="form-control" title="توضیحات" rows="5" cols="5"
                                      name="good[review]">{{old('good.review')}}</textarea>
                                                @if($errors->has('good.review'))
                                                    <span
                                                        class="help-block sub-error"> {{$errors->first('good.review')}} </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="goodBox" style="display: none;">
                                            <br/><br/>
                                            <div class="row form-group">
                                                <label class="control-label col-md-2 col-sm-2 col-xs-12">سایر عکس
                                                    ها </label>
                                                <div class="col-md-10 col-sm-10 col-xs-12">
                                                    <table class="table table-bordered" id="dynamicTable">
                                                        <tr>
                                                            <th>عنوان</th>
                                                            <th>عکس</th>
                                                            <th></th>
                                                        </tr>
                                                        <tr>
                                                            <td><input type="text"
                                                                       name="addMoreImage[0][file_title]"
                                                                       placeholder="لطفا عنوان عکس را وارد کنید"
                                                                       class="form-control"/></td>
                                                            <td><input type="file" name="addMoreImage[0][file]"
                                                                       class="form-control"/></td>
                                                            <td>
                                                                <button type="button" name="add" id="add"
                                                                        class="btn btn-success addRow"><i
                                                                        class="fa fa-plus"></i></button>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br/>

                                <div class="ln_solid"></div>
                                <div class="row form-group">
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <button class="btn btn-success agent-add" id="submit">ثبت کالا</button>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="page-header">
        <div>
            <h3>ثبت کالا </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">ثبت کالا</li>
                </ol>
            </nav>
        </div>
        @include('Admin.purchaseinvoice.layouts')
    </div>
    @if($purchaseInvoice->status != 9)
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{route('purchaseInvoice.product' , [$purchaseInvoice->id ])}}"
                  enctype="multipart/form-data">
                @csrf
				@include('Admin.layouts.errors')
                <div class="row">
                    <div class="col-12 col-md-4 mb-3">
                        <label>انتخاب کالا</label>
                        <select class="form-control select2" name="product_id">
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 mb-3">
                        <div class="btn btn-success" id="new-role" data-toggle="modal" data-target="#productModal">ایجاد
                            کالا جدید +
                        </div>
                    </div>
                </div>
                <div class="row">
                    <input type="hidden" value="{{ $purchaseInvoice->id }}" name="purchase_invoice_id">
                    <div class="col-12 col-md-2 mb-3">
                        <label>قیمت خرید</label>
                        <input type="text" class="form-control" name="purchase_price" oninput="separatePrices(this)">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>قیمت فروش</label>
                        <input type="text" class="form-control" name="sales_price" oninput="separatePrices(this)" value="0">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>تخفیف</label>
                        <input type="text" class="form-control" name="discount" oninput="separatePrices(this)" value="0">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>انبار</label>
                        <select class="form-control" name="store_room_id">
                            @foreach($store_rooms as $store_room)
                                <option value="{{ $store_room->id }}">{{ $store_room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>دسته بندی انبار</label>
                        <select class="form-control" name="category_store_room_id">
                            @foreach($category_store_rooms as $category_store_room)
                                <option
                                    value="{{ $category_store_room->id }}">{{ $category_store_room->title }}</option>
                            @endforeach
                        </select>
                    </div><div class="col-12 col-md-2 mb-3">
                        <label>محل نگهداری</label>
                        <select class="form-control" name="storage">
                                <option value="0">قفسه</option>
                                <option value="1">یخچال</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>واحد اصلی</label>
                        <select class="form-control" name="main_unit_id">
                            @foreach($counting_units as $counting_unit)
							    @if($counting_unit->type == 'packing')
                                <option value="{{ $counting_unit->id }}">{{ $counting_unit->title }}</option>
                                @endif
							@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>تعداد (واحد اصلی)</label>
                        <input type="number" class="form-control" name="main_unit_value">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>واحد فرعی</label>
                        <select class="form-control" name="sub_unit_id">
                            @foreach($counting_units as $counting_unit)
                                @if($counting_unit->type == 'counting')
                                <option value="{{ $counting_unit->id }}">{{ $counting_unit->title }}</option>
                                @endif
							@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>تعداد (واحد فرعی)</label>
                        <input type="number" class="form-control" name="sub_unit_value">
                    </div>
					<div class="col-12 col-md-2 mb-3">
                        <label>واحد اندازه گیری مصرفی</label>
                        <select class="form-control" name="measurement_unit_id">
                            @foreach($counting_units as $counting_unit)
                                @if($counting_unit->type == 'measurement')
                                <option value="{{ $counting_unit->id }}">{{ $counting_unit->title }}</option>
                                @endif
							@endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>موجودی(تعداد)</label>
                        <input type="number" class="form-control" name="stock">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>نقطه سفارش(تعداد)</label>
                        <input type="number" class="form-control" name="oreder_point" data-toggle="tooltip" title="منظور از نقطه سفارش مشخص کردن حداقل موجودی می باشد">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>مدت زمان سفارش(روز)</label>
                        <input type="text" class="form-control" name="order_day" data-toggle="tooltip" title="منظور از مدت زمان سفارش مدت زمانی ایست که طول می کشد تا کالا وارد انبار شود.">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>تاریخ انقضا</label>
                        <input type="text" class="form-control date" name="expire_date">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label> اعلام انقضا(تعداد روز)</label>
                        <input type="number" class="form-control" name="expire_alert"  data-toggle="tooltip" title="منظوراز اعلام انقضا تعداد روز باقی مانده به انقضا کالا میباشد.">
                    </div>
                    <div class="col-12 col-md-2 mb-3">
                        <label>بارکد</label>
                        <input type="text" class="form-control" name="barcode">
                    </div>
                </div>
				 <h6>آدرس کالا در انبار</h6>
            <div class="row mt-4">
                <div class="col-12 col-md-2 mb-3">
                    <label>شماره محل نگهداری</label>
                    <input type="number" class="form-control" name="storage_num" min="1">
                </div>
                <div class="col-12 col-md-2 mb-3">
                    <label>شماره سطر</label>
                    <input type="number" class="form-control" name="row_num" min="1">
                </div>
                <div class="col-12 col-md-2 mb-3">
                    <label>شماره ستون</label>
                    <input type="number" class="form-control" name="col_num" min="1">
                </div>
            </div>
                <div class="col-12 col-md-4 mb-3 mt-3">
                    <button type="submit" class="btn btn-success"> ثبت و درج کالا بعدی</button>
                </div>
            </form>
        </div>
    </div>
    @endif
    <div class="card position-relative">
        <div class="card-body">
            <h5>کالا ها فاکتور</h5>
            <hr>
                <table class="table table-bordered table-striped table-responsive-md">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>قیمت خرید (ریال)</th>
                        <th>قیمت فروش (ریال)</th>
                        <th>انبار</th>
                        <th>دسته بندی انبار</th>
                        <th>موجودی</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($purchase_invoice_products as $key => $purchase_invoice_product)
                        <tr class="active" id="item_{{$purchase_invoice_product->id}}" style="cursor: move;">
                            <td>{{ $purchase_invoice_product->product->name }}</td>
                            <td>{{ number_format($purchase_invoice_product->purchase_price) }}</td>
                            <td>{{ number_format($purchase_invoice_product->sales_price) }}</td>
                            <td>{{ $purchase_invoice_product->store_room->name }}</td>
                            <td>{{ $purchase_invoice_product->category_store_room->title }}</td>
                            <td>{{ $purchase_invoice_product->stock }}</td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                       class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="{{ route('purchaseInvoice.edit' , [$purchase_invoice_product->id]) }}">
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @if($purchaseInvoice->status == 8)
                <form class="form-horizontal" role="form" method="post"
                      action="{{route('purchaseInvoice.update' , [$purchaseInvoice->id ])}}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="9">
                    <div class="col-12">
                        <p class="text-warning col-12">انباردار محترم بستن فاکتور به منزله ثبت تمام کالاهای فاکتور می باشد.</p>
                        <input type="submit" class="btn btn-success border-0" value="بستن فاکتور">
                    </div>
                </form>
                @endif
        </div>
    </div>
@stop
@section('js')
<script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>
<script src="{{ asset('assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/product.js') }}"></script>
@endsection
