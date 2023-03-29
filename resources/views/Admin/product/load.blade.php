<div class="load" style="position: relative;">

    <input type="hidden" name="type_id" value="{{$type_id}}" class="form-control">
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <td>ردیف</td>
            @if($type_id == 1)
                <th>عکس</th>@endif
            <th>کد محصول</th>
            <th>عنوان</th>
            @if($type_id == 1)
                <th>گروه اصلی</th>
                <th> تعداد تنوع</th>
            @endif
            <th>برند</th>
            @if($type_id == 1)
                <th>وضعیت</th>
                <th>نمایش در سایت</th>
            @endif
            <th>اقدام</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $ind=>$item)
            <tr>
                <td>{{++$ind}}</td>
                @if($type_id == 1)
                    <td>@if(File::exists(public_path() .$item->image)) <img src="{{$item->image}}"
                                                                            alt="{{$item->title}}" width="60px"
                                                                            class="border-radius5"> @endif
                    </td> @endif
                <td>{{$item->id}}</td>
                <td>{{$item->name}} @if($item->en_name != null) | {{$item->en_name}} @endif</td>
                @if($type_id == 1)
                    <td>{{ App\Models\Category::findCategory($item->root_category_id,'title') }}</td>

                    <td><span class="disTable">{{\App\Models\Product::countPriceProduct($item->id)}}</span>
                        @if(isset($center_id) && $center_id != null && $item->status == 5)
                            <a class="btn btn-sm btn-info addVariety"
                               href="/dashboard/product/price/{{$item->id}}">افزودن تنوع</a>
                        @endif
                    </td>
                @endif
                <td>
                    @if($item->brand_id != null && \App\Models\Brand::find($item->brand_id))
                        {{\App\Models\Brand::find($item->brand_id)->title}}
                        - {{\App\Models\Brand::find($item->brand_id)->title_en}}
                    @endif
                </td>
                @if($type_id == 1)
                    <td>
                        @if($item->status != 0 && $item->status != 1 && $item->status != 5 && $item->status != 3)
                            <a class="bellMessage curpon" data-title="message" id="{{$item->id}}"> <i class="fa fa-bell"
                                                                                                      aria-hidden="true"></i>
                            </a>
                        @endif
                        @if($item->status != null)
                            {{ $item::STATUS[$item->status] }}
                        @endif
                    </td>
                    <td>{!! \App\Helpers\Helper::iconIsItOrNot($item->isShow) !!} </td>
                @endif

                <td>
                    @if(isset($center_id) && $center_id != null)
                        <a href="/dashboard/products/{{$item->id}}/edit"
                           class="btn btn-sm btn-warning btnCenter">ویرایش</a>

                        @if($item->status != null && $item->status == 1)
                            <a href="/dashboard/products/{{$item->id}}/3/status"
                               class="btn btn-sm btn-success btnCenter">انتشار</a>
                        @endif

                    @else
                        @include('Admin.modal.productSellers',['product_id'=>$item->id,'product_type'=>$item->type,'sellers'=>$item->sellers ])
                        <div data-v-15965e3b="" class="dropdown">
                            <a href="#" data-toggle="dropdown" aria-haspopup="true"
                               aria-expanded="false" class="btn btn-dark btn-floating btn-icon btn-sm">
                                <i aria-hidden="true" class="fa fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu">
                                <a href="/dashboard/products/{{$item->id}}/edit">
                                    <button type="button" class="dropdown-item pointer">ویرایش</button>
                                </a>

                                @if($type_id == 1)
                                    <a href="/dashboard/products/visit/{{$item->id}}">
                                        <button type="button" class="dropdown-item pointer">مشاهده</button>
                                    </a>
                                    @if(\App\Models\Attribute::attributeIsPriceByLastCat($item) > 0)
                                        <a href="/dashboard/product/pricesList/{{$item->id}}">
                                            <button type="button" class="dropdown-item pointer">تنوع کالا
                                            </button>
                                        </a>
                                    @else
                                        <a href="/dashboard/product/pricesListWithoutAttribute/{{$item->id}}">
                                            <button type="button" class="dropdown-item pointer">تنوع کالا
                                            </button>
                                        </a>
                                    @endif

                                @endif

                                @if(isset($item->sellers))
                                    <a>
                                        <button type="button" class="dropdown-item pointer" data-toggle="modal"
                                                data-target="#productSellers{{$item->id}}">فروشنده ها
                                        </button>
                                    </a>
                                @endif

                                <form method="post" action="{{route('products.destroy',[$item->id])}}">
                                    @method('DELETE')
                                    @csrf
                                    <button type="submit" class="dropdown-item pointer showConfirmDelete">حذف
                                    </button>
                                </form>

                            </div>
                        </div>
                    @endif

                </td>

            </tr>
        @endforeach
        </tbody>
    </table>

</div>
{{ $products->links() }}
