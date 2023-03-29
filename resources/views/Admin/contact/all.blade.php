@extends('Admin.master')
@section('title'){{ $title }}@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>{{ $title }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form  id="filter_form" method="get" role="form" action="/dashboard/contact/{{$url}}">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="نام ارسال کننده" />
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="text" placeholder="متن پیام ارسال کننده" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="card">
                        <option value="all">واحد مربوطه</option>
                        @foreach ($cards as $card)
                        <option value="{{ $card->id }}">{{ $card->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">وضعیت</option>
                        <option value="0">جدید</option>
                        <option value="1">خوانده شده</option>
                        <option value="2">تعلیق شده</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 mt-2 mb-md-0">
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
                        <th>نام کاربر</th>
                        <th>موبایل</th>
                        <th>بخشی از پیام</th>
                        <th>واحد مربوطه</th>
                        <th>تاریخ ثبت</th>
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
        <input type="hidden" value="{{ $url }}" id="url">
        <input type="hidden" value="{{$delete}}" id="delete">

        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>
@stop

@section('js')

<script src="/assets/dashboard/js/layouts.js"></script>
<script src="/assets/dashboard/js/contact.js?v=1.1"></script>

@endsection
