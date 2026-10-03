@extends('Admin.master')

@section('content')
    @php $in_wizard = $in_wizard ?? false; @endphp

    @if($in_wizard)
        @include('Admin.productWizard.partials.steps',['current'=>'variation','kind'=>'product'])
        <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap">
            <span>کالای «{{$product->name}}» ثبت شد. حالا تنوع‌های آن (رنگ، سایز و سایر ویژگی‌ها) را ثبت کنید.</span>
            <a href="{{$wizard_done_url}}" class="btn btn-sm btn-outline-primary">
                ادامه: افزودن به شگفت‌انگیز
            </a>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form id="priceForm" method="">

                <input type="hidden" class="product_id" value="{{$product->id}}" name="product_id">
                <h4 class="mb-4"> قیمت گذاری کالا </h4>

                <div class="row mb-4">
                    <div class="col-md-2 col-sm-12 col-xs-3">
                        <img src="{{$product->image_url}}" class="border-radius5 disTable" alt="{{$product->name}}"
                             width="120px">
                    </div>
                    <div class="col-md-10 col-sm-12 col-xs-8">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <h5>{{$product->name}}</h5>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="col-md-4 col-sm-4 col-xs-4 disTableCell">
                                <p><span class="colorLGray">گروه :</span>
                                    <span>{{\App\Models\Category::category($product->root_category_id,'title')}}</span>
                                </p>
                            </div>
                            <div class="col-md-4 col-sm-4 col-xs-4 disTableCell">
                                <p>
                                    <span class="colorLGray">تعداد تنوع فعال :</span>
                                    <span> {{\App\Models\Product::countPriceProduct($product->id)}}</span>
                                </p>
                            </div>
                            <div class="col-md-4 col-sm-4 col-xs-4 disTableCell">
                                <p>
                                    <span class="colorLGray">کمترین قیمت روی سایت :</span>
                                    <span> {{number_format(\App\Models\ProductAttribute::minPriceProductAttributeActive($product->id))}}</span>
                                    <span class="colorLGray">ریال</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if(isset($attributes) && $attributes != [])
                    <input type="hidden" class="has_attribute" value="1">
                    <div class="row mrgZ mb-4">
                        @include('Admin.partials.productAttribute.show',['attributes'=>$attributes])
                    </div>
                @else
                    <input type="hidden" class="has_attribute" value="0">
                @endif
                <br/><br/>

                <div class="row mb-4">
                    @if(isset($attributes) && $attributes != [])
                        <div class="col-md-3">
                            <label>ویژگی انتخابی <span class="required">*</span> :</label>
                            <input class="attribute_item_id" type="hidden" name="attribute_item_id" title="">
                            <input type="text" readonly class="form-control selectedAttribute" title="">
                        </div>
                    @endif
                    <div class="col-md-3">
                        <label for="guarantee">گارانتی <span class="required">*</span> :</label>
                        <select id=guarantee class="form-control guarantee" name="guarantee">
                            <option value=""></option>
                            <option value="1" selected>گارانتی اصالت و سلامت فیزیکی کالا</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="submission_time">بازه زمانی ارسال (روز) <span class="required">*</span>
                            :</label>
                        <select id="submission_time" class="form-control submission_time" name="submission_time">
                            <option value=""></option>
                            @for($i=1;$i<=15;$i++)
                                <option value="{{$i}}" @if($i == 5) selected @endif>{{$i}}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="stock">موجودی نزد شما (عدد) <span class="required">*</span> :</label>
                        <input id="stock" type="number" class="form-control stock" name="stock"
                               value="100{{--{{old('stock')}}--}}">
                    </div>
                    <div class="col-md-3">
                        <label for="seller_code">کد کالا فروشنده :</label>
                        <input id="seller_code" type="text" class="form-control seller_code" name="seller_code"
                               value="{{old('seller_code')}}">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <label for="reference_price">قیمت مرجع (ریال) :</label>
                        <input id="reference_price" class="form-control reference_price"
                               readonly="readonly"
                               value="{{number_format($product->reference_price)}}">
                    </div>
                    <div class="col-md-3">
                        <label for="currency-field">قیمت فروش (ریال) <span class="required">*</span> :</label>
                        <input id="currency-field" data-type="currency"
                               type="text" class="form-control price_by_attr sales_price" name="price"
                               value="{{old('price')}}"
                               max="{{number_format($product->reference_price)}}">
                        <span class="message_sales_price"></span>
                    </div>
                    <div class="col-md-3">
                        <label for="posted_by">فعال / غیر فعال <span class="required">*</span> :</label>
                        <div class="checkbox">
                            <label><input name="is_show" type="checkbox" class="is_show" value="1" checked>فعال /
                                غیر
                                فعال</label>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="posted_by">ارسال توسط <span class="required">*</span> :</label>
                        <div id=posted_by class="checkbox">
                            <label>
                                <input name="posted_by" class="posted_by" type="radio" value="1">شبکه لاله
                            </label>
                            <label>
                                <input name="posted_by" class="posted_by" type="radio" value="2" checked>فروشنده
                            </label>
                        </div>
                    </div>
                </div>

                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <button type="button" class="btn btn-success productPrice">ثبت</button>
                    </div>
                </div>

            </form>
        </div>
    </div>


@stop

@section('css')
    <style>
        .error {
            color: red;
        }
    </style>
@stop

@section('js')
    @if($in_wizard)
        {{-- ثبت تنوع با ajax انجام می‌شود؛ در حالت ویزارد به جای رفرش صفحه، به مرحله پایانی می‌رویم. --}}
        <script>window.PRODUCT_WIZARD_DONE_URL = '{{ $wizard_done_url }}';</script>
    @endif
    <script src="{{ asset('assets/dashboard/js/site/product/price/createPrice.js') }}"></script>
@stop
