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
                        <ul class="gh-steps" data-start="1">
                            <li class="gh-step active" onclick="goTo(1)">
                                <b>۱</b> اطلاعات
                            </li>

                            <li class="gh-step" onclick="goTo(2)">
                                <b>۲</b> لوگو
                            </li>

                            <li class="gh-step" onclick="goTo(3)">
                                <b>۳</b> منو
                            </li>

                            <li class="gh-step" onclick="goTo(4)">
                                <b>۴</b> تم
                            </li>
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

                                    <div class="col-12">

                                        <div class="card shadow-sm border-0 mb-4">

                                            {{-- Header --}}
                                            <div class="card-header bg-dark text-white py-3">
                                                <h6 class="mb-0 font-weight-bold">
                                                    <i class="fa fa-building ml-2"></i>
                                                    اطلاعات کسب‌وکار
                                                </h6>
                                            </div>

                                            <div class="card-body">

                                                <div class="row">

                                                    {{-- عنوان --}}
                                                    <div class="col-12 col-md-6 mb-4">

                                                        <label class="font-weight-bold">
                                                            عنوان فارسی
                                                            <small class="text-muted font-weight-normal">
                                                                (این عنوان در سایت نمایش داده می‌شود)
                                                            </small>
                                                        </label>

                                                        <div class="input-group">

                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">
                                                                    <i class="fa fa-building"></i>
                                                                </span>
                                                            </div>

                                                            <input type="text" value="{{ $ghorfeOnlineList->title }}"
                                                                class="form-control" name="title"
                                                                placeholder="عنوان کسب‌وکار را وارد کنید" required>

                                                        </div>

                                                    </div>


                                                    {{-- ایمیل --}}
                                                    <div class="col-12 col-md-6 mb-4">

                                                        <label class="font-weight-bold">
                                                            ایمیل
                                                        </label>

                                                        <div class="input-group">

                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">
                                                                    <i class="fa fa-envelope"></i>
                                                                </span>
                                                            </div>

                                                            <input type="email" value="{{ $ghorfeOnlineList->email }}"
                                                                class="form-control" name="email"
                                                                placeholder="example@email.com">

                                                        </div>

                                                    </div>


                                                    {{-- آدرس --}}
                                                    <div class="col-12 col-md-6 mb-4">

                                                        <label class="font-weight-bold">
                                                            آدرس
                                                        </label>

                                                        <div class="input-group">

                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">
                                                                    <i class="fa fa-map-marker"></i>
                                                                </span>
                                                            </div>

                                                            <input type="text" name="address" class="form-control"
                                                                value="{{ $company_info['address'] ?? '' }}"
                                                                placeholder="آدرس کسب‌وکار را وارد کنید">

                                                        </div>

                                                    </div>


                                                    {{-- تلفن --}}
                                                    <div class="col-12 col-md-6 mb-4">

                                                        <label class="font-weight-bold">
                                                            تلفن
                                                        </label>

                                                        <div class="input-group">

                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">
                                                                    <i class="fa fa-phone"></i>
                                                                </span>
                                                            </div>

                                                            <input type="text" name="phone" class="form-control"
                                                                value="{{ $company_info['phone'] ?? '' }}"
                                                                placeholder="شماره تماس کسب‌وکار">

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- دکمه ثبت --}}
                                    <div class="col-12 d-flex justify-content-end mb-3">

                                        <button type="submit" class="btn btn-success px-5 py-2 shadow-sm">
                                            ثبت تغییرات
                                        </button>

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

                                    <div class="col-12">

                                        <div class="card shadow-sm border-0 mb-4">

                                            {{-- Header --}}
                                            <div class="card-header bg-dark text-white py-3">
                                                <h6 class="mb-0 font-weight-bold">
                                                    <i class="fa fa-image ml-2"></i>
                                                    تصویر و لوگوی کسب‌وکار
                                                </h6>
                                            </div>

                                            <div class="card-body">

                                                <div class="row">

                                                    {{-- Logo --}}
                                                    <div class="col-12 col-md-6 mx-auto">

                                                        <div class="logo-upload-card">

                                                            {{-- Preview --}}
                                                            <div id="logo_src" class="logo-preview">

                                                                @if($ghorfeOnlineList->logo)

                                                                <img src="{{ $ghorfeOnlineList->logo }}"
                                                                    alt="لوگوی کسب‌وکار" class="logo-preview-image"
                                                                    width="100%">

                                                                @if($ghorfeOnlineList->mediaLibraryIsLegacy('logo'))

                                                                <div class="legacy-alert">
                                                                    <i class="fa fa-info-circle ml-1"></i>
                                                                    تصویر قدیمی است؛ با ذخیره به سیستم جدید منتقل
                                                                    می‌شود.
                                                                </div>

                                                                @endif

                                                                @else

                                                                <div class="logo-empty">

                                                                    <i class="fa fa-image"></i>

                                                                    <span>
                                                                        بدون تصویر
                                                                    </span>

                                                                </div>

                                                                @endif

                                                            </div>

                                                            {{-- Upload --}}
                                                            <div class="p-4">

                                                                <div class="text-center mb-3">

                                                                    <h6 class="font-weight-bold mb-1">
                                                                        لوگوی کسب‌وکار
                                                                    </h6>

                                                                    <small class="text-muted">
                                                                        فرمت‌های مجاز: JPG، JPEG، PNG
                                                                    </small>

                                                                </div>

                                                                <div class="text-center">

                                                                    <label for="logo"
                                                                        class="btn btn-primary px-4 py-2 upload-btn">

                                                                        <i class="fa fa-upload ml-2"></i>
                                                                        انتخاب تصویر

                                                                    </label>

                                                                    <input name="logo" type="file" id="logo"
                                                                        class="d-none file-upload file_upload_ajax"
                                                                        accept=".jpg,.jpeg,.png">

                                                                    <input type="hidden" id="logo_path" class="path"
                                                                        name="logo_path">

                                                                </div>

                                                                <div id="logo-file-name"
                                                                    class="text-center text-muted small mt-3">
                                                                </div>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- Submit --}}
                                    <div class="col-12 my-3 d-flex justify-content-end">

                                        <button type="submit" class="btn btn-success px-5 py-2 shadow-sm">
                                            ثبت تغییرات
                                        </button>

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
                                <div id="menu-wrapper" class="container-fluid">

                                    @include('Admin.layouts.errors')

                                    {{-- اگر قالب قبلاً انتخاب شده --}}
                                    @if($ghorfeOnlineList->template)

                                    <div class="card shadow-sm border-0 mb-4">
                                        <div class="card-header bg-dark text-white py-3">
                                            <h6 class="mb-0 font-weight-bold">
                                                <i class="fa fa-bars ml-2"></i>
                                                منو دلخواه را انتخاب کنید
                                            </h6>
                                        </div>

                                        <div class="card-body">
                                            <div class="row">

                                                @foreach($ghorfeOnlineList->menus as $index => $menu)

                                                <input type="hidden" name="ids[]" value="{{ $menu->id }}">

                                                <div class="col-12 col-md-3 mb-4">

                                                    <div class="card h-100 border shadow-sm">

                                                        <div class="card-body">

                                                            {{-- عنوان منو --}}
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold">
                                                                    عنوان منو
                                                                </label>

                                                                <input type="text" name="titles[]"
                                                                    value="{{ $menu->title }}" class="form-control"
                                                                    required>
                                                            </div>

                                                            {{-- وضعیت --}}
                                                            <div
                                                                class="d-flex align-items-center justify-content-between">

                                                                <span class="font-weight-bold">
                                                                    وضعیت
                                                                </span>

                                                                <label class="switch-status mb-0">
                                                                    <input type="checkbox" name="actives[{{ $index }}]"
                                                                        value="1" {{ $menu->is_active ? 'checked' : ''
                                                                    }}>

                                                                    <span class="slider round"></span>
                                                                </label>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                                @endforeach

                                            </div>
                                        </div>
                                    </div>


                                    {{-- اگر قالب انتخاب نشده --}}
                                    @else

                                    <div class="card shadow-sm border-0 mb-4">

                                        <div class="card-header bg-dark text-white py-3">
                                            <h6 class="mb-0 font-weight-bold">
                                                <i class="fa fa-paint-brush ml-2"></i>
                                                ابتدا قالب مورد نظر را انتخاب نمایید
                                            </h6>
                                        </div>

                                        <div class="card-body">

                                            @if(isset($templates) && count($templates))

                                            <div class="row">

                                                @foreach($templates as $template)

                                                @if($template->active)

                                                <div class="col-12 col-md-4 col-lg-4 mb-4">

                                                    <label class="template-label w-100 h-100 mb-0">

                                                        <input type="radio" name="template_id"
                                                            value="{{ $template->id }}" class="d-none template-radio"
                                                            id="template_{{ $template->id }}" {{ $template->id ?
                                                        'checked' : '' }}>

                                                        <div
                                                            class="template-card h-100 p-4 text-center border rounded shadow-sm">

                                                            <div class="mb-3">

                                                                <img src="{{ asset('img/upload.jpg') }}"
                                                                    class="img-fluid rounded"
                                                                    style="height: 70px; width: 100px; object-fit: cover;">

                                                            </div>

                                                            <h5 class="font-weight-bold mb-2">
                                                                {{ $template->name }}
                                                            </h5>

                                                            <span class="badge badge-primary px-3 py-2">
                                                                {{ $template->type }}
                                                            </span>

                                                            @if($template->menus)

                                                            <div class="text-right mt-4">

                                                                <div class="font-weight-bold mb-2">
                                                                    منوهای قالب:
                                                                </div>

                                                                <ul class="list-unstyled small text-muted mb-0">

                                                                    @foreach($template->menus as $menu)

                                                                    <li class="mb-2">
                                                                        <i class="fa fa-check text-success ml-1"></i>
                                                                        {{ $menu->title }}
                                                                    </li>

                                                                    @endforeach

                                                                </ul>

                                                            </div>

                                                            @endif

                                                        </div>

                                                    </label>

                                                </div>

                                                @endif

                                                @endforeach

                                            </div>

                                            @else

                                            <div class="alert alert-warning text-center mb-0">
                                                در حال حاضر قالب فعالی برای انتخاب وجود ندارد.
                                            </div>

                                            @endif

                                        </div>

                                    </div>

                                    @endif


                                    {{-- دکمه ثبت --}}
                                    <div class="row">

                                        <div class="col-12 d-flex justify-content-end">

                                            <button type="submit" class="btn btn-success px-5 py-2 shadow-sm">
                                                ثبت تغییرات
                                            </button>

                                        </div>

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

                                    <div class="col-12">

                                        {{-- =========================
                                        تم‌های آماده
                                        ========================== --}}
                                        <div class="card shadow-sm border-0 mb-4">

                                            <div class="card-header bg-dark text-white py-3">
                                                <h6 class="mb-0 font-weight-bold">
                                                    <i class="fa fa-palette ml-2"></i>
                                                    انتخاب تم آماده
                                                </h6>
                                            </div>

                                            <div class="card-body">

                                                <p class="text-muted small mb-4">
                                                    از بین تم‌های پیش‌فرض یکی را انتخاب کنید یا در بخش پایین
                                                    تم دلخواه خودتان را بسازید.
                                                </p>

                                                <div class="row">

                                                    @foreach($themes as $theme)

                                                    <div class="col-6 col-md-3 col-lg-2 mb-4">

                                                        <div class="theme-box" data-theme-id="{{ $theme->id }}"
                                                            data-primary="{{ $theme->color_primary }}"
                                                            data-secondary="{{ $theme->color_secondary }}">

                                                            <div class="theme-preview">

                                                                <div class="theme-color-primary"
                                                                    style="background: {{ $theme->color_primary }}">
                                                                </div>

                                                                <div class="theme-color-secondary"
                                                                    style="background: {{ $theme->color_secondary }}">
                                                                </div>

                                                            </div>

                                                            <div class="theme-info">
                                                                <span>
                                                                    تم {{ $theme->id }}
                                                                </span>
                                                            </div>

                                                        </div>

                                                    </div>

                                                    @endforeach

                                                </div>

                                                <div id="successMessage" class="alert alert-success d-none mt-2 mb-0">
                                                </div>

                                            </div>

                                        </div>


                                        {{-- =========================
                                        ساخت تم سفارشی
                                        ========================== --}}
                                        <div class="card shadow-sm border-0 mb-4">

                                            <div class="card-header bg-dark text-white py-3">

                                                <h6 class="mb-0 font-weight-bold">
                                                    <i class="fa fa-paint-brush ml-2"></i>
                                                    ساخت تم سفارشی
                                                </h6>

                                            </div>

                                            <div class="card-body">

                                                <p class="text-muted small mb-4">
                                                    نام و رنگ‌های موردنظر خود را انتخاب کنید.
                                                </p>

                                                <div class="row">

                                                    {{-- نام تم --}}
                                                    <div class="col-12 col-md-4 mb-4">

                                                        <label class="font-weight-bold">
                                                            نام تم
                                                        </label>

                                                        <div class="input-group">

                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">
                                                                    <i class="fa fa-tag"></i>
                                                                </span>
                                                            </div>

                                                            <input type="text" class="form-control" name="name"
                                                                value="{{ old('name', $ghorfeOnlineList->theme->name ?? 'تم سفارشی جدید') }}"
                                                                placeholder="مثلاً تم آبی" required>

                                                        </div>

                                                    </div>


                                                    {{-- رنگ اصلی --}}
                                                    <div class="col-12 col-md-4 mb-4">

                                                        <label class="font-weight-bold">
                                                            رنگ اصلی
                                                        </label>

                                                        <div class="color-picker-wrapper">

                                                            <input type="color" id="primaryPicker" name="color_primary"
                                                                class="color-picker"
                                                                value="{{ old('color_primary', $ghorfeOnlineList->theme->color_primary ?? '#ff2d20') }}">

                                                            <input type="text" id="primaryText"
                                                                class="form-control font-mono mt-2"
                                                                value="{{ old('color_primary', $ghorfeOnlineList->theme->color_primary ?? '#ff2d20') }}"
                                                                maxlength="7">

                                                        </div>

                                                    </div>


                                                    {{-- رنگ فرعی --}}
                                                    <div class="col-12 col-md-4 mb-4">

                                                        <label class="font-weight-bold">
                                                            رنگ فرعی
                                                        </label>

                                                        <div class="color-picker-wrapper">

                                                            <input type="color" id="secondaryPicker"
                                                                name="color_secondary" class="color-picker"
                                                                value="{{ old('color_secondary', $ghorfeOnlineList->theme->color_secondary ?? '#6c757d') }}">

                                                            <input type="text" id="secondaryText"
                                                                class="form-control font-mono mt-2"
                                                                value="{{ old('color_secondary', $ghorfeOnlineList->theme->color_secondary ?? '#6c757d') }}"
                                                                maxlength="7">

                                                        </div>

                                                    </div>

                                                </div>


                                                {{-- Preview --}}
                                                <div class="theme-live-preview mt-2">

                                                    <div class="preview-title">
                                                        پیش‌نمایش تم
                                                    </div>

                                                    <div class="preview-content">

                                                        <div id="previewPrimary" class="preview-primary">
                                                            رنگ اصلی
                                                        </div>

                                                        <div id="previewSecondary" class="preview-secondary">
                                                            رنگ فرعی
                                                        </div>

                                                    </div>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- ثبت --}}
                                    <div class="col-12 d-flex justify-content-end mb-3">

                                        <button type="submit" class="btn btn-success px-5 py-2 shadow-sm">
                                            ثبت تغییرات
                                        </button>

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
                        <ul class="gh-steps" data-start="5">
                            <li class="gh-step active" onclick="goTo(5)">
                                <b>۱</b> سوالات متداول
                            </li>

                            <li class="gh-step" onclick="goTo(6)">
                                <b>۲</b> مدیریت محتوا
                            </li>

                            <li class="gh-step" onclick="goTo(7)">
                                <b>۳</b> مدیریت مقالات
                            </li>

                            <li class="gh-step" onclick="goTo(8)">
                                <b>۴</b> تنظیمات سئو
                            </li>

                            <li class="gh-step" onclick="goTo(9)">
                                <b>۵</b> شبکه‌های اجتماعی
                            </li>
                        </ul>
                        <!-- مرحله ۱ -->
                        <div class="gh-pane active mt-3" id="p5">
                            <form id="question-insert-form"
                                action="{{route('ghorfeOnlineList.question', [$ghorfeOnlineList->slug])}}">
                                <div class="card border-0 shadow-sm mb-4">

                                    <div class="card-header bg-dark text-white py-3">
                                        <h6 class="mb-0 font-weight-bold">
                                            <i class="fa fa-plus-circle ml-2"></i>
                                            ثبت آیتم جدید
                                        </h6>
                                    </div>

                                    <div class="card-body">

                                        <input type="hidden" value="insert_question" name="update_type">

                                        <div class="form-row">

                                            {{-- سوال --}}
                                            <div class="col-12 col-md-5 mb-3">

                                                <label class="font-weight-bold">
                                                    سوال
                                                </label>

                                                <div class="input-group">

                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-question-circle"></i>
                                                        </span>
                                                    </div>

                                                    <input class="form-control" type="text"
                                                        placeholder="سوال را وارد نمایید" name="question" id="question">

                                                </div>

                                            </div>


                                            {{-- جواب --}}
                                            <div class="col-12 col-md-5 mb-3">

                                                <label class="font-weight-bold">
                                                    جواب
                                                </label>

                                                <div class="input-group">

                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-comment"></i>
                                                        </span>
                                                    </div>

                                                    <input class="form-control" type="text"
                                                        placeholder="جواب را وارد نمایید" name="answer" id="answer">

                                                </div>

                                            </div>


                                            {{-- ثبت --}}
                                            <div class="col-12 col-md-2 mb-3 d-flex align-items-end">

                                                <button type="button" class="btn btn-success w-100"
                                                    id="insert-ghorfe-online-question-submit">

                                                    <i class="fa fa-plus ml-1"></i>
                                                    ثبت

                                                </button>

                                            </div>

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
                            <div class="card shadow-sm border-0 mb-3">
                                <div class="card-header bg-dark text-white py-3">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fa fa-plus-circle ml-2"></i>
                                        ایجاد کالا | خدمات
                                    </h6>
                                </div>

                                <div class="card-body text-center">
                                    <div class="mb-3 text-muted"> <i class="fa fa-magic fa-2x"></i> </div>
                                    <h6 class="font-weight-bold mb-2"> ایجاد کالا یا خدمت </h6>
                                    <p class="text-muted small mb-3"> برای ایجاد کالا یا خدمات جدید از ویزارد
                                        استفاده کنید. </p>
                                    <a href="{{ route('productWizard.index') }}" class="btn btn-primary px-4 py-2">
                                        <span class="ml-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="12" y1="5" x2="12" y2="19"> </line>
                                                <line x1="5" y1="12" x2="19" y2="12"> </line>
                                            </svg> </span> ویزارد ایجاد کالا | خدمات </a>
                                </div>
                            </div>
                        </div>

                        <!-- مرحله ۳ -->
                        <div class="gh-pane mt-3" id="p7">
                            <div class="card shadow-sm border-0 mb-3">

                                <div class="card-header bg-dark text-white py-3">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fa fa-plus-circle ml-2"></i>
                                        ایجاد مقاله
                                    </h6>
                                </div>

                                <div class="card-body text-center">

                                    <div class="mb-3 text-muted">
                                        <i class="fa fa-file-text-o fa-2x"></i>
                                    </div>

                                    <h6 class="font-weight-bold mb-2">
                                        ایجاد مقاله جدید
                                    </h6>

                                    <p class="text-muted small mb-3">
                                        برای ایجاد مقاله جدید از گزینه زیر استفاده کنید.
                                    </p>

                                    <a href="{{ route('article.index') }}" class="btn btn-primary px-4 py-2">

                                        <span class="ml-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round">

                                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                                <line x1="5" y1="12" x2="19" y2="12"></line>

                                            </svg>
                                        </span>

                                        ایجاد مقاله
                                    </a>

                                </div>
                            </div>
                        </div>

                        <!-- مرحله ۴ -->
                        <div class="gh-pane mt-3" id="p8">
                            <form class="form-horizontal" role="form" method="post"
                                action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card shadow-sm border-0 mb-4">

                                    {{-- Header --}}
                                    <div class="card-header bg-dark text-white py-3">
                                        <h6 class="mb-0 font-weight-bold">
                                            <i class="fa fa-search ml-2"></i>
                                            تنظیمات سئو
                                        </h6>
                                    </div>

                                    <div class="card-body">

                                        @include('Admin.layouts.errors')

                                        <div class="row">

                                            {{-- عنوان سئو --}}
                                            <div class="col-12 mb-4">
                                                <label class="font-weight-bold">
                                                    عنوان مغازه
                                                    <small class="text-muted font-weight-normal">
                                                        (موثر در سئو سایت)
                                                    </small>
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-tag"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" value="{{ $ghorfeOnlineList->index_title }}"
                                                        class="form-control" name="index_title"
                                                        placeholder="عنوان مناسب برای سئو را وارد کنید" required>
                                                </div>
                                            </div>

                                            {{-- توضیحات سئو --}}
                                            <div class="col-12 mb-4">
                                                <label class="font-weight-bold">
                                                    توضیحات مربوط به فعالیت کاری
                                                    <small class="text-muted font-weight-normal">
                                                        (موثر در سئو سایت)
                                                    </small>
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text align-items-start pt-3">
                                                            <i class="fa fa-align-right"></i>
                                                        </span>
                                                    </div>

                                                    <textarea class="form-control" name="index_description" rows="6"
                                                        placeholder="توضیحات مربوط به فعالیت کاری را وارد کنید">{{ $ghorfeOnlineList->index_description }}</textarea>
                                                </div>
                                            </div>

                                            {{-- کلمات کلیدی --}}
                                            <div class="col-12 mb-3">
                                                <label class="font-weight-bold">
                                                    کلمات کلیدی مربوط به حوزه کاری مغازه
                                                    <small class="text-muted font-weight-normal">
                                                        (موثر در سئو سایت)
                                                    </small>
                                                </label>

                                                <small class="d-block text-muted mb-2">
                                                    کلمات را با , از هم جدا کنید.
                                                </small>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text align-items-start pt-3">
                                                            <i class="fa fa-key"></i>
                                                        </span>
                                                    </div>

                                                    <textarea class="form-control" name="footer_tag" rows="4"
                                                        placeholder="مثال: فروشگاه اینترنتی, خرید آنلاین, خدمات رفاهی">{{ $ghorfeOnlineList->footer_tag ? implode(',' , $ghorfeOnlineList->footer_tag) : '' }}</textarea>
                                                </div>
                                            </div>

                                        </div>

                                        {{-- دکمه ثبت --}}
                                        <div class="d-flex justify-content-end mt-3">
                                            <input type="submit" class="btn btn-success border-0 px-5 py-2 shadow-sm"
                                                value="ثبت تغییرات">
                                        </div>

                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- مرحله ۴ -->
                        <div class="gh-pane mt-3" id="p9">
                            <form class="form-horizontal" role="form" method="post"
                                action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="card shadow-sm border-0 mb-3">

                                    <div class="card-header bg-dark text-white py-3">
                                        <h6 class="mb-0 font-weight-bold">
                                            <i class="fa fa-share-alt ml-2"></i>
                                            لینک شبکه های اجتماعی
                                        </h6>
                                    </div>

                                    <div class="card-body">

                                        @include('Admin.layouts.errors')

                                        <div class="row">

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    آدرس اینستاگرام
                                                </label>

                                                <input type="text" value="{{$ghorfeOnlineList->instagram}}"
                                                    class="form-control" name="instagram">

                                                <small class="form-text text-muted">
                                                    مانند نمونه:
                                                    <span dir="ltr">https://www.instagram.com/lalecard.ir</span>
                                                </small>
                                            </div>

                                            <div class="col-12 col-md-6 mb-3">
                                                <label class="font-weight-bold">
                                                    آدرس تلگرام
                                                </label>

                                                <input type="text" value="{{$ghorfeOnlineList->telegram}}"
                                                    class="form-control" name="telegram">

                                                <small class="form-text text-muted">
                                                    مانند نمونه:
                                                    <span dir="ltr">https://t.me/lalecard_channel</span>
                                                </small>
                                            </div>

                                        </div>

                                        <div class="d-flex justify-content-end mt-3">
                                            <input type="submit" class="btn btn-success border-0 px-5 py-2 shadow-sm"
                                                value="ثبت تغییرات">
                                        </div>

                                    </div>
                                </div>
                            </form>
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
                            <div class="card shadow-sm border-0 mb-4">

                                {{-- Header --}}
                                <div class="card-header bg-dark text-white py-3">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fa fa-picture-o ml-2"></i>
                                        اسلایدر تبلیغات
                                    </h6>
                                </div>

                                <div class="card-body">

                                    @include('Admin.layouts.errors')

                                    <div class="row">

                                        {{-- تصویر اسلایدر --}}
                                        <div class="col-12 mb-4">

                                            <label class="font-weight-bold d-block mb-3">
                                                اسلایدر تبلیغات
                                                <small class="text-muted font-weight-normal">
                                                    (600 × 1440)
                                                </small>
                                            </label>

                                            <div class="border rounded bg-light p-2 text-center">

                                                <div id="addvertisment_slider_src" class="show">

                                                    @if($ghorfeOnlineList->addvertisment_slider)

                                                    <img src="{{ $ghorfeOnlineList->addvertisment_slider }}"
                                                        class="img-fluid rounded"
                                                        style="height:160px;width:100%;object-fit:contain;background:#f8f9fa;">

                                                    @if($ghorfeOnlineList->mediaLibraryIsLegacy('addvertisment_slider'))

                                                    <small class="d-block text-warning mt-2">
                                                        <i class="fa fa-warning ml-1"></i>
                                                        تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود
                                                    </small>

                                                    @endif

                                                    @else

                                                    <div class="d-flex align-items-center justify-content-center text-muted"
                                                        style="height:160px;background:#f8f9fa;">

                                                        <div>
                                                            <i class="fa fa-image fa-2x d-block mb-2"></i>
                                                            بدون تصویر
                                                        </div>

                                                    </div>

                                                    @endif

                                                </div>

                                            </div>

                                        </div>


                                        {{-- انتخاب تصویر --}}
                                        <div class="col-12 col-md-6 mb-4">

                                            <label class="font-weight-bold">
                                                انتخاب تصویر
                                            </label>

                                            <div class="input-group">

                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-upload"></i>
                                                    </span>
                                                </div>

                                                <label for="addvertisment_slider"
                                                    class="btn btn-primary mb-0 d-flex align-items-center px-4">

                                                    انتخاب تصویر

                                                </label>

                                            </div>

                                            <input name="addvertisment_slider" type="file" id="addvertisment_slider"
                                                class="d-none file-upload file_upload_ajax"
                                                accept=".jpg,.jpeg,.png,.gif">

                                            <input type="hidden" id="addvertisment_slider_path" class="path"
                                                name="addvertisment_slider_path">

                                            <small class="form-text text-muted">
                                                فرمت‌های مجاز: JPG، JPEG، PNG، GIF
                                            </small>

                                        </div>


                                        {{-- لینک اسلایدر --}}
                                        <div class="col-12 col-md-6 mb-4">

                                            <label class="font-weight-bold">
                                                آدرس لینک شده به این اسلایدر
                                            </label>

                                            <div class="input-group">

                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-link"></i>
                                                    </span>
                                                </div>

                                                <input type="text"
                                                    value="{{$ghorfeOnlineList->addvertisment_slider_link}}"
                                                    class="form-control" name="addvertisment_slider_link"
                                                    placeholder="https://www.lalecard.ir/categories">

                                            </div>

                                            <small class="form-text text-muted">
                                                مانند نمونه:
                                                <span dir="ltr">https://www.lalecard.ir/categories</span>
                                            </small>

                                        </div>

                                    </div>


                                    {{-- ثبت تغییرات --}}
                                    <div class="d-flex justify-content-end mt-3">

                                        <input type="submit" class="btn btn-success border-0 px-5 py-2 shadow-sm"
                                            value="ثبت تغییرات">

                                    </div>

                                </div>
                            </div>
                        </form>
                        <form class="form-horizontal" method="post"
                            action="{{ route('ghorfeOnlineList.gallerySave', [$ghorfeOnlineList]) }}"
                            enctype="multipart/form-data">

                            @csrf

                            <div class="card shadow-sm border-0 mb-4">

                                {{-- Header --}}
                                <div class="card-header bg-dark text-white py-3">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fa fa-picture-o ml-2"></i>
                                        تنظیمات بنر و اسلایدر صفحه اصلی
                                    </h6>
                                </div>

                                <div class="card-body">

                                    @include('Admin.layouts.errors')

                                    <div class="row">

                                        {{-- توضیحات --}}
                                        <div class="col-12 mb-4">
                                            <div class="alert alert-light border mb-0">
                                                <i class="fa fa-info-circle text-primary ml-2"></i>
                                                برای قسمت بنر (اسلایدر) صفحه اصلی استفاده می‌شود.
                                            </div>
                                        </div>

                                        {{-- دسته بندی --}}
                                        @php
                                        $selectedCategory = old('category', $ghorfeOnlineList->category_id ?? null);
                                        @endphp

                                        <div class="col-12 col-md-6 mb-4">

                                            <label class="font-weight-bold">
                                                دسته‌بندی‌ها
                                            </label>

                                            <div class="input-group">

                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-folder"></i>
                                                    </span>
                                                </div>

                                                <select name="category" class="selectMulti form-control">

                                                    <option value="">
                                                        انتخاب دسته‌بندی
                                                    </option>

                                                    @foreach($categories as $category)

                                                    <option value="{{ $category->id }}"
                                                        @selected($selectedCategory==$category->id)>

                                                        {{ $category->title }}

                                                    </option>

                                                    @endforeach

                                                </select>

                                            </div>

                                            @error('category')
                                            <span class="help-block sub-error">
                                                {{ $message }}
                                            </span>
                                            @enderror

                                        </div>


                                        {{-- خدمات --}}
                                        @php
                                        $selectedService = old('service', $ghorfeOnlineList->service_id ?? null);
                                        @endphp

                                        <div class="col-12 col-md-6 mb-4">

                                            <label class="font-weight-bold">
                                                خدمات
                                            </label>

                                            <div class="input-group">

                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fa fa-cogs"></i>
                                                    </span>
                                                </div>

                                                <select id="serviceSelect" name="service" class="form-control">

                                                    <option value="">
                                                        انتخاب خدمات
                                                    </option>

                                                    @foreach($ghorfeOnlineList->services as $service)

                                                    <option value="{{ $service->id }}"
                                                        @selected($selectedService==$service->id)>

                                                        {{ $service->title }}

                                                    </option>

                                                    @endforeach

                                                </select>

                                            </div>

                                        </div>


                                        {{-- گالری --}}
                                        <div class="col-12 mb-4">

                                            <label class="font-weight-bold d-block mb-3">
                                                تصاویر گالری
                                            </label>

                                            <div id="gallerySection">
                                                <x-gallery-form :galleries="$ghorfeOnlineList->galleries"
                                                    :ghorfeTemplet="$ghorfeOnlineList->template" />
                                            </div>

                                        </div>

                                    </div>


                                    {{-- ثبت تغییرات --}}
                                    <div class="d-flex justify-content-end mt-3">

                                        <input type="submit" class="btn btn-success border-0 px-5 py-2 shadow-sm"
                                            value="ثبت تغییرات">

                                    </div>

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
                            <div class="card shadow-sm border-0 mb-4">

                                {{-- Header --}}
                                <div class="card-header bg-dark text-white py-3">
                                    <h6 class="mb-0 font-weight-bold">
                                        <i class="fa fa-credit-card ml-2"></i>
                                        اطلاعات حساب‌های مالی
                                    </h6>
                                </div>

                                <div class="card-body">

                                    @include('Admin.layouts.errors')

                                    {{-- حساب مالیاتی --}}
                                    <div class="border-bottom pb-3 mb-4">

                                        <h6 class="font-weight-bold text-dark mb-4">
                                            <i class="fa fa-university ml-2"></i>
                                            اطلاعات حساب مالیاتی
                                        </h6>

                                        <div class="row">

                                            {{-- شماره شبا --}}
                                            <div class="col-12 col-md-4 mb-3">
                                                <label class="font-weight-bold">
                                                    شماره شبا
                                                    <small class="text-muted font-weight-normal">
                                                        (IR0000000000000000)
                                                    </small>
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-bank"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="shaba_number" class="form-control"
                                                        value="{{$company_info['shaba_number'] ?? ''}}">
                                                </div>
                                            </div>

                                            {{-- شماره کارت --}}
                                            <div class="col-12 col-md-4 mb-3">
                                                <label class="font-weight-bold">
                                                    شماره کارت
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-credit-card"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="card_number" class="form-control"
                                                        value="{{$company_info['card_number'] ?? ''}}">
                                                </div>
                                            </div>

                                            {{-- صاحب حساب --}}
                                            <div class="col-12 col-md-4 mb-3">
                                                <label class="font-weight-bold">
                                                    نام کامل صاحب حساب
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-user"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="card_holder" class="form-control"
                                                        value="{{$company_info['card_holder'] ?? ''}}">
                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                    {{-- حساب معمولی --}}
                                    <div class="border-bottom pb-3 mb-4">

                                        <h6 class="font-weight-bold text-dark mb-4">
                                            <i class="fa fa-money ml-2"></i>
                                            اطلاعات حساب معمولی
                                        </h6>

                                        <div class="row">

                                            {{-- شماره شبا --}}
                                            <div class="col-12 col-md-4 mb-3">
                                                <label class="font-weight-bold">
                                                    شماره شبا
                                                    <small class="text-muted font-weight-normal">
                                                        (IR0000000000000000)
                                                    </small>
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-bank"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="tax_free_shaba_number" class="form-control"
                                                        value="{{$company_info['tax_free_shaba_number'] ?? ''}}">
                                                </div>
                                            </div>

                                            {{-- شماره کارت --}}
                                            <div class="col-12 col-md-4 mb-3">
                                                <label class="font-weight-bold">
                                                    شماره کارت
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-credit-card"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="tax_free_card_number" class="form-control"
                                                        value="{{$company_info['tax_free_card_number'] ?? ''}}">
                                                </div>
                                            </div>

                                            {{-- صاحب حساب --}}
                                            <div class="col-12 col-md-4 mb-3">
                                                <label class="font-weight-bold">
                                                    نام کامل صاحب حساب
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-user"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="tax_free_card_holder" class="form-control"
                                                        value="{{$company_info['tax_free_card_holder'] ?? ''}}">
                                                </div>
                                            </div>

                                        </div>
                                    </div>


                                    {{-- تنظیمات پیامک --}}
                                    <div>
                                        <div class="row">

                                            @permission('ghorfe_online_list_sms_panel')
                                            <h6 class="font-weight-bold text-dark mb-4">
                                                <i class="fa fa-commenting ml-2"></i>
                                                تنظیمات پنل پیامکی
                                            </h6>
                                            <div class="col-12 col-md-4 mb-3">

                                                <label class="font-weight-bold">
                                                    پنل پیامکی
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-comments"></i>
                                                        </span>
                                                    </div>

                                                    <select name="sms_panel" class="form-control select2">
                                                        <option value="iranSms" {{ old('sms_panel',
                                                            $company_info['sms_panel'] ?? '' )=='iranSms' ? 'selected'
                                                            : '' }}>
                                                            ایران اس ام اس
                                                        </option>

                                                        <option value="neginSms" {{ old('sms_panel',
                                                            $company_info['sms_panel'] ?? '' )=='neginSms' ? 'selected'
                                                            : '' }}>
                                                            نگین اس ام اس
                                                        </option>
                                                    </select>
                                                </div>

                                            </div>
                                            @endpermission


                                            @permission('ghorfe_online_list_sms_apikey')
                                            <div class="col-12 col-md-4 mb-3">

                                                <label class="font-weight-bold">
                                                    iransms apikey
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-key"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="iranSms_apikey" class="form-control"
                                                        value="{{$company_info['iranSms_apikey'] ?? ''}}">
                                                </div>

                                            </div>
                                            @endpermission


                                            @permission('ghorfe_online_list_sms_linenumber')
                                            <div class="col-12 col-md-4 mb-3">

                                                <label class="font-weight-bold">
                                                    iransms linenumber
                                                </label>

                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">
                                                            <i class="fa fa-phone"></i>
                                                        </span>
                                                    </div>

                                                    <input type="text" name="iranSms_linenumber" class="form-control"
                                                        value="{{$company_info['iranSms_linenumber'] ?? ''}}">
                                                </div>

                                            </div>
                                            @endpermission

                                        </div>

                                    </div>


                                    {{-- ثبت تغییرات --}}
                                    <div class="d-flex justify-content-end mt-3">

                                        <input type="submit" class="btn btn-success border-0 px-5 py-2 shadow-sm"
                                            value="ثبت تغییرات">

                                    </div>

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

function goTo(n) {

    n = Number(n);

    // محدود کردن شماره پنل
    if (n < 1) n = 1;
    if (n > 9) n = 9;

    current = n;

    // -------------------------
    // نمایش پنل
    // -------------------------
    document.querySelectorAll('.gh-pane').forEach(p => {
        p.classList.remove('active');
    });

    const pane = document.getElementById('p' + n);

    if (pane) {
        pane.classList.add('active');
    }

    // -------------------------
    // فعال کردن مرحله مربوطه
    // -------------------------
    document.querySelectorAll('.gh-steps').forEach(container => {

        const start = Number(container.dataset.start);
        const steps = container.querySelectorAll('.gh-step');

        // همه خاموش
        steps.forEach(step => {
            step.classList.remove('active');
        });

        // شماره مرحله داخل این منو
        const index = n - start;

        // اگر این پنل متعلق به این منو بود
        if (index >= 0 && index < steps.length) {
            steps[index].classList.add('active');
        }
    });
}
</script>

@endsection
