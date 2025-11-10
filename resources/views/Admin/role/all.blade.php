@extends('Admin.master')
@section('title')فهرست نقش ها@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست نقش ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست نقش ها</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('role.create') }}">نقش جدید</a>
    </div>
</div>
<div class="card position-relative">
    <div class="card-body">
        <form id="filter_form" action="/dashboard/role/filter" method="get">
            @csrf
            <div class="row">
                <div class="col-12 col-md-3 mb-2 disInline">
                    <input class="form-control" name="display_name" placeholder="عنوان نقش">
                </div>
                <div class="col-12 col-md-3 mb-2">
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
                    <th>عنوان نقش</th>
                    <th>کلمه کلیدی نقش</th>
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
        @include('Admin.layouts.pagination')
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/pagination.js"></script>
<script src="/assets/dashboard/js/role.js?v=1.1"></script>
<script src="/assets/dashboard/js/layouts.js"></script>
@endsection