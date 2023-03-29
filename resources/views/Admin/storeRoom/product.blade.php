@extends('Admin.master')

@section('content')
<div class="row page-header">
    <div class="col-12 col-md-6">
        <h3>لیست کالا </h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">لیست کالا  {{ $storeRoom->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="heading-elements">
        <div class="heading-btn-group ltr mb-3">
            @permission('supplier')
            <a href="{{ route('supplier.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فهرست تامین کنندگان</span>
            </a>
            @endpermission
            @permission('store-room')
            <a href="{{ route('countingUnit.index') }}"
               class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-eye text-indigo-400" aria-hidden="true"></i>
                <span>فهرست واحدها</span>
            </a>
            @endpermission
            @permission('product' | 'product-detail')
            <a {{--href="{{ route('storeRoom.create.product' , [ $storeRoom->id ]) }}"--}} class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-edit text-indigo-400" aria-hidden="true"></i>
                <span>ایجاد محصول جدید</span>
            </a>
            @endpermission
            @permission('store-room')
            <a href="{{ route('storeRoom.index') }}" class="btn btn-link btn-float has-text text-size-small rtl">
                <i class="fa fa-credit-card text-indigo-400" aria-hidden="true"></i>
                <span>فهرست انبار</span>
            </a>
            @endpermission
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <form method="get" role="form" action="{{ route('storeRoom.product.filter', [ $storeRoom->id ]) }}">
            <div class="row">
                <div class="col-12 col-md-3 mb-2">
                    <input type="text" class="form-control" name="name" placeholder="نام محصول" />
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="storage">
                        <option value="all">محل نگهداری</option>
                            <option value="0">
                                قفسه
                            </option>
                            <option value="1">
                                 یخچال
                            </option>
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="main_unit">
                        <option value="all">واحد بسته بندی</option>
                        @foreach($main_units as $main_unit)
                            <option value="{{$main_unit->id}}">{{ $main_unit->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <select class="form-control" name="category_store_room">
                        <option value="all">نوع دسته بندی</option>
                        @foreach($category_store_rooms as $category_store_room)
                            <option value="{{ $category_store_room->id }}">{{ $category_store_room->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="انقضا از تاریخ" locale="fa,en" format="YYYY-M-D" name="start_date"></date-picker>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <date-picker placeholder="انقضا تا تاریخ" locale="fa,en" format="YYYY-M-D" name="end_date"></date-picker>
                </div>

				<div class="col-12 col-md-2 mb-2 mt-3">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="expiredProducts" name="expired_products">
                            <label class="custom-control-label" for="expiredProducts">محصولات منقضی شده</label>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-2 mb-2 mt-3">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="expiringProducts" name="expiring_products">
                            <label class="custom-control-label" for="expiringProducts">محصولات درحال انقضا</label>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-2 mb-2 mt-3">
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="finishingProducts" name="finishing_products">
                            <label class="custom-control-label" for="finishingProducts">محصولات روبه اتمام</label>
                        </div>
                    </div>
                </div>

				<div class="col-12 col-md-3 mb-2">
                    <div class="custom-control custom-switch mt-2">
                        <input type="checkbox" class="custom-control-input" name="exel" id="exel">
                        <label class="custom-control-label" for="exel">خروجی اکسل</label>
                    </div>
                </div>

                <div class="col-12 col-md-3 mb-2">
                    <button id="filter" type="submit" class="btn btn-primary">
                        فیلتر کن
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
    <div class="card">
        <div class="card-body">
            <div class="card position-relative">
                <div class="card-body">
                    <table class="vuetable table table-bordered table-striped table-responsive-md" id="table-id">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>نوع</th>
                        <th>قفسه-سطر-ستون</th>
						<th>واحد اصلی</th>
                        <th>تاریخ انقضا</th>
                        <th>اعلام انقضا (روز)</th>
                        <th>نقطه سفارش</th>
                        <th>زمان سفارش (روز)</th>
						<th>نوع محصول</th>
                        <th>موجودی</th>
						<th>انبار</th>
						<th>تاریخ ورود</th>
                        <th>اقدام</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($store_room_products as $store_room_product)
                        <tr
                                {{--{{ $store_room_product->expire_date && $store_room_product->expire_date < Carbon\Carbon::now() ? 'class=table-danger' : '' }}--}}
                        >
                            <td>{{ isset($store_room_product->product) ? $store_room_product->product->name : '' }}</td>
                            <td>{{ $store_room_product->category_store_room->title }}</td>
							<td>{{ $store_room_product->storage_num.' - '.$store_room_product->row_num.' - '.$store_room_product->col_num }}</td>
                            <td>{{ $store_room_product->main_unit->title }}</td>
                            <td>
{{--                                {{ verta($store_room_product->expire_date)->format('Y/m/d') }}--}}
                            </td>
                            <td>{{ $store_room_product->expire_alert ? $store_room_product->expire_alert : 'ندارد' }}</td>
                            <td>{{ $store_room_product->order_point }}</td>
                            <td>{{ $store_room_product->order_day }}</td>
                            <td>{{ isset($store_room_product->product) ? $store_room_product->product::TYPE[$store_room_product->product->type] : '' }}</td>
                            <td>{{ $store_room_product->stock }}</td>
							<td>{{ $store_room_product->store_room->name }}</td>
							<td>{{ verta($store_room_product->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <div data-v-15965e3b="" class="dropdown">
                                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                       class="btn btn-dark btn-floating btn-icon btn-sm">
                                        <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                                    </a>
                                    <div class="dropdown-menu">
                                 <a href="{{ route('product.edit' , [ isset($store_room_product->product) ?$store_room_product->product->slug : '' ]) }}">
                                            <button type="button" class="dropdown-item pointer">ویرایش</button>
                                        </a>
										<a
                                            {{--href="{{ route('storeRoom-product.destroy',[ $store_room_product->id]) }}"--}}
                                        >
                                        <button type="button" class="dropdown-item pointer">حذف</button>
                                    </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
				@include('Admin.layouts.pagination')
				تعداد موجودی کل : {{ $store_room_products->sum('stock') }}
              </div>
			</div>
        </div>
    </div>
@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/pagination.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/factorItem.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/product.js?v=1.1') }}"></script>
    <script>
      $(document).ready(function(){
      $('[data-toggle="tooltip"]').tooltip();
     });
    </script>
@endsection
