@extends('Admin.master')

@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <h4>ویرایش ویژگی
                <span class="green"> {{\App\Helpers\Helper::attributeGroups($attribute->attribute_group_id,'title')}}</span>
            </h4>

            <form class="form-horizontal" method="post"
                  action="{{route('attribute.update',$attribute->id)}}" enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <div class="row">

                <div class="col-12 col-md-4 mb-3">
                    <label>عنوان *</label>
                    <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید" name="title"
                           value="{{$attribute->title}}">
                    @if($errors->has('title'))
                        <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                    @endif
                </div>

                <div class="col-12 col-md-4 mb-3">
                    <label>نوع *</label>
                    <select class="form-control" name="type">
                        <option value="">لطفا انتخاب کنید</option>
                        <option value="select" @if($attribute->type == 'select') selected @endif>select</option>
                        <option value="text" @if($attribute->type == 'text') selected @endif>text</option>
                        <option value="switch" @if($attribute->type == 'switch') selected @endif>switch</option>
                        <option value="color" @if($attribute->type == 'color') selected @endif>color</option>
                    </select>
                </div>
                </div>
                <div class="row">
                <div class="col-12 col-md-4 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" id="customSwitchIsFilter" class="custom-control-input" name="isFilter"
                               value="1" @if($attribute->isFilter == 1) checked @endif>
                        <label for="customSwitchIsFilter" class="custom-control-label">قابلیت فیلتر</label>
                    </div>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" id="customSwitchIsPrice" class="custom-control-input" name="is_price"
                               value="1" @if($attribute->is_price == 1) checked @endif>
                        <label for="customSwitchIsPrice" class="custom-control-label">قابلیت قیمت گذاری</label>
                    </div>
                </div>

                <div class="col-12 col-md-4 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" id="customSwitchIsShow" class="custom-control-input" name="is_show"
                               value="1" @if($attribute->is_show == 1) checked @endif>
                        <label for="customSwitchIsShow" class="custom-control-label">قابلیت نمایش در صفحه تکی
                            محصول</label>
                    </div>
                </div>
                <br/><br/>

                <div class="col-md-12 col-sm-12 col-xs-12">
                    <a href="/dashboard/site/attribute/{{$attribute->attribute_group_id}}" class="btn btn-warning">بازگشت</a>
                    <button type="submit" class="btn btn-success">ویرایش</button>
                </div>
                </div>

            </form>

        </div>
    </div>

@stop
@section('js')

@stop