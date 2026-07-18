@extends('Admin.master')
@section('title')ویرایش مغازه دیجیتال@endsection

@section('content')
<div class="card edit-ghorfe">
    <div class="card-body">
        <div class="row">
            <input type="hidden" id="ghorfe_id" value="{{ $ghorfeOnlineList->id }}">
            @php
                $activeTab = session('active_tab', 'settings_info');
            @endphp
            <!-- Sidebar Menu -->
            <div class="col-12 col-sm-2">
                <ul class="nav nav-pills d-none d-md-block d-block nav-justified custom-steps mb-4">
                        <li class="nav-item mb-2">
                            <a class="nav-link {{ $activeTab == 'settings_info' ? 'active' : '' }}"
                               data-toggle="pill" href="#settings_info">
                                <span class="step-circle">1</span>
                                <span class="step-title">تنظیمات</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ $activeTab == 'official_info' ? 'active' : '' }}"
                               data-toggle="pill" href="#official_info">
                                <span class="step-circle">2</span>
                                <span class="step-title">اطلاعات رسمی</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ $activeTab == 'text_content' ? 'active' : '' }}"
                               data-toggle="pill" href="#text_content">
                                <span class="step-circle">3</span>
                                <span class="step-title">محتوا</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ $activeTab == 'package_content' ? 'active' : '' }}" data-toggle="pill" href="#package_content">
                                <span class="step-circle">4</span>
                                <span class="step-title"> انتخاب پکیج</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ $activeTab == 'image_content' ? 'active' : '' }}" data-toggle="pill" href="#image_content">
                                <span class="step-circle">5</span>
                                <span class="step-title"> تصاویر</span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ $activeTab == 'menu_content' ? 'active' : '' }}" data-toggle="pill" href="#menu_content">
                                <span class="step-circle">6</span>
                                <span class="step-title"> منوها </span>
                            </a>
                        </li>

                        <li class="nav-item mb-2">
                            <a class="nav-link {{ $activeTab == 'theme_content' ? 'active' : '' }}" data-toggle="pill" href="#theme_content">
                                <span class="step-circle">7</span>
                                <span class="step-title"> تم </span>
                            </a>
                        </li>


                    <li class="nav-item mb-2">
                        <a class="nav-link {{ $activeTab == 'gallery_content' ? 'active' : '' }}" data-toggle="pill" href="#gallery_content">
                            <span class="step-circle">8</span>
                            <span class="step-title"> گالری  </span>
                        </a>
                    </li>

                </ul>
            </div>

            <!-- Content -->
            <div class="col-12 col-sm-10">
                <div class="tab-content border justify-content-center p-3 rounded-2">
                    <div class="d-none d-md-flex justify-content-between mb-1">
                        <button type="button" class="btn btn-prev">
                            <span class="fa fa-arrow-circle-o-right"></span>
                        </button>
                        <button type="button" class="btn btn-next">
                            <span class="fa fa-arrow-circle-o-left"></span>
                        </button>
                    </div>

                    <div class="tab-pane fade p-t-20  {{ $activeTab == 'settings_info' ? 'show active' : '' }}"
                         id="settings_info" role="tabpanel">
                        <form action="{{ route('settings.updateCompanyInfo') }}" method="POST" class="mb-2">
                            @csrf
                            @method('PATCH')
                            <div class="row ">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">اطلاعات حساب مالیاتی</div>
                                <div class="col-md-4 form-group">
                                    <label for="shaba_number">شماره شبا (IR0000000000000000)</label>
                                    <input type="text" name="shaba_number" class="form-control"   value="{{$company_info['shaba_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="card_number">شماره کارت</label>
                                    <input type="text" name="card_number" class="form-control"   value="{{$company_info['card_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="card_holder">نام کامل صاحب حساب</label>
                                    <input type="text" name="card_holder" class="form-control"   value="{{$company_info['card_holder'] ?? ''}}">
                                </div>
                                <div class="card-header bg-dark-gradient col-md-12 border  border-right-0 border-left-0  ">اطلاعات حساب معمولی</div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_shaba_number">شماره شبا (IR0000000000000000)</label>
                                    <input type="text" name="tax_free_shaba_number" class="form-control"   value="{{$company_info['tax_free_shaba_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_card_number">شماره کارت</label>
                                    <input type="text" name="tax_free_card_number" class="form-control"   value="{{$company_info['tax_free_card_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_card_holder">نام کامل صاحب حساب</label>
                                    <input type="text" name="tax_free_card_holder" class="form-control"   value="{{$company_info['tax_free_card_holder'] ?? ''}}">
                                </div>
                                <div class="card-header bg-dark-gradient col-md-12 border  border-right-0 border-left-0  ">اطلاعات سایت</div>
                                <div class="col-md-12 form-group">
                                    <label for="fullName">آدرس</label>
                                    <input type="text" name="address" class="form-control" value="{{$company_info['address'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="fullName">تلفن</label>
                                    <input type="text" name="phone" class="form-control"   value="{{$company_info['phone'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="fullName">ایمیل</label>
                                    <input type="text" name="email" class="form-control  "   value="{{$company_info['email'] ?? ''}}">
                                </div>

                                @permission('ghorfe_online_list_sms_panel')
                                <div class="col-md-4 form-group">
                                    <label for="fullName">پنل پیامکی</label>
                                    <select name="sms_panel" class="form-control select2">
                                        <option value="iranSms"
                                            {{ old('sms_panel', $company_info['sms_panel'] ?? '') == 'iranSms' ? 'selected' : '' }}>
                                            ایران اس ام اس
                                        </option>

                                        <option value="neginSms"
                                            {{ old('sms_panel', $company_info['sms_panel'] ?? '') == 'neginSms' ? 'selected' : '' }}>
                                            نگین اس ام اس
                                        </option>
                                    </select>
                                </div>
                                @endpermission
                                @permission('ghorfe_online_list_sms_apikey')
                                <div class="col-md-4 form-group">
                                    <label for="fullName">iransms apikey</label>
                                    <input type="text" name="iranSms_apikey" class="form-control" value="{{$company_info['iranSms_apikey'] ?? ''}}">
                                </div>
                                @endpermission

                                @permission('ghorfe_online_list_sms_linenumber')
                                <div class="col-md-4 form-group">
                                    <label for="fullName">iransms linenumber</label>
                                    <input type="text" name="iranSms_linenumber" class="form-control" value="{{$company_info['iranSms_linenumber'] ?? ''}}">
                                </div>
                                @endpermission

                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                        </form>

                        @if($ghorfe_domain_active)
                            <form action="{{route('settings.updateExpireDates')}}" method="post">
                                @csrf
                                @method('PATCH')
                                <div class="row  ">
                                    <div class="card-header bg-dark-gradient col-md-12 border  border-right-0 border-left-0  ">موعد تمدید دامنه ها</div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">lalecard.ir</label>
                                        <input type="text" name="domain_expire_date_lalecard_ir" class="form-control date" value="{{$expire_dates['domain_expire_date_lalecard.ir'] ?? ''}}">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">lalecard.com</label>
                                        <input type="text" name="domain_expire_date_lalecard_com" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalecard.com'] ?? ''}}">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">ghorfe-online.ir</label>
                                        <input type="text" name="domain_expire_date_ghorfe_online_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_ghorfe-online.ir'] ?? ''}}">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">lalehkart.ir</label>
                                        <input type="text" name="domain_expire_date_lalehkart_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalehkart.ir'] ?? ''}}">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">lalekart.ir</label>
                                        <input type="text" name="domain_expire_date_lalekart_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalekart.ir'] ?? ''}}">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">lalemarket.ir</label>
                                        <input type="text" name="domain_expire_date_lalemarket_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalemarket.ir'] ?? ''}}">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">qorfe-online.ir</label>
                                        <input type="text" name="domain_expire_date_qorfe_online_ir" class="form-control date"  value="{{$expire_dates['domain_expire_date_qorfe-online.ir'] ?? ''}}">
                                    </div>
                                    <div class="col-md-2 form-group">
                                        <label for="fullName">lalebank.com</label>
                                        <input type="text" name="domain_expire_date_lalebank_com" class="form-control date"  value="{{$expire_dates['domain_expire_date_lalebank.com'] ?? ''}}">
                                    </div>
                                    <div class="form-group col-md-6 ">
                                        <label for="fullName"> موعد تمدید سرور مجازی رادکام</label>
                                        <input type="text" name="vps_expire_date" class="form-control date"  value="{{$expire_dates['vps_expire_date'] ?? ''}}">
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="fullName">موعد تمدید پنل های پلسک</label>
                                        <input type="text" name="plesk_expire_date" class="form-control date"  value="{{$expire_dates['plesk_expire_date'] ?? ''}}">
                                    </div>
                                    <div class="col-12 my-3 d-flex justify-content-end">
                                        <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                    </div>
                                </div>
                            </form>
                        @endif

                    </div>
                    <div class="tab-pane fade  p-t-20 {{ $activeTab == 'official_info' ? 'show active' : '' }}"
                         id="official_info">
                        @permission('ghorfe_online_list_official_info')
                        <form class="form-horizontal" role="form" method="post"
                            action="{{route('ghorfeOnlineList.officialInfoUpdate' , [$ghorfeOnlineList->slug])}}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row ">
                                @include('Admin.layouts.errors')
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                    اطلاعات رسمی
                                </div>
                                <div class="col-12 col-md-4 mb-2">
                                    <label>انتخاب مالک مغازه</label>
                                    <select class="form-control select2" name="user_id" required>
                                        <option value="all">انتخاب کنید</option>
                                        @foreach ($employees as $employee)
                                        <option value="{{$employee->user->id}}" {{$employee->user->id ==
                                            $ghorfeOnlineList->user_id ? 'selected' : ''}}>{{$employee->user->name.'
                                            '.$employee->user->family}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-2">
                                    <label>انتخاب نوع حوزه کاری</label>
                                    <select class="form-control" name="type_work" required>
                                        @foreach (App\Models\GhorfeOnlineList::TYPE as $key => $item)
                                        <option value="{{$key}}" {{$key==$ghorfeOnlineList->type_work ? 'selected' :
                                            ''}}>{{$item}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4 mb-2">
                                    <label>عدد پیامک تخصیص داده شده(اعداد را با , از هم جدا کنید)</label>
                                    <input type="text" value="{{ implode(',' , $ghorfeOnlineList->sms_code) }}" required
                                        class="form-control" name="sms_code">
                                </div>

                                <div class="col-12 col-md-3 mb-2">
                                    <label>دامنه فعال</label>
                                    <input type="text" value="{{$ghorfeOnlineList->domain_active}}" class="form-control" name="domain_active">
                                    <label>مانند نمونه: 'lalecard.ir'</label>
                                </div>
                                <div class="col-12 col-md-4 mb-3">
                                    <label>وضعیت</label>

                                    <select class="form-control" name="status">
                                        <option value="0" {{ $ghorfeOnlineList->status == 0 ? 'selected' : '' }}>
                                            غیر فعال
                                        </option>
                                        <option value="1" {{ $ghorfeOnlineList->status == 1 ? 'selected' : '' }}>
                                            فعال
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 mb-3">
                                    <label>تاریخ شروع اجاره نامه</label>
                                    <input value="{{ $ghorfeOnlineList->contract_start_date }}" type="text"
                                        name="contract_start_date" class="form-control date">
                                </div>
                                <div class="col-12 col-md-4 mb-3">
                                    <label>تاریخ پایان اجاره نامه</label>
                                    <input value="{{ $ghorfeOnlineList->contract_expiration_date }}" type="text"
                                        name="contract_expiration_date" class="form-control date">
                                </div>
                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                      </form>
                        @endpermission
                    </div>
                    <div class="tab-pane fade p-t-20 {{ $activeTab == 'text_content' ? 'show active' : '' }}"
                         id="text_content">
                        @permission('ghorfe_online_list_site_content')

                        <form class="form-horizontal" role="form" method="post"
                            action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">  محتوا</div>

                                @include('Admin.layouts.errors')

                                <div class="col-12 col-md-4 mb-2">
                                    <label>عنوان فارسی(این عنوان در سایت نمایش داده میشود)</label>
                                    <input type="text" value="{{$ghorfeOnlineList->title}}" class="form-control" name="title"
                                        required>
                                </div>

                                <div class="col-12 col-md-4 mb-2">
                                    <label>نشان اینترنتی مغازه (لطفا به انگلیسی وارد کنید)</label>
                                    <input type="text" value="{{$ghorfeOnlineList->en_title}}" class="form-control"
                                        name="en_title" required>
                                </div>

                                <div class="col-12 col-md-4 mb-2">
                                    <label>ایمیل</label>
                                    <input type="email" value="{{$ghorfeOnlineList->email}}" class="form-control" name="email">
                                </div>

                                <div class="col-12 col-md-4 mb-2">
                                    <label>عنوان مغازه(موثر در سئو سایت)</label>
                                    <input type="text" value="{{$ghorfeOnlineList->index_title}}" class="form-control"
                                        name="index_title" required>
                                </div>

                                <div class="col-12 col-md-4 mb-2">
                                    <label>کد پستی</label>
                                    <input type="text" value="{{$ghorfeOnlineList->postal_code}}" class="form-control"
                                        name="postal_code">
                                </div>

                                <div class="col-12 mb-2">
                                    <label>توضیحات قسمت اول سایت</label>
                                    <textarea class="form-control"
                                        name="description">{{$ghorfeOnlineList->description}}</textarea>
                                </div>

                                <div class="col-12 mb-2">
                                    <label>توضیحات مربوط به فعالیت کاری(موثر در سئو سایت)</label>
                                    <textarea class="form-control"
                                        name="index_description">{{$ghorfeOnlineList->index_description}}</textarea>
                                </div>

                                <div class="col-12 mb-2">
                                    <label>کلمات کلیدی مربوط به حوزه کاری مغازه(موثر در سئو سایت - کلمات را با , از هم جدا
                                        کنید)</label>
                                    <input type="text"
                                        value="{{ $ghorfeOnlineList->footer_tag ? implode(',' , $ghorfeOnlineList->footer_tag) : ''}}"
                                        required class="form-control" name="footer_tag">
                                </div>

                                <div class="col-12 mt-4 mb-2">
                                    <h6 class="border-bottom mb-2">لینک شبکه های اجتماعی</h6>
                                </div>
                                <div class="col-12 col-md-4 mb-2">
                                    <label>آدرس اینستاگرام</label>
                                    <input type="text" value="{{$ghorfeOnlineList->instagram}}" class="form-control"
                                        name="instagram">
                                    <label>مانند نمونه : 'https://www.instagram.com/lalecard.ir'</label>
                                </div>
                                <div class="col-12 col-md-4 mb-2">
                                    <label>آدرس تلگرام</label>
                                    <input type="text" value="{{$ghorfeOnlineList->telegram}}" class="form-control"
                                        name="telegram">
                                    <label>مانند نمونه : 'https://t.me/lalecard_channel'</label>
                                </div>
                                <div class="col-12 col-md-4 mb-2">
                                    <label>آدرس آپارت</label>
                                    <input type="text" value="{{$ghorfeOnlineList->aparat}}" class="form-control" name="aparat">
                                    <label>مانند نمونه: 'https://www.aparat.com/lalecard.ir'</label>
                                </div>

                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                        </form>
                        @endpermission
                    </div>
                    <div class="tab-pane fade p-t-20 {{ $activeTab == 'package_content' ? 'show active' : '' }}" id="package_content" role="tabpanel" aria-labelledby="package-content-tab">
                        @permission('ghorfe_online_list_package_update')
                        <form class="form-horizontal" role="form" method="post"
                              action="{{route('ghorfeOnlineList.packageUpdate', [$ghorfeOnlineList->slug])}}"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                @include('Admin.layouts.errors')
                                <div class="col-12 col-md-12">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-dark-gradient text-white">
                                            <h6 class="mb-0">پکیج‌ها</h6>
                                        </div>

                                        <div class="card-body">
                                            <div class="row">

                                                @if(isset($packages) && count($packages))
                                                    @foreach($packages as $package)
                                                        @if($package->is_active)

                                                            <div class="col-md-3 mb-3">
                                                                <label class="package-label w-100">
                                                                    <input type="radio"
                                                                           name="package_id"
                                                                           value="{{ $package->id }}"
                                                                           class="d-none package-radio"
                                                                           id="package_{{ $package->id }}"
                                                                        {{ (optional($ghorfeOnlineList?->shahinPackage)->id == $package->id) ? 'checked' : '' }}>

                                                                    <div class="package-card p-3 border rounded">

                                                                        <img src="{{ asset($package->image ?? 'images/default-package.png') }}"
                                                                             class="img-fluid mb-2"
                                                                             style="height:80px; object-fit:cover; border-radius:10px;">

                                                                        <h6 class="font-weight-bold">{{ $package->name }}</h6>

                                                                        <small class="text-muted d-block">
                                                                            {{ $package->type }} | {{ $package->duration_days }} روز
                                                                        </small>

                                                                        @php
                                                                            $features = is_array($package->features)
                                                                                ? $package->features
                                                                                : json_decode($package->features, true);
                                                                        @endphp

                                                                        @if($features)
                                                                            <ul class="list-unstyled small text-muted mt-2 mb-2">
                                                                                @foreach($features as $feature)
                                                                                    <li>✔ {{ $feature }}</li>
                                                                                @endforeach
                                                                            </ul>
                                                                        @endif

                                                                        <div class="text-success font-weight-bold">
                                                                            {{ number_format($package->price) }} تومان
                                                                        </div>

                                                                    </div>
                                                                </label>
                                                            </div>

                                                        @endif
                                                    @endforeach
                                                @else
                                                    <p class="text-muted w-100">پکیجی وجود ندارد</p>
                                                @endif
                                                <div class="col-12 my-3 d-flex justify-content-end">
                                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </form>

                    @endpermission
                    </div>
                    <div class="tab-pane fade p-t-20 {{ $activeTab == 'image_content' ? 'show active' : '' }}" id="image_content" role="tabpanel" aria-labelledby="image-content-tab">
                        @permission('ghorfe_online_list_site_content')
                        <form class="form-horizontal" role="form" method="post"
                            action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">  تصاویر</div>

                                @include('Admin.layouts.errors')
                                    <div class="col-12 row mt-1">
                                        {{-- Spatie single-file settings images. Each: display via the model
                                             accessor (full url), blue ajax upload -> hidden {field}_path holds the
                                             temp media id -> contentUpdate attachTo()s it into the {field} collection. --}}
                                        <div class="col-12 col-md-6">
                                            <div class="card">
                                                <div id="logo_src" class="show">
                                                    @if($ghorfeOnlineList->logo)
                                                        <img src="{{ $ghorfeOnlineList->logo }}" class="card-img-top" style="height:200px;width:100%;object-fit:contain;background:#f8f9fa;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center text-muted" style="height:200px;background:#f8f9fa;">بدون تصویر</div>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <label>لوگو</label>
                                                    <br>
                                                    <label for="logo" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                                    <input name="logo" type="file" id="logo" class="d-none file-upload file_upload_ajax" accept=".jpg,.jpeg,.png">
                                                    <input type="hidden" id="logo_path" class="path" name="logo_path">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="card">
                                                <div id="footer_logo_src" class="show">
                                                    @if($ghorfeOnlineList->footer_logo)
                                                        <img src="{{ $ghorfeOnlineList->footer_logo }}" class="card-img-top" style="height:200px;width:100%;object-fit:contain;background:#f8f9fa;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center text-muted" style="height:200px;background:#f8f9fa;">بدون تصویر</div>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <label>لوگو فوتر (همراه با نام برند)</label>
                                                    <br>
                                                    <label for="footer_logo" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                                    <input name="footer_logo" type="file" id="footer_logo" class="d-none file-upload file_upload_ajax" accept=".jpg,.jpeg,.png">
                                                    <input type="hidden" id="footer_logo_path" class="path" name="footer_logo_path">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="card">
                                                <div id="fav_src" class="show">
                                                    @if($ghorfeOnlineList->fav)
                                                        <img src="{{ $ghorfeOnlineList->fav }}" class="card-img-top" style="height:200px;width:100%;object-fit:contain;background:#f8f9fa;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center text-muted" style="height:200px;background:#f8f9fa;">بدون تصویر</div>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <label>لوگو مرورگر (فاویکون)</label>
                                                    <br>
                                                    <label for="fav" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                                    <input name="fav" type="file" id="fav" class="d-none file-upload file_upload_ajax" accept=".jpg,.jpeg,.png,.ico">
                                                    <input type="hidden" id="fav_path" class="path" name="fav_path">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <div class="card">
                                                <div id="mohr_src" class="show">
                                                    @if($ghorfeOnlineList->mohr)
                                                        <img src="{{ $ghorfeOnlineList->mohr }}" class="card-img-top" style="height:200px;width:100%;object-fit:contain;background:#f8f9fa;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center text-muted" style="height:200px;background:#f8f9fa;">بدون تصویر</div>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <label>مهر (جهت نمایش در فاکتور رسمی)</label>
                                                    <br>
                                                    <label for="mohr" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                                    <input name="mohr" type="file" id="mohr" class="d-none file-upload file_upload_ajax" accept=".jpg,.jpeg,.png">
                                                    <input type="hidden" id="mohr_path" class="path" name="mohr_path">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="card">
                                                <div id="addvertisment_slider_src" class="show">
                                                    @if($ghorfeOnlineList->addvertisment_slider)
                                                        <img src="{{ $ghorfeOnlineList->addvertisment_slider }}" class="card-img-top" style="height:88px;width:100%;object-fit:contain;background:#f8f9fa;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center text-muted" style="height:88px;background:#f8f9fa;">بدون تصویر</div>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <label>اسلایدر تبلیغات(88*1400)</label>
                                                    <br>
                                                    <label for="addvertisment_slider" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                                    <input name="addvertisment_slider" type="file" id="addvertisment_slider" class="d-none file-upload file_upload_ajax" accept=".jpg,.jpeg,.png,.gif">
                                                    <input type="hidden" id="addvertisment_slider_path" class="path" name="addvertisment_slider_path">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                        </form>
                        @endpermission
                    </div>
                    <div class="tab-pane fade p-t-20    {{ $activeTab == 'menu_content' ? 'show active' : '' }}" id="menu_content" role="tabpanel" aria-labelledby="text-content-tab">
                        <form class="form-horizontal" role="form" method="post"
                              action="{{route('ghorfeOnlineList.menuUpdate', [$ghorfeOnlineList->slug])}}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row" id="menu-wrapper" >
                                @include('Admin.layouts.errors')

                                @if($ghorfeOnlineList->template)
                                    <div class="card-header mb-2 bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                    منو دلخواه را انتخاب کنید
                                    </div>
{{--                                @dd($ghorfeOnlineList->menus)--}}
                                    @foreach($ghorfeOnlineList->menus as $index => $menu)
                                        <div class="menu-item border p-2 mb-3 row">
                                            <input type="hidden" name="ids[]" value="{{ $menu->id }}">

                                            <div class="col-12">
                                                <label>عنوان</label>
                                                <input type="text"
                                                       name="titles[]"
                                                       value="{{ $menu->title }}"
                                                       class="form-control"
                                                       required>
                                            </div>

                                            <div class="col-12">
                                                <label>لینک</label>
                                                <input type="text"
                                                       name="links[]"
                                                       value="{{ $menu->link }}"
                                                       class="form-control"
                                                       required>

                                            </div>
                                            <div class="col-12">
                                                <span>وضعیت </span>
                                                <label class="switch-status">
                                                    <input type="checkbox" name="actives[{{ $index }}]" value="1" {{ $menu->is_active ? 'checked' : '' }}>
                                                    <span class="slider round "></span>
                                                </label>

                                            </div>
                                        </div>
                                    @endforeach

                                @else
                                    <div class="card-header mb-2 bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                    ابتدا قالب مورد نظر را انتخاب نمایید
                                    </div>
                                    @if(isset($templates) && count($templates))
                                        @foreach($templates as $template)
                                            @if($template->active)
                                                <div class="col-md-4 mb-3">
                                                    <label class="template-label w-100">
                                                        <input type="radio"
                                                               name="template_id"
                                                               value="{{ $template->id }}"
                                                               class="d-none template-radio"
                                                               id="template_{{ $template->id }}"
                                                            {{ $template->id ? 'checked' : '' }}>

                                                        <div class="template-card p-3 text-center border rounded">
                                                            <img src="{{asset('img/upload.jpg')}}"
                                                                 class="img-fluid mb-2"
                                                                 style="height: 32px">
                                                            <h6 class="font-weight-bold">{{ $template->name }}</h6>
                                                            <strong>{{$template->type}}</strong>

                                                            @if($template->menus)
                                                                <ul class="list-unstyled small text-muted mt-2 mb-2">
                                                                    @foreach($template->menus as $menu)
                                                                        <li>✔ {{ $menu->title }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            @endif
                                        @endforeach
                                    @endif
                                @endif

                                 <div class="col-12 my-3 d-flex justify-content-end">
                                <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                            </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade p-t-20  {{ $activeTab == 'theme_content' ? 'show active' : '' }}" id="theme_content" role="tabpanel" aria-labelledby="text-content-tab">
                        @permission('ghorfe_online_list_site_content')
                        <form class="form-horizontal" role="form" method="post"
                              action="{{ route('ghorfeOnlineList.themeUpdate', [$ghorfeOnlineList]) }}"
                              enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                @include('Admin.layouts.errors')
                                <div class="row mb-3">
                                    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                         از بین تم های پیش فرض میتونید انتخاب کنید و یا تم مورد نظر خودتون رو بسازید
                                    </div>

                                    <div class="d-flex flex-wrap gap-3 mt-2">
                                        @foreach($themes as $theme)
                                            <div
                                                class="theme-box"
                                                data-theme-id="{{ $theme->id }}"
                                                data-primary="{{ $theme->color_primary }}"
                                                data-secondary="{{ $theme->color_secondary }}"
                                            >
                                                <div
                                                    class="primary-color"
                                                    style="background: {{ $theme->color_primary }}"
                                                ></div>

                                                <div
                                                    class="secondary-color"
                                                    style="background: {{ $theme->color_secondary }}"
                                                ></div>
                                            </div>
                                        @endforeach

                                        <div id="successMessage" class="alert alert-success d-none mt-3"></div>

                                    </div>
                                    <div class="card-header mt-2 bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                        تم دلخواه خودتان  را میتوانید بسازید
                                    </div>

                                    <div class="col-12 col-md-4 mt-2 d-block">
                                        <label>انتخاب نام برای تم </label>
                                        <input type="text" class="form-control mt-1" name="name"
                                               value="{{ old('name', $ghorfeOnlineList->theme->name ?? 'تم سفارشی جدید') }}" required>
                                    </div>

                                    <div class="col-12 col-md-4 mb-3">
                                        <label class="d-block">رنگ اصلی</label>
                                        {{-- 2. Fixed variable pointing to the theme relationship --}}
                                        <input type="color"
                                               id="primaryPicker"
                                               name="color_primary"
                                               class="form-control-color w-100"
                                               value="{{ old('color_primary', $ghorfeOnlineList->theme->color_primary ?? '#ff2d20') }}"
                                               oninput="document.getElementById('primaryText').value = this.value">

                                        <input type="text"
                                               id="primaryText"
                                               class="form-control mt-2 font-mono"
                                               value="{{ old('color_primary', $ghorfeOnlineList->theme->color_primary ?? '#ff2d20') }}"
                                               readonly>
                                    </div>

                                    <div class="col-12 col-md-4 mb-3">
                                        <label class="d-block">رنگ فرعی</label>
                                        {{-- 3. Live syncing added via inline native JS 'oninput' --}}
                                        <input type="color"
                                               id="secondaryPicker"
                                               name="color_secondary"
                                               class="form-control-color w-100"
                                               value="{{ old('color_secondary', $ghorfeOnlineList->theme->color_secondary ?? '#6c757d') }}"
                                               oninput="document.getElementById('secondaryText').value = this.value">

                                        <input type="text"
                                               id="secondaryText"
                                               class="form-control mt-2 font-mono"
                                               value="{{ old('color_secondary', $ghorfeOnlineList->theme->color_secondary ?? '#6c757d') }}"
                                               readonly>
                                    </div>

                                    <div class="col-12 my-3 d-flex justify-content-end">
                                        <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                    </div>
                                </div>
                            </div>
                        </form>
                        @endpermission
                    </div>
                    <div class="tab-pane fade p-t-20 {{ $activeTab == 'gallery_content' ? 'show active' : '' }}"
                         id="gallery_content"
                         role="tabpanel"
                         aria-labelledby="text-content-tab">

                        <form class="form-horizontal"
                              method="post"
                              action="{{ route('ghorfeOnlineList.gallerySave', [$ghorfeOnlineList]) }}"
                              enctype="multipart/form-data">

                            @csrf

                            <div class="row">

                                @include('Admin.layouts.errors')
                                <div class="row">
                                    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                               برای قسمت دیتاماینیگ صفحه اصلی استفاده می شود
                                    </div>
                                    @php
                                        $selectedCategory = old('category', $ghorfeOnlineList->category_id ?? null);
                                    @endphp

                                    <div class="col-12 mb-5">
                                        <label>دسته بندی ها</label>

                                        <select name="category" class="selectMulti form-control">
                                            <option value="">انتخاب دسته بندی</option>

                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                        @selected($selectedCategory == $category->id)>
                                                    {{ $category->title }}
                                                </option>
                                            @endforeach

                                        </select>

                                        @error('category')
                                        <span class="help-block sub-error">{{ $message }}</span>
                                        @enderror
                                    </div>


                                    @php
                                        $selectedService = old('service', $ghorfeOnlineList->service_id ?? null);
                                    @endphp

                                    <select id="serviceSelect" name="service" class="form-control">
                                        <option value="">انتخاب سرویس</option>

                                        @foreach($ghorfeOnlineList->services as $service)
                                            <option value="{{ $service->id }}"
                                                    @selected($selectedService == $service->id)>
                                                {{ $service->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="gallerySection">
                                        {{-- Spatie slot gallery (per-ghorfe). The selected category/service above
                                             is stored as each slot's parent_id custom property on save. --}}
                                        <x-media-gallery-form :owner="$ghorfeOnlineList" />
                                    </div>
                                </div>

                                {{-- ================= SUBMIT ================= --}}
                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="d-none d-md-flex justify-content-between mb-1">
                        <button type="button" class="btn btn-prev">
                            <span class="fa fa-arrow-circle-o-right"></span>
                        </button>
                        <button type="button" class="btn btn-next">
                            <span class="fa fa-arrow-circle-o-left"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {

        // =====================================================
        // CSRF SETUP (AJAX)
        // =====================================================
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
            }
        });

        // =====================================================
        // ELEMENTS (SAFE CHECK)
        // =====================================================
        const serviceSelect = document.getElementById('serviceSelect');
        const itemSelect = document.getElementById('serviceItemSelect');
        const categorySelect = document.getElementById('categorySelect');
        const ghorfeId = document.getElementById('ghorfe_id');

        // =====================================================
        // SERVICE -> SERVICE ITEM (FETCH)
        // =====================================================
        function loadServiceItems(serviceId, selectedId = null) {
            if (!itemSelect) return;

            itemSelect.innerHTML = '<option value="">در حال بارگذاری...</option>';

            if (!serviceId) {
                itemSelect.innerHTML = '<option value="">ابتدا سرویس را انتخاب کنید</option>';
                return;
            }

            fetch(`/service-items/${serviceId}`)
                .then(res => res.json())
                .then(data => {

                    if (!data || data.length === 0) {
                        itemSelect.innerHTML = '<option value="">خالی است</option>';
                        return;
                    }

                    let options = '<option value="">انتخاب زیرسرویس</option>';

                    data.forEach(item => {
                        const selected = selectedId == item.id ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.title}</option>`;
                    });

                    itemSelect.innerHTML = options;
                })
                .catch(() => {
                    itemSelect.innerHTML = '<option value="">خطا در بارگذاری</option>';
                });
        }

        if (serviceSelect) {
            serviceSelect.addEventListener('change', function () {
                loadServiceItems(this.value);
            });
        }

        // (removed) legacy getGallery AJAX that replaced #gallerySection on every
        // category/service change — the Spatie media-gallery-form component now renders
        // the per-ghorfe gallery directly, so there's nothing to reload.

        if (serviceSelect && serviceSelect.value) {
            loadServiceItems(serviceSelect.value);
        }

        // =====================================================
        // IMAGE PREVIEW
        // =====================================================
        window.previewImage = function (event, previewId) {
            const file = event.target.files?.[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const el = document.getElementById(previewId);
                if (el) el.src = e.target.result;
            };
            reader.readAsDataURL(file);
        };

        // =====================================================
        // REMOVE IMAGE (UI ONLY)
        // =====================================================
        window.removeImage = function (previewId) {
            const el = document.getElementById(previewId);
            if (el) {
                el.src = "https://via.placeholder.com/150x100?text=Removed";
            }
        };

        // =====================================================
        // THEME SELECT (SAFE)
        // =====================================================
        document.querySelectorAll('.theme-box').forEach(box => {
            box.addEventListener('click', function () {

                document.querySelectorAll('.theme-box')
                    .forEach(b => b.classList.remove('selected'));

                this.classList.add('selected');

                const themeId = this.dataset.themeId;

                $.ajax({
                    url: "/ghorfe-online-list/theme-update",
                    type: "POST",
                    data: { themeId },
                    success: function () {
                        const msg = document.getElementById('successMessage');
                        if (!msg) return;

                        msg.innerText = 'تم با موفقیت تغییر کرد';
                        msg.classList.remove('d-none');

                        setTimeout(() => {
                            msg.classList.add('d-none');
                        }, 3000);
                    }
                });
            });
        });

    });
</script>

@endsection
