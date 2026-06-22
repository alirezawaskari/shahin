@extends('Admin.master')

@section('css')

@stop

@section('content')


    <div class="card">
        <div class="card-body">
            <div class="x_title">
                <h4>ثبت آیتم های تخفیف</h4>
            </div>
            <br/>
            <ul id="myTab" role="tablist" class="nav nav-tabs">
                <li class="nav-item"><a id="category-tab" data-toggle="tab" href="#category" role="tab"
                                        aria-controls="category" aria-selected="true"
                                        class="nav-link show active">دسته بندی</a>
                </li>
                <li class="nav-item"><a id="service-tab" data-toggle="tab" href="#service" role="tab"
                                        aria-controls="service" aria-selected="false"
                                        class="nav-link show">خدمات</a>
                </li>
                <li class="nav-item"><a id="product-tab" data-toggle="tab" href="#product" role="tab"
                                        aria-controls="product" aria-selected="false"
                                        class="nav-link show">کالا</a>
                </li>
                <li class="nav-item"><a id="center-tab" data-toggle="tab" href="#center" role="tab"
                                        aria-controls="center" aria-selected="false"
                                        class="nav-link show">شعبه</a>
                </li>
            </ul>
            <div id="myTabContent" class="tab-content">

                <div id="category" role="tabpanel" aria-labelledby="category-tab" class="tab-pane fade p-t-50 active show">
                    <form class="form-horizontal" method="post"
                          action="{{route('couponItem.store')}}" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input name="type" value="category" type="hidden">
                        <input name="coupon_id" class="coupon_id" value="{{$coupon_id}}" type="hidden">
                        @include('Admin.site.step.step_one')
                        <br/>
                        <br/>
                        <button type="submit" class="btn btn-success">ثبت و افزودن آیتم بعدی</button>
                    </form>
                </div>

                <div id="service" role="tabpanel" aria-labelledby="service-tab" class="tab-pane fade p-t-50">
                    <form class="form-horizontal" method="post"
                          action="{{route('couponItem.store')}}" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input name="type" value="service" type="hidden">
                        <input name="coupon_id" class="coupon_id" value="{{$coupon_id}}" type="hidden">
                        <div class="row">
                            <div class="col-12 col-md-3 mb-3">
                                <label for="name_lbl">دسته بندی خدمات </label>
                                <select name="services_category_id" class="form-control servicesCategory" title="">
                                    <option value=""></option>
                                    @foreach($services as $service)
                                        <option value="{{$service->id}}">{{$service->title}}</option>
                                    @endforeach
                                </select>
                                @if($errors->has('services_category_id'))
                                    <span class="help-block sub-error"> {{$errors->first('services_category_id')}} </span>
                                @endif
                                <input name="services_category_type" value="App\Models\Category" type="hidden">
                            </div>

                            <div class="col-md-3 col-sm-12 col-xs-12 mrgB40 servicesBox">
                                <input name="service_type" value="App\Models\Service" type="hidden">
                            </div>
                            <div class="col-md-3 col-sm-12 col-xs-12 mrgB40 serviceItemsBox">
                                <input name="service_item_type" value="App\Models\ServiceItem" type="hidden">
                            </div>

                            <div class="col-12 col-md-12 mb-3">
                                <button type="submit" class="btn btn-success">ثبت و افزودن آیتم بعدی</button>
                            </div>
                        </div>


                    </form>
                </div>

                <div id="product" role="tabpanel" aria-labelledby="product-tab" class="tab-pane fade p-t-50">
                    <form class="form-horizontal" method="post"
                          action="{{route('couponItem.store')}}" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input name="coupon_id" class="coupon_id" value="{{$coupon_id}}" type="hidden">
                        <input name="type" value="product" type="hidden">
                        <div class="row">
                            <div class="col-12 col-md-8 mb-3"><label>عنوان کالا </label>
                                <input title="product_id" placeholder="کالا " class="form-control productName">
                            </div>
                            <div class="col-12 col-md-4 mb-3"><label>شعبه</label>
                                <select class="form-control center_id" name="center_id" title="center_id">
                                    <option value=""></option>
                                    @foreach($centers as $center)
                                        <option value="{{$center->id}}">{{$center->title}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                        <div class="row productsBox mb-3" style="display: none">

                        </div>
                        <div class="row productAttribute mb-3" style="display: none">

                        </div>


                    </form>
                </div>

                <div id="center" role="tabpanel" aria-labelledby="center-tab" class="tab-pane fade p-t-50">
                    <form class="form-horizontal" method="post"
                          action="{{route('couponItem.store')}}" enctype="multipart/form-data">
                        {{csrf_field()}}
                        <input name="coupon_id" class="coupon_id" value="{{$coupon_id}}" type="hidden">
                        <input name="center_type" value="App\Models\Center" type="hidden">
                        <input name="type" value="center" type="hidden">
                        <div class="row">
                            <div class="col-12 col-md-4 mb-3"><label>شعبه</label>
                                <select class="form-control" name="center_id" title="center_id">
                                    <option value=""></option>
                                    @foreach($centers as $center)
                                        <option value="{{$center->id}}">{{$center->title}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-12 mb-3">
                                <button type="submit" class="btn btn-success">ثبت و افزودن آیتم بعدی</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>


@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/coupon/couponItemCraete.js') }}"></script>


@stop
