@extends('Admin.master')
@section('title')فهرست تعرفه های شاخص@endsection

@section('content')

<div class="card">
    <div class="card-body">
        <h4>ثبت و ویرایش</h4>
        <hr>
        <form id="form_data">
            <div class="row">
                <div class="col-md-4">
                    <label>عنوان تعرفه</label>
                    <select class="form-control select2" name="service_index_tariff" id="serviceIndexTariff">
                        <option>عنوان تعرفه را تایپ کنید</option>
                        @foreach ($service_index_tariffs as $item)
                        <option value="{{$item->id}}">{{$item->title}}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="service_id"  value="{{$service->id}}"/>
                <div class="col-md-4">
                    <label>مبلغ خرید</label>
                    <input class="form-control" type="text" oninput="separatePrices(this)" name="purchase_price" id="purchase_price">
                </div>
                <div class="col-4-12">
                    <button id="createUpdeteIndexTariff" type="button" class="btn btn-outline-success py-2 px-4">ثبت</button>
                </div>

            </div>
        </form>
    </div>
</div>
<div class="page-header">
    <div>
        <h3>فهرست تعرفه های شاخص</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست تعرفه های شاخص</li>
            </ol>
        </nav>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form role="form" id="filter_form" action="/dashboard/serviceIndexTariff/filter" method="get">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="title" placeholder="عنوان"/>
                </div>
                <input type="hidden" name="service_id"  value="{{$service->id}}"/>
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="excel" id="excel">
                        <label class="custom-control-label" for="excel">خروجی اکسل</label>
                    </div>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button id="do_filter"  class="btn btn-primary">
                        فیلتر کن
                    </button>
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
                    <th>عنوان</th>
                    <th>ثبت کننده</th>
                    <th>قیمت خرید</th>
                    <th>تاریخ ثبت</th>
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
        <input type="hidden" value="{{$delete}}" id="delete">
        <p id="page_text"></p>
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/serviceIndexTariff.js?v=2.0"></script>
<script src="/assets/dashboard/js/layouts.js"></script>
@endsection
