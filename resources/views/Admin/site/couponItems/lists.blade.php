@extends('Admin.master')
@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست آیتم های تخفیف</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست آیتم های تخفیف</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('couponItem.createByCouponId',[$coupon_id])}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th> محصول / دسته بندی / خدمات / مرکز</th><th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($coupon_items as $item)
                    <tr>
                        <td>
                        @if($item->category_title)
                           {{$item->category_title}}
                        @elseif($item->product_name) {{$item->product_name}} @if($item->value != null) ({{$item->value}}) @endif
                        @endif
                        @if($item->center_name) -  {{ $item->center_name }} @endif
                        </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <form method="post" action="{{route('couponItem.destroy',[$item->id])}}">
                                        {{ method_field('DELETE') }}
                                        {{csrf_field()}}
                                        <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {{$coupon_items->links()}}
        </div>
    </div>

@stop
@section('js')
@stop
