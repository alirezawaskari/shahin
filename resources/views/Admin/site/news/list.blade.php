@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>اخبار</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page"> لیست اخبار</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('news.create')}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="/dashboard/news/filter" method="get">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text"   class="form-control" name="title" placeholder="عنوان را وارد نمایید" />
                    </div>
                    <div class="col-12 col-md-2 mb-2" >
                        <date-picker placeholder="از تاریخ" class="transfer_start_date" locale="fa,en" format="YYYY-M-D"
                                     name="startDate">
                        </date-picker>
                    </div>
                    <div class="col-12 col-md-2 mb-2" >
                        <date-picker class="transfer_end_date" placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                     name="endDate"></date-picker>
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
                    <th>#</th>
                    <th>عکس</th>
                    <th>عنوان</th>
                    <th>موضوع خبر</th>
                    <th>مولف</th>
                    <th> نمایش در سایت</th>
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
    <script src="{{ asset('assets/dashboard/js/news.js') }}"></script>
@stop
