@extends('Admin.master')
@section('title')فهرست درخواست مستاجرین و شرکای تجاری@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>فهرست درخواست مستاجرین و شرکای تجاری</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page"> فهرست درخواست مستاجرین و شرکای تجاری</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('centerRequest.create') }}">درخواست جدید</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form role="form" id="form" action="/dashboard/centerRequest/filter" method="get">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="نام و نام خانوادگی" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="subject" placeholder="موضوع" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="phone" placeholder="موبایل" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="province">
                        <option value="all">استان</option>
                        @foreach(App\Models\Province::all() as $province)
                        <option value="{{$province->id}}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="service_category">
                        <option value="all">انتخاب دسته بندی خدمت</option>
                        @foreach ($service_categories as $service_category)
                        <option value="{{ $service_category->id }}">{{ $service_category->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="product_category">
                        <option value="all">انتخاب دسته بندی کالا</option>
                        @foreach ($product_categories as $product_category)
                        <option value="{{ $product_category->id }}">{{ $product_category->title }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="type">
                        <option value="all">انتخاب جایگاه شغلی</option>
                        <option value="seller">فروشنده</option>
                        <option value="producer">تولید کننده</option>
                        <option value="supplier">تامین کننده</option>
                        <option value="service_provider">خدمات دهنده</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="status">
                        <option value="all">انتخاب وضعیت</option>
                        <option value="0">جدید</option>
                        <option value="1">فعال</option>
                        <option value="2">معلق</option>
                        <option value="3">رد شده</option>
                    </select>
                </div>
                @if(!auth()->user()->admin_representation_id)
                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="representation">
                        <option value="all">انتخاب نمایندگی</option>
                        <option value="">نمایندگی کل</option>
                        @foreach($representations as $representation)
                            <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D"
                                 name="end_date"></date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                        <label class="custom-control-label" for="exel">خروجی اکسل</label>
                    </div>
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
                    <th>موضوع</th>
                    <th>نام و نام خانوادگی</th>
                    <th>موبایل</th>
                    <th>استان</th>
                    <th>نمایندگی</th>
                    <th>دسته بندی</th>
                    <th>جایگاه شغلی</th>
                    <th>آپدیت کننده</th>
                    <th>تاریخ ثبت</th>
                    <th>وضعیت</th>
                    <th>اقدام</th>
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
        <div id="pagination-box">
            <ul id="pagination-demo" class="pagination"></ul>
        </div>
    </div>
</div>
@stop

@section('js')

<script src="/assets/dashboard/js/centerRequest.js?v=1.1"></script>

@endsection
