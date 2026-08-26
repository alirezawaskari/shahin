@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ثبت تخفیف</h4>
                <div class="clearfix"></div>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('coupon.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row mb-3">
                    <div class="col-12 col-md-6 mb-3"><label>عنوان *</label>
                        <input type="text" value="" name="title" class="form-control">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 mb-3"><label>کد لاتین *</label>
                        <input type="text" value="" name="code" class="form-control">
                        @if($errors->has('code'))
                            <span class="help-block sub-error  "> {{$errors->first('code')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 mb-3"><label>درصد تخفیف *</label>
                        <input type="text" value="" name="discount" class="form-control">
                        @if($errors->has('discount'))
                            <span class="help-block sub-error  "> {{$errors->first('discount')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 mb-3"><label>ظرفیت</label>
                        <input type="text" value="" name="capacity" class="form-control">
                        @if($errors->has('capacity'))
                            <span class="help-block sub-error  "> {{$errors->first('capacity')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 mb-3"><label>تاریخ آغاز</label>
                        <input id="date" type="text" name="start_at" value=""
                               class="form-control start_at">
                        @if($errors->has('start_at'))
                            <span class="help-block sub-error  "> {{$errors->first('start_at')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 mb-3"><label>تاریخ پایان</label>
                        <input id="date" type="text" name="end_at" value=""
                               class="form-control end_at">
                        @if($errors->has('end_at'))
                            <span class="help-block sub-error  "> {{$errors->first('end_at')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-3 mb-3"><label>وضعیت</label>
                        <select name="status" class="form-control">
                            <option value="0">غیر فعال</option>
                            <option value="1">فعال</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-12 col-md-6 mb-5">
                        <label></label>
                        <input class="purchase_type_online" type="radio" name="purchase_type" title="آنلاین" value="0"
                        ><span class="lblRadioBtn font-weight-bold font16">آنلاین </span>
                        @if($errors->has('purchase_type'))<span
                                class="help-block sub-error  ">{{$errors->first('purchase_type')}}</span>
                        @endif

                        <div class="purchase_type_box" style="display: none;">
                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="online_pay_type_zero"
                                           id="check_pay_type_online_zero" value="0">
                                    <label class="custom-control-label" for="check_pay_type_online_zero">نقدی</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="online_pay_type"
                                           id="check_pay_type_online_one" value="1">
                                    <label class="custom-control-label" for="check_pay_type_online_one">اقساط</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-5">
                        <label></label>
                        <input class="purchase_type_presence" type="radio" name="purchase_type" title="حضوری" value="1">
                        <span class="lblRadioBtn font-weight-bold font16">حضوری </span>
                        @if($errors->has('purchase_type'))<span
                                class="help-block sub-error  ">{{$errors->first('purchase_type')}}</span>@endif

                        <div class="presence_type_box" style="display: none;">
                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="presence_pay_type_zero"
                                           id="check_pay_type_presence_zero" value="0">
                                    <label class="custom-control-label" for="check_pay_type_presence_zero">نقدی</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="presence_pay_type"
                                           id="check_pay_type_presence_one" value="1">
                                    <label class="custom-control-label" for="check_pay_type_presence_one">اقساط</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-5">
                    <div class="col-12">
                        <input type="submit" value="ثبت" class="btn btn-success border-0">
                    </div>
                </div>

            </form>

        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/coupon/couponCreate.js') }}"></script>
@stop
