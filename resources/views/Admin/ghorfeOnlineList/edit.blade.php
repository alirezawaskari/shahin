@extends('Admin.master')
@section('title')ویرایش مغازه دیجیتال@endsection

@section('content')
<div class="card edit-ghorfe">
    <div class="card-body">
        <div class="row">
            <input type="hidden" id="ghorfe_id" value="{{ $ghorfeOnlineList->id }}">
            @php
            $activeTab = session('active_tab', 'ghorfe_customization');
            @endphp
            <!-- Sidebar Menu -->
            <div class="col-12 col-sm-3">
                <ul class="nav nav-pills d-none d-md-block d-block nav-justified custom-steps mb-4">
                    <li class="nav-item mb-2">
                        <a class="nav-link {{ $activeTab == 'ghorfe_customization' ? 'active' : '' }}"
                            data-toggle="pill" href="#ghorfe_customization">
                            <span class="step-circle">1</span>
                            <span class="step-title">شخصی سازی ویترین</span>
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a class="nav-link {{ $activeTab == 'content_manager' ? 'active' : '' }}" data-toggle="pill"
                            href="#content_manager">
                            <span class="step-circle">2</span>
                            <span class="step-title">مدیریت محتوا</span>
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a class="nav-link {{ $activeTab == 'banners_manager' ? 'active' : '' }}" data-toggle="pill"
                            href="#banners_manager">
                            <span class="step-circle">3</span>
                            <span class="step-title">مدیریت بنرهای ویترین</span>
                        </a>
                    </li>

                    <li class="nav-item mb-2">
                        <a class="nav-link {{ $activeTab == 'settings_mali' ? 'active' : '' }}" data-toggle="pill"
                            href="#settings_mali">
                            <span class="step-circle">4</span>
                            <span class="step-title"> تنظیمات مالی</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Content -->
            <div class="col-12 col-sm-9">
                <div class="tab-content border justify-content-center p-3 rounded-2">
                    <div class="d-none d-md-flex justify-content-between mb-1">
                        <button type="button" class="btn btn-prev">
                            <span class="fa fa-arrow-circle-o-right"></span>
                        </button>
                        <button type="button" class="btn btn-next">
                            <span class="fa fa-arrow-circle-o-left"></span>
                        </button>
                    </div>

                    <div class="tab-pane fade p-t-20  {{ $activeTab == 'ghorfe_customization' ? 'show active' : '' }}"
                        id="ghorfe_customization" role="tabpanel">
                        <ul class="gh-steps">
                            <li class="gh-step active" onclick="goTo(1)"><b>۱</b> اطلاعات</li>
                            <li class="gh-step" onclick="goTo(2)"><b>۲</b> لوگو</li>
                            <li class="gh-step" onclick="goTo(3)"><b>۳</b> منو</li>
                            <li class="gh-step" onclick="goTo(4)"><b>۴</b> تم</li>
                        </ul>

                        <!-- مرحله ۱ -->
                        <div class="gh-pane active mt-3" id="p1">
                            @permission('ghorfe_online_list_site_content')
                            <form class="form-horizontal" role="form" method="post"
                                action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">

                                    @include('Admin.layouts.errors')

                                    <div class="col-12 col-md-6 mb-2">
                                        <label>عنوان فارسی(این عنوان در سایت نمایش داده میشود)</label>
                                        <input type="text" value="{{$ghorfeOnlineList->title}}" class="form-control"
                                            name="title" required>
                                    </div>
                                    <div class="col-12 col-md-6 mb-2">
                                        <label>ایمیل</label>
                                        <input type="email" value="{{$ghorfeOnlineList->email}}" class="form-control"
                                            name="email">
                                    </div>
                                    <div class="col-12 col-md-6 form-group">
                                        <label for="fullName">آدرس</label>
                                        <input type="text" name="address" class="form-control"
                                            value="{{$company_info['address'] ?? ''}}">
                                    </div>
                                    <div class="col-12 col-md-6 form-group">
                                        <label for="fullName">تلفن</label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{$company_info['phone'] ?? ''}}">
                                    </div>
                                </div>
                            </form>
                            @endpermission
                        </div>

                        <!-- مرحله ۲ -->
                        <div class="gh-pane mt-3" id="p2">
                            <form class="form-horizontal" role="form" method="post"
                                action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    @include('Admin.layouts.errors')
                                    <div class="col-12 row">
                                        {{-- Spatie single-file settings images. Each: display via the model
                                        accessor (full url), blue ajax upload -> hidden {field}_path holds the
                                        temp media id -> contentUpdate attachTo()s it into the {field} collection. --}}
                                        <div class="col-12 col-md-6">
                                            <div class="card">
                                                <div id="logo_src" class="show">
                                                    @if($ghorfeOnlineList->logo)
                                                    <img src="{{ $ghorfeOnlineList->logo }}" class="card-img-top"
                                                        style="height:200px;width:100%;object-fit:contain;background:#f8f9fa;">
                                                    {{-- legacy here is a media_libraries *_id reference, not a path
                                                    --}}
                                                    @if($ghorfeOnlineList->mediaLibraryIsLegacy('logo'))
                                                    <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره
                                                        به
                                                        سیستم جدید منتقل می‌شود</small>
                                                    @endif
                                                    @else
                                                    <div class="d-flex align-items-center justify-content-center text-muted"
                                                        style="height:200px;background:#f8f9fa;">بدون تصویر</div>
                                                    @endif
                                                </div>
                                                <div class="card-body">
                                                    <label>لوگو</label>
                                                    <br>
                                                    <label for="logo"
                                                        class="btn btn-primary custom-file-upload border border-1"><i
                                                            class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب
                                                        تصویر
                                                    </label>
                                                    <input name="logo" type="file" id="logo"
                                                        class="d-none file-upload file_upload_ajax"
                                                        accept=".jpg,.jpeg,.png">
                                                    <input type="hidden" id="logo_path" class="path" name="logo_path">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 my-3 d-flex justify-content-end">
                                        <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- مرحله ۳ -->
                        <div class="gh-pane mt-3" id="p3">
                            <form class="form-horizontal" role="form" method="post"
                                action="{{route('ghorfeOnlineList.menuUpdate', [$ghorfeOnlineList->slug])}}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row" id="menu-wrapper">
                                    @include('Admin.layouts.errors')

                                    @if($ghorfeOnlineList->template)
                                    <div
                                        class="card-header mb-2 bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                        منو دلخواه را انتخاب کنید
                                    </div>
                                    @foreach($ghorfeOnlineList->menus as $index => $menu)
                                    <div class="menu-item border p-2 mb-3 row">
                                        <input type="hidden" name="ids[]" value="{{ $menu->id }}">

                                        <div class="col-12">
                                            <label>عنوان</label>
                                            <input type="text" name="titles[]" value="{{ $menu->title }}"
                                                class="form-control" required>
                                        </div>

                                        <div class="col-12">
                                            <label>لینک</label>
                                            <input type="text" name="links[]" value="{{ $menu->link }}"
                                                class="form-control" required>

                                        </div>
                                        <div class="col-12">
                                            <span>وضعیت </span>
                                            <label class="switch-status">
                                                <input type="checkbox" name="actives[{{ $index }}]" value="1" {{
                                                    $menu->is_active ? 'checked' : '' }}>
                                                <span class="slider round "></span>
                                            </label>

                                        </div>
                                    </div>
                                    @endforeach
                                    @else
                                    <div
                                        class="card-header mb-2 bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                        ابتدا قالب مورد نظر را انتخاب نمایید
                                    </div>
                                    @if(isset($templates) && count($templates))
                                    @foreach($templates as $template)
                                    @if($template->active)
                                    <div class="col-md-4 mb-3">
                                        <label class="template-label w-100">
                                            <input type="radio" name="template_id" value="{{ $template->id }}"
                                                class="d-none template-radio" id="template_{{ $template->id }}" {{
                                                $template->id ? 'checked' : '' }}>

                                            <div class="template-card p-3 text-center border rounded">
                                                <img src="{{asset('img/upload.jpg')}}" class="img-fluid mb-2"
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

                        <!-- مرحله ۴ -->
                        <div class="gh-pane mt-3" id="p4">
                            @permission('ghorfe_online_list_site_content')
                            <form class="form-horizontal" role="form" method="post"
                                action="{{ route('ghorfeOnlineList.themeUpdate', [$ghorfeOnlineList]) }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    @include('Admin.layouts.errors')
                                    <div class="row mb-3">
                                        <div
                                            class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                            از بین تم های پیش فرض میتونید انتخاب کنید و یا تم مورد نظر خودتون رو بسازید
                                        </div>

                                        <div class="d-flex flex-wrap gap-3 mt-2">
                                            @foreach($themes as $theme)
                                            <div class="theme-box" data-theme-id="{{ $theme->id }}"
                                                data-primary="{{ $theme->color_primary }}"
                                                data-secondary="{{ $theme->color_secondary }}">
                                                <div class="primary-color"
                                                    style="background: {{ $theme->color_primary }}">
                                                </div>

                                                <div class="secondary-color"
                                                    style="background: {{ $theme->color_secondary }}"></div>
                                            </div>
                                            @endforeach

                                            <div id="successMessage" class="alert alert-success d-none mt-3"></div>

                                        </div>
                                        <div
                                            class="card-header mt-2 bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                            تم دلخواه خودتان را میتوانید بسازید
                                        </div>

                                        <div class="col-12 col-md-4 mt-2 d-block">
                                            <label>انتخاب نام برای تم </label>
                                            <input type="text" class="form-control mt-1" name="name"
                                                value="{{ old('name', $ghorfeOnlineList->theme->name ?? 'تم سفارشی جدید') }}"
                                                required>
                                        </div>

                                        <div class="col-12 col-md-4 mb-3">
                                            <label class="d-block">رنگ اصلی</label>
                                            {{-- 2. Fixed variable pointing to the theme relationship --}}
                                            <input type="color" id="primaryPicker" name="color_primary"
                                                class="form-control-color w-100"
                                                value="{{ old('color_primary', $ghorfeOnlineList->theme->color_primary ?? '#ff2d20') }}"
                                                oninput="document.getElementById('primaryText').value = this.value">

                                            <input type="text" id="primaryText" class="form-control mt-2 font-mono"
                                                value="{{ old('color_primary', $ghorfeOnlineList->theme->color_primary ?? '#ff2d20') }}">
                                        </div>

                                        <div class="col-12 col-md-4 mb-3">
                                            <label class="d-block">رنگ فرعی</label>
                                            {{-- 3. Live syncing added via inline native JS 'oninput' --}}
                                            <input type="color" id="secondaryPicker" name="color_secondary"
                                                class="form-control-color w-100"
                                                value="{{ old('color_secondary', $ghorfeOnlineList->theme->color_secondary ?? '#6c757d') }}"
                                                oninput="document.getElementById('secondaryText').value = this.value">

                                            <input type="text" id="secondaryText" class="form-control mt-2 font-mono"
                                                value="{{ old('color_secondary', $ghorfeOnlineList->theme->color_secondary ?? '#6c757d') }}">
                                        </div>

                                        <div class="col-12 my-3 d-flex justify-content-end">
                                            <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                        </div>
                                    </div>
                                </div>
                            </form>
                            @endpermission
                        </div>
                        <!-- دکمههای ناوبری -->
                        <button class="btn btn-primary" type="button" onclick="goTo(current-1)">قبلی</button>
                        <button class="btn btn-primary" type="button" onclick="goTo(current+1)">بعدی</button>
                    </div>
                    <div class="tab-pane fade  p-t-20 {{ $activeTab == 'content_manager' ? 'show active' : '' }}"
                        id="content_manager">
                        <ul class="gh-steps">
                            <li class="gh-step active" onclick="goTo(5)"><b>۱</b> سوالات متداول</li>
                            <li class="gh-step" onclick="goTo(6)"><b>۲</b>مدیریت محتوا</li>
                            <li class="gh-step" onclick="goTo(7)"><b>3</b>مدیریت مقالات</li>
                            <li class="gh-step" onclick="goTo(8)"><b>۴</b>تنظیمات سئو</li>
                            <li class="gh-step" onclick="goTo(9)"><b>5</b>شبکه ها اجتماعی</li>
                        </ul>

                        <!-- مرحله ۱ -->
                        <div class="gh-pane active mt-3" id="p5">
                            <form id="question-insert-form"
                                action="{{route('ghorfeOnlineList.question', [$ghorfeOnlineList->slug])}}">
                                <div class="border border-success rounded "
                                    style="margin-top: 20px;padding: 5px;padding-right: 10px">
                                    <input type="hidden" value="insert_question" name="update_type">
                                    <div class="col-12 col-md-12 text-right">
                                        <span class="badge badge-info  " style="margin-top: 20px"> ثبت آیتم جدید
                                            :</span>
                                    </div>

                                    <div class="form-row" style="margin-top: 20px">
                                        <div class="col-12 col-md-5 mb-3">
                                            <input class="form-control" type="text" placeholder="سوال را وارد نمایید !"
                                                name="question" id="question">
                                        </div>
                                        <div class="col-12 col-md-5 mb-3">
                                            <input class="form-control" type="text" placeholder="جواب را وارد نمایید !"
                                                name="answer" id="answer">
                                        </div>
                                        <div class="col-12 col-md-2 mb-3">
                                            <span class="btn btn-success"
                                                id="insert-ghorfe-online-question-submit">ثبت</span>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                            </form>
                            <div class="row">
                                <div class="col-md-12 col-sm-12 col-xs-12 mb-5">
                                    @include('Admin.ghorfeOnlineList.layouts.editAskedQuestion')
                                </div>
                            </div>
                        </div>

                        <!-- مرحله ۲ -->
                        <div class="gh-pane mt-3" id="p6">
                            <div class="col-12 form-control mb-3">
                                <a href="{{ route('category.index') }}"><span>دسته بندی</span></a>
                            </div>
                            @if($ghorfeOnlineList->type_work === 'all')
                            <div class="col-12 form-control mb-3">
                                <a href="{{ route('service.index') }}"><span>خدمات</span></a>
                            </div>
                            <div class="col-12 form-control mb-3">
                                <a href="{{ route('products.index') }}"><span>کالاها</span></a>
                            </div>
                            @elseif($ghorfeOnlineList->type_work === 'product')
                            <div class="col-12 form-control mb-3">
                                <a href="{{ route('products.index') }}"><span>کالاها</span></a>
                            </div>
                            @elseif($ghorfeOnlineList->type_work === 'service')
                            <div class="col-12 form-control mb-3">
                                <a href="{{ route('service.index') }}"><span>خدمات</span></a>
                            </div>
                            @endif
                        </div>

                        <!-- مرحله ۳ -->
                        <div class="gh-pane mt-3" id="p7">
                            <div class="col-12 form-control mb-3">
                                <a href="{{ route('article.index') }}"><span>مدیریت مقالات</span></a>
                            </div>
                        </div>

                        <!-- مرحله ۴ -->
                        <div class="gh-pane mt-3" id="p8">

                        </div>

                        <!-- مرحله ۴ -->
                        <div class="gh-pane mt-3" id="p9">

                        </div>
                        <!-- دکمههای ناوبری -->
                        <button class="btn btn-primary" type="button" onclick="goTo(current-1)">قبلی</button>
                        <button class="btn btn-primary" type="button" onclick="goTo(current+1)">بعدی</button>
                    </div>
                    <div class="tab-pane fade p-t-20 {{ $activeTab == 'banners_manager' ? 'show active' : '' }}"
                        id="banners_manager">
                        <form class="form-horizontal" role="form" method="post"
                            action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="row">

                                @include('Admin.layouts.errors')
                                <div class="col-12 row mt-1">
                                    <div class="col-12">
                                        <div class="card">
                                            <div id="addvertisment_slider_src" class="show">
                                                @if($ghorfeOnlineList->addvertisment_slider)
                                                <img src="{{ $ghorfeOnlineList->addvertisment_slider }}"
                                                    class="card-img-top"
                                                    style="height:88px;width:100%;object-fit:contain;background:#f8f9fa;">
                                                @if($ghorfeOnlineList->mediaLibraryIsLegacy('addvertisment_slider'))
                                                <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به
                                                    سیستم جدید منتقل می‌شود</small>
                                                @endif
                                                @else
                                                <div class="d-flex align-items-center justify-content-center text-muted"
                                                    style="height:88px;background:#f8f9fa;">بدون تصویر</div>
                                                @endif
                                            </div>
                                            <div class="card-body">
                                                <label>اسلایدر تبلیغات(600*1440)</label>
                                                <br>
                                                <label for="addvertisment_slider"
                                                    class="btn btn-primary custom-file-upload border border-1"><i
                                                        class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                                </label>
                                                <input name="addvertisment_slider" type="file" id="addvertisment_slider"
                                                    class="d-none file-upload file_upload_ajax"
                                                    accept=".jpg,.jpeg,.png,.gif">
                                                <input type="hidden" id="addvertisment_slider_path" class="path"
                                                    name="addvertisment_slider_path">
                                            </div>
                                            <div class="col-12 mb-2">
                                                <label>آدرس لینک شده به این اسلایدر</label>
                                                <input type="text"
                                                    value="{{$ghorfeOnlineList->addvertisment_slider_link}}"
                                                    class="form-control" name="addvertisment_slider_link">
                                                <label>مانند نمونه: 'https://www.lalecard.ir/categories'</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                        </form>
                        <form class="form-horizontal" method="post"
                            action="{{ route('ghorfeOnlineList.gallerySave', [$ghorfeOnlineList]) }}"
                            enctype="multipart/form-data">

                            @csrf

                            <div class="row">

                                @include('Admin.layouts.errors')
                                <div class="row">
                                    <div
                                        class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                        برای قسمت بنر(اسلایدر) صفحه اصلی استفاده می شود
                                    </div>
                                    @php
                                    $selectedCategory = old('category', $ghorfeOnlineList->category_id ?? null);
                                    @endphp

                                    <div class="col-12 mb-5">
                                        <label>دسته بندی ها</label>

                                        <select name="category" class="selectMulti form-control">
                                            <option value="">انتخاب دسته بندی</option>

                                            @foreach($categories as $category)
                                            <option value="{{ $category->id }}" @selected($selectedCategory==$category->
                                                id)>
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
                                    <div class="col-12 mb-5">
                                        <select id="serviceSelect" name="service" class="form-control">
                                            <option value="">انتخاب خدمات</option>

                                            @foreach($ghorfeOnlineList->services as $service)
                                            <option value="{{ $service->id }}" @selected($selectedService==$service->
                                                id)>
                                                {{ $service->title }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div id="gallerySection">
                                        <x-gallery-form :galleries="$ghorfeOnlineList->galleries"
                                            :ghorfeTemplet="$ghorfeOnlineList->template" />
                                    </div>
                                </div>

                                {{-- ================= SUBMIT ================= --}}
                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade p-t-20 {{ $activeTab == 'settings_mali' ? 'show active' : '' }}"
                        id="settings_mali" role="tabpanel" aria-labelledby="image-content-tab">
                        @permission('ghorfe_online_list_site_content')
                        <form action="{{ route('settings.updateCompanyInfo') }}" method="POST" class="mb-2">
                            @csrf
                            @method('PATCH')
                            <div class="row ">
                                <div
                                    class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0 ">
                                    اطلاعات حساب مالیاتی</div>
                                <div class="col-md-4 form-group">
                                    <label for="shaba_number">شماره شبا (IR0000000000000000)</label>
                                    <input type="text" name="shaba_number" class="form-control"
                                        value="{{$company_info['shaba_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="card_number">شماره کارت</label>
                                    <input type="text" name="card_number" class="form-control"
                                        value="{{$company_info['card_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="card_holder">نام کامل صاحب حساب</label>
                                    <input type="text" name="card_holder" class="form-control"
                                        value="{{$company_info['card_holder'] ?? ''}}">
                                </div>
                                <div
                                    class="card-header bg-dark-gradient col-md-12 border  border-right-0 border-left-0  ">
                                    اطلاعات حساب معمولی</div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_shaba_number">شماره شبا (IR0000000000000000)</label>
                                    <input type="text" name="tax_free_shaba_number" class="form-control"
                                        value="{{$company_info['tax_free_shaba_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_card_number">شماره کارت</label>
                                    <input type="text" name="tax_free_card_number" class="form-control"
                                        value="{{$company_info['tax_free_card_number'] ?? ''}}">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="tax_free_card_holder">نام کامل صاحب حساب</label>
                                    <input type="text" name="tax_free_card_holder" class="form-control"
                                        value="{{$company_info['tax_free_card_holder'] ?? ''}}">
                                </div>

                                @permission('ghorfe_online_list_sms_panel')
                                <div class="col-md-4 form-group">
                                    <label for="fullName">پنل پیامکی</label>
                                    <select name="sms_panel" class="form-control select2">
                                        <option value="iranSms" {{ old('sms_panel', $company_info['sms_panel'] ?? ''
                                            )=='iranSms' ? 'selected' : '' }}>
                                            ایران اس ام اس
                                        </option>

                                        <option value="neginSms" {{ old('sms_panel', $company_info['sms_panel'] ?? ''
                                            )=='neginSms' ? 'selected' : '' }}>
                                            نگین اس ام اس
                                        </option>
                                    </select>
                                </div>
                                @endpermission
                                @permission('ghorfe_online_list_sms_apikey')
                                <div class="col-md-4 form-group">
                                    <label for="fullName">iransms apikey</label>
                                    <input type="text" name="iranSms_apikey" class="form-control"
                                        value="{{$company_info['iranSms_apikey'] ?? ''}}">
                                </div>
                                @endpermission

                                @permission('ghorfe_online_list_sms_linenumber')
                                <div class="col-md-4 form-group">
                                    <label for="fullName">iransms linenumber</label>
                                    <input type="text" name="iranSms_linenumber" class="form-control"
                                        value="{{$company_info['iranSms_linenumber'] ?? ''}}">
                                </div>
                                @endpermission

                                <div class="col-12 my-3 d-flex justify-content-end">
                                    <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                                </div>
                            </div>
                        </form>

                        @endpermission
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
<script src="{{ asset('/assets/dashboard/js/ghorfeOnlineUpdate.js') }}"></script>

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
    let current = 1;
function goTo(n){
  n = Math.max(1, Math.min(9, n));
  current = n;
  document.querySelectorAll('.gh-pane').forEach(p => p.classList.remove('active'));
  document.getElementById('p'+n).classList.add('active');
  document.querySelectorAll('.gh-step').forEach(s => {
    s.classList.remove('active');
    if(+s.getAttribute('data-s') === n) s.classList.add('active');
  });
}
</script>

@endsection