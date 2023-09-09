@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>فهرست رزومه ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">فهرست رزومه ها</li>
                </ol>
            </nav>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('resume.create') }}">رزومه جدید</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form role="form" id="filter_form" action="/dashboard/resume/filter" method="get">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="name" placeholder="نام"/>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input type="text" class="form-control" name="phone" placeholder="شماره موبایل"/>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="job_title">
                            <option value="all">انتخاب عنوان شغلی</option>
                            @foreach($jobs as $job)
                                <option value="{{$job->id}}">{{$job->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="status">
                                <option value="all">انتخاب وضعیت رزومه</option>
                                <option value="0">جدید</option>
                                <option value="5">درحال بررسی</option>
                                <option value="1">قرار مصاحبه اولیه</option>
                                <option value="2">تعلیق شده</option>
                                <option value="3">تایید شده</option>
                                <option value="4">رد شده</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="representation">
                            <option value="all">انتخاب نمایندگی</option>
                            @foreach($representations as $representation)
                                <option value="">نمایندگی کل</option>
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                        </date-picker>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                     name="endDate"></date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select name="province" id="province_id" class="custom-select select2" >
                            <option value="all">انتخاب استان</option>
                            @foreach($provinces as $province)
                                <option value="{{$province->id}}">{{$province->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" name="excel" id="excel">
                            <label class="custom-control-label" for="excel">خروجی اکسل</label>
                        </div>
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
                    <th style="width: 20px">#</th>
                    <th>نام و نام خانوادگی</th>
                    <th> موبایل</th>
                    <th>تاریخ ثبت</th>
                    <th>رزومه</th>
                    <th>عنوان شغلی</th>
                    <th>نمایندگی</th>
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
            <input type="hidden" value="filter" id="url">
            <input type="hidden" value="complete" id="type">
            <div id="pagination-box">
                <ul id="pagination-demo" class="pagination"></ul>
            </div>
        </div>
    </div>
 @stop

@section('js')
    <script src="/assets/dashboard/js/resume.js?v=1.0"></script>
@endsection
