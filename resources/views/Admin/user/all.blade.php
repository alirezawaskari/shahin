@extends('Admin.master')
@section('title'){{ $title }}@endsection

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
        @if(auth()->user()->admin_organization_id)
        <div>
            <a class="btn btn-primary" href="{{ route('organization.user.create') }}">کاربر جدید</a>
        </div>
        @endif
    </div>
    <div>
        <form id="filter_form" action="/dashboard/{{$url}}" method="get">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-3 mb-2"><input type="text" name="name" placeholder="نام"
                                                                 class="form-control"></div>
                        <div class="col-12 col-md-3 mb-2"><input type="text" name="family" placeholder="نام خانوادگی"
                                                                 class="form-control"></div>
                        <div class="col-12 col-md-3 mb-2"><input type="text" name="phone" placeholder="موبایل"
                                                                 class="form-control"></div>
                        <div class="col-12 col-md-3 mb-2"><input type="text" name="melicode" placeholder="کد ملی"
                                                                 class="form-control"></div>
                        <div class="col-12 col-md-3 mb-2">
                            <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="startDate">
                            </date-picker>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                         name="endDate"></date-picker>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <select name="level" id="level" class="custom-select" >
                                <option value="all">انتخاب نوع کاربر </option>
                                <option value="user">کاربر عادی</option>
                                <option value="admin">مدیر</option>
                                <option value="ghorfeAdmin">همکار غرفه</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <select name="sex" class="custom-select" >
                                <option value="all">جنسیت</option>
                                <option value="1">خانم</option>
                                <option value="0">آقا</option>
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
                            <select name="representation" id="has_session" class="custom-select select2" >
                                <option value="all">انتخاب نمایندگی</option>
                                     @foreach($representations as $representation)
                                          <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                                     @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <select name="organization" id="has_session" class="custom-select select2" >
                                <option value="all">انتخاب سازمان</option>
                                    @foreach($organizations as $organization)
                                    <option value="{{$organization->id}}">{{$organization->fa_name}}</option>
                                    @endforeach
                            </select>
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
                            <select name="city" id="city_id" class="custom-select select2" >
                                <option value="all">انتخاب شهر</option>

                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                                <label class="custom-control-label" for="exel">خروجی اکسل</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <button type="button" class="btn btn-outline-primary py-2 px-4" id="do_filter">فیلتر کن</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="card position-relative">
            <div class="card-body table-responsive" style="padding-bottom: 120px">
                <table class="vuetable table table-bordered table-striped table-responsive-md" id="datatable-json">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>نام</th>
                        <th>نام خانوادگی</th>
                        <th>موبایل</th>
                        <th>کد ملی</th>
                        <th>کد کاربری</th>
                        <th>نمایندگی</th>
                        <th>تاریخ</th>
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

                <p id="page_text" style="display: initial" ></p><p id="sum_price" style="display: initial" ></p>
                <input type="hidden" value="{{ $url }}" id="url">
                <input type="hidden" value="complete" id="type">
                <input type="hidden" value="{{$delete}}" id="delete">
                <input type="hidden" value="{{$can_manual_login}}" id="can-manual-login">
                <div id="pagination-box">
                    <ul id="pagination-demo" class="pagination"></ul>
                </div>
            </div>
        </div>
    </div>
@stop
@section('js')
    <script src="/assets/dashboard/js/user.js?v=1.1"></script>
    <script src="/assets/dashboard/js/layouts.js?v=1.0"></script>
@endsection
