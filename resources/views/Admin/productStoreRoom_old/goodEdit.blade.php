@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-body ">
            <div class="x_title"><h4>  ویرایش محصول<span>   {{$product->name}}</span></h4></div>

            <br/>
            <form class="form-horizontal mb-3" method="post"
                  action="{{route('productStoreRoomUpdate',$product->id)}}" enctype="multipart/form-data">
                {{csrf_field()}} {{ method_field('PATCH') }}
                <div class="row mb-3">
                    <div class="col-12 col-md-12 mb-3">
                        <input type="hidden" name="product_store_rooms_id" value="{{$product_store_rooms_id}}">
                        @include('siteAdmin.partials.editProductStoreRoom',['store_rooms_rec'=>$pro_store_rooms,'type'=>$product->type])
                    </div>

                    <br/><br/><h4 class="marginAuto mb-5">ویرایش اطلاعات محصول</h4>

                    @include('siteAdmin.partials.editGoodForm',['product'=>$product])
                </div>

                <br/>

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