@extends('Admin.master')

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('coupon.update' , [$coupon->id ])}}"
                  enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div class="row mb-5">

                    <div class="col-12 col-md-6 mb-3">
                        <label>عنوان *</label>
                        <input type="text" value="{{ $coupon->title }}" class="form-control" name="title">
                        @if($errors->has('title'))
                            <span class="help-block sub-error  "> {{$errors->first('title')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>کد لاتین *</label>
                        <input type="text" value="{{ $coupon->code }}"
                               class="form-control" name="code">
                        @if($errors->has('code'))
                            <span class="help-block sub-error  "> {{$errors->first('code')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>درضد تخفیف *</label>
                        <input type="text" value="{{ $coupon->discount }}"
                               class="form-control" name="discount">
                        @if($errors->has('discount'))
                            <span class="help-block sub-error  "> {{$errors->first('discount')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>ظرفیت *</label>
                        <input type="text" value="{{ $coupon->capacity }}"
                               class="form-control"
                               name="capacity">
                        @if($errors->has('capacity'))
                            <span class="help-block sub-error  "> {{$errors->first('capacity')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>تاریخ آغاز *</label>
                        <input id="start_at" type="text" class="form-control start_at" name="start_at"
                               value="{{ $coupon->start_at }}"/>
                        @if($errors->has('start_at'))
                            <span class="help-block sub-error  "> {{$errors->first('start_at')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>تاریخ پایان *</label>
                        <input id="end_at" type="text" class="form-control end_at" name="end_at"
                               value="{{ $coupon->end_at }}"/>
                        @if($errors->has('end_at'))
                            <span class="help-block sub-error  "> {{$errors->first('end_at')}} </span>
                        @endif
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>وضعیت *</label>
                        <select class="form-control" name="status">
                            <option value="0" {{ $coupon->status == '0' ? 'selected' : '' }}>
                                غیر فعال
                            </option>
                            <option value="1" {{ $coupon->status == '1' ? 'selected' : '' }}>
                                فعال
                            </option>
                        </select>
                    </div>

                </div>

                <div class="row mb-5">
                    <div class="col-12 col-md-6 mb-5">
                        <label></label>
                        <input class="purchase_type_online" type="radio" name="purchase_type" title="آنلاین" value="0"
                               @if($coupon->purchase_type === 2 || $coupon->purchase_type === 0) checked @endif
                        ><span class="lblRadioBtn font-weight-bold font16">آنلاین </span>
                        @if($errors->has('purchase_type'))<span
                                class="help-block sub-error  ">{{$errors->first('purchase_type')}}</span>
                        @endif

                        <div class="purchase_type_box" style="display: none;">
                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="online_pay_type_zero"
                                           id="check_pay_type_online_zero" value="0"
                                           @if($coupon->purchase_type !== 1)  @if($coupon->pay_type === 2 || $coupon->pay_type === 0) checked @endif @endif>
                                    <label class="custom-control-label" for="check_pay_type_online_zero">نقدی</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="online_pay_type"
                                           id="check_pay_type_online_one" value="1"
                                           @if($coupon->purchase_type !== 1)  @if($coupon->pay_type === 2 || $coupon->pay_type === 1) checked @endif @endif>
                                    <label class="custom-control-label" for="check_pay_type_online_one">اقساط</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-5">
                        <label></label>
                        <input class="purchase_type_presence" type="radio" name="purchase_type" title="حضوری" value="1"
                               @if($coupon->purchase_type === 2 || $coupon->purchase_type === 1) checked @endif
                        ><span class="lblRadioBtn font-weight-bold font16">حضوری </span>
                        @if($errors->has('purchase_type'))<span
                                class="help-block sub-error  ">{{$errors->first('purchase_type')}}</span>@endif

                        <div class="presence_type_box" style="display: none;">
                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="presence_pay_type_zero"
                                           id="check_pay_type_presence_zero" value="0"
                                           @if($coupon->purchase_type !== 0) @if($coupon->pay_type === 2 || $coupon->pay_type === 0) checked @endif @endif>
                                    <label class="custom-control-label" for="check_pay_type_presence_zero">نقدی</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-2 disInline">
                                <label></label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" name="presence_pay_type"
                                           id="check_pay_type_presence_one" value="1"
                                           @if($coupon->purchase_type !== 0) @if($coupon->pay_type === 2 || $coupon->pay_type === 1) checked @endif @endif>
                                    <label class="custom-control-label" for="check_pay_type_presence_one">اقساط</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-12">
                    <input type="submit" class="btn btn-success border-0" value="ویرایش">
                </div>


            </form>

        </div>
    </div>
@stop

@section('js')

    <script src="{{ asset('assets/dashboard/js/site/coupon/couponEdit.js') }}"></script>

@endsection
