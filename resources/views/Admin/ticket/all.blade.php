@extends('Admin.master')
@section('title')اتوماسیون اداری@endsection
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
    <div class="btn-group" role="group">
        <div class="heading-elements">
            <div class="heading-btn-group ltr mb-3">
                <a href="{{ route('ticket.create') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                    <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                    <span>مکاتبه جدید</span>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form  id="filter_form" method="get" role="form" action="/dashboard/ticket/{{$url}}">
            <div class="row">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <input type="text" class="form-control" name="title" placeholder="متن مکاتبه" />
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <select class="form-control select2" name="user">
                        <option value="all">ارسال کننده</option>
                        @foreach($employees as $employee)
                            <option value="{{$employee->user->id}}">{{ $employee->user->name }} - {{ $employee->user->family }}</option>
                        @endforeach
                    </select>
                </div>
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
                <th>دریافت کننده</th>
                <th>ارسال کننده</th>
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

        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="{{asset('assets/dashboard/js/tickets.js')}}"></script>
@endsection
