@extends('Admin.master')

@section('css')
@stop
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>جستجو یا درج محصول </h4>
                <br/>
                <span> محصولی که قصد فروش آن را دارید ، جستجو کنید در غیر این صورت از "ایجاد کالای جدید " اقدام به درج کالا خود کنید</span>

                <div class="clearfix"></div>
            </div>
            <div class="row">

                <div class="col-md-8 col-sm-8 col-xs-12">
                    <div class="frmSearch">
                        <input type="text" id="search-box" class="form-control" placeholder="نام محصول"
                               autocomplete="off"/>
                        <div id="suggesstion-box"></div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-4 col-xs-12">
                    <a href="{{route('createGood')}}" class="btn btn-success floatLeft"> ایجاد محصول جدید </a>
                </div>
                <br/><br/>
            </div>
            <div class="row resultSelectTransferProduct">

            </div>
        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/product/findProduct.js') }}"></script>

@stop
