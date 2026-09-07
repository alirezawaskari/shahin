@extends('Admin.master')
@section('title')ویرایش همکار@endsection

@php
    // Saving the "پروفایل عمومی" tab posts to its own endpoint, which flashes this back so
    // the page reopens on that tab instead of dropping the admin on اطلاعات اداری.
    $activeTab = session('active_tab', 'official_info');
@endphp

@section('content')
<div class="col-12 card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-10">
                <h5 class="card-title">اطلاعات همکار</h5>
            </div>
            <div class="col-12 col-md-2">
                @include('Admin.businessTransfer.individualAction', [
                    'entity' => 'employee',
                    'recordId' => $employee->id,
                    'currentBusinessIds' => $employee->ghorfe_online_list_id ? [$employee->ghorfe_online_list_id] : [],
                    'class' => 'btn btn-outline-primary btn-block mb-2'
                ])
                @if ($employee->contract_status == 1)
                <h5 class="btn btn-success float-left">قرارداد تایید شده</h5>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-12 col-md-10">
                <h5> نام : {{ $employee->user->name.' '. $employee->user->family }}</h5>
                <h5> شماره تماس : {{ $employee->user->phone }}</h5>
                <h5> استان : {{ $employee->user->province ? $employee->user->province->name : 'ثبت نشده' }}</h5>
                <h5> نمایندگی : {{ $employee->user->admin_representation ?
                    $employee->user->admin_representation->fa_name : 'م تهران' }}</h5>
            </div>
            <div class="col-12 col-md-2">
                <div class="row">
                    <div class="col-12 float-left">
                        <img src="{{ $employee->user->avatar }}" width="100%">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link show {{ $activeTab == 'official_info' ? 'active' : '' }}" id="official-info-tab"
                    data-toggle="tab" href="#official_info"
                    role="tab" aria-controls="official_info" aria-selected="false">اطلاعات اداری</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="mali-info-tab" data-toggle="tab" href="#mali_info" role="tab"
                    aria-controls="mali_info" aria-selected="true">اطلاعات مالی</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="document-tab" data-toggle="tab" href="#document" role="tab"
                    aria-controls="document" aria-selected="true">مدارک</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="resume-tab" data-toggle="tab" href="#resume" role="tab"
                    aria-controls="resume" aria-selected="false">رزومه کاری</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show" id="contract-tab" data-toggle="tab" href="#contract" role="tab"
                    aria-controls="contract" aria-selected="false">قرارداد کاری</a>
            </li>
            <li class="nav-item">
                <a class="nav-link show {{ $activeTab == 'public_profile' ? 'active' : '' }}" id="public-profile-tab"
                    data-toggle="tab" href="#public_profile" role="tab"
                    aria-controls="public_profile" aria-selected="false">پروفایل عمومی</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 {{ $activeTab == 'official_info' ? 'active show' : '' }}" id="official_info" role="tabpanel"
                aria-labelledby="official-info-tab">
                <form method="post" action="{{route('employee.update' , [ $employee->id])}}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-12">
                            @include('Admin.layouts.errors')
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نام</label>
                            <input value="{{ $employee->user->name }}" type="text" name="name" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نام خانوادگی</label>
                            <input value="{{ $employee->user->family }}" type="text" name="family" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>موبایل</label>
                            <input value="{{ $employee->user->phone }}" type="text" class="form-control"
                                disabled="disabled">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>شماره تماس کاری</label>
                            <input name="employee[business_phone]" value="{{ $employee->business_phone }}" type="text" class="form-control">
                        </div>

                        <div class="col-12 col-md-4 mb-3">
                            <label>کد ملی</label>
                            <input value="{{ $employee->user->melicode }}" type="text" name="melicode"
                                class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>جنسیت کاربر</label>
                            <select class="form-control" name="sex">
                                <option value="0" {{ $employee->user->sex == 0 ? 'selected' : '' }}>
                                    مرد
                                </option>
                                <option value="1" {{ $employee->user->sex == 1 ? 'selected' : '' }}>
                                    زن
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>شماره تماس اضطراری</label>
                            <input value="{{ $employee->user->call }}" type="text" name="call" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>آدرس</label>
                            <input value="{{ $employee->user->address }}" type="text" name="address"
                                class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>عنوان شغلی</label>
                            <input value="{{ $employee->work_title }}" type="text" name="employee[work_title]"
                                class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>رمز عبور جدید</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>تکرار رمز عبور جدید</label>
                            <input type="password" class="form-control" name="password_confirmation">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>وضعیت همکار</label>
                            <select class="form-control" name="status">
                                <option value="0" {{ $employee->status != 1 ? 'selected' : '' }}>
                                    غیرفعال
                                </option>
                                <option value="1" {{ $employee->status == 1 ? 'selected' : '' }}>
                                    فعال
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>وضعیت نمایش</label>
                            <select class="form-control" name="show_status">
                                <option value="1" {{ $employee->show_status == 1 ? 'selected' : '' }}>
                                    نمایان
                                </option>
                                <option value="0" {{ $employee->show_status == 0 ? 'selected' : '' }}>
                                    پنهان
                                </option>
                            </select>
                        </div>
                        @permission('change_user_role')
                        <div class="col-12 col-md-4 mb-3">
                            <label>نقش کاربر</label>
                            <select class="form-control" name="role_id">
                                <option value="0" {{ $employee->user->roles->count() == 0 ? 'selected' : ''}}>
                                    بدون نقش
                                </option>
                                @foreach(\App\Models\Role::get() as $role)
                                <option value="{{ $role->id }}" {{ $employee->user->roles->count() > 0 &&
                                    $employee->user->roles[0]->id == $role->id ? 'selected' : '' }}>{{
                                    $role->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <a href="{{ route('role.create') }}">
                                <div class="btn btn-success" id="new-role">ایجاد نقش جدید +</div>
                            </a>
                        </div>
                        @endpermission
                        <div class="col-12 col-md-4 mb-3">
                            <label>نمایندگی</label>
                            <select class="form-control" name="admin_representation_id">
                                <option value="">
                                    بدون نمایندگی (دفتر تهران)
                                </option>
                                @foreach($representations as $representation)
                                <option value="{{ $representation->id }}" {{ $employee->user->admin_representation_id ==
                                    $representation->id ? 'selected' : '' }}>{{ $representation->fa_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>انتخاب شعبه</label>
                            <select class="form-control" name="center_id">
                                <option value="" {{ $employee->user->center_id == null ? 'selected' : ''}}>
                                    بدون شعبه
                                </option>
                                @foreach($centers as $center)
                                <option value="{{ $center->id }}" {{ $employee->user->center_id == $center->id ? 'selected' : '' }}>{{ $center->title }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Business ownership is read-only here. The dedicated action supplies
                             permission checks, an active Select2 destination, confirmation,
                             validation and an atomic write. --}}
                        <div class="col-12 col-md-4 mb-3">
                            <label>کسب و کار</label>
                            <input type="text" class="form-control" readonly
                                   value="{{ optional($employee->ghorfeOnline)->title ?: 'بدون کسب و کار' }}">
                            <small class="text-muted">
                                تغییر کسب و کار فقط از طریق عملیات «انتقال به کسب و کار» انجام می‌شود.
                            </small>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>انتخاب سرپرست</label>
                            <select class="form-control" name="parent_id">
                                <option value="null" {{is_null($employee->parent_id)?'selected':''}}>ندارد</option>
                                @foreach($employees as $employee_item)
                                <option value="{{$employee_item->id}}" {{$employee->parent_id==$employee_item->id
                                    ?'selected':''}}>{{$employee_item->user->name . " " . $employee_item->user->family}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        @permission('user-edit')
                        <div class="col-12 mb-3">
                            <button type="submit" class="btn btn-success">ثبت</button>
                        </div>
                        @endpermission
                    </div>
                </form>
            </div>
            <div class="tab-pane fade p-t-20" id="mali_info" role="tabpanel" aria-labelledby="mali-info-tab">
                <form method="post" action="{{route('employee.update' , [ $employee->id])}}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-12">
                            @include('Admin.layouts.errors')
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>شماره شبا</label>
                            <input value="{{ $employee->shaba_number }}" type="text" name="employee[shaba_number]"
                                class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>شماره کارت</label>
                            <input value="{{ $employee->card_number }}" type="text" name="employee[card_number]"
                                class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>کیف پول کاربر</label>
                            <input value="{{ number_format($employee->user->wallet) }}" type="text" name="wallet"
                                class="form-control" oninput="separatePrices(this)">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>نوع همکاری</label>
                            <select class="form-control" name="employee[pay_type]">
                                <option value="pay_end_work" {{ $employee->pay_type == 'pay_end_work' ? 'selected' : ''
                                    }}>
                                    پرداخت بعد از اتمام کار
                                </option>
                                <option value="pay_end_session" {{ $employee->pay_type == 'pay_end_session' ? 'selected'
                                    : '' }}>
                                    پرداخت با تکمیل جلسه
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>درصد همکاری</label>
                            <input value="{{ $employee->percent }}" type="text" name="employee[percent]"
                                class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>درصد حسن انجام کار</label>
                            <input value="{{ $employee->blocked_percentage }}" type="text"
                                name="employee[blocked_percentage]" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>تعهد فروش (براساس روز)</label>
                            <input value="{{ $employee->sales_commitment }}" type="number" min="0"
                                name="employee[sales_commitment]" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>دارای ضمانت</label>
                            <select class="form-control" name="employee[guarantee]">
                                <option value="1" {{ $employee->guarantee == true ? 'selected' : '' }}>
                                    بله
                                </option>
                                <option value="0" {{ $employee->guarantee == false ? 'selected' : '' }}>
                                    خیر
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>مالیات</label>
                            <input value="{{ $employee->tax }}" type="text" name="employee[tax]" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>تاریخ دسترسی به اطلاعات</label>
                            <input value="{{ $employee->data_access_date }}" type="text"
                                name="employee[data_access_date]" class="form-control date">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>تاریخ شروع قرارداد</label>
                            <input value="{{ $employee->contract_start_date }}" type="text"
                                name="employee[contract_start_date]" class="form-control date">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>تاریخ انقضاء قرارداد</label>
                            <input value="{{ $employee->contract_expiration_date }}" type="text"
                                name="employee[contract_expiration_date]" class="form-control date">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>شماره قرارداد</label>
                            <input value="{{ $employee->contract_number }}" type="text" name="employee[contract_number]"
                                class="form-control">
                        </div>
                        @permission('employee-mali-edit')
                        <div class="col-12 mb-3">
                            <button type="submit" class="btn btn-success">ثبت</button>
                        </div>
                        @endpermission
                    </div>
                </form>
            </div>
            <div class="tab-pane fade p-t-20" id="document" role="tabpanel" aria-labelledby="document-tab">
                <form method="post" action="{{route('employee.update' , [ $employee->id])}}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="row">
                        <div class="col-12">
                            @include('Admin.layouts.errors')
                        </div>
                        <div class="col-12">
                            <div class="card-group">
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->user->imgsignature)
                                        <div id="imgsignature_src" class="show">
                                            <a href="{{ $employee->user->imgsignature }}" target="_blank">
                                                <img src="{{ $employee->user->imgsignature }}"
                                                    class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                            </a>
                                            @if($employee->user->mediaIsLegacy('imgsignature'))
                                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        </div>
                                        @else
                                        <div id="imgsignature_src" class="show">
                                            <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>امضاء کاربر</label>
                                            <br>
                                            <label for="imgsignature" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="imgsignature" type="file" id="imgsignature"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgsignature_path" class="path" name="urlimgsignature">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->user->avatar)
                                        <div id="avatar_src" class="show">
                                            <a href="{{ $employee->user->avatar }}" target="_blank">
                                                <img src="{{ $employee->user->avatar }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                            </a>
                                            @if($employee->user->mediaIsLegacy('avatar'))
                                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        </div>
                                        @else
                                        <div id="avatar_src" class="show">
                                            <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>تصویر پرسنلی</label>
                                            <br>
                                            <label for="avatar" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="avatar" type="file" id="avatar"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="avatar_path" class="path" name="urlavatar">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->user->imgmeli)
                                        <div id="imgmeli_src" class="show">
                                            <a href="{{ $employee->user->imgmeli }}" target="_blank">
                                                <img src="{{ $employee->user->imgmeli }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                            </a>
                                            @if($employee->user->mediaIsLegacy('imgmeli'))
                                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        </div>
                                        @else
                                        <div id="imgmeli_src" class="show">
                                            <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>کارت ملی</label>
                                            <br>
                                            <label for="imgmeli" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="imgmeli" type="file" id="imgmeli"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgmeli_path" class="path" name="urlimgmeli">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->user->imgshenasname)
                                        <div id="imgshenasname_src" class="show">
                                            <a href="{{ $employee->user->imgshenasname }}" target="_blank">
                                                <img src="{{ $employee->user->imgshenasname }}"
                                                    class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                            </a>
                                            @if($employee->user->mediaIsLegacy('imgshenasname'))
                                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        </div>
                                        @else
                                        <div id="imgshenasname_src" class="show">
                                            <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>شناسنامه</label>
                                            <br>
                                            <label for="imgshenasname" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="imgshenasname" type="file" id="imgshenasname"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgshenasname_path" class="path"
                                                name="urlimgshenasname">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->imgaccess)
                                        <div id="imgaccess_src" class="show">
                                            <a href="{{ $employee->imgaccess }}" target="_blank">
                                                <img src="{{ $employee->imgaccess }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                            </a>
                                            @if($employee->mediaIsLegacy('imgaccess'))
                                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        </div>
                                        @else
                                        <div id="imgaccess_src" class="show">
                                            <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>تصویر دریافت دسترسی پنل</label>
                                            <br>
                                            <label for="imgaccess" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="employee[imgaccess]" type="file" id="imgaccess"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgaccess_path" class="path" name="urlimgaccess">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->imglastcertificate)
                                        <div id="imglastcertificate_src" class="show">
                                            <a href="{{ $employee->imglastcertificate }}" target="_blank">
                                                <img src="{{ $employee->imglastcertificate }}"
                                                    class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                            </a>
                                            @if($employee->mediaIsLegacy('imglastcertificate'))
                                            <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                            @endif
                                        </div>
                                        @else
                                        <div id="imglastcertificate_src" class="show">
                                            <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>تصویر آخرین مدرک تحصیلی یا تخصصی</label>
                                            <br>
                                            <label for="imglastcertificate" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="employee[imglastcertificate]" type="file"
                                                id="imglastcertificate"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imglastcertificate_path" class="path"
                                                name="urlimglastcertificate">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @permission('user-edit')
                        <div class="col-12 mb-3">
                            <button type="submit" class="btn btn-success">ثبت</button>
                        </div>
                        @endpermission
                    </div>
                </form>
            </div>
            <div class="tab-pane fade p-t-20" id="resume" role="tabpanel" aria-labelledby="resume-tab">
                @permission('employee_resume_detail')
                @include('Admin.employee.resume.form')
                @endpermission
            </div>
            <div class="tab-pane fade p-t-20" id="contract" role="tabpanel" aria-labelledby="contract-tab">
                @permission('employee_contract_show_detail')
                {{-- قراردادهای نقش: the contracts defined for the roles this employee holds,
                     resolved through ContractUsage. Shown above the employee's own contract,
                     which carries the signing/confirmation flow and stays as it was. --}}
                @foreach ($roleContracts as $roleUsage)
                    @include('Admin.contract.partials.show', [
                        'usage' => $roleUsage,
                        'contract' => $roleUsage->contract,
                        'title' => 'قرارداد ' . $roleUsage->owner_label,
                        'exportParams' => ['employee_id' => $employee->id],
                    ])
                @endforeach
                <form method="post" action="{{route('employee.update.contract' , [ $employee->id])}}"
                    enctype="multipart/form-data">
                    @csrf
                    @method('POST')
                    <div class="row">
                        <div class="col-12 mb-3">
                            @if ($employee->contract_status == 1)
                            @include('Admin.employee.contract.content')
                            @else
                            @permission('employee_contract_edit')
                            <div class="col-12 col-md-4 mb-3 mt-3">
                                <select class="form-control" name="contract_status">
                                    <option value="0" {{ $employee->contract_status == 0 ? 'selected' : '' }}>
                                        وضعیت قرارداد - تنظیم نشده
                                    </option>
                                    <option value="3" {{ $employee->contract_status == 3 ? 'selected' : '' }}>
                                        وضعیت قرارداد - پیش نویس
                                    </option>
                                    <option value="2" {{ $employee->contract_status == 2 ? 'selected' : '' }}>
                                        وضعیت قرارداد - آماده امضا توسط همکار
                                    </option>
                                </select>
                            </div>
                            <textarea id="contractContent" class="editor-content mb-3"
                                name="contract">{!! $employee->contract !!} </textarea>

                            <div class="col-12 mb-3">
                                <button type="submit" class="btn btn-success mt-3">ویرایش</button>
                            </div>
                            @else
                            @include('Admin.employee.contract.content')
                            @endpermission
                            @endif
                            @include('Admin.totalfactoritem.layouts.contract.histories')
                        </div>
                    </div>
                </form>
                @endpermission
            </div>

            {{-- ================= پروفایل عمومی ================= --}}
            {{-- What the partner-profile frontend renders. Its own endpoint, like the resume
                 and contract tabs: it owns relations (services, نمونه کارها, نظرات) that the
                 shared employee.update path knows nothing about. --}}
            <div class="tab-pane fade p-t-20 {{ $activeTab == 'public_profile' ? 'active show' : '' }}"
                 id="public_profile" role="tabpanel" aria-labelledby="public-profile-tab">
                @permission('user-edit')
                <form method="post" action="{{ route('employee.profile.update', [$employee->id]) }}"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            @include('Admin.layouts.errors')
                        </div>

                        <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                            معرفی
                        </div>

                        <div class="col-12 col-md-4 mb-3">
                            <label>تصویر پروفایل (صفحه عمومی)</label>
                            <div id="profile_image_src" class="show mb-2">
                                @if($employee->profile_image)
                                    <img src="{{ $employee->profile_image }}" class="border-radius5"
                                         style="height:140px;object-fit:cover;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center text-muted"
                                         style="height:140px;background:#f8f9fa;">بدون تصویر</div>
                                @endif
                            </div>
                            <label for="profile_image" class="btn btn-primary custom-file-upload border border-1">
                                <i class="fa fa-cloud-upload"></i> بارگذاری تصویر
                            </label>
                            <input id="profile_image" class="d-none file-upload file_upload_ajax" type="file"
                                   name="profile_image" accept=".jpg,.jpeg,.png,.webp">
                            <input type="hidden" id="profile_image_path" class="path" name="profile_image_path">
                            <small class="d-block text-muted mt-1">جدا از «تصویر پرسنلی» تب مدارک؛ این تصویر در سایت نمایش داده می‌شود.</small>
                        </div>

                        <div class="col-12 col-md-8 mb-3">
                            <label>متن معرفی</label>
                            <textarea class="form-control" name="bio" rows="7"
                                      placeholder="معرفی کوتاه همکار که در بالای صفحه پروفایل نمایش داده می‌شود">{{ old('bio', $employee->bio) }}</textarea>
                        </div>

                        <div class="col-12 col-md-4 mb-3">
                            <label>متن توصیفی اول</label>
                            <input type="text" class="form-control" name="description_one"
                                   value="{{ old('description_one', $employee->description_one) }}"
                                   placeholder="دکترای حرفه‌ای دندانپزشکی - دانشگاه علوم پزشکی شیراز (۱۳۸۸)">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>متن توصیفی دوم</label>
                            <input type="text" class="form-control" name="description_two"
                                   value="{{ old('description_two', $employee->description_two) }}"
                                   placeholder="تخصص جراحی لثه و ایمپلنت - دانشگاه علوم پزشکی اصفهان">
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label>متن توصیفی سوم</label>
                            <input type="text" class="form-control" name="description_three"
                                   value="{{ old('description_three', $employee->description_three) }}"
                                   placeholder="عضو انجمن پریودنتولوژی ایران">
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label>روزهای حضور</label>
                            <input type="text" class="form-control" name="attendance_days"
                                   value="{{ old('attendance_days', $employee->attendance_days) }}"
                                   placeholder="شنبه تا دوشنبه">
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <label>ساعات حضور</label>
                            <input type="text" class="form-control" name="attendance_hours"
                                   value="{{ old('attendance_hours', $employee->attendance_hours) }}"
                                   placeholder="۱۰ صبح تا ۱۰ شب">
                            @if($employee->center)
                                <small class="text-muted">شعبه: {{ $employee->center->title }}</small>
                            @endif
                        </div>

                        <div class="col-12 mb-3">
                            <label>محتوای تکمیلی</label>
                            {{-- Free-form block: a clinic puts its شماره نظام پزشکی here, a law
                                 firm its bar number, a workshop its certifications. --}}
                            <textarea id="extra_content" class="form-control" name="extra_content"
                                      rows="8">{!! old('extra_content', $employee->extra_content) !!}</textarea>
                        </div>

                        {{-- ---------------- خدمات ---------------- --}}
                        <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                            خدمات
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label>عنوان بخش</label>
                            <input type="text" class="form-control" name="services_title"
                                   value="{{ old('services_title', $employee->services_title) }}" placeholder="خدمات">
                        </div>

                        @php $employeeServices = $employee->services->keyBy('id'); @endphp

                        <div class="col-12 mb-3">
                            <label>انتخاب خدمات</label>
                            {{-- Picker only — it posts nothing. Choosing a service adds its row to
                                 the table below, and that row is what carries service_ids[]. With
                                 a hundred-plus services a checkbox per service was unusable. --}}
                            <select id="profile_services_select" class="selectMulti form-control" multiple>
                                @foreach($profileServices as $service)
                                    <option value="{{ $service->id }}" @selected($employeeServices->has($service->id))>{{ $service->title }}</option>
                                @endforeach
                            </select>
                            @if($profileServices->isEmpty())
                                <small class="text-muted">خدمتی در دسترس شما ثبت نشده است.</small>
                            @endif
                        </div>

                        <div class="col-12 mb-3">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>خدمت</th>
                                    <th style="width:35%">زیرعنوان</th>
                                    <th style="width:20%">قیمت از (تومان)</th>
                                    <th style="width:60px"></th>
                                </tr>
                                </thead>
                                <tbody id="profile-services-rows">
                                @foreach($employee->services as $service)
                                    <tr data-service="{{ $service->id }}">
                                        <td class="align-middle">
                                            {{ $service->title }}
                                            {{-- the row IS the selection; subtitle/price are keyed by
                                                 service id so removing a row never shifts the others --}}
                                            <input type="hidden" name="service_ids[]" value="{{ $service->id }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control"
                                                   name="service_subtitle[{{ $service->id }}]"
                                                   value="{{ $service->pivot->subtitle }}"
                                                   placeholder="تک دندان، کامل، فوری">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control"
                                                   name="service_from_price[{{ $service->id }}]"
                                                   value="{{ $service->pivot->from_price ? number_format($service->pivot->from_price) : '' }}"
                                                   placeholder="3,000,000">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-sm service-remove">
                                                <i class="fa fa-remove"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr id="profile-services-empty" class="{{ $employee->services->isEmpty() ? '' : 'd-none' }}">
                                    <td colspan="4" class="text-muted">خدمتی انتخاب نشده است.</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- ---------------- نمونه کارها ---------------- --}}
                        <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                            نمونه کارها
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <label>عنوان بخش</label>
                            <input type="text" class="form-control" name="portfolio_title"
                                   value="{{ old('portfolio_title', $employee->portfolio_title) }}" placeholder="نمونه کارها">
                        </div>

                        {{-- Saved portfolio images. Same widget as the service edit page: an
                             unlimited Spatie collection, caption per image, ✕ removes it via the
                             shared removeMedia ajax. --}}
                        <div class="col-12 mb-3" id="portfolio_list_wrap"
                             @if($employee->getMedia('portfolio')->count() == 0) style="display:none" @endif>
                            <label>نمونه کارهای ذخیره شده</label>
                            <table class="table table-striped">
                                <tbody id="portfolio_list" data-title-name="existing_portfolio" data-box-h="80">
                                @foreach($employee->getMedia('portfolio') as $media)
                                    <tr class="row_image" id="media_{{ $media->id }}">
                                        <td>
                                            <img src="{{ $media->getFullUrl() }}" alt="{{ $media->getCustomProperty('title') }}"
                                                 class="rounded border"
                                                 style="height:80px;width:auto;object-fit:contain;background:#f8f9fa;">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" placeholder="عنوان"
                                                   name="existing_portfolio[{{ $media->id }}][title]"
                                                   value="{{ $media->getCustomProperty('title') }}">
                                        </td>
                                        <td>
                                            <a data-id="{{ $media->id }}" title="حذف عکس"
                                               class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="col-12 mb-4">
                            @include('Admin.partials.multipleImages',['has_link'=>0,'listKey'=>'portfolio'])
                        </div>

                        {{-- ---------------- نظرات کاربران ---------------- --}}
                        <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
                            نظرات کاربران
                        </div>

                        <div class="col-12 mb-3">
                            {{-- Moderation only: ratings come from visitors through the API. --}}
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

                        <div class="col-12 mb-3">
                            <button type="submit" class="btn btn-success">ثبت</button>
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
<script src="{{ asset('/assets/script/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script>
    $(document).ready(function () {
            CKEDITOR.replace('contractContent',ck_uploader) ;
            // free-form block of the public profile tab
            if (document.getElementById('extra_content')) {
                CKEDITOR.replace('extra_content', ck_uploader);
            }

            // ---- خدمات: the select2 drives the table below it ----
            // Rows are added/removed one at a time rather than re-rendered, so a subtitle
            // or price already typed into another row is never wiped by a later selection.
            var $servicePicker = $('#profile_services_select');
            var $serviceRows   = $('#profile-services-rows');

            function toggleServicesEmptyRow() {
                $('#profile-services-empty').toggleClass('d-none', $serviceRows.find('tr[data-service]').length > 0);
            }

            function addServiceRow(id, title) {
                if ($serviceRows.find('tr[data-service="' + id + '"]').length) { return; }

                $('#profile-services-empty').before(
                    '<tr data-service="' + id + '">' +
                    '<td class="align-middle">' + $('<div>').text(title).html() +
                    '<input type="hidden" name="service_ids[]" value="' + id + '"></td>' +
                    '<td><input type="text" class="form-control" name="service_subtitle[' + id + ']" placeholder="تک دندان، کامل، فوری"></td>' +
                    '<td><input type="text" class="form-control" name="service_from_price[' + id + ']" placeholder="3,000,000"></td>' +
                    '<td class="text-center"><button type="button" class="btn btn-danger btn-sm service-remove">' +
                    '<i class="fa fa-remove"></i></button></td>' +
                    '</tr>'
                );
                toggleServicesEmptyRow();
            }

            function removeServiceRow(id) {
                $serviceRows.find('tr[data-service="' + id + '"]').remove();
                toggleServicesEmptyRow();
            }

            $servicePicker.on('select2:select', function (e) {
                addServiceRow(e.params.data.id, e.params.data.text);
            });

            $servicePicker.on('select2:unselect', function (e) {
                removeServiceRow(e.params.data.id);
            });

            // Removing from the table must also clear the picker, or the two disagree.
            $serviceRows.on('click', '.service-remove', function () {
                var id = $(this).closest('tr').data('service');
                $servicePicker.val(($servicePicker.val() || []).filter(function (v) {
                    return String(v) !== String(id);
                })).trigger('change');
                removeServiceRow(id);
            });
        })
</script>
@endsection
