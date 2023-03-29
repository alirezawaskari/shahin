@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('content')
    <div class="page-header">
        <div>
           <h3> مدیریت محصولات </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        مدیریت محصولات | <span>برای ویرایش و مدیریت محصولات ، گروه ، تصویر محصولات و درج تنوع (گارانتی ، به همراه رنگ یا سایز یا گارانتی) از این قسمت استفاده نمایید.</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{$create_link}}" class="dropdown-item">ایجاد </a>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="get" action="{{$page_link}}">
                <div class="row">

                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="id" placeholder="کد محصول"
                               value="@if(isset($queries['id'])) @if($queries['id']) {{$queries['id']}} @endif @endif">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="name" placeholder=" عنوان فارسی محصول"
                               value="@if(isset($queries['name'])) @if($queries['name']) {{$queries['name']}} @endif @endif">
                    </div>

                    @if($type_id == 1)
                        <div class="col-12 col-md-3 mb-2">
                            <input class="form-control" name="en_name" placeholder=" عنوان انگلیسی محصول"
                                   value="@if(isset($queries['en_name'])) @if($queries['en_name']) {{$queries['en_name']}} @endif @endif">
                        </div>

                        <div class="col-12 col-md-3 mb-2">
                            <select class="form-control" name="status" title="status">
                                <option value="">وضعیت</option>
                                @foreach(\App\Models\Product::STATUS as $ind=>$status)
                                    <option value="{{$ind}}"
                                            @if(isset($queries['status']))@if($queries['status'] == $ind) selected @endif @endif>{{$status}}</option>
                                @endforeach
                            </select>
                        </div>

                        @if(isset($brands))
                            <div class="col-12 col-md-3 mb-2">
                                <select title="brand_id" name="brand_id" class="custom-select selectSearch">
                                    <option value="all">برند</option>
                                    @foreach($brands as $ind=>$brand)
                                        <option value="{{$ind}}" @if(isset($queries['brand_id']))
                                        @if($queries['brand_id'] == $ind) selected @endif
                                                @endif>{{$brand}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if(isset($categories))
                            <div class="col-12 col-md-3 mb-2">
                                <select title="category_id" name="root_category_id" class="custom-select selectSearch">
                                    <option value="all">دسته بندی</option>
                                    @foreach($categories as $category)
                                        <option value="{{$category->id}}" @if(isset($queries['root_category_id']))
                                        @if($queries['root_category_id'] == $category->id) selected @endif
                                                @endif>{{$category->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                    @endif

                    <div class="col-12 col-md-3 mb-2">
                        <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                        <a type="button" href="{{$page_link}}" class="btn btn-outline-danger py-2 px-4">حذف فیلتر ها</a>
                    </div>

                </div>
            </form>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            @if(isset($center_id) && $center_id != null)<br/>
            <p>توجه : محصول با قابلیت پیش نویس فقط برای شما قابل نمایش است و توسط مرکز فروشندگان قابل رويت نیست . محصول
                پیش نویس خود را انتشار داده و منتظر تایید آن باشید.</p>
            <br/> @endif

            @if (count($products) > 0)
                <section class="dataPagination">
                    @include('Admin.center.products.load')
                </section>
            @endif
        </div>

    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/center/products/productCenter.js') }}"></script>

@stop
