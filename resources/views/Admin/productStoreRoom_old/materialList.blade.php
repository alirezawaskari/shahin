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
                    <li class="breadcrumb-item active" aria-current="page">مواد اولیه</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                @if(isset($create_link)) <a href="{{$create_link}}" class="dropdown-item">ایجاد </a> @endif
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
                    <th>شناسه</th>
                    <th>عنوان</th>
                    <th>موجودی</th>
                    <th>دسته بندی انبار </th>
                    <th>محل نگهداری</th>
                    <th>واحد اصلی</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($products_store_room as $product)
                    <tr>
                        <td>{{\App\Models\StoreRoom::findProductStoreRoom($store_room_id,$product->id,'id')}}</td>
                        <td>{{$product->name}} </td>
                        <td>{{$product->stock}} </td>
                        <td>{{$product->category_store_room_title}} </td>
                        <td>{{$product->storage_store_room_title}} </td>
                        <td>{{$product->main_unit_title}} </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="/siteAdmin/editStoreRoomMaterial/{{\App\Models\StoreRoom::findProductStoreRoom($store_room_id,$product->id,'id')}}/{{$product->id}}">
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>

                                    @if(isset($store_room_remove) && isset($store_room_id))
                                        <form class="product_form" method="post"
                                              action="{{route('productStoreRoom.destroy',[$product->id])}}">
                                            @method('DELETE')@csrf
                                            <input type="hidden" name="product_store_room_id" value="{{\App\Models\StoreRoom::findProductStoreRoom($store_room_id,$product->id,'id')}}">
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
            {{$products_store_room->links()}}
        </div>

    </div>

@stop
@section('js')

@stop
