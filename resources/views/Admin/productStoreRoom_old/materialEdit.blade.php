@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body ">
            <div class="x_title"><h4>  ویرایش کالا<span>   {{$product->name}}</span></h4></div>

            <br/>
            <form class="form-horizontal mb-3" method="post"
                  action="{{route('productStoreRoomUpdate',$product->id)}}" enctype="multipart/form-data">
                {{csrf_field()}} {{ method_field('PATCH') }}

                <div class="row">
                        <div class="col-12 col-md-12 mb-3"><h4 class="text-center"> مواد اولیه مصرفی</h4></div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نام *</label>
                            <input id="name_lbl" type="text" class="form-control" name="name"
                                   placeholder="لطفا عنوان را وارد کنید" value="{{$product->name}}">
                            @if($errors->has('name'))
                                <span class="help-block sub-error"> {{$errors->first('name')}} </span>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>برند فارسی </label>
                            <input id="brand_name_fa_lbl" type="text" class="form-control" name="brand_name_fa"
                                   placeholder="لطفا برند فارسی را وارد کنید" value="@if(isset($brand)){{$brand->title}} @endif">
                            @if($errors->has('brand_name_fa'))
                                <span class="help-block sub-error"> {{$errors->first('brand_name_fa')}} </span>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>برند انگلیسی </label>
                            <input id="brand_name_en_lbl" type="text" class="form-control" name="brand_name_en"
                                   placeholder="لطفا برند انگلیسی را وارد کنید" value="@if(isset($brand)){{$brand->title_en}} @endif">
                            @if($errors->has('brand_name_en'))
                                <span class="help-block sub-error"> {{$errors->first('brand_name_en')}} </span>
                            @endif
                        </div>
                        <div class="col-12 col-md-12 mb-3">
                            <input type="hidden" name="product_store_rooms_id" value="{{$product_store_rooms_id}}">
                            @include('siteAdmin.partials.editProductStoreRoom',['store_rooms_rec'=>$pro_store_rooms,'type'=>$product->type])
                        </div>

                    </div>

                <div class="col-12 col-md-12 mb-3">
                    <button type="submit" class="btn btn-success">ویرایش</button>
                </div>

            </form>

        </div>
    </div>
@stop

@section('css')

@stop

@section('js')
    <script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>

    <script>
        $(document).ready(function () {
            $(".selectMultiTag").select2({
                tags: true,
                tokenSeparators: [',', ' ']
            })
        });
    </script>


@stop
