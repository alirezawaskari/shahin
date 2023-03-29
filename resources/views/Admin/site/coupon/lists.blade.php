@extends('Admin.master')

@section('css')
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>لیست تخفیف ها</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">لیست تخفیف ها</li>
                </ol>
            </nav>
        </div>
        <div role="group" class="btn-group">
            <button id="btnGroupDrop1" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    class="btn btn-primary dropdown-toggle">اقدامات
            </button>
            <div aria-labelledby="btnGroupDrop1" x-placement="bottom-start" class="dropdown-menu dropdown-menu-list">
                <a href="{{route('coupon.create')}}" class="dropdown-item"> ایجاد </a>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>عنوان</th><th>کد تخفیف</th><th>درصد تخفیف</th><th>ظرفیت</th><th>ظرفیت مصرف شده</th>
                    <th>تاریخ آغاز</th><th>تاریخ پایان</th><th>وضعیت</th>
                    <th>نوع خرید</th><th>نوع پرداخت</th>
                    <th>فعال / غیرفعال</th><th>اقدام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($coupons as $item)
                    <tr>
                        <td>{{$item->title}}</td>
                        <td>{{$item->code}} </td>
                        <td>{{$item->discount}} %</td>
                        <td>{{$item->capacity}} </td>
                        <td>{{$item->reserved}} </td>
                        <td>{{ verta($item->start_at)->format('%d %B ، %Y') }}</td>
                        <td>{{ verta($item->end_at)->format('%d %B ، %Y') }}</td>
                        <td>
                            @if($item->status == 0) <span class="label label-danger">غیر فعال</span>
                            @else <span class="label label-success">فعال</span>
                            @endif
                        </td>
                        <td>
                            @if($item->pay_type === 0) نقدی @elseif($item->pay_type === 1)
                                اقساط @elseif($item->pay_type == 2) نقدی | اقساط @endif
                        </td>

                        <td>
                            @if($item->purchase_type === 0) آنلاین @elseif($item->purchase_type === 1)
                                حضوری @elseif($item->purchase_type == 2) آنلاین | حضوری @endif
                        </td>
                        <td>
                            <a id="{{$item->id}}" data-title="@if($item->status == 0) 1 @else 0 @endif"
                               class="btn btn-sm btn-info colorWhite statusCoupon">
                                @if($item->status == 1) فعال  @else غیر فعال @endif
                            </a>
                        </td>
                        <td>
                            <div data-v-15965e3b="" class="dropdown">
                                <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                   aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                    <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu">
                                    <a href="{{route('coupon.edit',[$item->id])}}" >
                                        <button type="button" class="dropdown-item pointer">ویرایش</button>
                                    </a>
                                    <a href="{{route('couponItem.show',[$item->id])}}" >
                                        <button type="button" class="dropdown-item pointer">آیتم های تخفیف</button>
                                    </a>
                                    <form method="post" action="{{route('coupon.destroy',[$item->id])}}">
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
            {{$coupons->links()}}
        </div>
    </div>

@stop
@section('js')

    <script src="{{ asset('assets/dashboard/js/site/coupon/couponList.js') }}"></script>
@stop
