@extends('site.master')

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
                                <div class="col-12 w-75">
                                    @include('Admin.layouts.errors')
                                    <div class="form-row">
                                        <div class="col-12 mb-3">
                                            <label>نام و نام خانوادگی</label>
                                            <input type="text" name="name" placeholder="نام و نام خانوادگی" class="form-control">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label>شماره موبایل</label>
                                            <input type="number" name="phone" placeholder="شماره موبایل" class="form-control">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-12 mb-3">
                                            <label>پیام مربوط به کدام بخش است؟</label>
                                            <select class="form-control" name="card_id">
                                                <option selected disabled>واحد مربوطه</option>
                                                @foreach($cards as $card)
                                                <option value="{{ $card->id }}">{{ $card->description }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>متن پیام</label>
                                        <textarea name="text" class="form-control no-resize" rows="6" placeholder="پیام خود را وارد نمایید"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>تصاویر :</label>
                                        <input type="file" name="images[]" class="form-control" multiple="multiple">
                                    </div>
                                    <div class="form-group">
                                        <input type="submit" value="ثبت" class="btn btn-orange btn-block text-white font-14">
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
