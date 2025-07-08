@extends('Admin.master')

@section('css')

@stop
@section('content')

    <div class="page-header">
        <div>
            <h3> تخفیف های هوشمند </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <span> تخفیف های هوشمند | در این قسمت می توانید برای کالاهای خود تخفیف تعیین کنید . </span>
                    </li>
                </ol>
            </nav>
        </div>

    </div>

    <div class="card position-relative">
        <div class="card-body">
            @if(isset($product_attributes))
                <div class="row">
                    <div class="col-12 col-md-6 mb-2">
                        <h4>کالاهای دارای تخفیف</h4>
                    </div>
                    <div class="col-12 col-md-6 mb-2">
                        <a class="btn btn-success floatLeft" href="/dashboard/periodicPrices/create">افزودن کالای جدید
                            به لیست تخفیف ها</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-12 mb-4">
                        <form method="get" action="/dashboard/periodicPrices">
                            <div class="col-12 col-md-2 mb-3 padZ disInline">
                                <label>جستجو : </label>
                                <select class="form-control all_items" name="all_items" title="all_items">
                                    <option value="">لطفا انتخاب کنید</option>
                                    <option value="product_id">کد کالا</option>
                                    <option value="id">کد تنوع</option>
                                    <option value="seller_code">کد کالا فروشنده</option>
                                    <option value="product_name">عنوان فارسی کالا</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4 mb-3 disInline">
                                <label></label>
                                <input type="text" class="form-control search_term" placeholder="عبارت جستجو ..."
                                       name="search_term" value="{{old('search_term')}}">
                            </div>
                            <div class="col-12 col-md-1 mb-3 disInline">
                                <button type="button" class="searchIcon">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    @if(isset($product_attributes) && count($product_attributes))
                    <div class="col-12 col-md-12 mb-2">
                        <label> ویرایش قیمت و تعداد همه کالا ها </label>
                        <div class="col-12 col-md-2 disInline">
                            <label>% تخفیف از قیمت شما </label>
                            <input type="text" class="form-control globalDiscount " placeholder="%"
                                   name="globalDiscount">
                        </div>
                        <div class="col-12 col-md-2 disInline">
                            <label> تعداد در تخفیف </label>
                            <input type="text" class="form-control globalCapacity" placeholder="" name="globalCapacity">
                        </div>
                    </div>
                        <div class="col-12 col-md-12 mb-3">
                            <p> <span>نکته :</span>
                                <span> تاریخ های تخفیف بایستی از فردا تا آینده انتخاب شوند.  </span>
                            </p>
                            <p> <span>نکته :</span>
                                <span> با شروع تاریخ تخفیف امکان ویرایش و حذف تخفیف وجود ندارد.  </span>
                            </p>
                            <table class="table table-bordered table-striped smartDiscount">
                                <thead>
                                <tr>
                                    <th>عکس</th>
                                    <th> عنوان و کد تنوع کالا</th>
                                    <th> قیمت کالا <span class="font10 colorLGray">ریال</span></th>
                                    <th>% تخفیف</th>
                                    <th> قیمت پس از تخفیف <span class="font10 colorLGray">ریال</span></th>
                                    <th>بازه زمانی تخفیف</th>
                                    <th>موجودی فعلی کالا</th>
                                    <th>تعداد در تخفیف</th>
                                    <th>نوع تخفیف</th>
                                    <th>وضعیت</th>
                                    <th>اصلاح</th>
                                    <th>اقدام</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($product_attributes as $item)
                                    <tr class="trPeriodicPrices" id="{{$item->id}}">
                                        <td>
                                            @if(File::exists(public_path() .$item->product->image))
                                                <img src="{{$item->product->image}}" alt="{{$item->product->title}}"
                                                     width="60px"
                                                     class="border-radius5">
                                            @endif
                                        </td>
                                        <td>
                                            <span class="disBlock"> {{$item->product->name}}</span>
                                            @if($item->value != null) <span> | {{$item->value}} </span> @endif
                                            <span> | {{ $item->guarantee}} </span>
                                            <span class="laleP">{{$item->id}}</span>
                                            <br/>
                                        </td>
                                        <td>
                                            <input type="hidden" title="price" id="{{$item->id}}" class="price"
                                                   name="price[]" value="{{ number_format($item->price) }}">
                                            {{ number_format($item->price) }}
                                        </td>
                                        <td>
                                            <input type="text" id="{{$item->id}}" class="form-control discount"
                                                   placeholder="%"
                                                   name="periodic[{{$item->id}}][discount]"
                                                   minlength="{{$item->minimum_discount}}"
                                                   value="{{$item->discount}}">
                                            @if($item->minimum_discount != null)
                                                <span class="font10"> حداقل تخفیف {{ $item->minimum_discount }} ٪</span> @endif
                                            @if($errors->has("periodic.$item->id.discount"))
                                                <span class="help-block sub-error"> {{$errors->first("periodic.$item->id.discount")}} </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span id="{{$item->id}}"
                                                  class="priceByDiscount">{{number_format($item->price_by_discount)}}</span>
                                        </td>
                                        <td>
                                            <span class="disInline">از</span>
                                            <input type="text" name="periodic[{{$item->id}}][start_date]"
                                                   onkeydown="return false"
                                                   value="{{$item->start_at}}"
                                                   data-title="{{$item->product_id}}" title="start_date"
                                                   class="form-control date mb-2 pwt-datepicker-input-element disInline width85">
                                            <br/>
                                            <span class="disInline">تا</span>
                                            <input type="text" name="periodic[{{$item->id}}][end_date]"
                                                   onkeydown="return false"
                                                   value="{{$item->end_at}}"
                                                   data-title="{{$item->product_id}}" title="end_date"
                                                   class="form-control date mb-2 pwt-datepicker-input-element disInline width85">
                                            <br/>
                                            @if($errors->has("periodic.$item->id.start_date"))
                                                <span class="help-block sub-error"> {{$errors->first("periodic.$item->id.start_date")}} </span>
                                            @endif
                                            <br/>
                                            @if($errors->has("periodic.$item->id.end_date"))
                                                <span class="help-block sub-error"> {{$errors->first("periodic.$item->id.end_date")}} </span>
                                            @endif
                                            <div id="{{$item->product_id}}"
                                                 class="alert alert-warning alert_date_message"
                                                 style="display: none"></div>
                                        </td>
                                        <td>{{$item->stock}}</td>
                                        <td>
                                            <input id="{{$item->id}}" type="text" class="form-control capacity"
                                                   placeholder=""
                                                   name="periodic[{{$item->id}}][capacity]" value="{{$item->capacity}}">
                                            @if($errors->has("periodic.$item->id.capacity"))
                                                <span class="help-block sub-error"> {{$errors->first("periodic.$item->id.capacity")}} </span>
                                            @endif
                                        </td>
                                        <td>
                                            <select id="{{$item->id}}" name="periodic[{{$item->id}}][pay_type]"
                                                    title="pay_type" class="form-control pay_type mrgB4 font10 width80">
                                                @if($center->pay_type == 2)
                                                    <option value="0" @if($item->pay_type == 0) selected @endif>نقدی
                                                    </option>
                                                    <option value="1" @if($item->pay_type == 1) selected @endif>اقساطی
                                                    </option>
                                                    <option value="2" @if($item->pay_type == 2) selected @endif>هر دو
                                                    </option>
                                                @elseif($center->pay_type == 1)
                                                    <option value="1" @if($item->pay_type == 1) selected @endif>اقساطی
                                                    </option>
                                                @elseif($center->pay_type == 0)
                                                    <option value="0" @if($item->pay_type == 0) selected @endif>نقدی
                                                    </option>
                                                @endif
                                            </select>
                                            @if($errors->has("periodic.$item->id.pay_type"))
                                                <span class="help-block sub-error"> {{$errors->first("periodic.$item->id.pay_type")}} </span>
                                            @endif

                                            <select id="{{$item->id}}" name="periodic[{{$item->id}}][purchase_type]"
                                                    title="purchase_type"
                                                    class="form-control purchase_type width80 font10 ">
                                                @if($center->purchase_type == 2)
                                                    <option value="0" @if($item->purchase_type == 0) selected @endif>
                                                        آنلاین
                                                    </option>
                                                    <option value="1" @if($item->purchase_type == 1) selected @endif>
                                                        حضوری
                                                    </option>
                                                    <option value="2" @if($item->purchase_type == 2) selected @endif>هر
                                                        دو
                                                    </option>
                                                @elseif($center->pay_type == 1)
                                                    <option value="1" @if($item->purchase_type == 1) selected @endif>
                                                        حضوری
                                                    </option>
                                                @elseif($center->pay_type == 0)
                                                    <option value="0" @if($item->purchase_type == 0) selected @endif>
                                                        آنلاین
                                                    </option>
                                                @endif
                                            </select>
                                            @if($errors->has("periodic.$item->id.purchase_type"))
                                                <span class="help-block sub-error"> {{$errors->first("periodic.$item->id.purchase_type")}} </span>
                                            @endif
                                        </td>
                                        <td>{{ $item->status_title }}</td>
                                        <td>
                                            <a id="{{$item->id}}"
                                               class="btn btn-sm btn-default bColorLGray periodicUpdate
                                                @if($item->able_to_edit == 0) disabled  @endif"><i
                                                        class="fa fa-check fa-check-gray" aria-hidden="true"></i></a>
                                        </td>
                                        <td>
                                            <a id="{{$item->id}}" data-title="{{$item->coupon_id}}"
                                               class="btn btn-sm btn-default deleteProductAttribute
                                                    @if($item->able_to_edit == 0) disabled  @endif">
                                                <i class="fa fa-trash trashIcon"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            {{$product_attributes->links()}}
                        </div>
                    @endif
                </div>

            @else
                <p>شما هیچ کالایی را در لیست تخفیف های هوشمند قرار نداده اید.</p>
                <p>با قرار دادن کالاهای خود در این لیست و اعمال تخفیف بر روی آنها بازدید و فروش کالاهای خود را در وب
                    سایت لاله افزایش دهید.</p>
                <a class="btn btn-success" href="/dashboard/periodicPrices/create">افزودن کالای جدید به لیست تخفیف
                    ها</a>
            @endif


        </div>

    </div>

@stop
@section('js')
    <script src="{{ asset('assets/dashboard/js/site/product/price/smartDiscounts/smartDiscountsIndex.js') }}"></script>

@stop
