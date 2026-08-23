@extends('Admin.master')
@section('title')فهرست مراکز@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست مراکز</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست مراکز</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('center.create') }}">مرکز جدید</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form role="form" id="form" action="/dashboard/center/filter" method="get">
            <div class="row">

                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="title" placeholder="نام مرکز">
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="service">
                        <option value="all">خدمات شامل این مرکز</option>
                        @foreach(\App\Models\Service::orderBy('title')->get() as $service)
                            <option value="{{ $service->id }}">{{ $service->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">وضعیت مرکز</option>
                        <option value="1">فعال</option>
                        <option value="0">غیر فعال</option>
                    </select>
                </div>

                @if(!auth()->user()->admin_representation_id)
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="representation">
                        <option value="all">نمایندگی</option>
                        <option value="none">نمایندگی کل</option>
                        @foreach(\App\Models\Representation::orderBy('fa_name')->get() as $representation)
                            <option value="{{ $representation->id }}">
                                {{ $representation->fa_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @include('Admin.filter.businessSelect')

                <div class="col-12 col-md-3 mb-2">
                    <button id="filter" type="submit" class="btn btn-primary">
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
                    <th>نام مرکز</th>
                    <th>خدمات</th>
                    <th>نمایندگی</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center"
             id="loading">
            <div role="status" class="spinner-border text-primary"></div>
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
<script src="/assets/dashboard/js/center.js"></script>
@endsection
