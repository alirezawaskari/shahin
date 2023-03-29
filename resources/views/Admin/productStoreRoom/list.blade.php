@extends('Admin.master')

@section('css') @stop
@section('content')

    <div class="page-header">
        <div>
            <h3> انبار</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست انبار
                        <span>{{$product->name}}</span>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">

                <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                        <th>دسته بندی انبار</th><th>انبار</th><th>نوع خرید</th><th>نوع پرداخت</th>
                        @if($product->type == 0)<th>موجودی </th> @endif<th>قیمت فروش </th><th>قیمت خرید </th>
                        <th> واحد اصلی</th><th>  تعداد واحد اصلی</th>
                        <th> واحد فرعی</th><th>  تعداد واحد فرعی</th>
                        <th>محل نگهداری</th><th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($product_store_rooms as $item)
                        <tr>
                            <td>{{ $item->category_store_room_title  }} </td>
                            <td>{{$item->store_room_name}} </td>
                            <td>{{$item->purchase_type}}</td>
                            <td>{{$item->pay_type}}</td>
                            @if($product->type == 0)<td>{{$item->pivot->stock }} </td>@endif
                            <td>@if($item->pivot->purchase_price){{number_format($item->pivot->purchase_price )}} @endif</td>
                            <td>@if($item->pivot->sales_price){{number_format($item->pivot->sales_price) }} @endif</td>
                            <td>{{$item->main_unit_title}} </td>
                            <td>{{$item->pivot->main_unit_value }} </td>
                            <td>{{$item->sub_unit_title}} </td>
                            <td>{{$item->pivot->sub_unit_value }} </td>
                            <td>{{$item->storage_store_room_title }} </td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true"  aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                        <a href="{{route('getProductStoreRoom',['product_id'=>$product->id,'id'=>$item->pivot->id])}}" >
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
                                    </div>
                                </div>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{$product_store_rooms->links()}}

        </div>
    </div>



@stop
@section('js') @stop