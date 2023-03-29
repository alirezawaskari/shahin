@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title"><h4>ثبت درخواست </h4></div>
            <form class="form-horizontal" method="post" action="{{route('request.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row">
                    <div class="col-12 col-md-4 mb-4">
                        <label>انبار *</label>
                        <select class="form-control productByStoreRoom store_room_id" name="store_room_id">
                            <option value="">لطفا انتخاب کنید</option>
                            @foreach($store_rooms as $store_room)
                                <option value="{{$store_room->id}}">{{$store_room->name}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('store_room_id'))<span
                                class="help-block sub-error">{{$errors->first('store_room_id')}}</span>@endif
                    </div>

                    <div class="col-12 col-md-4 mb-4">
                        <label>نوع کالا *</label>
                        <select class="form-control type productByStoreRoom" name="type">
                            <option value="">لطفا انتخاب کنید</option>
                            <option value="0">مواد اولیه</option>
                            <option value="1">کالا</option>
                        </select>
                        @if($errors->has('type'))<span
                                class="help-block sub-error">{{$errors->first('type')}}</span>@endif
                    </div>

                    <br/> <br/>
                    <div class="col-12 col-md-4 mb-4">
                        <label>عکس</label><br/>
                        <label for="image" class="custom-file-upload">
                            <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                        </label>
                        <input id="image" class="file-upload file_upload_ajax" type="file"
                               name="image"/>
                        <div id="image_src" class="show"></div>
                        <input type="hidden" id="image" class="path" name="file_path" value="">
                        @if($errors->has('file_path'))
                            <span class="help-block sub-error"> {{$errors->first('file_path')}} </span>
                        @endif
                    </div>


                    <div class="col-12 col-md-12 mb-4 productsBox" style="display: none;">
                        <label>محصولات *</label>

                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-12 mb-4 selectedProductsBox" style="display: none;">

                    </div>
                </div>
                <div class="row">
                    <br/> <br/>
                    <div class="ln_solid"></div>
                    <div class="col-12 col-md-12 mb-3">
                        <a href="{{route('request.index')}}" class="btn btn-warning">بازگشت</a>
                        <button type="submit" class="btn btn-success">ارسال</button>
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
    $(document).on('change', '.productByStoreRoom', function () {
        $('.productsBox').html('');$('.selectedProductsBox').html('');
        var store_room_id = $('.store_room_id').children("option:selected").val();
        var type = $('.type').val();
        if(store_room_id != '' && type != ''){
            $.ajax({
                url: "/dashboard/productByStoreRoom",
                type: 'post',
                data: {store_room_id: store_room_id,type:type},
                success: function (result) {
                    if(result['status'] == 'true'){
                        var lbl='<label>محصولات</label>';
                        var a_sel = $('<select class="form-control selectMulti productSelect" name="product_store_room_id[]" multiple="multiple">');
                        $(result['products']).each(function (index, val) {
                            if(val.brand_title != null){
                                a_sel.append($("<option>").attr('value', val.product_store_room_id).text(val.name + val.brand_title + ' - ' + val.store_room_name));
                            }
                            else{
                                a_sel.append($("<option>").attr('value', val.product_store_room_id).text(val.name + ' - ' + val.store_room_name));
                            }

                        });
                        $('.productsBox').html('').append(lbl).append(a_sel).css({'display':'block'});
                        $(".selectMulti").select2();
                    }
                    if(result['status'] == 'false'){
                        $('.productsBox').html('');
                    }
                }
            })
        }
    });

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
                if(result['status'] == 'false'){
                    $('.selectedProductsBox').html('');
                }
            }
        });

    });


</script>

@stop

