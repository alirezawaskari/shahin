@extends('Admin.master')
@section('title')فهرست چک ها@endsection

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست بیماری ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست بیماری ها</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="{{route('disease.filter')}}" method="get">
                <div class="row">
                    <div class="col-12 col-md-4 mb-2">
                        <input type="text" class="form-control" name="title" placeholder="عنوان"/>
                    </div>
                    <div class="col-12 col-md-4 mb-2">
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
                    <th scope="col" >#</th>
                    <th scope="col">عنوان</th>
                    <th scope="col">عملیات </th>
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
{{--            <input type="hidden" value="{{$can_update_status_check}}" id="can-update-status-check">--}}
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script src="/assets/dashboard/js/layouts.js"></script>
    <script src="/assets/dashboard/js/disease.js?v=1.0"></script>
@endsection
