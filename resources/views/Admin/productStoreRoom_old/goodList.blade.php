@extends('layouts.master')

@section('css')


@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست محصولات</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">کالا</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                @if(isset($create_link)) <a href="{{$create_link}}" class="dropdown-item">ایجاد </a> @endif
                @if(isset($create_link_material)) <a href="{{$create_link_material}}" class="dropdown-item">ایجاد کالا مصرف داخلی</a> @endif
                <input type="hidden" name="internal_use_material" value="{{$internal_use_material}}">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="get" action="{{$page_link}}">
                <div class="col-12 col-md-3 mb-5 disInline">
                    <input class="form-control" name="name" placeholder="عنوان"
                           value="@if(isset($queries['name'])) @if($queries['name']) {{$queries['name']}} @endif @endif">
                </div>
                @if($type_id == 1)
                    <div class="col-12 col-md-3 mb-5 disInline">
                        <select title="category_id" name="root_category_id" class="custom-select">
                            <option value="all">دسته بندی</option>
                            @foreach($categories as $category)
                                <option value="{{$category->id}}"
                                        @if(isset($queries['root_category_id']))
                                        @if($queries['root_category_id'] == $category->id) selected @endif
                                        @endif>{{$category->title}}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-md-2 mb-5 disInline">
                    <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>عکس</th>
                    <th>شناسه</th>
                    <th>عنوان</th>
                    <th>دسته بندی</th>
                    <th>نمایش در سایت</th>
                    <th>مصرف داخلی </th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($products as $item)
                    <tr>
                        <td>
                            @if(File::exists(public_path() .$item->image))
                                <img src="{{$item->image}}" alt="{{$item->title}}" width="60px"
                                     class="border-radius5">
                            @endif
                        </td>
                        <td>{{$item->id}}</td>
                        <td>{{$item->name}} </td>

                        <td>{{ App\Models\Category::findCategory($item->root_category_id,'title') }}</td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->isShow) !!} </td>
                        <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->internal_use) !!} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    @if($item->internal_use == 1)
                                        <a href="/siteAdmin/editStoreRoomMaterial/{{\App\Models\StoreRoom::findProductStoreRoom($store_room_id,$item->id,'id')}}/{{$item->id}}">
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                    @else
                                        <a href="/siteAdmin/editStoreRoomGood/{{\App\Models\StoreRoom::findProductStoreRoom($store_room_id,$item->id,'id')}}/{{$item->id}}">
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                    @endif


                                    @if(\App\Models\Attribute::attributeIsPriceByLastCat($item) > 0)
                                        <a href="/siteAdmin/product/pricesList/{{$item->id}}">
                                            <button type="button" class="dropdown-item pointer">تنوع کالا
                                            </button>
                                        </a>
                                    @else
                                        <a href="/siteAdmin/product/pricesListWithoutAttribute/{{$item->id}}">
                                            <button type="button" class="dropdown-item pointer">تنوع کالا
                                            </button>
                                        </a>
                                    @endif

                                    @if(isset($store_room_remove) && isset($store_room_id))
                                        <form class="product_form" method="post"
                                              action="{{route('productStoreRoom.destroy',[$item->id])}}">
                                            @method('DELETE')@csrf
                                            <input type="hidden" name="product_store_room_id" value="{{\App\Models\StoreRoom::findProductStoreRoom($store_room_id,$item->id,'id')}}">
                                            <button type="submit" class="dropdown-item pointer"
                                                    onclick="return confirm('آیا مطمئن هستید؟ ')">حذف محصول از انبار شما
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </div>
                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$products->links()}}
        </div>

    </div>

@stop
@section('js')

@stop
