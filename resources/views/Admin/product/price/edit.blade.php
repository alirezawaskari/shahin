@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="x_title"><h4>ویرایش تنوع کالا</h4></div>
            <form class="form-horizontal" method="post"
                  action="{{route('price.update',$product_attribute->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}} {{ method_field('PATCH') }}
                  <div class="x_content">
                <br/> <br/>
                <div class="row">
                    <div class="col-md-3">
                        <label>ویژگی انتخابی <span class="required">*</span> :</label>
                        <input type="text" readonly value="{{$product_attribute->value}}" class="form-control" title="">
                    </div>
                    <div class="col-md-3">
                        <label for="guarantee">گارانتی <span class="required">*</span> :</label>
                        <select id="guarantee" class="form-control guarantee" name="guarantee">
                            <option value=""></option>
                            <option value="1" @if($product_attribute->guarantee == 1) selected @endif>گارانتی اصالت و سلامت فیزیکی کالا</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="submission_time">بازه زمانی ارسال (روز) <span class="required">*</span>
                            :</label>
                        <select id=submission_time class="form-control submission_time" name="submission_time">
                            <option value=""></option>
                            @for($i=1;$i<=15;$i++)
                                <option value="{{$i}}" @if($product_attribute->submission_time == $i) selected @endif>{{$i}}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="seller_code">کد محصول فروشنده :</label>
                        <input id="seller_code" type="text" class="form-control seller_code" name="seller_code"
                               value="{{$product_attribute->seller_code}}">
                    </div>
                </div>

                <br/><br/>
                <div class="row">
                    <div class="col-md-3">
                        <label for="stock">موجودی نزد شما (عدد) <span class="required">*</span> :</label>
                        <input id="stock" type="text" class="form-control stock" name="stock" value="{{$product_attribute->stock}}">
                    </div>
                    <div class="col-md-3">
                        <label for="is_show">فعال / غیر فعال <span class="required">*</span> :</label>
                        <div class="checkbox">
                            <label><input name="is_show" type="checkbox" class="is_show"
                                          @if($product_attribute->is_show == 1) checked @endif
                                          value="1">فعال / غیر فعال</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="posted_by">ارسال توسط <span class="required">*</span> :</label>
                        <div id=posted_by class="checkbox">
                            <label>
                                <input name="posted_by" class="posted_by" type="radio" value="1"
                                @if($product_attribute->posted_by == 1) checked @endif>شبکه لاله
                            </label>
                            <label>
                                <input name="posted_by" class="posted_by" type="radio" value="2"
                                       @if($product_attribute->posted_by == 2) checked @endif>فروشنده
                            </label>
                        </div>
                    </div>
                </div>
                <br/><br/>

                <div class="row">
                    <div class="col-md-4">
                        <label for="reference_price">قیمت مرجع (ریال) :</label>
                        <input id="reference_price" type="text" class="form-control reference_price" readonly="readonly"
                               value="{{number_format($product->reference_price)}}">
                    </div>
                    <div class="col-md-4">
                        <label for="currency-field">قیمت فروش (ریال) <span class="required">*</span> :</label>
                        <input id="currency-field" data-type="currency"
                               type="text" class="form-control sales_price" name="price" value="{{number_format($product_attribute->price)}}"
                               max="{{number_format($product->reference_price)}}">
                        <span class="message_sales_price"></span>
                    </div>
                </div>
                <br/><br/>


                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <button type="submit" class="btn btn-success">ویرایش</button>
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
<script>
    var sales_price = $('.sales_price');var reference_price = $('.reference_price');
    sales_price.keypress(function() {  check_price(sales_price,reference_price) ; });
    sales_price.keyup(function() {  check_price(sales_price,reference_price) ;  });

    function check_price(sales_price,reference_price) {
        sales_price = parseInt(sales_price.val().replace(/,/g, ''));
        reference_price = parseInt(reference_price.val().replace(/,/g, ''));
        if (sales_price > reference_price) {
            $('.message_sales_price').html(' لطفا قیمت را کمتر از قیمت مرجع را وارد نمایید.');
        } else {  $('.message_sales_price').html('');  }
    }

</script>

@stop
