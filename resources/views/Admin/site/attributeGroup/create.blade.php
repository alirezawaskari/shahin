@extends('Admin.master')

@section('css')

@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <h4>ثبت گروه های ویژگی</h4>
            <form class="form-horizontal" method="post"
                  action="{{route('attributeGroup.store')}}" enctype="multipart/form-data">
                {{csrf_field()}}
                @if(request('wizard'))<input type="hidden" name="wizard" value="1">@endif

                <div class="row">
                    <div class="col-12 col-md-4 mb-5">
                        <label>عنوان *</label>
                        <input type="text" class="form-control" placeholder="لطفا عنوان را وارد کنید"
                               name="title" value="{{old('title')}}">
                        @if($errors->has('title'))
                            <span class="help-block sub-error"> {{$errors->first('title')}} </span>
                        @endif
                    </div>
                </div>
                @if(isset($parents))
                <div class="row">
                    <div class="col-12 col-md-12 mb-4">
                        @include('Admin.site.step.step_one')
                    </div>
                </div>
                @endif

                @if(isset($category))
                    <h4>دسته بندی انتخاب شده :
                    <span>{{$category->title}}</span>
                    </h4>
                    <input type="hidden" name="choose_category_id" value="{{$category->id}}" title="category_id">
                @endif

                <br/> <br/>
                <div class="col-md-12 col-sm-12 col-xs-12">
                    @if(request('wizard') && isset($category))
                        <a href="{{route('productWizard.attributes',$category->id)}}" class="btn btn-warning">بازگشت</a>
                    @else
                        <a href="{{route('attributeGroup.index')}}" class="btn btn-warning">بازگشت</a>
                    @endif
                    <button type="submit" class="btn btn-success">ارسال</button>
                </div>
            </form>
        </div>
    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>
@stop