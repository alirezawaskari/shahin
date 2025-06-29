@extends('Admin.master')
@section('title')ویرایش طرح مالی@endsection

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
        <form class="form-horizontal" role="form" method="post"
            action="{{route('financialPlansType.update' , [$financialPlansType->id])}}"
            enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            @include('Admin.layouts.errors')
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <label>عنوان طرح</label>
                    <input type="text" class="form-control" name="title" value="{{ $financialPlansType->title }}"
                        required>
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>هزینه ثبت نام (ریال)</label>
                    <input type="text" class="form-control" name="price" oninput="separatePrices(this)"
                        value="{{ number_format($financialPlansType->price) }}">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>نوع پرداخت</label>
                    <select class="form-control" name="payment_type" id="payment_type">
                        @foreach (App\Models\FinancialPlansType::TYPE as $key => $item)
                        <option value="{{ $key }}" {{ $financialPlansType->payment_type == $key ? 'selected' : ''}}>{{
                            $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 {{ $financialPlansType->payment_type == 'check' ? '' : 'd-none' }}"
                    id="type_number_pay_steps">
                    <label>تعداد چک ها</label>
                    <select class="form-control" id="select_type_number_pay_steps">
                        <option value="limit" {{ $financialPlansType->number_pay_steps ? 'selected' : '' }}>محدود
                        </option>
                        <option value="unlimit" {{ $financialPlansType->number_pay_steps ? '' : 'selected' }}>نامحدود
                        </option>
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 {{ $financialPlansType->number_pay_steps ? '' : 'd-none' }}"
                    id="number_pay_steps">
                    <label>تعداد چک ها جهت پرداخت قسط</label>
                    <input type="number" class="form-control"
                        value="{{ $financialPlansType->number_pay_steps }}" name="number_pay_steps" min="1" max="20">
                </div>
                <div class="col-12 col-md-3 mb-3">
                    <label>ظرفیت طرح (حداکثر تعداد استفاده از طرح)</label>
                    <input type="number" value="{{ $financialPlansType->capacity }}" class="form-control" name="capacity"
                        min="1" required>
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label>تاریخ آغاز</label>
                    <date-picker value="{{ $financialPlansType->start_date }}" format="YYYY/M/D HH:mm:ss" name="start_date"
                        type="datetime" display-format="jYYYY/jMM/jDD HH:mm:ss" id="start_date">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-3">
                    <label>تاریخ پایان</label>
                    <date-picker value="{{ $financialPlansType->end_date }}" format="YYYY/M/D HH:mm:ss" name="end_date"
                        type="datetime" display-format="jYYYY/jMM/jDD HH:mm:ss" id="end_date">
                    </date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2 {{ $financialPlansType->payment_type == 'cash' ? 'd-none' : '' }}" id="guarantee_type">
                    <label>نوع ضمانت</label>
                    <select class="form-control select2" name="guarantee_type[]" multiple>
                        @foreach (App\Models\TotalFactorItem::GuaranteeTYPE as $key => $item)
                        <option value="{{ $key }}" {{ $financialPlansType->guarantee_type && in_Array($key , $financialPlansType->guarantee_type) ? 'selected'
                            : ''}}>{{ $item }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 mb-2 {{ $financialPlansType->payment_type == 'cash' ? 'd-none' : '' }}" id="prepayment_percent">
                    <label>درصد پیش پرداخت (%)</label>
                    <input type="number" class="form-control" name="prepayment_percent" min="0" max="100"
                        value="{{ $financialPlansType->prepayment_percent }}">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>درصد تخفیف (%)</label>
                    <input type="number" class="form-control" name="discount_percent" min="0" max="100"
                        value="{{ $financialPlansType->discount_percent }}">
                </div>
                <div class="col-12 col-md-3 mb-2">
                    <label>وضعیت</label>
                    <select class="form-control" name="status">
                        <option value="0" {{ $financialPlansType->status == 0 ? 'selected' : '' }}>غیرفعال</option>
                        <option value="1" {{ $financialPlansType->status == 1 ? 'selected' : '' }}>فعال</option>
                    </select>
                </div>
                @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-3 mb-3">
                        <label>نمایندگی</label>
                        <select name="representation_id[]" class="custom-select select2" multiple>
                            @foreach($representations as $representation)
                                <option value="{{$representation->id}}" {{ in_array(trim($representation->id) , $financialPlansType->representations()->pluck('id')->toArray()) ? 'selected' : '' }}>{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <label>مالیات</label>
                        <input
                            value="{{ $financialPlansType?->task }}"
                            type="text"
                            class="form-control"
                            name="task"
                            required
                        />
                    </div>
                    <div class="col-12 col-md-2 mb-4">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_show" value="1"
                                       @if($financialPlansType->is_show == 1) checked @endif>نمایش در سایت نمایندگی کل
                            </label>
                            @if($errors->has('is_show'))<span
                                    class="help-block sub-error  ">{{$errors->first('is_show')}}</span>@endif
                        </div>
                    </div>
                    @endif
            </div>
            <div class="row">
                <div class="col-12 col-md-6 mb-4 mt-3 pt-3 border-top">
                    <label>خدمات شامل این طرح</label>
                    <select class="form-control select2" name="service[]" multiple>
                        @foreach ($services as $service)
                        <option value="{{ $service->id }}" {{ in_Array(trim($service->id) ,
                            $financialPlansType->services()->pluck('financialplanstypeitemable_id')->toArray()) ?
                            'selected' : '' }}>{{ $service->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 mb-4 mt-3 pt-3 border-top">
                    <label>دسته بندی شامل این طرح</label>
                    <select class="form-control select2" name="category[]" multiple>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ in_Array(trim($category->id) ,
                            $financialPlansType->categories()->pluck('financialplanstypeitemable_id')->toArray()) ?
                            'selected' : '' }}>{{ $category->title }}</option>
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
