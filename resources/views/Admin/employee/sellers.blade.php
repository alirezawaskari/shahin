@extends('Admin.master')
@section('title')فهرست پرسنل بخش فروش@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست پرسنل بخش فروش </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">فهرست پرسنل بخش فروش </li>
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
                    <select class="form-control" name="roles[]" id="role" multiple="multiple">
                         @foreach($callcenter_roles as $role_item)
                        <option value="{{$role_item->id}}"> {{$role_item->display_name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="parents[]" id="parents" multiple="multiple">
                         @foreach($parents as $parent_item)
                        <option value="{{$parent_item->id}}"> {{$parent_item->user->name.' '.$parent_item->user->family}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all" selected>انتخاب وضعیت همکار</option>
                        <option value="1"  >
                            فعال
                        </option>
                        <option value="0">
                           غیر فعال
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
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>نام و نام خانوادگی</th>
                    <th>موبایل</th>
                    <th>محل کار</th>
                    <th>نقش</th>
                    <th>سرپرست</th>
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
<script src="/assets/dashboard/js/sellers.js?v=1.0"></script>
@endsection
