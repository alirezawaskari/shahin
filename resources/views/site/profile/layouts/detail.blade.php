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
                            <div class="col-12 col-md-4">
                                <div class="card">
                                    @if(empty($user->imgmeli))
                                        <p class="my-4 text-danger text-center">ثبت نشده</p>
                                        <div class="card-body">
                                            <h5 class="card-title">کارت ملی</h5>
                                            <input name="imgmeli" type="file" class="filestyle" data-buttonname="btn-white">
                                        </div>
                                    @else
                                        <a href="{{ $user->imgmeli }}">
                                            <img src="{{ $user->imgmeli }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                        </a>
                                        <div class="card-body">
                                            <h5 class="card-title">کارت ملی</h5>
                                            <input name="imgmeli" type="file" class="filestyle" data-buttonname="btn-white">
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="card">
                                    @if(empty($user->imgshenasname))
                                        <p class="my-4 text-danger text-center">ثبت نشده</p>
                                        <div class="card-body">
                                            <h5 class="card-title">شناسنامه</h5>
                                            <input name="imgshenasname" type="file" class="filestyle" data-buttonname="btn-white">
                                        </div>
                                    @else
                                        <a href="{{ $user->imgshenasname }}">
                                            <img src="{{ $user->imgshenasname }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                        </a>
                                        <div class="card-body">
                                            <h5 class="card-title">شناسنامه</h5>
                                            <input name="imgshenasname" type="file" class="filestyle" data-buttonname="btn-white">
                                        </div>
                                    @endif
                                </div>
                            </div>
							<div class="col-12 col-md-4">
                                <div class="card">
                                    @if(empty($user->avatar))
                                        <p class="my-4 text-danger text-center">ثبت نشده</p>
                                        <div class="card-body">
                                            <h5 class="card-title">تصویر پرسنلی 3*4</h5>
                                            <input name="avatar" type="file" class="filestyle" data-buttonname="btn-white">
                                        </div>
                                    @else
                                        <a href="{{ $user->avatar }}">
                                            <img src="{{ $user->avatar }}" class="card-img-top" style="height:150px;width:100%;object-fit:contain;background:#f8f9fa;">
                                        </a>
                                        <div class="card-body">
                                            <h5 class="card-title">تصویر پرسنلی 3*4</h5>
                                            <input name="avatar" type="file" class="filestyle" data-buttonname="btn-white">
                                        </div>
                                    @endif
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
