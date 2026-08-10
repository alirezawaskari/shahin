@extends('Admin.master')
@section('title')فهرست تعرفه ها@endsection

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست تعرفه ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item"><a href="/{{$center->id}}">شعبه {{$center->title}}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست تعرفه ها</li>
                </ol>
            </nav>
        </div>
        <a href="{{ url('dashboard/center/'.$center->id.'/'.$service->slug.'/serviceTariff/create') }}" style="float: left" class="btn btn-primary"> ایجاد تعرفه جدید</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="/dashboard/serviceTariff/filter" method="get">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="title" placeholder="عنوان"/>
                    </div>
                    <div class="col-12 col-md-3 mb-2" >
                        <select class="form-control" name="tariff_id">
                            <option value="" >دوره تعرفه را انتخاب کنید </option>
                            @foreach($tariffs as $item)
                                <option value="{{$item->id}}">{{$item->title}}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="center_id" value="{{$center->id}}"/>
                        <input type="hidden" name="service_id"  value="{{$service->id}}"/>
                    </div>
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
                    <th>عنوان تعرفه</th>
                    <th>بهای تمام شده(ریال)</th>
                    @if($access_selling_price)
                    <th>قیمت مصرف کننده (ریال)</th>
                    <th>نرخ اختلاف خرید</th>
                    @endif
                    <th>آخرین ویرایش</th>
                    <th>تاریخ ثبت</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading">
                <div role="status" class="spinner-border text-primary">
                </div>
                <br>
                <span>در حال بارگذاری...</span>
            </div>

            <p id="page_text"></p>
            <input type="hidden" value="{{$access_selling_price}}" id="access_selling_price">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="{{ asset('/assets/dashboard/js/serviceTariff.js?v=2.1') }}"></script>
@endsection
