@extends('Admin.master')

@section('css')

@stop

@section('content')

    <div class="card">
        <div class="card-body">

            <div class="x_title">
                <h4>ویرایش اقساط
                    @if($installment->installmentable_type == 'category')
                        <span>{{\App\Models\Category::findCategory($installment->installmentable_id,'title')}}</span>
                    @elseif($installment->installmentable_type == 'service')
                        <span>{{\App\Models\Service::serviceById($installment->installmentable_id,'title')}}</span>
                    @endif
                </h4>
                <div class="clearfix"></div>
            </div>

            <form class="form-horizontal" method="post"
                  action="{{route('installment.update',$installment->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <div class="row">

                    <div class="col-12 col-md-6 mb-3">
                        <label>تعداد *</label>
                        <input type="text" class="form-control" placeholder="لطفا تعداد را وارد کنید"
                               name="num" value="{{$installment->num}}">
                        @if($errors->has('num'))
                            <span class="help-block sub-error"> {{$errors->first('num')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>حد پایین *</label>
                        <input type="text" name="down_interval" id="currency-field" value="{{number_format($installment->down_interval)}}"
                               class="form-control" data-type="currency" placeholder="لطفا حد پایین را وارد کنید">
                        @if($errors->has('down_interval'))
                            <span class="help-block sub-error"> {{$errors->first('down_interval')}} </span>
                        @endif
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>حد بالا *</label>
                        <input type="text" name="high_interval" id="currency-field" value="{{number_format($installment->high_interval)}}"
                               class="form-control" data-type="currency" placeholder="لطفا حد بالا را وارد کنید">
                        @if($errors->has('high_interval'))
                            <span class="help-block sub-error"> {{$errors->first('high_interval')}} </span>
                        @endif
                    </div>

                    <br/> <br/>
                    <div class="ln_solid"></div>
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <button type="submit" class="btn btn-success">ارسال</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
@section('js')

@stop