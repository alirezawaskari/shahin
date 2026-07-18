@extends('Admin.master')

@section('css')
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/site/sweetalert2.css')}}">
@stop
@section('content')

    <div class="page-header">
        <div>
            <h3>مدیریت تنوع و قیمت گذاری</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        @if($type_id == 1)  مدیریت تنوع و قیمت گذاری | <span>فعال و غیرفعال کردن تنوع های کالا، تغییر قیمت ، موجودی و اعمال تخفیف از این قسمت امکان پذیر است.</span> @endif
                    </li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="get" action="">
                <div class="row">

                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="product_id" placeholder="کد کالا"
                               value="@if(isset($queries['product_id'])) @if($queries['product_id']) {{$queries['product_id']}} @endif @endif">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="id" placeholder="کد تنوع"
                               value="@if(isset($queries['id'])) @if($queries['id']) {{$queries['id']}} @endif @endif">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="seller_code" placeholder="کد کالا فروشنده"
                               value="@if(isset($queries['seller_code'])) @if($queries['seller_code']) {{$queries['seller_code']}} @endif @endif">
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" name="product_name" placeholder=" عنوان فارسی کالا"
                               value="@if(isset($queries['product_name'])) @if($queries['product_name']) {{$queries['product_name']}} @endif @endif">
                    </div>

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

                    <div class="col-12 col-md-3 mb-2">
                        <select class="form-control" name="is_show" title="is_show">
                            <option value="all"
                                    @if(isset($queries['is_show'])) @if($queries['is_show'] == 'all') selected @endif @endif>
                                نمایش همه کالا
                            </option>
                            <option value="1"
                                    @if(isset($queries['is_show'])) @if($queries['is_show'] == '1') selected @endif @endif>
                                فعال
                            </option>
                            <option value="0"
                                    @if(isset($queries['is_show'])) @if($queries['is_show'] == '0') selected @endif @endif>
                                غیر فعال
                            </option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-2">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" id="no_stock" class="custom-control-input" name="no_stock"
                                   @if(isset($queries['no_stock'])) @if($queries['no_stock'] == 1) checked @endif @endif
                                   value="1">
                            <label for="no_stock" class="custom-control-label">کالاهای بدون موجودی</label>
                        </div>
                    </div>


                    <div class="col-12 col-md-3 mb-2">
                        <button type="submit" id="filter" class="btn btn-outline-primary py-2 px-4">فیلتر</button>
                        <a type="button" href="/dashboard/product/editInformation/1"
                           class="btn btn-outline-danger py-2 px-4">حذف
                            فیلتر ها</a>
                    </div>

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
                    <th>کد کالا و وضعیت</th>
                    <th>کد کالا فروشنده</th>
                    <th> عنوان و کد تنوع کالا</th>
                    <th>گروه کالا</th>
                    <th> قیمت مرجع <span class="font10 colorLGray disBlock">ریال</span></th>
                    <th> قیمت کالا <span class="font10 colorLGray disBlock">ریال</span></th>
                    <th> بازه ارسال <span class="font10 colorLGray disBlock">روز</span></th>
                    <th>موجودی فروشنده</th>
                    <th>اقدام</th>
                    <th>فعال / غیرفعال</th>
                </tr>
                </thead>
                <tbody>
                @foreach($product_attributes as $item)
                    <tr>
                        <td>
                            @if(File::exists(public_path() .$item->product->image))
                                <img src="{{$item->product->image}}" alt="{{$item->product->title}}" width="60px"
                                     class="border-radius5">
                            @endif
                        </td>
                        <td><span class="colorBlue">{{$item->product_id}}</span>
                            @if($item->product->status != null)
                                <p class="mrgZ">{{ \App\Models\Product::STATUS[$item->product->status] }}</p>
                            @endif
                        </td>
                        <td>{{$item->seller_code}}</td>
                        <td>
                            <span class="disBlock"> {{$item->product->name}}</span>
                            <span class="font11 colorLGray"> کد تنوع : <span
                                        class="colorBlue">{{$item->id}}</span></span>
                        </td>
                        <td>{{ App\Models\Category::findCategory($item->product->root_category_id,'title') }}</td>
                        <td>{{ number_format($item->product->reference_price) }}</td>
                        <td><input class="form-control price" id="{{$item->id}}"
                                   value="{{ number_format($item->price) }}" type="text" title="price">
                        </td>
                        <td>
                            <select id="{{$item->id}}" class="form-control submission_time minWidth"
                                    title="submission_time" name="submission_time">
                                <option value=""></option>
                                @for($i=1;$i<=15;$i++)
                                    <option value="{{$i}}"
                                            @if($i ==  $item->submission_time ) selected @endif>{{$i}}</option>
                                @endfor
                            </select>
                        </td>
                        <td><input id="{{$item->id}}" class="form-control stock" value="{{ $item->stock }}"
                                   type="number" title="stock"></td>
                        <td>
                            <a id="{{$item->product_id}}" data-title="{{$item->id}}"
                               class="btn btn-sm btn-default bColorLGray updateProductAttribute
                                @if($item->able_to_edit == 0) disabled  @endif ">تایید</a>
                            <a id="{{$item->product_id}}" data-title="{{$item->id}}"
                               class="btn btn-sm btn-danger returnProductAttribute
                                @if($item->able_to_edit == 0) disabled  @endif
                                       "><i class="fa fa-remove"></i></a>
                        </td>
                        <td>
                            <a id="{{$item->id}}" data-title="@if($item->is_show == 0) 1 @else 0 @endif"
                               class="btn btn-sm btn-default bColorLGray changeIsShow @if($item->able_to_edit == 0) disabled  @endif">
                                @if($item->is_show == 1) غیرفعال  @else فعال @endif
                            </a>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{$product_attributes->links()}}
        </div>

    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/sweetalert2.all.js') }}"></script>
    <script src="{{ asset('assets/dashboard/js/site/product/editInformation.js') }}"></script>

@stop
