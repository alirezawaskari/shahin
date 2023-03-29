@extends('Admin.master')

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست سازمان ها</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست سازمان ها</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('organization.create') }}">سازمان جدید</a>
        <a class="btn btn-primary" href="{{ route('user.create') }}">کاربر جدید</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form role="form" id="form">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="نام سازمان" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="type">
                        <option value="all">انتخاب نوع همکاری</option>
                        <option value="referred">
                            مراجعه کننده
                        </option>
                        <option value="contracting_party">
                            طرف قرارداد
                        </option>
                    </select>
                </div>

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
                    <th scope="col">شماره بایگانی</th>
                    <th scope="col">عنوان</th>
                    <th scope="col">نوع همکاری</th>
                    <th scope="col">تعداد زیر سازمان</th>
                    <th scope="col">تاریخ ثبت</th>
                    <th scope="col">وضعیت بایگانی</th>
                    <th scope="col">اقدام</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <div class="position-absolute ajax-loading align-items-center flex-column pt-5 mb-n5 d-none text-center" id="loading">
            <div role="status" class="spinner-border text-primary">
            </div>
            <br>
            <span>در حال بارگذاری...</span>
        </div>

        <p id="page_text"></p>
        <input type="hidden" value="{{$delete}}" id="delete">
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>

    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/organization.js?v=1.1"></script>
@endsection
