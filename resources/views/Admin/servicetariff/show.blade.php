@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post"
                  action="{{route('product.update' , [ $product->id])}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                @include('Admin.layouts.errors')
                <div class="row">

                    <div class="col-12 col-md-4 mb-3">
                        <label>نام</label>
                        <input type="text" value="{{ $product->name }}"
                               class="form-control"
                               name="name" required>
                    </div>

                    <div class="col-12 col-md-4 mb-3">
                        <label>مبلغ</label>
                        <input type="number" value="{{ $product->price }}"
                               class="form-control"
                               name="price" required>
                    </div>
                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>

                </div>
            </form>
        </div>
    </div>
@stop
