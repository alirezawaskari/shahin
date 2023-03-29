@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست درخواست عاملیت</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست درخواست عاملیت</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('agency.create') }}">درخواست جدید</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form role="form" id="form">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="نام" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="phone" placeholder="موبایل" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="province">
                        <option value="all">استان</option>
                        @foreach($provinces as $province)
                        <option value="{{$province->name}}">{{ $province->name }} - {{ $province->code }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="city" placeholder="شهر" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">وضعیت</option>
                        <option value="0">
                            جدید
                        </option>
                        <option value="1">
                            بررسی شده
                        </option>
                        <option value="2">
                            تایید شده
                        </option>
                        <option value="3">
                            تعلیق شده
                        </option>
                        <option value="4">
                            اعطا شده
                        </option>
                        <option value="5">
                            نیاز به تماس
                        </option>
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="agency_type">
                        <option value="all">نوع عاملیت</option>
                        @foreach($agency_types as $agency_type)
                        <option value="{{$agency_type->id}}">{{ $agency_type->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                        <label class="custom-control-label" for="exel">خروجی اکسل</label>
                    </div>
                </div>

                <div class="col-12 col-md-3 mb-2 mt-3">
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
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>نام و نام خانوادگی</th>
                    <th>شماره موبایل</th>
                    <th>استان</th>
                    <th>شهر</th>
                    <th>نوع عاملیت</th>
                    <th>تاریخ ثبت</th>
                    <th>تاریخ ویرایش</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            <tbody class="text-center">
            </tbody>
        </table>

        <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading">
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

<script src="{{asset('assets/dashboard/js/agency.js')}}"></script>

<script>
    @if(session() -> get('agency_delete') == 'success')
    toastr.success('با موفقیت حذف شد!');
    @elseif(session() -> get('agency_create') == 'success')
    toastr.success('با موفقیت ایجاد شد!');
    @elseif(session() -> get('agency_update') == 'success')
    toastr.success('با موفقیت آپدیت شد!');
    @endif
</script>

@endsection
