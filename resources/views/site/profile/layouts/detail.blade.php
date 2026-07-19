@extends('site.profile.master')

@section('content2')

    <div class="col-12 col-md-9 mt-4 mt-md-0">
        <div class="tab-content" id="nav-tabContent">
            <form action="/profile" method="post" enctype="multipart/form-data">
                @csrf
                <div class="tab-pane fade show active" id="list-profile" role="tabpanel"
                     aria-labelledby="list-profile-list">
                    @foreach($errors->all() as $error)
                        <div class="alert alert-danger mb-4" role="alert">
                            {{ $error }}
                        </div>
                    @endforeach

                    <div class="col-12 card profile-box bg-gray p-5 my-4 position-relative">

                        <span class="title-profile-box text-white position-absolute px-3 py-2">اطلاعات تکمیلی</span>
                        <div class="form-row my-2">
                            <div class="col-12 col-md-6">
                                <input class="form-control mb-1" type="text" disabled placeholder="شماره موبایل" name="phone"
                                       value="{{$user->phone}}">
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
                            <div class="col-12 col-md-6">
                                <input type="password" class="form-control mb-1" name="password" placeholder="رمز عبور جدید">
                            </div>
                            <div class="col-12 col-md-6">
                                <input type="password" class="form-control mb-1" name="password_confirmation" placeholder="تکرار رمز عبور جدید">
                            </div>
                        </div>
                        <div class="form-row my-2">
                            <div class="col-12 col-md-4">
                                <select class="form-control mb-1" name="birth_year" id="">
                                    @foreach(\App\Models\year::get() as $year)
                                        <option value="{{ $year->id }}" {{ $user->birth_year == $year->id ? 'selected' : '' }}>{{ $year->year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <select class="form-control mb-1" name="birth_month" id="">
                                    @foreach(\App\Models\month::get() as $month)
                                        <option value="{{ $month->id }}" {{ $user->birth_month == $month->id ? 'selected' : '' }}>{{ $month->month }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <select class="form-control mb-1" name="birth_day" id="">
                                    @foreach(\App\Models\day::get() as $day)
                                        <option value="{{ $day->id }}" {{ $user->birth_day == $day->id ? 'selected' : '' }}>{{ $day->day }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 card profile-box bg-gray p-5 my-4 position-relative">
                        <span class="title-profile-box text-white position-absolute px-3 py-2">اطلاعات تکمیلی</span>
                        <div class="form-row my-2">
                            <div class="col-12 col-md-4">
                                <input class="form-control mb-1" type="text" placeholder="شماره شناسنامه" name="shenasnamecode"
                                       value="{{ $user->shenasnamecode }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <input class="form-control mb-1" type="text" placeholder="شماره ثابت" name="call"
                                       value="{{ $user->call }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <input class="form-control mb-1" type="text" placeholder="ایمیل" name="email"
                                       value="{{ $user->email }}">
                            </div>
                        </div>
                        <div class="form-row my-2">
                            <div class="col-12 col-md-4">
                                <input class="form-control mb-1" type="text" placeholder="حروف سریال شناسنامه" name="serialcharacter"
                                       required value="{{ $user->serialcharacter }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <input class="form-control mb-1" type="text" placeholder="عدد بالا سریال شناسنامه" name="serialtop"
                                       required  value="{{ $user->serialtop }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <input class="form-control mb-1" type="text" placeholder="عدد پایین سریال شناسنامه" name="serialbottom"
                                       required  value="{{ $user->serialbottom }}">
                            </div>
                        </div>
                        <div class="form-row my-2">
                            <div class="col-12 col-md-4">
                                <select class="form-control mb-1" name="sex">
                                    <option value="0" {{ $user->sex == 0 ? 'selected' : '' }}>مرد</option>
                                    <option value="1" {{ $user->sex == 1 ? 'selected' : '' }}>زن</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <select class="form-control mb-1" name="province_id" id="province_id">
                                    @foreach(\App\Models\Province::get() as $pro)
                                        <option value="{{ $pro->id }}" {{ $pro->id == $user->province_id ? 'selected' : '' }}>{{ $pro->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <select class="form-control mb-1" name="city_id" id="city_id" required>
                                    @foreach(\App\Models\City::get() as $city)
                                        <option value="{{ $city->id }}" {{ $city->id == $user->city_id ? 'selected' : '' }}>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row my-2">
                            <div class="col-12 col-md-4">
                                <input class="form-control mb-1" type="text" placeholder="کد پستی" name="postalcode"
                                       value="{{ $user->postalcode }}">
                            </div>
                            <div class="col-12 col-md-8">
                                <input class="form-control mb-1" type="text" name="address"
                                       placeholder="آدرس دقیق" value="{{ $user->address }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 card profile-box bg-gray p-5 my-4 position-relative">
                        <span class="title-profile-box text-white position-absolute px-3 py-2">مدارک</span>
                        <div class="form-row my-2">
                            {{-- Ajax widgets (mirrors profile/layouts/index). The hidden url* fields are
                                 exactly what ProfileController@update already reads -> attachTo(), so these
                                 land in the user's Spatie collections instead of a raw file POST. --}}
                            <div class="col-12 col-md-4">
                                <div class="card">
                                    <div id="imgmeli_src" class="show">
                                        @if(empty($user->imgmeli))
                                        <p class="my-4 text-danger text-center">ثبت نشده</p>
                                        @else
                                        <a href="{{ $user->imgmeli }}" target="_blank" rel="noopener">
                                            <img src="{{ $user->imgmeli }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                        </a>
                                        {{-- inside _src: an ajax upload empties this div, clearing the hint --}}
                                        @if($user->mediaIsLegacy('imgmeli'))
                                        <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                        @endif
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">کارت ملی</h5>
                                        <label for="imgmeli" class="btn btn-primary custom-file-upload border border-1">
                                            <i class="fa fa-upload ml-1" aria-hidden="true"></i>انتخاب تصویر
                                        </label>
                                        <input name="imgmeli" type="file" id="imgmeli"
                                            class="d-none filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgmeli_path" class="path" name="urlimgmeli">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card">
                                    <div id="imgshenasname_src" class="show">
                                        @if(empty($user->imgshenasname))
                                        <p class="my-4 text-danger text-center">ثبت نشده</p>
                                        @else
                                        <a href="{{ $user->imgshenasname }}" target="_blank" rel="noopener">
                                            <img src="{{ $user->imgshenasname }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                        </a>
                                        @if($user->mediaIsLegacy('imgshenasname'))
                                        <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                        @endif
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">شناسنامه</h5>
                                        <label for="imgshenasname" class="btn btn-primary custom-file-upload border border-1">
                                            <i class="fa fa-upload ml-1" aria-hidden="true"></i>انتخاب تصویر
                                        </label>
                                        <input name="imgshenasname" type="file" id="imgshenasname"
                                            class="d-none filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgshenasname_path" class="path" name="urlimgshenasname">
                                    </div>
                                </div>
                            </div>
							<div class="col-12 col-md-4">
                                <div class="card">
                                    {{-- avatar: the controller reads urlpersonalPicture for the ajax path
                                         (it keeps `avatar` for the direct-file useravatar widget). --}}
                                    <div id="personalPicture_src" class="show">
                                        @if(empty($user->avatar))
                                        <p class="my-4 text-danger text-center">ثبت نشده</p>
                                        @else
                                        <a href="{{ $user->avatar }}" target="_blank" rel="noopener">
                                            <img src="{{ $user->avatar }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                        </a>
                                        {{-- personalPicture is the DOM field name for the 'avatar' collection --}}
                                        @if($user->mediaIsLegacy('avatar'))
                                        <small class="d-block text-warning mt-1 px-2">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
                                        @endif
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">تصویر پرسنلی 3*4</h5>
                                        <label for="personalPicture" class="btn btn-primary custom-file-upload border border-1">
                                            <i class="fa fa-upload ml-1" aria-hidden="true"></i>انتخاب تصویر
                                        </label>
                                        <input name="personalPicture" type="file" id="personalPicture"
                                            class="d-none filestyle file-upload file_upload_ajax file-up"
                                            data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="personalPicture_path" class="path" name="urlpersonalPicture">
                                    </div>
                                </div>
                            </div>



                        </div>
                        <p class="bold font-16 mt-3 text-black-50">راهنمای بارگزاری فایل در سامانه</p>
                        <ul class="px-0 mr-3">
                            <li class="my-1">پسوند های مجاز: jpg,jpeg,png</li>
                            <li class="my-1">حجم فایل نباید بیشتر از 4 مگابایت باشد</li>
                        </ul>

                    </div>
                    <div class="col-12 mx-0">
                        <input type="submit" class="btn btn-success py-2 px-5 mx-0" value="ثبت">
                    </div>
                </div>
            </form>
        </div>
    </div>


    @if(session()->get('profile_update')=='success')
        <span id="profile_update"></span>
    @endif


@endsection
