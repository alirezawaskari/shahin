@extends('Admin.master')
@section('title')فهرست خدمات@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست خدمات</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست خدمات</li>
            </ol>
        </nav>
    </div>
    <div>
        @permission('create_service')
        <a class="btn btn-primary" href="{{ route('service.create') }}">خدمت جدید</a>
        @endpermission
    </div>
</div>
<div class="card position-relative">
    <div class="card-body">
        <form  id="filter_form" method="get" role="form" action="/dashboard/service/filter">
            <div class="row">
                <div class="col-12 col-md-3 mb-2 disInline">
                    <input class="form-control" name="title" placeholder="عنوان">
                </div>
                <div class="col-12 col-md-3 mb-2 disInline">
                    <select title="pay_type" name="pay_type" class="custom-select">
                        <option value="all">نوع پرداخت</option>
                        @foreach($pay_type as $ind=>$pt)
                        <option value="{{$ind}}">{{$pt}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 disInline">
                    <select name="purchase_type" class="custom-select" title="">
                        <option value="all">نوع خرید</option>
                        @foreach($purchase_type as $ind=>$put)
                        <option value="{{$ind}}">{{$put}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 mb-2 disInline">
                    <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card position-relative">
    <div class="card-body table-responsive">
        <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
            <thead>
                <tr>
                    <th>#</th>
                    <th>شناسه</th>
                    <th>عکس</th>
                    <th>عنوان</th>
                    <th>دسته بندی</th>
                    <th>قیمت</th>
                    <th>نوع خرید</th>
                    <th>نوع پرداخت</th>
                    <th>نمایش در سایت</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
        <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center"
            id="loading">
            <div role="status" class="spinner-border text-primary">
            </div>
            <br>
            <span>در حال بارگذاری...</span>
        </div>
        <p id="page_text"></p>
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>

@stop

@section('js')
<script src="{{asset('assets/dashboard/js/service.js')}}"></script>
@endsection
