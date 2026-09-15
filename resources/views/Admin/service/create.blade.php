@extends('Admin.master')
@section('title')ایجاد خدمت@endsection

@section('content')
    @php
        $in_wizard = $in_wizard ?? false;
        $preselected_category = $wizard_category ?? null;
    @endphp

    @if($in_wizard)
        @include('Admin.productWizard.partials.steps',['current'=>'create','kind'=>'service'])
        @if($preselected_category)
            <div class="mb-3">
                <a href="{{route('category.subCategory',[$preselected_category->parent_id ?: $preselected_category->id,'wizard'=>1])}}"
                   class="btn btn-outline-secondary btn-sm">بازگشت به دسته‌بندی‌ها</a>
            </div>
        @endif
    @endif

    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('service.store')}}"
                  enctype="multipart/form-data">
                @csrf
                @if($in_wizard)<input type="hidden" name="wizard" value="1">@endif
                @include('Admin.layouts.errors')
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-md-12 mb-4">
                            <h5>ایجاد خدمت جدید</h5>
                        </div>
                        <div class="col-12 col-md-6 mb-4">
                            <label>عنوان</label>
                            <input type="text" value="{{old('title')}}"
                                   class="form-control" placeholder="لطفا عنوان را وارد کنید"
                                   name="title" required>
                        </div>
                        <div class="col-12 col-md-6 mb-5">
                            <label>عنوان منو</label>

                            <input id="menu_title_lbl" type="text" class="form-control" name="menu_title"
                                   placeholder="لطفا عنوان منو را وارد کنید">

                        </div>
                        <div class="col-12 mb-4">
                            <label>عنوان لاتین (توجه داشته باشید که این عنوان در url نمایش داده میشود و حتما باید به صورت انگلیسی وارد شود.)</label>
                            <input type="text" value="{{old('en_title')}}"
                                   class="form-control" placeholder="لطفا عنوان انگلیسی را وارد کنید"
                                   name="en_title" required>
                        </div>
                        <div class="col-12 col-md-12 mb-4">
                            <label>توضیحات</label>
                            <input type="text" value="{{old('description')}}"
                                   class="form-control"
                                   name="description" required>
                        </div>

                        <div class="col-12 col-md-2 mb-3">
                            <label>وضعیت</label>

                            <select class="form-control" name="status">
                                <option selected="selected" value="0">
                                    فعال
                                </option>
                                <option value="1">
                                    غیر فعال
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mb-3">
                            <label>نمایش در سایت اصلی</label>

                            <select class="form-control" name="isShow">
                                <option selected="selected" value="0">
                                    غیر فعال
                                </option>
                                <option value="1">
                                    فعال
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mb-3">
                            <label>دارای آیتم</label>

                            <select class="form-control" name="items">
                                <option selected="selected" value="0">
                                    خیر
                                </option>
                                <option value="1">
                                    بله
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mb-3">
                            <label>دارای شعبه فعال</label>

                            <select class="form-control" name="center">
                                <option value="0">
                                    خیر
                                </option>
                                <option value="1">
                                    بله
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mb-3">
                            <label>قابلیت چند بار خرید</label>
                            <select class="form-control" name="multiple">
                                <option value="0">
                                    خیر
                                </option>
                                <option value="1">
                                    بله
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mb-3">
                            <label>قابلیت دعوت دوستان</label>
                            <select class="form-control" name="ability_invite_friend">
                                <option value="0">
                                    خیر
                                </option>
                                <option value="1">
                                    بله
                                </option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3 mb-4">
                            <label>هزینه رزرو (ریال)</label>
                            <input type="text" value="{{old('price')}}" class="form-control"
                                   placeholder="لطفا هزینه رزرو را وارد کنید" name="price" required oninput="separatePrices(this)">
                        </div>

                        <div class="col-12 col-md-3 mb-5">
                            <label>دسته بندی *</label>
                            {{-- دسته‌بندی مرحله قبلِ ویزارد از پیش انتخاب می‌شود. --}}
                            @php $selected_category_id = old('category_id', optional($preselected_category)->id); @endphp
                            <select class="form-control" name="category_id" title="">
                                <option value="">لطفا انتخاب کنید</option>
                                @foreach($service_categories as $service_category)
                                    <option value="{{$service_category->id}}"
                                            @selected($selected_category_id == $service_category->id)>{{$service_category->title}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('category_id'))<span
                                class="help-block sub-error">{{$errors->first('category_id')}}</span>@endif

                        </div>



                        <div class="col-12 col-md-3 mb-5">
                            <label>دسته بندی های مشابه </label>

                            <select name="similar_categories[]" title="" class="selectMulti form-control" multiple="multiple">
                                @foreach($all_categories as $all_category)
                                    <option value="{{$all_category->id}}">{{$all_category->title}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('similar_categories'))
                                <span class="help-block sub-error"> {{$errors->first('similar_categories')}} </span>
                            @endif

                        </div>
                        <div class="col-12 col-md-3 mb-5">
                            <label>خدمات مشابه </label>

                            <select name="similar_services[]" title="" class="selectMulti form-control" multiple="multiple">
                                @foreach($all_services as $all_service)
                                    <option value="{{$all_service->id}}">{{$all_service->title}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('similar_services'))
                                <span class="help-block sub-error"> {{$errors->first('similar_services')}} </span>
                            @endif

                        </div>


                        <div class="col-12">
                            <input type="submit" class="btn btn-success border-0" value="ثبت">
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
@stop

@section('js')
    <script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>
    <script>
        //select2
        $(document).ready(function () {
            $('.selectMulti').select2({
                dir: "rtl"
            });
        });

        //asked_question
        var k = 0;
        $("#add_asked_question").click(function () {
            ++k;
            $("#askedQuestion").append('<tr><td><input type="text" name="askedQuestion[' + k + '][question]" placeholder="لطفا سوال را وارد کنید" class="form-control" /></td><td><input type="text" name="askedQuestion[' + k + '][answer]"  class="form-control" placeholder="لطفا جواب را وارد کنید" /></td><td><button type="button" class="btn btn-danger remove-tr"><i class="fa fa-remove"></i></button></td></tr>');
        });
    </script>
@endsection
