@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>لیست رنگ ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">رنگ ها</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="btn btn-primary dropdown-toggle">اقدامات</button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('color.create')}}" class="dropdown-item"> ایجاد  </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form   id="filter_form">
                <div class="col-12 col-md-3 mb-5 disInline">
                    <input class="form-control" name="title"  placeholder="عنوان یا کد رنگ">
                </div>
                <div class="col-12 col-md-2 mb-5 disInline">
                    <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
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
                        <th>عنوان</th> <th>رنگ</th><th>کد رنگ</th><th>وضعیت</th><th>اقدام</th>
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
    <script src="{{ asset('assets/dashboard/js/color.js') }}"></script>
@stop
