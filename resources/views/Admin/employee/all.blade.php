@extends('Admin.master')
@section('title')فهرست همکاران@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست همکاران </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست همکاران </li>
            </ol>
        </nav>
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
                    <input type="text" class="form-control" name="family" placeholder="نام خانوادگی" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="phone" placeholder="موبایل" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="work_title" placeholder="شغل" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">انتخاب وضعیت همکار</option>
                        <option value="1">
                            فعال
                        </option>
                        <option value="0">
                           غیر فعال
                        </option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2" id="role_box">
                    <select name="role" id="role_id" class="custom-select select2" >
                        <option value="all">انتخاب نقش</option>
                        @foreach($roles as $role)
                            <option value="{{$role->id}}">{{$role->display_name}}</option>
                        @endforeach
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
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>نام و نام خانوادگی</th>
                    <th>موبایل</th>
                    <th>محل کار</th>
                    <th>شغل</th>
                    <th>تاریخ ثبت</th>
                    <th>فعال</th>
                    <th> نمایش</th>
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
<script src="/assets/dashboard/js/employee.js?v=1.1"></script>
@endsection
