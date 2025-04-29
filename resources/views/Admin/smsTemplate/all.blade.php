@extends('Admin.master')
@section('title')فهرست قالب ها پیامکی@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست قالب ها پیامکی</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست قالب ها پیامکی</li>
            </ol>
        </nav>
    </div>
    <div>
        @permission('sms-template-create')
        <a class="btn btn-primary" href="{{ route('smsTemplate.create') }}">ایجاد قالب جدید</a>
        @endpermission
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form role="form" id="filter_form" action="/dashboard/smsTemplate/filter" method="get">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="title" placeholder="عنوان قالب" />
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">وضعیت</option>
                        <option value="0">غیرفعال</option>
                        <option value="1">فعال</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <button id="do_filter" class="btn btn-outline-primary">
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
                    <th>نام قالب</th>
                    <th>محتوا قالب</th>
                    <th>وضعیت</th>
                    <th>ثبت کننده</th>
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
<script src="/assets/dashboard/js/layouts.js"></script>
<script src="/assets/dashboard/js/smsTemplate.js?v=1.0"></script>
@endsection
