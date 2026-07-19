@extends('site.representation.master')

@section('title')
تماس با ما
@endsection

@section('content')

<div class="contact">
    {{--contact--}}
    <div class="section position-relative">
        <div class="overlay position-absolute"></div>
        <div class="container py-4 col-12 col-sm-6 col-md-6">
            <div class="row">
                <div class="col-12 pt-5">
                    <h2 class="text-orange text-center">{{ $contact->title }}</h2>
                    <p class="mt-3">{{ $contact->description }}</p>
                </div>
                <div class="col-12 my-auto pt-4">
                    <div class="card">
                        <div class="card-body">
                            <form action="/savecontact" method="post" class="row" enctype="multipart/form-data">
                                @csrf
                                <div class="row col-12 w-75">
                                    <div class="col-12 mb-3">
                                        @include('Admin.layouts.errors')
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <input type="text" name="name" placeholder="نام" class="form-control">
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <input type="text" name="family" placeholder="نام خانوادگی"
                                            class="form-control">
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <input type="number" name="phone" placeholder="شماره موبایل"
                                            class="form-control">
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <select class="form-control" type="int" name="gender" id="gender" required>
                                            <option value="" disabled selected>جنسیت</option>
                                            <option value="0">آقا</option>
                                            <option value="1">خانم</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <select class="form-control" type="int" name="province" id="province_id"
                                            required>
                                            <option value="">انتخاب استان</option>
                                            @foreach(\App\Models\Province::all() as $province)
                                            <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6 mb-3">
                                        <select class="form-control" type="int" name="city" id="city_id"
                                            required>
                                            <option value="">انتخاب شهر</option>
                                            @foreach(\App\Models\City::all() as $city)
                                            <option value="{{ $city->id }}">{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label>پیام مربوط به کدام بخش است؟</label>
                                        <select class="form-control" name="card_id">
                                            <option selected disabled>واحد مربوطه</option>
                                            @foreach($cards as $card)
                                            <option value="{{ $card->id }}">{{ $card->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label>متن پیام</label>
                                        <textarea name="text" class="form-control no-resize" rows="6"
                                            placeholder="پیام خود را وارد نمایید"></textarea>
                                    </div>
                                    {{-- Ajax multi-upload (mirrors site/contact.blade.php): hidden urlimages[] ids
                                         are attached to the Contact's Spatie collection by savecontact(). --}}
                                    <div class="col-12 mb-3">
                                        <label>تصاویر :</label>
                                        <div id="addnew" class="mt-1">
                                            <label for="images" class="btn btn-primary custom-file-upload border border-1">
                                                <i class="fa fa-upload ml-1" aria-hidden="true"></i>بارگذاری تصاویر
                                            </label>
                                            <input name="images[]" type="file" id="images"
                                                accept="image/*" data-images-only="1"
                                                class="d-none filestyle file-upload file_upload_ajax file-up"
                                                data-buttonname="btn-white">
                                        </div>
                                        <div id="images_newlink" class="col-12 row">
                                            <div></div>
                                        </div>
                                        <div id="images_newlinktpl" class="d-none">
                                            <div id="images_src" class="show"></div>
                                            <input type="hidden" id="images" class="path" name="urlimages[]">
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <input type="submit" value="ثبت"
                                            class="btn btn-orange btn-block text-white font-14">
                                    </div>

                                </div>
                            </form>
                            <div class="col-12 pb-4 social">
                                <ul class="d-flex list-unstyled justify-content-around align-items-center">
                                    <li>
                                        <a href="{{ $setting->linkedin }}">
                                            <span>
                                                <i class="fab fa-linkedin i-icon"></i>
                                            </span>
                                        </a>
                                    </li>
                                    |
                                    <li>
                                        <a href="{{ $setting->instagram }}">
                                            <span>
                                                <i class="fab fa-instagram i-icon"></i>
                                            </span>
                                        </a>
                                    </li>
                                    |
                                    <li>
                                        <a href="{{ $setting->telegram }}">
                                            <span>
                                                <i class="fab fa-telegram-plane i-icon"></i>
                                            </span>
                                        </a>
                                    </li>
                                    |
                                    <li>
                                        <a href="{{ $setting->twitter }}">
                                            <span>
                                                <i class="fab fa-twitter i-icon"></i>
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>

</div>

@if(session()->get('contact_success')=='success')
<span id="contact_success"></span>
@elseif(session()->get('contact_error')=='success')
<span id="contact_error"></span>
@endif

@endsection
