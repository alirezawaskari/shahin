@extends('Admin.master')

@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <h4>ویرایش گروه های ویژگی</h4>
            <form class="form-horizontal" method="post"
                  action="{{route('attributeGroup.update',$attribute_group->id)}}"
                  enctype="multipart/form-data">
                {{csrf_field()}}   {{ method_field('PATCH') }}

                <div class="col-12 col-md-4 mb-3">
                    <label>عنوان *</label>
                    <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید" name="title"
                           value="{{$attribute_group->title}}">
                    @if($errors->has('title'))
                        <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                    @endif
                </div>
                <div class="col-12 col-md-4 mb-3">
                    <label> دسته بندی انتخاب شده:</label>
                    {{ App\Models\Category::findCategory($attribute_group->category_id,'title') }}
                    <a class="btn btn-sm btn-warning changeCategory">تغییر دسته بندی</a>
                </div>

                <div class="row changeCategoryBox" style="display: none;">
                    <div class="col-12 col-md-12 mb-4">
                        @include('Admin.site.step.step_one')
                    </div>
                </div>

                <br/> <br/>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <a href="{{route('attributeGroup.index')}}" class="btn btn-info">بازگشت</a>
                    <button type="submit" class="btn btn-success">ویرایش</button>
                </div>

            </form>
        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>
@stop