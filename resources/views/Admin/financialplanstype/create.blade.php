@extends('Admin.master')
@section('title')ایجاد طرح مالی@endsection

@section('content')
<div class="page-header">
    <div>
        <h3>ایجاد طرح جدید</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page"><a
                        href="{{ route('financialPlansType.index') }}">فهرست طرح های مالی</a></li>
                <li class="breadcrumb-item active" aria-current="page">ایجاد طرح جدید</li>
            </ol>
        </nav>
    </div>
    <div>
        <a class="btn btn-primary" href="{{ route('financialPlansType.index') }}">فهرست طرح های مالی</a>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form class="form-horizontal" role="form" method="post" action="{{route('financialPlansType.store')}}"
            enctype="multipart/form-data">
            @csrf
            @include('Admin.layouts.errors')
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <label>عنوان طرح</label>
                    <input type="text" class="form-control" name="title" required>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>هزینه ثبت نام (ریال)</label>
                    <input type="text" class="form-control" name="price" oninput="separatePrices(this)">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>نوع پرداخت</label>
                    <select class="form-control" name="payment_type" id="payment_type">
                        @foreach (App\Models\FinancialPlansType::TYPE as $key => $item)
                        <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="type_number_pay_steps">
                    <label>تعداد چک ها</label>
                    <select class="form-control" id="select_type_number_pay_steps">
                        <option value="limit">محدود</option>
                        <option value="unlimit">نامحدود</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 d-none" id="number_pay_steps">
                    <label>تعداد چک ها جهت پرداخت قسط</label>
                    <input type="number" class="form-control" name="number_pay_steps" min="1" max="20">
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <label>ظرفیت طرح (حداکثر تعداد استفاده از طرح)</label>
                    <input type="text" class="form-control" name="capacity"
                        required>
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label>تاریخ آغاز</label>
                    <date-picker format="YYYY/M/D HH:mm:ss" name="start_date"
                        type="datetime" display-format="jYYYY/jMM/jDD HH:mm:ss" id="start_date">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label>تاریخ پایان</label>
                    <date-picker format="YYYY/M/D HH:mm:ss" name="end_date"
                        type="datetime" display-format="jYYYY/jMM/jDD HH:mm:ss" id="end_date">
                    </date-picker>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>نوع ضمانت</label>
                    <select class="form-control select2" name="guarantee_type[]" multiple>
                        @foreach (App\Models\TotalFactorItem::GuaranteeTYPE as $key => $item)
                        <option value="{{ $key }}">{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>درصد پیش پرداخت (%)</label>
                    <input type="number" class="form-control" name="prepayment_percent" min="0" max="100">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>درصد تخفیف (%)</label>
                    <input type="number" class="form-control" name="discount_percent" min="0" max="100">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>وضعیت</label>
                    <select class="form-control" name="status">
                        <option value="0">غیرفعال</option>
                        <option value="1">فعال</option>
                    </select>
                </div>
                @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-4 mb-3">
                        <label>نمایندگی</label>
                        <select name="representation_id" class="custom-select select2" multiple>
                            @foreach($representations as $representation)
                                <option value="{{$representation->id}}">{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 mb-4">
                        <label class="col-md-2 col-sm-2 col-xs-12 control-label"></label>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="status" value="1"
                                       @if(old('status') == 1) checked @endif>نمایش در سایت نمایندگی کل
                            </label>
                            @if($errors->has('status'))<span
                                    class="help-block sub-error  ">{{$errors->first('status')}}</span>@endif
                        </div>
                    </div>
                    @endif
            </div>
            <div class="row">
                <div class="col-12 col-md-6 mb-4 mt-3 pt-3 border-top">
                    <label>خدمات شامل این طرح</label>
                    <select class="form-control select2" name="service[]" multiple>
                        @foreach ($services as $service)
                        <option value="{{ $service->id }}">{{ $service->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-4 mt-3 pt-3 border-top">
                    <label>دسته بندی شامل این طرح</label>
                    <select class="form-control select2" name="category[]" multiple>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12">
                <input type="submit" class="btn btn-success border-0" value="ثبت">
            </div>
        </form>
    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/financialPlansType.js') }}"></script>
@endsection
