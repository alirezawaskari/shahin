@extends('Admin.master')
@section('title')ویرایش {{ $center->label_word }}@endsection

@php
    $activeTab = session('active_tab', 'base_info');
    $weekDays  = \App\Models\Center::WEEK_DAYS;

    $tabs = [
        'base_info'    => 'اطلاعات پایه',
        'profile_info' => 'پروفایل ' . $center->label_word,
        'hours_info'   => 'ساعات کاری',
        'plans_info'   => 'خدمات و طرح های مالی',
        'staff_info'   => 'همکاران ' . $center->label_word,
        'faq_info'     => 'سوالات متداول',
        'rating_info'  => 'نظرات کاربران',
    ];
@endphp

@section('content')
<div class="page-header">
    <div>
        <h3>{{ $center->display_title }}</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('center.index') }}">مستاجرین و شرکای تجاری</a></li>
                <li class="breadcrumb-item active" aria-current="page">ویرایش</li>
            </ol>
        </nav>
    </div>
</div>

<form class="form-horizontal" role="form" method="post" action="{{route('center.update' , [$center->id])}}"
    enctype="multipart/form-data">
    @csrf
    @method('PATCH')

    @include('Admin.layouts.errors')

    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
    {{-- filled by the tab links so the save returns to the pane the admin was on --}}
    <input type="hidden" name="active_tab" id="active_tab" value="{{ $activeTab }}">

    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Sidebar Menu -->
                <div class="col-12 col-sm-2">
                    <ul class="nav nav-pills d-block nav-justified custom-steps mb-4">
                        @foreach($tabs as $key => $label)
                            <li class="nav-item mb-2">
                                <a class="nav-link center-tab {{ $activeTab == $key ? 'active' : '' }}"
                                   data-toggle="pill" data-bs-toggle="pill" data-tab="{{ $key }}" href="#{{ $key }}">
                                    <span class="step-circle">{{ $loop->iteration }}</span>
                                    <span class="step-title">{{ $label }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Content -->
                <div class="col-12 col-sm-10">
                    <div class="tab-content border justify-content-center p-3 rounded-2">

                        {{-- ================= اطلاعات پایه (unchanged contract-side form) ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'base_info' ? 'show active' : '' }}"
                             id="base_info" role="tabpanel">
                            <div class="form-row">
                                <div class="col-12 mb-3">
                                    <h5>1 - انتخاب نوع دسته بندی همکاری</h5>
                                    <br>
                                    فروش کالا <input id="category-type" type="radio" name="cat_type" value="category"
                                        onclick="catType(this.value)" {{ $center->owner_type == 'App\Category' ? 'checked' : ''}}>
                                    ارائه خدمات <input id="service-type" type="radio" name="cat_type" value="service"
                                        onclick="catType(this.value)" {{ $center->owner_type == 'App\Models\Service' ? 'checked' :
                                    ''}}>
                                </div>
                                <div class="col-12 mb-5">
                                    <select class="form-control mb-3" name="item[service_category]" id="service_category">
                                        <option selected disabled>انتخاب دسته بندی خدمت مربوطه</option>
                                        @foreach ($service_categories as $service_category)
                                        <option value="{{ $service_category->id }}" {{ $center_request->center_category_id ==
                                            $service_category->id ? 'selected' : '' }}>{{ $service_category->title }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <select class="form-control mb-3 services-select" multiple="multiple" name="services[]"
                                        id="service">
                                        @foreach ($services as $service)
                                        <option value="{{ $service->id }}" {{$center->services->where('id',$service->id
                                            )->count()==1?'selected' : '' }}>{{ $service->title }}
                                        </option>
                                        @endforeach
                                    </select>

                                    <select class="form-control mb-3" name="item[product_category]" id="product_category">
                                        <option selected disabled>انتخاب دسته بندی کالا مربوطه</option>
                                        @foreach ($product_categories as $product_category)
                                        <option value="{{ $product_category->id }}" {{ $center_request->center_category_id ==
                                            $product_category->id ? 'selected' : '' }}>{{ $product_category->title }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <select class="form-control mb-3" name="category" id="category">
                                        <option selected disabled>انتخاب دسته بندی مربوطه</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ $category->id == $center->owner_id ? 'selected' :
                                            '' }}>{{ $category->title }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 mb-4">
                                    <h5>2 - انتخاب جایگاه شغلی</h5>
                                    <br>
                                    فروشنده <input type="radio" value="seller" name="item[type]" {{ $center_request->type == 'seller' ? 'checked' : '' }}>
                                    تولید کننده <input type="radio" value="producer" name="item[type]" {{ $center_request->type == 'producer' ? 'checked' : '' }}>
                                    تامین کننده <input type="radio" value="supplier" name="item[type]" {{ $center_request->type == 'supplier' ? 'checked' : '' }}>
                                    خدمات دهنده <input type="radio" value="service_provider" name="item[type]" {{ $center_request->type == 'service_provider' ? 'checked' : '' }}>
                                </div>
                            </div>

                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    3 - سایر اطلاعات
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>موضوع</label>
                                    <input type="text" value="{{ $center_request->subject }}" class="form-control"
                                        name="item[subject]">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>نام و نام خانوادگی</label>
                                    <input type="text" name="item[name]" placeholder="نام و نام خانوادگی" class="form-control"
                                        value="{{ $center_request->name}}">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>شماره تماس {{ $center->label_word }}</label>
                                    <input type="text" name="phone" placeholder="شماره تماس" class="form-control"
                                        value="{{ $center->phone }}">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>نحوه آشنایی</label>
                                    <input type="text" value="{{ $center_request->connections }}" class="form-control"
                                        name="item[connections]">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>استان</label>
                                    <select class="form-control" type="int" name="item[province_id]" id="province_id">
                                        <option value="">انتخاب استان</option>
                                        @foreach(\App\Models\Province::all() as $province)
                                        <option value="{{ $province->id }}" {{ $center_request->province_id == $province->id ?
                                            'selected' : '' }}>{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>شهر</label>
                                    <select class="form-control" type="int" name="item[city_id]" id="city_id">
                                        <option value="">انتخاب شهر</option>
                                        @foreach(\App\Models\City::all() as $city)
                                        <option value="{{ $city->id }}" {{ $center_request->city_id == $city->id ? 'selected' : ''
                                            }}>{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>عنوان</label>
                                    <input type="text" value="{{ $center->title }}" class="form-control" name="title">
                                </div>

                                @if(!auth()->user()->admin_representation_id)
                                <div class="col-12 col-md-4 mb-3">
                                    <label>وضعیت</label>
                                    <select class="form-control" name="status">
                                        <option value="1" {{ $center->status == 1 ? 'selected' : '' }}>فعال</option>
                                        <option value="0" {{ $center->status == 0 ? 'selected' : '' }}>غیرفعال</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 mb-3">
                                    <label>نمایندگی ایجاد کننده</label>
                                    <select name="creator_representation_id" class="form-control">
                                        <option value="">م تهران</option>
                                        @foreach($representations as $representation)
                                        <option value="{{$representation->id}}" {{ $representation->id == $center->creator_representation_id ? 'selected' : ''
                                            }}>{{$representation->fa_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 mb-3">
                                    <label>نمایندگی های ذینفع</label>
                                    <select name="representation_id[]" class="custom-select select2" multiple>
                                        @foreach($representations as $representation)
                                        <option value="{{$representation->id}}" {{$center->representations->where('id',$representation->id
                                            )->count()==1?'selected' : '' }}>{{$representation->fa_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 mb-4">
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" name="is_show" value="1" @if($center->is_show == 1) checked
                                            @endif> نمایش در سایت نمایندگی کل
                                        </label>
                                        @if($errors->has('is_show'))<span
                                            class="help-block sub-error">{{$errors->first('is_show')}}</span>@endif
                                    </div>
                                </div>
                                @endif

                                <div class="col-12 mb-3">
                                    <label>آدرس</label>
                                    <input type="text" value="{{ $center->address }}" class="form-control" name="address">
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>شماره قرارداد</label>
                                    <input type="text" value="{{ $center->contract_number }}" class="form-control"
                                        name="contract_number">
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>تاریخ قرارداد</label>
                                    <input value="{{ $center->contract_start_date }}" type="text" name="contract_start_date"
                                        class="form-control date">
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>توضیحات {{ $center->label_word }} (درخواست همکاری)</label>
                                    <textarea class="form-control" name="item[description]">{{ $center_request->description }}</textarea>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>توضیحات بررسی همکار (داخلی)</label>
                                    <textarea class="form-control" name="description">{{ $center->description }}</textarea>
                                </div>

                                <div class='col-12 mb-3'>
                                    <label class="d-block">فایل قرارداد :</label>

                                    {{-- Currently saved contract file (Spatie single-file collection). It's a
                                         document (pdf/doc), so shown as a "مشاهده قرارداد" link + in-place
                                         remove (✕), not the image-oriented currentMedia partial. --}}
                                    @php
                                        $__contract = $center->getFirstMedia('contract_file');
                                        // not yet adopted -> keep the legacy file reachable (link, not img: it's a pdf/doc)
                                        $__legacyContract = (! $__contract && method_exists($center, 'legacyMediaUrl'))
                                            ? $center->legacyMediaUrl('contract_file') : '';
                                    @endphp
                                    <div class="mb-2">
                                        @if($__contract)
                                            <span class="div_remove_btn d-inline-block" id="media_{{ $__contract->id }}">
                                                <a href="{{ $__contract->getFullUrl() }}" target="_blank" rel="noopener"
                                                   class="btn btn-primary py-1 px-2">مشاهده قرارداد</a>
                                                {{-- contract_file is optional here, so a ✕ is legitimate — but only
                                                     once nothing legacy is left to resurrect it. See
                                                     AdoptsLegacyMedia::mediaIsRemovable(). --}}
                                                @if($center->mediaIsRemovable('contract_file'))
                                                <a data-id="{{ $__contract->id }}" title="حذف فایل"
                                                   class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>
                                                @endif
                                            </span>
                                        @elseif($__legacyContract)
                                            <a href="{{ $__legacyContract }}" target="_blank" rel="noopener"
                                               class="btn btn-primary py-1 px-2">مشاهده قرارداد</a>
                                            <small class="d-block text-warning mt-1">فایل قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                        @else
                                            <span class="text-muted">فایلی بارگذاری نشده است</span>
                                        @endif
                                    </div>

                                    <label for="contract_file" class="btn btn-primary custom-file-upload border border-1">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری فایل قرارداد
                                    </label>
                                    <input name="contract_file" type="file" id="contract_file"
                                           class="d-none file-upload file_upload_ajax">
                                    <div id="contract_file_src" class="show"></div>
                                    <input type="hidden" id="contract_file_path" class="path" name="contract_file_path" value="">
                                </div>
                            </div>
                        </div>

                        {{-- ================= پروفایل شعبه ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'profile_info' ? 'show active' : '' }}"
                             id="profile_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    اطلاعاتی که در صفحه عمومی {{ $center->label_word }} نمایش داده می‌شود
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>عنوان منو</label>
                                    {{-- The customer picks the word: a chain says شعبه, a single-site business says مستاجرین و شرکای تجاری. --}}
                                    <select class="form-control" name="label_type">
                                        @foreach(\App\Models\Center::LABELS as $key => $word)
                                            <option value="{{ $key }}" @selected(old('label_type', $center->label_type ?: 'branch') === $key)>{{ $word }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">عنوان صفحه: {{ $center->display_title }}</small>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>ایمیل</label>
                                    <input type="text" class="form-control" name="email"
                                           value="{{ old('email', $center->email) }}" placeholder="info@example.com">
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>عنوان بخش معرفی</label>
                                    <input type="text" class="form-control" name="about_title"
                                           value="{{ old('about_title', $center->about_title) }}"
                                           placeholder="بیایید درباره {{ $center->label_word }} صحبت کنیم">
                                </div>

                                <div class="col-12 mb-3">
                                    <label>متن معرفی (در صفحه عمومی نمایش داده می‌شود)</label>
                                    <textarea class="form-control" name="intro_text" rows="6">{{ old('intro_text', $center->intro_text) }}</textarea>
                                </div>

                                <div class="col-12 col-md-3 mb-3">
                                    <label>عرض جغرافیایی (lat)</label>
                                    <input type="text" class="form-control" name="lat" value="{{ old('lat', $center->lat) }}">
                                </div>
                                <div class="col-12 col-md-3 mb-3">
                                    <label>طول جغرافیایی (lon)</label>
                                    <input type="text" class="form-control" name="lon" value="{{ old('lon', $center->lon) }}">
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>لینک نقشه (iframe یا آدرس گوگل مپ)</label>
                                    <input type="text" class="form-control" name="map_iframe_link"
                                           value="{{ old('map_iframe_link', $center->map_iframe_link) }}">
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>تصویر اصلی</label>
                                    <div id="image_src" class="show mb-2">
                                        @if($center->image)
                                            <img src="{{ $center->image }}" class="border-radius5"
                                                 style="height:120px;object-fit:cover;">
                                            @if($center->mediaIsLegacy('image'))
                                                <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        @else
                                            <div class="d-flex align-items-center justify-content-center text-muted"
                                                 style="height:120px;background:#f8f9fa;">بدون تصویر</div>
                                        @endif
                                    </div>
                                    <label for="image" class="btn btn-primary custom-file-upload border border-1">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="image" class="d-none file-upload file_upload_ajax" type="file"
                                           name="image" accept=".jpg,.jpeg,.png,.webp">
                                    <input type="hidden" id="image_path" class="path" name="image_path">
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>تصویر کاور</label>
                                    <div id="background_image_src" class="show mb-2">
                                        @if($center->background_image)
                                            <img src="{{ $center->background_image }}" class="border-radius5"
                                                 style="height:120px;object-fit:cover;">
                                            @if($center->mediaIsLegacy('background_image'))
                                                <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        @else
                                            <div class="d-flex align-items-center justify-content-center text-muted"
                                                 style="height:120px;background:#f8f9fa;">بدون تصویر</div>
                                        @endif
                                    </div>
                                    <label for="background_image" class="btn btn-primary custom-file-upload border border-1">
                                        <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                                    </label>
                                    <input id="background_image" class="d-none file-upload file_upload_ajax" type="file"
                                           name="background_image" accept=".jpg,.jpeg,.png,.webp">
                                    <input type="hidden" id="background_image_path" class="path" name="background_image_path">
                                </div>
                            </div>
                        </div>

                        {{-- ================= ساعات کاری ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'hours_info' ? 'show active' : '' }}"
                             id="hours_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    ساعات کاری هفتگی
                                </div>

                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th style="width:20%">روز</th>
                                            <th>ساعت شروع</th>
                                            <th>ساعت پایان</th>
                                            <th style="width:15%">تعطیل</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($weekDays as $key => $label)
                                            @php $hours = $center->workingHoursFor($key); @endphp
                                            <tr>
                                                <td class="align-middle">{{ $label }}</td>
                                                <td>
                                                    <input type="time" class="form-control day-open"
                                                           name="working_hours[{{ $key }}][open]"
                                                           value="{{ $hours['open'] }}" @disabled($hours['closed'])>
                                                </td>
                                                <td>
                                                    <input type="time" class="form-control day-close"
                                                           name="working_hours[{{ $key }}][close]"
                                                           value="{{ $hours['close'] }}" @disabled($hours['closed'])>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <input type="checkbox" class="day-closed" value="1"
                                                           name="working_hours[{{ $key }}][closed]"
                                                           @checked($hours['closed'])>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <small class="text-muted">
                                        روزی که «تعطیل» علامت بخورد، ساعت‌های آن ذخیره نمی‌شود و در صفحه عمومی به صورت تعطیل نمایش داده می‌شود.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- ================= خدمات و طرح های مالی ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'plans_info' ? 'show active' : '' }}"
                             id="plans_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    خدمات و طرح های مالی فعال برای این {{ $center->label_word }}
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان بخش خدمات</label>
                                    <input type="text" class="form-control" name="services_title"
                                           value="{{ old('services_title', $center->services_title) }}" placeholder="خدمات ما">
                                    <small class="text-muted">خدمات این {{ $center->label_word }} در تب «اطلاعات پایه» انتخاب می‌شود.</small>
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان بخش طرح های مالی</label>
                                    <input type="text" class="form-control" name="plans_title"
                                           value="{{ old('plans_title', $center->plans_title) }}"
                                           placeholder="اعتبار اقساطی و وام های مربوطه">
                                </div>

                                <div class="col-12 mb-3">
                                    <label class="d-block">طرح های مالی</label>
                                    @forelse($financial_plans as $plan)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="financial_plan_ids[]"
                                                   value="{{ $plan->id }}" id="plan_{{ $plan->id }}"
                                                   @checked(in_array($plan->id, old('financial_plan_ids', $selectedFinancialPlans)))>
                                            <label class="form-check-label" for="plan_{{ $plan->id }}">
                                                {{ $plan->title }}
                                                <small class="text-muted">
                                                    ({{ \App\Models\FinancialPlansType::TYPE[$plan->payment_type] ?? $plan->payment_type }}
                                                    @if($plan->number_pay_steps) - {{ $plan->number_pay_steps }} قسط @endif)
                                                </small>
                                            </label>
                                        </div>
                                    @empty
                                        <div class="alert alert-warning">طرح مالی فعالی ثبت نشده است.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- ================= همکاران شعبه ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'staff_info' ? 'show active' : '' }}"
                             id="staff_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    همکاران این {{ $center->label_word }}
                                </div>

                                <div class="col-12 col-md-6 mb-3">
                                    <label>عنوان بخش</label>
                                    <input type="text" class="form-control" name="staff_title"
                                           value="{{ old('staff_title', $center->staff_title) }}" placeholder="پزشکان شعبه">
                                </div>

                                <div class="col-12">
                                    {{-- Read-only: an employee belongs to exactly one branch through
                                         employees.center_id, which is set on the employee's own page. --}}
                                    <div class="alert alert-info">
                                        هر همکار فقط به یک {{ $center->label_word }} تعلق دارد و این ارتباط در صفحه ویرایش همان همکار تنظیم می‌شود.
                                    </div>

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>نام و نام خانوادگی</th>
                                            <th>عنوان شغلی</th>
                                            <th>وضعیت</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($branchEmployees as $employee)
                                            <tr>
                                                <td>{{ $employee->full_name }}</td>
                                                <td>{{ $employee->work_title }}</td>
                                                <td>{{ \App\Models\Employee::status($employee->status ?? 0) }}</td>
                                                <td>
                                                    <a href="{{ route('employee.edit', $employee->id) }}"
                                                       class="btn btn-sm btn-dark">ویرایش همکار</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-muted">همکاری برای این {{ $center->label_word }} ثبت نشده است.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- ================= سوالات متداول ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'faq_info' ? 'show active' : '' }}"
                             id="faq_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    سوالات متداول این {{ $center->label_word }}
                                </div>

                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th style="width:35%">سوال</th>
                                            <th>پاسخ</th>
                                            <th style="width:60px"></th>
                                        </tr>
                                        </thead>
                                        <tbody id="faq-rows">
                                        @foreach($askedQuestions as $asked)
                                            <tr>
                                                <td>
                                                    <input type="hidden" name="asked_question_ids[]" value="{{ $asked->id }}">
                                                    <input type="text" class="form-control" name="asked_question_questions[]"
                                                           value="{{ $asked->question }}" placeholder="سوال">
                                                </td>
                                                <td>
                                                    <textarea class="form-control" name="asked_question_answers[]" rows="2"
                                                              placeholder="پاسخ">{{ $asked->answer }}</textarea>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm faq-remove">
                                                        <i class="fa fa-remove"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    <button type="button" class="btn btn-primary btn-sm" id="faq-add">
                                        <i class="fa fa-plus"></i> افزودن سوال
                                    </button>
                                    <small class="d-block text-muted mt-2">
                                        سوالی که متن آن خالی بماند ذخیره نمی‌شود و سوال حذف شده از جدول، هنگام ثبت پاک می‌شود.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- ================= نظرات کاربران ================= --}}
                        <div class="tab-pane fade p-t-20 {{ $activeTab == 'rating_info' ? 'show active' : '' }}"
                             id="rating_info" role="tabpanel">
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                                    نظرات و امتیاز کاربران
                                    @if($center->average_rating)
                                        <span class="badge badge-light">میانگین: {{ $center->average_rating }} از ۵</span>
                                    @endif
                                </div>

                                <div class="col-12">
                                    {{-- Moderation only: ratings are submitted by visitors through the API,
                                         and nothing reaches the frontend until it is تایید شده. --}}
                                    <div class="alert alert-info">
                                        نظرات از سمت کاربران سایت ثبت می‌شود. فقط نظرهای «تایید شده» در صفحه عمومی نمایش داده می‌شوند.
                                    </div>

                                    <table class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th>کاربر</th>
                                            <th>امتیاز</th>
                                            <th>متن</th>
                                            <th>تاریخ</th>
                                            <th style="width:160px">وضعیت</th>
                                            <th style="width:60px">حذف</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($ratings as $rating)
                                            <tr>
                                                <td>{{ $rating->full_name }}</td>
                                                <td>{{ $rating->score }} / ۵</td>
                                                <td>{{ $rating->text }}</td>
                                                <td>{{ $rating->created_at }}</td>
                                                <td>
                                                    <select class="form-control" name="rating_status[{{ $rating->id }}]">
                                                        @foreach(\App\Models\Rating::STATUS as $value => $label)
                                                            <option value="{{ $value }}" @selected($rating->status == $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="rating_delete[]" value="{{ $rating->id }}">
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-muted">نظری ثبت نشده است.</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 my-3 d-flex justify-content-end">
                            <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/center.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.services-select').select2({
            placeholder: "انتخاب خدمت مربوطه"
        });

        // Remember the open pane so a save returns to it instead of the first tab.
        $('.center-tab').on('click', function () {
            $('#active_tab').val($(this).data('tab'));
        });

        // "تعطیل" greys out that day's two time inputs. A disabled input posts nothing,
        // which is exactly right — the controller clears the times for a closed day anyway.
        $('#hours_info').on('change', '.day-closed', function () {
            var $row = $(this).closest('tr');
            var $times = $row.find('.day-open, .day-close').prop('disabled', this.checked);
            if (this.checked) { $times.val(''); }
        });

        // Question repeater. A new row posts an EMPTY asked_question_ids[] so the three
        // arrays stay index-aligned — the controller pairs them by position.
        $('#faq-add').on('click', function () {
            $('#faq-rows').append(
                '<tr>' +
                '<td><input type="hidden" name="asked_question_ids[]" value="">' +
                '<input type="text" class="form-control" name="asked_question_questions[]" placeholder="سوال"></td>' +
                '<td><textarea class="form-control" name="asked_question_answers[]" rows="2" placeholder="پاسخ"></textarea></td>' +
                '<td class="text-center"><button type="button" class="btn btn-danger btn-sm faq-remove">' +
                '<i class="fa fa-remove"></i></button></td>' +
                '</tr>'
            );
        });

        $('#faq-rows').on('click', '.faq-remove', function () {
            $(this).closest('tr').remove();
        });
    });
</script>
@if (session()->get('center_success') == 'success')
<span id="contact_success"></span>
@elseif(session()->get('center_error')=='success')
<span id="contact_error"></span>
@endif
@endsection
