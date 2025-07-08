@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title"><h4>ویرایش آیتم های درخواست </h4></div>
            <br/> <br/>
            <form class="form-horizontal" method="post"
                  action="{{route('request.update',$request_record->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <input class="store_room_id" type="hidden" value="{{$request_record->store_room_id}}">
                <input class="type" type="hidden" value="{{$request_record->type}}">
                <input class="request_id" type="hidden" value="{{$request_record->id}}">

                <div class="row">
                    <div class="col-12 col-md-12 mb-5">
                        <label>کالا ها</label>
                        <select class="form-control selectMulti productSelect" name="" multiple="multiple">
                            @foreach($products as $product)
                                <option value="{{$product->product_store_room_id}}" {{ in_array(trim($product->product_store_room_id) , $product_store_room_ids) ? 'selected' : '' }}>
                                    {{$product->name}}
                                    @if ($product->brand_title != '') {{$product->brand_title}} @endif
                                    @if ($product->store_room_name != '') {{$product->store_room_name}} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row selectedProductsBox">
                    @foreach($lists as $index=>$list)
                        <div class="disInline col-12 col-md-12 mb-4">
                            <label>مقدار درخواستی برای <span>{{$list['name']}}   @if(isset($list['unit'])){{$list['unit']}} @endif</span>
                            </label>

                            @if($list['type'] == 0 || $list['internal_use'] == 1)
                                <div class="disInline col-12 col-md-1 mb-4">
                                    <select class="form-control" name="products_number[{{$index}}][number]">
                                        @for($i=1 ; $i <=$list['stock'] ; $i++)
                                            <option value="{{$i}}" @if(isset($list['number']) && $list['number'] != 0)
                                            @if($list['number'] == $i) selected @endif
                                                    @endif>{{$i}}</option>
                                        @endfor
                                    </select>
                                </div>


                            @elseif($list['type'] == 1 || $list['internal_use'] == 0)
                                <div class="row">
                                    <div class="col-12 col-md-12 mb-6">
                                        @foreach($list['attributes'] as $ind=>$attributes)
                                            <div class="col-md-3 mb-4 custom-control disInline custom-checkbox">
                                                <input data-title="{{$attributes['stock']}}" type="checkbox"
                                                       class="custom-control-input" name="productAttributeSelect[]" id="customCheck{{$ind}}"
                                                       value="{{$ind}}" @if(isset($attributes['number']) && $attributes['number'] != 0) checked @endif>
                                                <label class="custom-control-label"
                                                       for="customCheck{{$ind}}">{{$attributes['value']}} </label>

                                                <select class="col-md-3 form-control disInline" name="product_attribute_ids[{{$ind}}][number]">
                                                    @for($i=1 ; $i <=$attributes['stock'] ; $i++)
                                                        <option value="{{$i}}" @if(isset($attributes['number']) && $attributes['number'] != 0)
                                                        @if($attributes['number'] == $i) selected @endif
                                                                @endif>{{$i}}</option>
                                                    @endfor
                                                </select>

                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    <div class="col-12 col-md-12 mb-5">
                        <a href="{{route('request.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ویرایش</button>
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
    //used in request
    $(document).on('change', '.productSelect', function () {
        var store_room_id = $('.store_room_id').children("option:selected").val();
        if(store_room_id == undefined) store_room_id = $('.store_room_id').val();
        var request_id = 0 ;
        if($('.request_id')) request_id = $('.request_id').val();

        var type = $('.type').val();
        $.ajax({
            url: "/dashboard/stockProduct",
            type: 'post',
            data: {ids:$(this).val(),type:type,store_room_id:store_room_id,request_id:request_id},
            headers: {"Authorization": localStorage.getItem('token')},
            success: function (result) {
                if(result['status'] == 'true'){
                    $('.selectedProductsBox').html('').html(result['view_result']).css({'display':'block'});
                }
            }
        });

    });

</script>

@stop

