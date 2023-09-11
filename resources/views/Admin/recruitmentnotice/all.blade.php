@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست موقعیت های شغلی</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست موقعیت های شغلی</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('recruitmentNotice.create') }}">موقعیت شغلی جدید</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form role="form" id="filter_form" action="/dashboard/recruitment-notice/filter" method="get">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="title" placeholder="عنوان"/>
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
                        @foreach(\App\Models\Province::all() as $province)
                            <option value="{{$province->id}}">{{$province->name}}</option>
                        @endforeach
                    </select>
                </div>
                @if(!auth()->user()->admin_representation_id)
                <div class="col-12 col-md-3 mb-2">
                    <select name="representation" id="representation_id" class="custom-select select2" >
                        <option value="all">انتخاب نمایندگی</option>
                        @foreach(\App\Models\Representation::all() as $representation)
                            <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
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
                <th>#</th>
                <th>عنوان</th>
                <th>استان</th>
                <th>نمایش در سایت</th>
                <th>نمایندگی</th>
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

        <input type="hidden" value="{{ $recruitment_notice_delete_ability }}" id="delete">
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/layouts.js"></script>
<script src="/assets/dashboard/js/recruitmentNotice.js?v=1.0"></script>
@endsection
