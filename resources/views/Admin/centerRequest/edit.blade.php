@extends('Admin.master')
@section('title')مشاهده درخواست مرکز@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="col-12 mb-5">
                <h5> <i class="fa fa-history" aria-hidden="true"></i> تاریخچه پیگیری درخواست</h5>
                <table class="vuetable table table-bordered table-striped table-responsive-md mt-5">
                    <thead>
                        <tr>
                            <th>آپدیت کننده</th>
                            <th>پیگیری کننده</th>
                            <th>تاریخ</th>
                            <th>توضیحات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $history)

                            <tr>
                                <td>{{ $history?->user->name .' '.$history?->user->family }}</td>
                                <td>
                                    {{ $history?->callcenter?->name .' '.$history?->callcenter?->family }}
                                </td>
                                <td>
                                    {{ verta( $history->created_at )->format( '%d %B ، %Y - H:i' ) }}
                                </td>
                                <td>{{ $history->description }}</td>
                            </tr>
                        @empty
                            <tr>

                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<form class="form-horizontal" role="form" method="post" action="{{route('centerRequest.update' , [ $center_request->id])}}"
    enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    @include('Admin.layouts.errors')
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                <div class="col-12 mb-3">
                    <h5>1 - انتخاب نوع دسته بندی همکاری</h5>
                    <br>
                    <br>
                    فروش کالا <input type="radio" name="cat_type" value="category" onclick="catType(this.value)" {{
                        $center_request->center_category->type == 'category' ? 'checked' : ''}}>
                    ارائه خدمات <input type="radio" name="cat_type" value="service" onclick="catType(this.value)" {{
                        $center_request->center_category->type == 'service' ? 'checked' : ''}}>
                </div>
                <div class="col-12 mb-5">
                    <select class="form-control mb-3" name="service_category" id="service_category">
                        <option selected disabled>انتخاب دسته بندی خدمت مربوطه</option>
                        @foreach ($service_categories as $service_category)
                        <option value="{{ $service_category->id }}" {{ $center_request->center_category_id == $service_category->id ? 'selected' :
                            ''
                            }}>{{ $service_category->title }}
                        </option>
                        @endforeach
                    </select>

                    <select class="form-control mb-3" name="product_category" id="product_category">
                        <option selected disabled>انتخاب دسته بندی کالا مربوطه</option>
                        @foreach ($product_categories as $product_category)
                        <option value="{{ $product_category->id }}" {{ $center_request->center_category_id == $product_category->id ? 'selected'
                            : ''
                            }}>{{ $product_category->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-row">
                <div class="col-12 mb-5">
                    <h5>2 - انتخاب جایگاه شغلی</h5>
                    <br>
                    <br>
                    فروشنده <input type="radio" value="seller" name="type" {{ $center_request->type == 'seller' ? 'checked' : ''
                    }}>
                    تولید کننده <input type="radio" value="producer" name="type" {{ $center_request->type == 'producer' ?
                    'checked' : ''
                    }}>
                    تامین کننده <input type="radio" value="supplier" name="type" {{ $center_request->type == 'supplier' ?
                    'checked' : ''
                    }}>
                    خدمات دهنده <input type="radio" value="service_provider" name="type" {{ $center_request->type ==
                    'service_provider'
                    ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <h5> 3 - سایر اطلاعات</h5>
                <br>
                <br>
                <div class="row">
                    <div class="col-12 col-md-3 mb-3">
                        <label>موضوع</label>
                        <input type="text" value="{{ $center_request->subject }}" class="form-control" name="subject" required>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>نام و نام خانوادگی</label>
                        <input type="text" name="name" placeholder="نام و نام خانوادگی" class="form-control"
                            value="{{ $center_request->name}}">
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>شماره موبایل</label>
                        <input type="number" name="phone" placeholder="شماره موبایل" class="form-control"
                            value="{{ $center_request->phone }}">
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>نحوه آشنایی</label>
                        <input type="text" value="{{ $center_request->connections }}" class="form-control" name="connections">
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>استان</label>
                        <select class="form-control" type="int" name="province_id" id="province_id" required>
                            <option value="">انتخاب استان</option>
                            @foreach(\App\Models\Province::all() as $province)
                            <option value="{{ $province->id }}" {{ $center_request->province_id == $province->id ? 'selected' :
                                '' }}>{{
                                $province->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>شهر</label>
                        <select class="form-control" type="int" name="city_id" id="city_id" required>
                            <option value="">انتخاب شهر</option>
                            @foreach(\App\Models\City::all() as $city)
                            <option value="{{ $city->id }}" {{ $center_request->city_id == $city->id ? 'selected' : '' }}>{{
                                $city->name
                                }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 mb-3">
                        <label>وضعیت</label>
                        <select class="form-control" name="status">
                            <option value="0" {{ $center_request->status == 0 ? 'selected' : '' }}>
                                جدید
                            </option>
                            @if(!auth()->user()->admin_representation_id)
                            <option value="1" {{ $center_request->status == 1 ? 'selected' : '' }}>
                                فعال
                            </option>
                            @endif
                            <option value="2" {{ $center_request->status == 2 ? 'selected' : '' }}>
                                معلق
                            </option>
                            <option value="3" {{ $center_request->status == 3 ? 'selected' : '' }}>
                                رد شده
                            </option>
                        </select>
                    </div>
                    @if(!auth()->user()->admin_representation_id)
                    <div class="col-12 col-md-3 mb-3">
                        <label>نمایندگی</label>
                        <select name="representation_id" class="custom-select select2">
                        <option value=" " {{ $center_request->representation_id == null ? 'selected' : '' }}>نمایندگی کل</option>
                            @foreach($representations as $representation)
                                <option value="{{$representation->id}}" {{ $center_request->representation_id == $representation->id ? 'selected' : '' }}>{{$representation->fa_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-12 col-md-6 mb-3">
                        <label>توضیحات مرکز</label>
                        <textarea type="text" class="form-control"
                            name="description">{{ $center_request->description }}</textarea>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <label>توضیحات بررسی همکار</label>
                        <textarea type="text" class="form-control" name="answer"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <h5> 3 - تصاویر</h5>
                <br>
                <br>
                <div class="row">
                    <div class="col-12 row">
                        @foreach ($galleries as $gallery)
                        <div class="card col-md-3">
                            <a href="{{ asset($gallery->image) }}" target="_blank">
                                <img src="{{ $gallery->image }}" class="card-img-top" height="150px">
                            </a>
                        </div>
                        @endforeach
                    </div>
                    <div class="col-12">
                        <p class="text-warning font-weight-bolder"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> توجه : همکار گرامی توجه داشته باشید درصورتی که وضعیت را به فعال تغییر دهید این درخواست به عنوان یک مرکز فعال سایت درنظر گرفته خواهد شد. </p>
                    </div>
                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/centerRequest.js') }}"></script>

@endsection
