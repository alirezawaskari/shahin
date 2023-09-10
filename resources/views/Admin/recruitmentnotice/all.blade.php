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
        <form role="form" id="filter_form" action="/dashboard/resume/filter" method="get">
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
                <div class="col-12 col-md-3 mb-2">
                    <select name="province" id="province_id" class="custom-select select2" >
                        <option value="all">انتخاب نمایندگی</option>
                        @foreach(\App\Models\Representation::all() as $representation)
                            <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
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
                    <th>عنوان</th>
                    <th>استان</th>
                    <th>نمایش در سایت</th>
                    <th>نمایندگی</th>
                    <th>اقدام</th>
                </tr>
            </thead>
            @foreach($recruitment_notices as $recruitment_notice)
            <tr>
                <td>
                    {{ $recruitment_notice->title }}
                </td>
                <td>
                    {{ $recruitment_notice->province->name }}
                </td>
                <td>
                    {!! \App\Helpers\Helper::iconIsItOrNot($recruitment_notice->status) !!}
                </td>
                <td>{{$recruitment_notice->representation ? $recruitment_notice->representation->fa_name : 'نمایندگی کل'}} </td>
                <td>
                    <div data-v-15965e3b="" class="dropdown dropup">
                        <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                            class="btn btn-dark btn-floating btn-icon btn-sm">
                            <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                        </a>
                        <div class="dropdown-menu">
                            <a href="{{ route('recruitmentNotice.edit' , [ $recruitment_notice->id]) }}"><button
                                    type="button" class="dropdown-item pointer">ویرایش</button></a>
                            @permission('purchase-invoice-detail')
                            <form class="delete_form" method="post"
                                action="{{ route('recruitmentNotice.destroy',[$recruitment_notice->id]) }}">
                                @method('DELETE')
                                @csrf
                                <input type="button" class="dropdown-item pointer delete_submit"
                                    id="{{ $recruitment_notice->id }}" value="حذف">
                            </form>
                            @endpermission
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@stop

@section('js')
<script src="/assets/dashboard/js/layouts.js"></script>
@endsection
