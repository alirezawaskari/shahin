@extends('Admin.master')

@section('css') @stop
@section('content')
    <div class="page-header">
        <div>
            <h3>لیست تنوع های کالا</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست ویژگی های کالا
                        <span>{{\App\Helpers\Helper::product($product_id,'name')}}</span>
                    </li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('price.show',[$product_id])}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">

            <table class="table table-bordered table-striped table-responsive-md">
                <thead>
                <tr>
                    <th>ویژگی</th>
                    <th>مالک</th>
                    <th>قیمت</th>
                    <th> موجودی اولیه</th>
                    <th>موجودی</th>
                    <th>گارانتی</th>
                    <th>ارسال با</th>
                    <th>فعال / غیر فعال</th>
                    <th>بازه زمانی ارسال(روز)</th>
                    <th>کد کالا</th>
                    <th>اقدام</th>
                </tr>
                </thead>
                <tbody>

                @foreach($product_attributes as $item)
                    <tr>
                        <td>
                            {{\App\Models\Attribute::find($item->pivot->attribute_id)->title}}
                            {{\App\Models\AttributeItem::find($item->pivot->attribute_item_id)->title}}
                        </td>
                        <td>
                            @if($item->pivot->productattributeable_type == 'App\Models\Center') {{\App\Models\Center::find( $item->pivot->productattributeable_id)->title }}
                            @elseif($item->pivot->productattributeable_type == 'App\Models\ProductStoreRoom')  {{ $item->store_room_name  }}
                            @endif
                        </td>
                        <td>{{number_format($item->pivot->price)}}</td>
                        <td>{{$item->pivot->initial_stock}}</td>
                        <td>{{$item->pivot->stock}}</td>
                        <td>
                            @if($item->pivot->guarantee == 1)
                                گارانتی اصالت و سلامت فیزیکی کالا
                            @endif
                        </td>
                        <td>
                            @if($item->pivot->posted_by == 1) شبکه لاله
                            @elseif($item->pivot->posted_by == 2) فروشنده
                            @endif
                        </td>
                        <td>
                            {{\App\Helpers\Helper::activeOrNot($item->pivot->is_show)}}
                        </td>
                        <td>{{$item->pivot->submission_time}}</td>
                        <td>{{$item->pivot->seller_code}}</td>

                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('price.edit',[$item->pivot->id])}}" >
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                </div>
                            </div>

                        </td>

                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>

@stop
@section('js') @stop
