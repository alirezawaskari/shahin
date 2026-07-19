@extends('Admin.master')

@section('title')
    فهرست منو ها
@endsection

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست منو ها</h3>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active">فهرست منو ها</li>
                </ol>
            </nav>
        </div>

        <div>
            @permission('menu_setting_create')
            <a class="btn btn-primary" href="{{ route('menuSetting.create') }}">
                ایجاد منو جدید
            </a>
            @endpermission
        </div>
    </div>


    {{-- FILTER --}}
    <div class="card">
        <div class="card-body">

            <form id="filter_form">
                <div class="row">

                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="title" placeholder="عنوان">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <button type="button" id="do_filter" class="btn btn-outline-primary">
                            فیلتر کن
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>


    {{-- TABLE --}}
    <div class="card position-relative">
        <div class="card-body table-responsive">

            <table class="table table-bordered table-striped" id="datatable-json">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام</th>
                    <th>ایکون</th>
                    <th>تاریخ ویرایش</th>
                    <th>اقدام</th>
                </tr>
                </thead>

                <tbody></tbody>
            </table>


            {{-- LOADING --}}
            <div id="loading"
                 class="ajax-loading position-absolute align-items-center flex-column pt-5 mb-n5 text-center d-none">

                <div class="spinner-border text-primary" role="status"></div>
                <br>
                <span>در حال بارگذاری...</span>

            </div>


            <p id="page_text" class="text-muted"></p>

            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>

        </div>
    </div>

@endsection


@section('js')
    <script src="/assets/dashboard/js/menuSetting.js?v=1.0"></script>
@endsection
