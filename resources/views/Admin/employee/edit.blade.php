@extends('Admin.master')
@section('title')ویرایش همکار@endsection

@section('content')
<div class="col-12 card">
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-10">
                <h5 class="card-title">اطلاعات همکار</h5>
            </div>
            <div class="col-12 col-md-2">
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
                <a class="nav-link show active" id="official-info-tab" data-toggle="tab" href="#official_info"
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
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade p-t-20 active show" id="official_info" role="tabpanel"
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
                            <label>انتخاب مرکز</label>
                            <select class="form-control" name="employee[center_id]">
                                <option value="" {{ $employee->center_id == null ? 'selected' : ''}}>
                                    بدون مرکز
                                </option>
                                @foreach($centers as $center)
                                <option value="{{ $center->id }}" {{ $employee->user->center_id != null &&
                                    $employee->center_id == $center->id ? 'selected' : '' }}>{{ $center->title }}
                                </option>
                                @endforeach
                            </select>
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
                            <label>تاریخ شروع همکاری</label>
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
                                            <a href="{{ asset($employee->user->imgsignature) }}" target="_blank">
                                                <img src="{{ asset($employee->user->imgsignature) }}"
                                                    class="card-img-top" height="150px">
                                            </a>
                                        </div>
                                        @else
                                        <div id="imgsignature_src" class="show">
                                            <img class="card-img-top" height="150px">
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>امضاء کاربر</label>
                                            <br>
                                            <label for="imgsignature" id="up-label" class="btn badge-secondary"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="imgsignature" type="file" id="imgsignature"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgsignature" class="path" name="urlimgsignature">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->user->avatar)
                                        <div id="avatar_src" class="show">
                                            <a href="{{ asset($employee->user->avatar) }}" target="_blank">
                                                <img src="{{ asset($employee->user->avatar) }}" class="card-img-top"
                                                    height="150px">
                                            </a>
                                        </div>
                                        @else
                                        <div id="avatar_src" class="show">
                                            <img class="card-img-top" height="150px">
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>تصویر پرسنلی</label>
                                            <br>
                                            <label for="avatar" id="up-label" class="btn badge-secondary"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="avatar" type="file" id="avatar"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="avatar" class="path" name="urlavatar">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->user->imgmeli)
                                        <div id="imgmeli_src" class="show">
                                            <a href="{{ asset($employee->user->imgmeli) }}" target="_blank">
                                                <img src="{{ asset($employee->user->imgmeli) }}" class="card-img-top"
                                                    height="150px">
                                            </a>
                                        </div>
                                        @else
                                        <div id="imgmeli_src" class="show">
                                            <img class="card-img-top" height="150px">
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>کارت ملی</label>
                                            <br>
                                            <label for="imgmeli" id="up-label" class="btn badge-secondary"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="imgmeli" type="file" id="imgmeli"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgmeli" class="path" name="urlimgmeli">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->user->imgshenasname)
                                        <div id="imgshenasname_src" class="show">
                                            <a href="{{ asset($employee->user->imgshenasname) }}" target="_blank">
                                                <img src="{{ asset($employee->user->imgshenasname) }}"
                                                    class="card-img-top" height="150px">
                                            </a>
                                        </div>
                                        @else
                                        <div id="imgshenasname_src" class="show">
                                            <img class="card-img-top" height="150px">
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>شناسنامه</label>
                                            <br>
                                            <label for="imgshenasname" id="up-label" class="btn badge-secondary"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="imgshenasname" type="file" id="imgshenasname"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgshenasname" class="path"
                                                name="urlimgshenasname">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->imgaccess)
                                        <div id="imgaccess_src" class="show">
                                            <a href="{{ asset($employee->imgaccess) }}" target="_blank">
                                                <img src="{{ asset($employee->imgaccess) }}" class="card-img-top"
                                                    height="150px">
                                            </a>
                                        </div>
                                        @else
                                        <div id="imgaccess_src" class="show">
                                            <img class="card-img-top" height="150px">
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>تصویر دریافت دسترسی پنل</label>
                                            <br>
                                            <label for="imgaccess" id="up-label" class="btn badge-secondary"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="employee[imgaccess]" type="file" id="imgaccess"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imgaccess" class="path" name="urlimgaccess">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="card">
                                        @if($employee->imglastcertificate)
                                        <div id="imglastcertificate_src" class="show">
                                            <a href="{{ asset($employee->imglastcertificate) }}" target="_blank">
                                                <img src="{{ asset($employee->imglastcertificate) }}"
                                                    class="card-img-top" height="150px">
                                            </a>
                                        </div>
                                        @else
                                        <div id="imglastcertificate_src" class="show">
                                            <img class="card-img-top" height="150px">
                                        </div>
                                        @endif
                                        <div class="card-body">
                                            <label>تصویر آخرین مدرک تحصیلی یا تخصصی</label>
                                            <br>
                                            <label for="imglastcertificate" id="up-label" class="btn badge-secondary"><i
                                                    class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر
                                            </label>
                                            <input name="employee[imglastcertificate]" type="file"
                                                id="imglastcertificate"
                                                class="filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                            <input type="hidden" id="imglastcertificate" class="path"
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
        })
</script>
@endsection
