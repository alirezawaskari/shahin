@extends('Admin.master')

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="col-md-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title"><h4>@if(isset($page_title)) {{$page_title}} @endif</h4></div>

                    <form class="form-horizontal" method="post" action="{{route('products.storeMaterials')}}"
                          enctype="multipart/form-data">
                        {{csrf_field()}}
                        <div class="row rawMaterialBox">
                            <div class="col-12 col-md-6 mb-3">
                                <label>عنوان محصول</label>
                                <select class="js-example-basic-single form-control" name="material[product_id]">
                                    <option value="">لطفا انتخاب کنید</option>
                                    @foreach($material_products as $product)
                                        <option value="{{$product->id}}"
                                                @if(old("material.product_id") == $product->id ) selected @endif>{{$product->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-12 mb-3">
                                <button type="button" class="btn btn-info btn-md" data-toggle="modal"
                                        data-target="#materialTitleProductModal"> ایجاد عنوان جدید
                                </button>
                                @include('Admin.modal.materialTitleProduct',['arrayName'=>'material'])
                            </div>

                            <div class="col-12 col-md-12 mb-3">
                                @include('Admin.partials.productStoreRoom',['arrayName'=>'material','stock'=>true])
                            </div>

                            <div class="col-12 col-md-12 mb-3">
                                <button type="submit" class="btn btn-success">ثبت و درج محصول بعدی</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>


@stop

@section('css')
@stop

@section('js')

    <script>

        if (($(".expire_date").length > 0)) {
            $('.expire_date').persianDatepicker({
                observer: true,
                format: 'YYYY-MM-DD HH:mm:ss',
                timePicker: {
                    enabled: true, meridiem: {enabled: true}
                }
            });
        }

    </script>


@stop
