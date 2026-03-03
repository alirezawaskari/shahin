@extends('Admin.master')
@section('content')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">ایجاد لینک کوتاه</h4>
            <form class="form-horizontal" id="short-link-form" method="post" action="{{route('short-link.store')}}">
                @csrf
                <div class="form-group">
                    <label for="url" class="control-label col-md-3">آدرس وب سایت:</label>
                    <div class="col-md-9">
                        <input type="text" id="url" name="original_link" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-9 col-md-offset-3">
                        <button type="submit" class="btn btn-primary">ثبت کوتاه کننده</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title"></h4>
            <div id="new_short_link"></div>
        </div>
    </div>

{{--    <div class="card">--}}
{{--        <div class="card-header">--}}
{{--            <h4 class="card-title">جدول لینک‌های کوتاه شده</h4>--}}
{{--        </div>--}}
{{--        <div class="card-body">--}}
{{--            <table id="short-links-table" class="table table-bordered">--}}
{{--                <thead>--}}
{{--                <tr>--}}
{{--                    <th>آدرس اصلی</th>--}}
{{--                    <th>آدرس کوتاه</th>--}}
{{--                    <th>تاریخ ایجاد</th>--}}
{{--                    <th>عملیات</th>--}}
{{--                </tr>--}}
{{--                </thead>--}}
{{--                <tbody>--}}
{{--                @foreach($short_links as $short_link)--}}
{{--                    <tr>--}}
{{--                        <td>{{ $short_link->original_link }}</td>--}}
{{--                        <td>{{ $short_link->short_link }}</td>--}}
{{--                        <td>{{ $short_link->created_at }}</td>--}}
{{--                        <td>--}}
{{--                            <button class="btn btn-primary btn-copy"--}}
{{--                                    data-clipboard-text="{{ $short_link->short_link }}">کپی--}}
{{--                            </button>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                @endforeach--}}
{{--                </tbody>--}}
{{--            </table>--}}
{{--        </div>--}}
{{--    </div>--}}

{{--pagination part--}}
    <div class="page-header">
        <div>
            <h3>فهرست لینک ها</h3>
        </div>
    </div>
    <form id="filter_form" method="get" action="/dashboard/short-links/filter">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2"><input type="text" name="original_link" placeholder="لینک اصلی " class="form-control">
                    </div>
                    <div class="col-12 col-md-3 mb-2"><input type="text" name="short_link" placeholder="لینک کوتاه شده"
                                                             class="form-control"></div>

                    <div class="col-12 col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form id="form-factor-action">
        <div class="card position-relative">
            <div class="card-body table-responsive ">
                <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>آدرس اصلی</th>
                        <th>آدرس کوتاه</th>
                        <th>تاریخ ایجاد</th>
                        <th>عملیات</th>
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
                <input type="hidden" value="filter" id="url">
                <div id="pagination-box">
                    <ul id="pagination-demo" class="pagination"></ul>
                </div>
            </div>

        </div>
    </form>
@stop
@section('css')
@stop
@section('js')
    <script src="{{ asset('/assets/dashboard/js/clipboard.min.js?v=1.0') }}"></script>
    <script src="{{ asset('/assets/dashboard/js/shortLink.js?v=1.0') }}"></script>

@stop

