@extends('Admin.master')
@section('title'){{ $title }}@endsection
@section('content')
    <div class="page-header">
        <div>
            <h3>{{ $title }}</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست {{ $title }}<</li>
                </ol>
            </nav>
        </div>
        <div>
            @permission('create_service')
            <a class="btn btn-primary" href="{{route('tariff.create')}}">ایجاد تعرفه</a>
            @endpermission
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form  id="filter_form" method="get" role="form" action="/dashboard/tariff/search">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2 mb-md-0">
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
                    <th>عنوان</th>
                    <th>خدمت</th>
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
            <p id="page_text"></p>

            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{asset('assets/dashboard/js/tariff.js')}}"></script>
@endsection
