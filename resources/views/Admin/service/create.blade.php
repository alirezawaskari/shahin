@extends('Admin.master')
@section('title')ایجاد خدمت@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form class="form-horizontal" role="form" method="post" action="{{route('service.store')}}"
                  enctype="multipart/form-data">
                @csrf
                @include('Admin.layouts.errors')
                <div class="container">
                    <div class="row">
                        <div class="col-12 col-md-12 mb-4">
                            <h5>ایجاد خدمت جدید</h5>
                        </div>
                        <div class="col-12 col-md-4 mb-4">
                            <label>عنوان</label>
                            <input type="text" value="{{old('title')}}"
                                   class="form-control" placeholder="لطفا عنوان را وارد کنید"
                                   name="title" required>
                        </div>

                        <div class="col-12 col-md-4 mb-4">
                            <label>عنوان لاتین</label>
                            <input type="text" value="{{old('en_title')}}"
                                   class="form-control" placeholder="لطفا عنوان انگلیسی را وارد کنید"
                                   name="en_title" required>
                        </div>
                        <div class="col-12 col-md-4 mb-4">
                            <label>عنوان لینک</label>
                            <input type="text" value="{{old('slug')}}"
                                   class="form-control" placeholder="لطفا عنوان لیتک را وارد کنید"
                                   name="slug" required>
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
                                <option value="2">
                                    غیر فعال
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mb-3">
                            <label>نمایش در سایت مرکز تهران</label>

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
                            <label>دارای مرکز</label>

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

                        <div class="col-12 col-md-2 mb-4">
                            <label>درصد پیش پرداخت</label>
                            <input type="number" value="{{old('percent')}}" class="form-control" name="percent"
                                   placeholder="لطفا درصد پیش پرداخت را وارد کنید" required>
                        </div>

                        <div class="col-12 col-md-2 mb-4">
                            <label>درصد سود سالیانه</label>
                            <input type="number" value="{{old('wage_percent')}}" class="form-control"
                                   placeholder="لطفا درصد سود سالیانه را وارد کنید"
                                   name="wage_percent" required>
                        </div>

                        <div class="col-12 col-md-2 mb-4">
                            <label>قیمت (ریال)</label>
                            <input type="text" value="{{old('price')}}" class="form-control"
                                   placeholder="لطفا قیمت را وارد کنید" name="price" required oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 col-md-12 text-center">  <br/><hr/><br/>
                            <h3 class="mb-4 alert alert-dark">اطلاعات برای سایت .com</h3>
                        </div>
                        <div class="col-12 col-md-3 mb-5">
                            <label>دسته بندی *</label>
                            <select class="form-control" name="category_id" title="">
                                <option value="">لطفا انتخاب کنید</option>
                                @foreach($service_categories as $service_category)
                                    <option value="{{$service_category->id}}">{{$service_category->title}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('category_id'))<span
                                class="help-block sub-error">{{$errors->first('category_id')}}</span>@endif

                        </div>

                        <div class="col-12 col-md-3 mb-5">
                            <label>کمترین اعتبار *</label>
                            <input type="text" name="min_credit" id="currency-field" value="{{old('min_credit')}}"
                                   class="form-control" data-type="currency" placeholder="لطفا کمترین اعتبار را وارد کنید">
                            @if($errors->has('min_credit'))
                                <span class="help-block sub-error"> {{$errors->first('min_credit')}} </span>
                            @endif
                        </div>
                        <div class="col-12 col-md-3 mb-5">
                            <label>بیشترین اعتبار *</label>
                            <input type="text" name="max_credit" id="currency-field" value="{{old('max_credit')}}"
                                   class="form-control" data-type="currency" placeholder="لطفا بیشترین اعتبار را وارد کنید">
                            @if($errors->has('max_credit'))
                                <span class="help-block sub-error"> {{$errors->first('max_credit')}} </span>
                            @endif

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
                        <div class="col-12 col-md-3 mb-5">
                            <label>نوع خرید *</label>
                            <select name="purchase_type" class="form-control">
                                <option value="0" @if(old('purchase_type') == 0) selected @endif>آنلاین</option>
                                <option value="1" @if(old('purchase_type') == 1) selected @endif>حضوری</option>
                                <option value="2" @if(old('purchase_type') == 2) selected @endif>هر دو</option>
                            </select>
                            @if($errors->has('purchase_type'))
                                <span class="help-block sub-error"> {{$errors->first('purchase_type')}} </span>
                            @endif
                        </div>
                        <div class="col-12 col-md-3 mb-5">
                            <label>نوع پرداخت *</label>
                            <select name="pay_type" class="form-control">
                                <option value="0" @if(old('pay_type') == 0) selected @endif>نقدی</option>
                                <option value="1" @if(old('pay_type') == 1) selected @endif>اقساطی</option>
                                <option value="2" @if(old('pay_type') == 2) selected @endif>هر دو</option>
                            </select>
                            @if($errors->has('pay_type'))
                                <span class="help-block sub-error"> {{$errors->first('pay_type')}} </span>
                            @endif
                        </div>

                        <div class="col-12 col-md-6 mb-5">
                            <label>عنوان منو</label>

                            <input id="menu_title_lbl" type="text" class="form-control" name="menu_title"
                                   placeholder="لطفا عنوان منو را وارد کنید">

                            {{--                        <div class="checkbox">--}}
                            {{--                            <label>--}}
                            {{--                                <input type="checkbox" name="is_menu" value="1">نمایش در منو--}}
                            {{--                            </label>--}}
                            {{--                            @if($errors->has('is_menu'))<span--}}
                            {{--                                    class="help-block sub-error">{{$errors->first('is_menu')}}</span>@endif--}}
                            {{--                        </div>--}}

                        </div>


                        {{--                    <div class="col-md-12 col-sm-12 col-xs-12 mb-5">--}}
                        {{--                        <label>نمونه کارها </label>--}}
                        {{--                        @include('Admin.partials.multipleImages',['has_link'=>0])--}}
                        {{--                    </div>--}}


                        {{--                    <div class="col-md-12 col-sm-12 col-xs-12 mb-5">--}}
                        {{--                        @include('Admin.partials.service.askedQuestion')--}}
                        {{--                    </div>--}}

                        {{--                    @include('Admin.partials.createBannerHomePage')--}}

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
    {{--<script src="{{ asset('/assets/dashboard/js/layouts.js?v=2.0') }}"></script>--}}

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
