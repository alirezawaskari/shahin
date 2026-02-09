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
        </div>

    </div>
</div>
@stop
@section('js')
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@endsection
