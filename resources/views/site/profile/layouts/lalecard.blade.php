@extends('site.profile.master')

@section('content2')
    <div class="col-12 col-md-9 mt-4 mt-md-0">
        <div class="tab-content" id="nav-tabContent">
			 <div class="col-12">
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="list-home" role="tabpanel"
                         aria-labelledby="list-home-list">
						@if(auth()->user()->lalecard == 1)
                        <div class="alert alert-success text-center">
                           لاله کارت شما صادر شده است
                        </div>
						@endif

						@if(auth()->user()->lalecard == 2)
                        <div class="alert alert-danger text-center">
                           اطلاعات شما برای دریافت لاله کارت کامل نیست
                        </div>
						@endif
                    </div>
                </div>
            </div>

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
                        <span class="title-profile-box text-white position-absolute px-3 py-2">مدارک</span>
                        <div class="form-row my-2">
                            <div class="col-12 col-md-4">
                                <div class="card">
                                    @if($user->imgmeli)
                                        <div id="imgmeli_src" class="show">
                                            <a href="{{ $user->imgmeli }}" target="_blank">
                                                <img src="{{ $user->imgmeli }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                            </a>
                                            {{-- inside _src: an ajax upload empties this div, clearing the hint --}}
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
                                        <h5 class="card-title">کارت ملی</h5>
                                        <label for="imgmeli" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="imgmeli" type="file" id="imgmeli" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgmeli_path" class="path" name="urlimgmeli">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card">
                                    @if($user->imgshenasname)
                                        <div id="imgshenasname_src" class="show">
                                            <a href="{{ $user->imgshenasname }}" target="_blank">
                                                <img src="{{ $user->imgshenasname }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
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
                                        <h5 class="card-title">شناسنامه</h5>
                                        <label for="imgshenasname" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="imgshenasname" type="file" id="imgshenasname" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
                                        <input type="hidden" id="imgshenasname_path" class="path" name="urlimgshenasname">
                                    </div>
                                </div>
                            </div>
							<div class="col-12 col-md-4">
                                <div class="card">
                                    @if($user->avatar)
                                        <div id="personalPicture_src" class="show">
                                            <a href="{{ $user->avatar }}" target="_blank">
                                                <img src="{{ $user->avatar }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
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
                                        <h5 class="card-title">تصویر پرسنلی 3*4</h5>
                                        <label for="personalPicture" id="up-label" class="btn btn-primary custom-file-upload border border-1"><i class="fa fa-upload ml-2" aria-hidden="true"></i> انتخاب تصویر </label>
                                        <input name="personalPicture" type="file" id="personalPicture" class="filestyle file-upload file_upload_ajax file-up" data-buttonname="btn-white" accept=".jpg,.jpeg,.png">
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
@endsection
