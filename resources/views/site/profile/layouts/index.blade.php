@extends('site.profile.master')

@section('content2')

<div class="col-12 col-md-9 mt-md-0">
    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="list-home" role="tabpanel" aria-labelledby="list-home-list">
            @if($profile_percent != 100)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                لطفا اطلاعات کاربری خود را از بخش اطلاعات تکمیلی کامل کنید
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            <div class="name-box d-flex align-items-center">
                <label for="uploadAvatar">
                    <img src="{{ asset($user->avatar) }}" class="img-thumbnail rounded-circle w-50 ml-2">
                </label>
                <form action="{{ route('useravatar') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" class="d-none" id="uploadAvatar" name="image">
                </form>

                <div>
                    <h2 class="text-orange">{{ $user->name }} {{ $user->family }}</h2>
                    <span class="text-black-50 font-16">موجودی کیف پول شما: {{ number_format($user->wallet) }} ریال
                        (قابل خرید - غیر قابل برداشت) </span>
                    <br>
                    <br>
                    <span class="text-black-50 font-16">موجودی اعتبار شما: {{ number_format($user->credit) }} ریال (غیر
                        قابل خرید - غیر قابل برداشت) </span>


                    @if(auth()->check() && auth()->user()->organization_id)
                    @foreach(auth()->user()->organization->services as $service)
                    <br>
                    <br>
                    <span class="text-black-50 font-16"> خدمت {{ $service->title }} : <a
                            class="btn btn-primary mr-2 text-white py-1"
                            href="/کانون-پرورش-فکری-کودکان-و-نوجوانان-استان-البرز/services/{{ $service->slug }}">برای
                            استفاده اینجا کلیک کنید</a> </span>
                    @endforeach
                    @endif

                </div>

            </div>
            <a style="display: none" class="btn btn-success text-white py-1 mt-3 mb-3"
                href="{{ route('profile.wallet') }}">افزایش
                موجودی</a>
            <h6 class="text-black-50 mb-2">درصد تکمیل اطلاعات کاربری شما </h6>
            <div class="progress">
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $profile_percent }}%;"
                    aria-valuenow="{{ $profile_percent }}" aria-valuemin="0" aria-valuemax="100">{{ $profile_percent }}%
                </div>
            </div>
        </div>
        <form action="/profile" method="post" enctype="multipart/form-data">
            @csrf
            <div class="tab-pane fade show active" id="list-profile" role="tabpanel"
                aria-labelledby="list-profile-list">
                <div id="error-container">
                    @foreach($errors->all() as $error)
                        <div class="alert alert-danger mb-4" role="alert">
                            {{ $error }}
                        </div>
                    @endforeach
                </div>
                <div class="col-12 card profile-box bg-gray p-5 my-4 position-relative">

                    <span class="title-profile-box text-white position-absolute px-3 py-2">اطلاعات تکمیلی</span>
                    <div class="form-row my-2">
                        <div class="col-12 col-md-6">
                            <input class="form-control mb-1" type="text" disabled placeholder="شماره موبایل"
                                name="phone" value="{{$user->phone}}">
                        </div>
                        <div class="col-12 col-md-6">
                            <input class="form-control mb-1" type="text" placeholder="کد ملی" name="melicode"
                                value="{{ $user->melicode }}">
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-12 col-md-4">
                            <input class="form-control mb-1" type="text" placeholder="نام" name="name"
                                value="{{ $user->name }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <input class="form-control mb-1" type="text" placeholder="نام خانوادگی" name="family"
                                value="{{ $user->family }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <input class="form-control mb-1" type="text" placeholder="نام پدر" name="father"
                                value="{{ $user->father }}">
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-12 col-md-4">
                            <input class="form-control mb-1" type="text" placeholder="ایمیل" name="email"
                                value="{{ $user->email }}">
                        </div>
                        <div class="col-12 col-md-4">
                            <select class="form-control mb-1" name="province_id" id="province_id">
                                @foreach(\App\Models\Province::get() as $pro)
                                <option value="{{ $pro->id }}" {{ $pro->id == $user->province_id ? 'selected' : '' }}>{{
                                    $pro->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <select class="form-control mb-1" name="city_id" id="city_id" required>
                                @foreach(\App\Models\City::get() as $city)
                                <option value="{{ $city->id }}" {{ $city->id == $user->city_id ? 'selected' : '' }}>{{
                                    $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row my-2">
                        <div class="col-12 col-md-4">
                            <select class="form-control mb-1" name="birth_year" id="">
                                @foreach(\App\Models\year::get() as $year)
                                <option value="{{ $year->id }}" {{ $user->birth_year == $year->id ? 'selected' : ''
                                    }}>{{ $year->year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <select class="form-control mb-1" name="birth_month" id="">
                                @foreach(\App\Models\month::get() as $month)
                                <option value="{{ $month->id }}" {{ $user->birth_month == $month->id ? 'selected' : ''
                                    }}>{{ $month->month }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <select class="form-control mb-1" name="birth_day" id="">
                                @foreach(\App\Models\day::get() as $day)
                                <option value="{{ $day->id }}" {{ $user->birth_day == $day->id ? 'selected' : '' }}>{{
                                    $day->day }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-12 card profile-box bg-gray p-5 my-4 position-relative">
                    <span class="title-profile-box text-white position-absolute px-3 py-2">بارگذاری مدارک</span>
                    <div class="col-12">
                        <div class="card-group">
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    @if($user->imgmeli)
                                    <div id="imgmeli_src" class="show">
                                    <a href="{{ $user->imgmeli }}" target="_blank">
                                        <img src="{{ asset($user->imgmeli) }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                    </a>
                                    {{-- inside _src on purpose: an ajax upload empties this div, so the
                                         hint disappears by itself once the image is no longer legacy --}}
                                    @if($user->mediaIsLegacy('imgmeli'))
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
                                        <label for="imgmeli" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="imgmeli"  type="file" id="imgmeli" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgmeli_path" class="path" name="urlimgmeli">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card">
                                    @if($user->imgshenasname)
                                    <div id="imgshenasname_src" class="show">
                                    <a href="{{ $user->imgshenasname }}" target="_blank">
                                        <img src="{{ asset($user->imgshenasname) }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                    </a>
                                    @if($user->mediaIsLegacy('imgshenasname'))
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
                                        <label for="imgshenasname" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="imgshenasname"  type="file" id="imgshenasname" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgshenasname_path" class="path" name="urlimgshenasname">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card">
                                    @if($user->imgsignature)
                                    <div id="imgsignature_src" class="show">
                                    <a href="{{ $user->imgsignature }}" target="_blank">
                                        <img src="{{ asset($user->imgsignature) }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                    </a>
                                    @if($user->mediaIsLegacy('imgsignature'))
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
                                        <label for="imgsignature" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="imgsignature"  type="file" id="imgsignature" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgsignature_path" class="path" name="urlimgsignature">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card">
                                    @if($user->avatar)
                                    <div id="personalPicture_src" class="show">
                                    <a href="{{ $user->avatar }}" target="_blank">
                                        <img src="{{ asset($user->avatar) }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                    </a>
                                    {{-- personalPicture is the DOM field name for the 'avatar' collection --}}
                                    @if($user->mediaIsLegacy('avatar'))
                                    <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                    @endif
                                    </div>
                                    @else
                                    <div id="personalPicture_src" class="show">
                                    <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                    </div>
                                    @endif
                                    <div class="card-body">
                                        <label>تصویر پرسنلی</label>
                                        <br>
                                        <label for="personalPicture" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="personalPicture"  type="file" id="personalPicture" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="personalPicture_path" class="path" name="urlpersonalPicture">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="card">
                                    @if($user->imgturnover)
                                    <div id="imgturnover_src" class="show">
                                    <a href="{{ $user->imgturnover }}" target="_blank">
                                        <img src="{{ asset($user->imgturnover) }}"
                                            class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                    </a>
                                    @if($user->mediaIsLegacy('imgturnover'))
                                    <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                    @endif
                                    </div>
                                    @else
                                    <div id="imgturnover_src" class="show">
                                    <div class="d-flex align-items-center justify-content-center text-muted" style="height:150px;background:#f8f9fa;">بدون تصویر</div>
                                    </div>
                                    @endif
                                    <div class="card-body">
                                        <label>تصویر ثنا</label>
                                        <br>
                                        <label for="imgturnover" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i
                                                class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="imgturnover" type="file" id="imgturnover"
                                            class="filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgturnover_path" class="path" name="urlimgturnover">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 card profile-box bg-gray p-5 my-4 position-relative">
                    <span class="title-profile-box text-white position-absolute px-3 py-2">ویرایش رمز عبور</span>
                    <div class="form-row my-2">
                        <div class="col-12 col-md-6">
                            <input type="password" class="form-control mb-1" name="password"
                                placeholder="رمز عبور جدید">
                        </div>
                        <div class="col-12 col-md-6">
                            <input type="password" class="form-control mb-1" name="password_confirmation"
                                placeholder="تکرار رمز عبور جدید">
                        </div>
                    </div>
                </div>

                <div class="col-12 mx-0">
                    <input type="submit" class="btn btn-success py-2 px-5 mx-0" value="ثبت">
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
@section('js')
{{--
    No adminJs.js here — /profile is a CUSTOMER page (auth, not admin). site-upload.js
    (site/master.blade.php, via site/profile/master.blade.php) already serves the five
    document uploads above, and @yield('js') renders AFTER it, so adminJs.js overrode
    it: every remove (X) posted to /dashboard/removeMedia, which requires
    CheckAdminAuthenticated — a logged-in customer passes auth but fails that, so the
    removes silently failed. The province -> city cascade and #uploadAvatar are in
    site/js/app.js.
--}}

<script type="text/javascript">
    // Default Configuration
    $(document).ready(function () {
        toastr.options = {
            'closeButton': true,
            'debug': false,
            'newestOnTop': false,
            'progressBar': false,
            'positionClass': 'toast-top-right',
            'preventDuplicates': false,
            'showDuration': '1000',
            'hideDuration': '1000',
            'timeOut': '5000',
            'extendedTimeOut': '1000',
            'showEasing': 'swing',
            'hideEasing': 'linear',
            'showMethod': 'fadeIn',
            'hideMethod': 'fadeOut',
        }
    });
    @if (session() -> get('payment') == 'danger')
        toastr.error('شما قبلا از این اعتبار استفاده کرده ایدد')
    @endif
</script>
@endsection
