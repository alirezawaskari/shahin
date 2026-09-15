@extends('Admin.master')

@section('css')

@stop

@section('content')
    @php $in_wizard = (bool) request('wizard'); @endphp

    @if($in_wizard)
        @include('Admin.productWizard.partials.steps',['current'=>'attributes','kind'=>'product'])
    @endif

    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ویرایش آیتم مربوط به ویژگی:
                    <span class="colGreen"> {{\App\Helpers\Helper::attribute($attribute_item->attribute_id,'title')}}</span>
                </h4>
            </div>
            <form class="form-horizontal" method="post"
                  action="{{route('attributeItem.update',$attribute_item->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}
                @if($in_wizard)<input type="hidden" name="wizard" value="1">@endif

                <div class="row mb-4">
                    <div class="col-12 col-md-4 ">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title"
                               value="{{$attribute_item->title}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-12 col-md-4 ">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" id="status" class="custom-control-input" name="status"
                                   value="1" @if($attribute_item->status == 1) checked @endif>
                            <label for="status" class="custom-control-label">فعال / غیر فعال</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        @if($in_wizard)
                            <a href="{{route('attributeItem.show',[$attribute_item->attribute_id,'wizard'=>1])}}" class="btn btn-warning">بازگشت</a>
                        @endif
                        <button type="submit" class="btn btn-success">ویرایش</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

@stop
@section('js')

@stop
