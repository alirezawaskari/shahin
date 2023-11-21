@extends('Admin.master')
@section('title')فهرست طرح های مالی@endsection

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
    <div>
        <a class="btn btn-primary" href="{{ route('financialPlansType.create') }}">طرح جدید</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form role="form" id="filter_form" action="/dashboard/financialPlansType/filter" method="get">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="title" placeholder="عنوان طرح" />
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="payment_type" id="payment_type">
                        <option value="all">نوع پرداخت</option>
                        @foreach (App\Models\FinancialPlansType::TYPE as $key => $item)
                        <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!auth()->user()->admin_representation_id)
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="representation">
                        <option value="all">انتخاب نمایندگی</option>
                        <option value="general">نمایندگی کل</option>
                        @foreach($representations as $representation)
                        <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" name="endDate"></date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">وضعیت</option>
                        <option value="0">غیرفعال</option>
                        <option value="1">فعال</option>
                    </select>
                </div>
                <input type="hidden" name="service" value="{{ isset($service) ? isset($service) : ''}}">
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
                    <th>عنوان طرح</th>
                    <th>هزینه ثبت نام</th>
                    <th>نوع پرداخت</th>
                    <th>درصد پیش پرداخت</th>
                    <th>درصد تخفیف</th>
                    <th>ظرفیت</th>
                    <th>ظرفیت مصرف شده</th>
                    <th>تاریخ آغاز</th>
                    <th>تاریخ پایان</th>
                    <th>وضعیت</th>
                    <th>نمایش در سایت نمایندگی کل</th>
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
<script src="/assets/dashboard/js/financialPlansType.js?v=1.0"></script>
@endsection
