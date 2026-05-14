@extends('Admin.master')
@section('title')ویرایش مغازه دیجیتال@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link show active" id="official-info-tab" data-toggle="tab" href="#official_info"
                    role="tab" aria-controls="official_info" aria-selected="false">اطلاعات اداری</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="mali-info-tab" data-toggle="tab" href="#mali_info" role="tab"
                    aria-controls="mali_info" aria-selected="true">اطلاعات مالی</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="text-content-tab" data-toggle="tab" href="#text_content" role="tab"
                    aria-controls="text_content" aria-selected="true">محتوای متنی مغازه</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="image-content-tab" data-toggle="tab" href="#image_content" role="tab"
                    aria-controls="image_content" aria-selected="false">محتوای تصویری مغازه</a>
            </li>

            <li class="nav-item">
                <a class="nav-link show" id="menu-content-tab" data-toggle="tab" href="#menu_content" role="tab"
                   aria-controls="menu-content" aria-selected="false">انتخاب منو</a>
            </li>

            <li class="nav-item">
                <a class="nav-link show" id="theme-content-tab" data-toggle="tab" href="#theme_content" role="tab"
                   aria-controls="theme_content" aria-selected="false">انتخاب تم </a>
            </li>

            <li class="nav-item">
                <a class="nav-link show" id="category-content-tab" data-toggle="tab" href="#category_content" role="tab"
                   aria-controls="category_content" aria-selected="false">انتخاب دسته ها  </a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="official_info" role="tabpanel"
                aria-labelledby="official-info-tab">
                @permission('ghorfe_online_list_official_info')
                <form class="form-horizontal" role="form" method="post"
                    action="{{route('ghorfeOnlineList.officialInfoUpdate' , [$ghorfeOnlineList->slug])}}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        @include('Admin.layouts.errors')

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
                        <div class="col-12">
                            <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                        </div>
                    </div>
                </form>
                @endpermission
            </div>
            <div class="tab-pane fade p-t-20" id="mali_info" role="tabpanel" aria-labelledby="mali-info-tab">
                @permission('ghorfe_online_list_mali_info')
                <p>به زودی</p>
                @endpermission
            </div>
            <div class="tab-pane fade p-t-20" id="text_content" role="tabpanel" aria-labelledby="text-content-tab">
                @permission('ghorfe_online_list_site_content')
                <form class="form-horizontal" role="form" method="post"
                    action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
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
                            <label>شماره تماس</label>
                            <input type="text" value="{{$ghorfeOnlineList->call}}" class="form-control" name="call">
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
                            <label>آدرس</label>
                            <input type="text" value="{{$ghorfeOnlineList->address}}" class="form-control"
                                name="address">
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

                        <div class="col-12">
                            <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                        </div>

                    </div>
                </form>
                @endpermission
            </div>
            <div class="tab-pane fade p-t-20" id="image_content" role="tabpanel" aria-labelledby="image-content-tab">
                @permission('ghorfe_online_list_site_content')
                <form class="form-horizontal" role="form" method="post"
                    action="{{route('ghorfeOnlineList.contentUpdate', [$ghorfeOnlineList->slug])}}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        @include('Admin.layouts.errors')

                        <div class="col-12 row">
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="large_vertical_image_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->large_vertical_image) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>تصویر عمودی بزرک (340*294)</label>
                                        <br>
                                        <label for="large_vertical_image" id="up-label" class="btn badge-secondary"><i
                                                class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="large_vertical_image" type="file" id="large_vertical_image"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="large_vertical_image" class="path"
                                            name="urllarge_vertical_image">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="large_horizontal_image_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->large_horizontal_image) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>تصویر افقی بزرگ (227*318)</label>
                                        <br>
                                        <label for="large_horizontal_image" id="up-label" class="btn badge-secondary"><i
                                                class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="large_horizontal_image" type="file" id="large_horizontal_image"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="large_horizontal_image" class="path"
                                            name="urllarge_horizontal_image">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="left_small_horizontal_image_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->left_small_horizontal_image) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>تصویر افقی کوچک سمت چپ (110*155)</label>
                                        <br>
                                        <label for="left_small_horizontal_image" id="up-label"
                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="left_small_horizontal_image" type="file"
                                            id="left_small_horizontal_image"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="left_small_horizontal_image" class="path"
                                            name="urlleft_small_horizontal_image">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="right_small_horizontal_image_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->right_small_horizontal_image) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>تصویر افقی کوچک سمت راست (110*155)</label>
                                        <br>
                                        <label for="right_small_horizontal_image" id="up-label"
                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="right_small_horizontal_image" type="file"
                                            id="right_small_horizontal_image"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="right_small_horizontal_image" class="path"
                                            name="urlright_small_horizontal_image">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="logo_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->logo) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>لوگو</label>
                                        <br>
                                        <label for="logo" id="up-label"
                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="logo" type="file"
                                            id="logo"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="logo" class="path"
                                            name="urllogo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="footer_logo_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->footer_logo) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>لوگو فوتر (همراه با نام برند)</label>
                                        <br>
                                        <label for="footer_logo" id="up-label"
                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="footer_logo" type="file"
                                            id="footer_logo"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="footer_logo" class="path"
                                            name="urlfooter_logo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="footer_logo_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->footer_logo) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>لوگو مرورگر (استفاده از لوگو اصلی مجاز)</label>
                                        <br>
                                        <label for="footer_logo" id="up-label"
                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="footer_logo" type="file"
                                            id="footer_logo"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="footer_logo" class="path"
                                            name="urlfooter_logo">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="card">
                                    <div id="mohr_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->mohr) }}
                                        class="card-img-top" width="100%"
                                        height="200px">
                                    </div>
                                    <div class="card-body">
                                        <label>مهر (جهت نمایش در فاکتور رسمی)</label>
                                        <br>
                                        <label for="mohr" id="up-label"
                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="mohr" type="file"
                                            id="mohr"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="mohr" class="path"
                                            name="urlmohr">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="card">
                                    <div id="addvertisment_slider_src" class="show">
                                        <img src={{ asset($ghorfeOnlineList->addvertisment_slider) }}
                                        class="card-img-top" width="100%"
                                        height="88px">
                                    </div>
                                    <div class="card-body">
                                        <label>اسلایدر تبلیغات(88*1400)</label>
                                        <br>
                                        <label for="addvertisment_slider" id="up-label"
                                            class="btn badge-secondary"><i class="fa fa-upload ml-2"
                                                aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="addvertisment_slider" type="file"
                                            id="addvertisment_slider"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png,.gif">
                                        <input type="hidden" id="addvertisment_slider" class="path"
                                            name="urladdvertisment_slider">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <input type="submit" class="btn btn-success border-0" value="ثبت">
                        </div>

                    </div>
                </form>
                @endpermission
            </div>
            <div class="tab-pane fade p-t-20" id="menu_content" role="tabpanel" aria-labelledby="text-content-tab">
                @permission('ghorfe_online_list_site_content')
                <form class="form-horizontal" role="form" method="post"
                      action="{{route('ghorfeOnlineList.menuUpdate', [$ghorfeOnlineList->slug])}}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row" id="menu-wrapper" >
                        @include('Admin.layouts.errors')

                        @foreach($ghorfeOnlineList->menus as $index => $menu)
                            <div class="menu-item border p-2 mb-3">

                                {{-- ✅ تشخیص برای update --}}
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

                            </div>
                        @endforeach

                    </div>

                    @if($ghorfeOnlineList->menus->count() < 5)
                        <button type="button" id="add-menu" class="btn btn-primary mb-3">
                            افزودن منو
                        </button>
                    @endif

                    <div class="col-12">
                        <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                    </div>
                </form>
                @endpermission
            </div>
            <div class="tab-pane fade p-t-20" id="theme_content" role="tabpanel" aria-labelledby="text-content-tab">
                @permission('ghorfe_online_list_site_content')
                <form class="form-horizontal" role="form" method="post"
                      action="{{ route('ghorfeOnlineList.themeUpdate', [$ghorfeOnlineList]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        @include('Admin.layouts.errors')

                        {{-- 1. Form fields now rend er using an inline default if theme doesn't exist yet --}}
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
                                   class="form-control mt-2 text-center font-mono"
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
                                   class="form-control mt-2 text-center font-mono"
                                   value="{{ old('color_secondary', $ghorfeOnlineList->theme->color_secondary ?? '#6c757d') }}"
                                   readonly>
                        </div>

                        <div class="col-12 mt-3">
                            <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                        </div>

                    </div>
                </form>
                @endpermission
            </div>
            <div class="tab-pane fade p-t-20" id="category_content" role="tabpanel" aria-labelledby="text-content-tab">
                @permission('ghorfe_online_list_site_content')
                <form class="form-horizontal" role="form" method="post"
                      action="{{ route('ghorfeOnlineList.categoriesGhorfe', [$ghorfeOnlineList]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        @include('Admin.layouts.errors')

                        <div class="col-12  mt-2">
                            <label>دسته‌بندی‌ها</label>
                            <select name="categories[]" class="form-control select2" multiple>
                                @php
                                    $selectedCategories = $ghorfeOnlineList->ghorfeCategories->pluck('id')->toArray();
                                @endphp
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 mt-3">
                            <input type="submit" class="btn btn-success border-0" value="ثبت تغییرات">
                        </div>

                    </div>
                </form>
                @endpermission
            </div>
        </div>

    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

    const btn = document.getElementById('add-menu');
    const wrapper = document.getElementById('menu-wrapper');

    if (!btn || !wrapper) return;

    btn.addEventListener('click', function () {

    let items = document.querySelectorAll('.menu-item');

    // ❗ حداکثر 5 تا
    if (items.length >= 5) {
    alert('حداکثر 5 منو می‌توانید اضافه کنید');
    return;
}

    // ❗ چک کردن کامل بودن آیتم قبلی
    let last = items[items.length - 1];

    if (last) {
    let inputs = last.querySelectorAll('input[required]');

    for (let input of inputs) {
    if (!input.value.trim()) {
    alert('لطفا ابتدا اطلاعات منوی قبلی را کامل کنید');
    input.focus();
    return;
}
}
}

    // ✅ ساخت آیتم جدید
    let div = document.createElement('div');
    div.className = 'menu-item border p-2 mb-3';

        div.innerHTML = `
    <input type="hidden" name="ids[]" value="">

    <div class="col-12">
        <label>عنوان</label>
        <input type="text" name="titles[]" class="form-control" required>
    </div>

    <div class="col-12">
        <label>لینک</label>
        <input type="text" name="links[]" class="form-control" required>
    </div>
`;

    wrapper.appendChild(div);

    // ❗ اگر رسید به 5 → دکمه مخفی
    if (document.querySelectorAll('.menu-item').length >= 5) {
    btn.style.display = 'none';
}

});

});

    document.addEventListener('DOMContentLoaded', function () {
        const primaryPicker   = document.getElementById('primaryPicker');
        const secondaryPicker = document.getElementById('secondaryPicker');

        const primaryText     = document.getElementById('primaryText');
        const secondaryText   = document.getElementById('secondaryText');

        const preview         = document.getElementById('colorPreview');

        function updateUI() {
            primaryText.value = primaryPicker.value;
            secondaryText.value = secondaryPicker.value;

            preview.style.background =
                `linear-gradient(to right, ${primaryPicker.value}, ${secondaryPicker.value})`;
        }

        updateUI();

        primaryPicker.addEventListener('input', updateUI);
        secondaryPicker.addEventListener('input', updateUI);

    });
</script>

@endsection
