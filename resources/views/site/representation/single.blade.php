@extends('site.representation.master')
@section('title'){{ $service->title }}@endsection
@section('description'){{ $service->description }}@endsection

@section('content')
    <div class="service">
        <div class="register-section py-4" id="one-section">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-5 py-5 my-auto mr-auto">
                        {!! $service->left_content !!}
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger my-3" role="alert">
                                {{ $error }}
                            </div>
                        @endforeach
                        @if(auth()->check() || isset($factor['id']))
                            @if($service->special == true)
                                <h6 class="text-green my-2 font-16">مرکز واقع در کرج می باشد.</h6>
                            @endif
                            @if(isset($check) && !$check)
                                @if(!is_null($required))
                                    @if($required == false)
                                        <div class="rol2">
                                            <p>برای خرید این خدمت باید ابتدا خدمات زیر را خریداری نمایید.</p>
                                            @foreach($requireServices as $ser)
                                                <a href="{{ $ser->path() }}" class="btn btn-orange text-white mb-2">
                                                    {{ $ser->title }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => $representation->id , 'callcenter' => $callcenter->id])
                                    @endif
                                @else
                                    @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => $representation->id , 'callcenter' => $callcenter->id])
                                @endif
                            @else
                                <span>شما قبلا این خدمت را خریده اید و امکان خرید مجدد وجود ندارد.</span>
                            @endif
                        @else
                            @include('auth.layout.check_register')
                            @include('auth.layout.login')
                            <form action="/fastregister" method="POST" class="my-4" id="registerForm">
                                @csrf
                                <div class="row" id="get-service-box" style="display: none">
                                    <input type="hidden" name="service_id" value="{{ $service->id }}">
                                    <div class="col-12 col-md-6">
                                        <input class="form-control my-2" type="text" name="name" required placeholder="نام"
                                               value="{{ old('name') }}" pattern="^[\u0600-\u06FF\s]+$"  title="لطفا با حروف فارسی وارد نمایید">
                                        @if ($errors->has('name'))
                                            <span class="d-block text-red text-left">{{ $errors->first('name') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input class="form-control my-2" type="text" name="family" required
                                               placeholder="نام خانوادگی" value="{{ old('family') }}" pattern="^[\u0600-\u06FF\s]+$"  title="لطفا با حروف فارسی وارد نمایید">
                                        @if ($errors->has('family'))
                                            <span class="d-block text-red text-left">{{ $errors->first('family') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <select class="form-control my-2" type="int" name="gender" id="gender" required>
                                            <option value="" disabled selected>جنسیت</option>
                                            <option value="0">آقا</option>
                                            <option value="1">خانم</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input class="form-control my-2 phone" type="text" id="user_service_phone" name="phone" required
                                               placeholder="شماره موبایل" value="{{ old('phone') }}">
                                        @if ($errors->has('phone'))
                                            <span class="d-block text-red text-left">{{ $errors->first('phone') }}</span>
                                        @endif
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <select class="form-control my-2" type="int" name="financial_plans_type_id" id="financial_plans_type_id" required>
                                                @foreach($financial_plans_types as $item)
                                                    <option value="{{ $item->id }}" {{ !$item->status ? 'disabled' : '' }}>{{ $item->title }}</option>
                                                @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <select class="form-control my-2" type="int" name="province_id" id="province_id" required>
                                            <option value="">انتخاب استان</option>
                                            @foreach(\App\Models\Province::all() as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <select class="form-control my-2" type="int" name="city_id" id="city_id" required>
                                            <option value="">انتخاب شهر</option>
                                            @foreach(\App\Models\City::all() as $city)
                                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <select class="form-control my-2" type="int" name="area_id" id="area_id">
                                            <option value="">انتخاب منطقه</option>
                                            @foreach(\App\Models\Area::all() as $area)
                                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            {{--<input style="cursor: pointer" class="form-check-input form-control border border-warning" type="checkbox" value="" id="invalidCheck2"   required>--}}
                                            <label class="form-check-label mr-3 alert alert-dark" for="invalidCheck2" >
                                               توضیحات مربوط به خدمت درخواستی را مطالعه نمودم
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary text-white px-3 mt-2 btn-block">ثبت نام جدید دریافت خدمت
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="callcenter_id" value="{{ $callcenter->id }}">
                                <input type="hidden" name="representation_id" value="{{ $representation->id }}">
                            </form>
{{--                            <a href="{{ route('login') }}" class="btn btn-orange text-white px-3 mt-2 btn-block">قبلا ثبت نام کرده اید؟اینجا کلیک کنید</a>--}}
                        @endif
                    </div>
                    <div class="d-none d-md-block col-md-6 pl-0 text-left">
                        <img src="{{ $service->img_box_1 }}" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>

        <div class="container mb-4" dir="rtl">
        <h5 class="mt-6">سوالات متداول طرح از دم قسط ویژه روز پدر: </h5>
        <div class="accordion mt-4" id="accordionExample">
            <div class="card text-right bg-white">
                <div class="card-header text-right" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapseOne" style="color: #333;">
                        میخوام الان ثبت نام کنم بعدا میتونم از خدمات این طرح استفاده کنم و از دم قسط پرداخت کنم ؟
                        </button>
                    </h2>
                </div>
                <div id="collapse1" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                    بله طرح های ویژه لاله کارت معمولا محدودیت زمانی در استفاده‌هم دارند ولی طرح از دم قسط روز پدر در صورت تکمیل فرایند ثبت نام و پرداخت هزینه تشکیل پرونده قابلیت استفاده تا یک سال پس از ثبت نام دارد منتهی صرفا درج مشخصات و عدم واریز هزینه تشکیل پرونده به منزله تکمیل ثبت نام نیست و ثبت نام شما کامل نمیشه و نفر بعدی فهرست جایگزین پرونده شما خواهد شد.
                    </div>
                </div>
            </div>
            <div class="card text-right bg-white">
                <div class="card-header text-right" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapseOne" style="color: #333;">
                        آیا تعداد وامهای خدماتی و اعتباری لاله در این طرح محدوده ؟ دلیلش چیه ؟
                        </button>
                    </h2>
                </div>
                <div id="collapse2" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                    بله کلا ۱۰۰ فقره پرونده از دم قسط در این طرح قابل اجرا و ثبت نام است ،
  همراه همیشگی لاله در شرایط کنونی متاسفانه پزشکان ، جراحان ، وکلا و حتی بنها ها و کابینت سازان کلا امکان اینکه درامدهاشون رو طی مدت زمان حتی ده روز بعد از خدمت دریافت کنند ندارند و تهیه لوازم و امکانات خاص نیاز شما هزینه بردار است ، لذا لاله برحسب توان مالی صندوق خود بخشی ازدرآمد های صندوق رو صرف حمایت از شما عزیزان میکنه که یقینا نمیشه به تعداد همگی رسید منتهی تا زمانی که ثبت نام این طرح انجام میشه و ردیفش بازه( گزینه سبز رنگ طرح ویژه ) میتونید ثبت نام کنید
                </div>
                </div>
            </div>
            <div class="card text-right bg-white">
                <div class="card-header text-right" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse3" aria-expanded="true" aria-controls="collapseOne" style="color: #333;">
                        اگر چک نداشته باشیم میتونیم از خدمات از دم قسط استفاده کنیم؟ 
                        </button>
                    </h2>
                </div>
                <div id="collapse3" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                    متاسفانه شرایط اقتصادی اجازه این رو نمیده که تمام مبلغ خدمت رو با سفته پرداخت کنید ولی میشه جای چک اصلی ( ضمانت) سفته داد و میشه تعداد چکهارو کمتر کرد.
                    </div>
                </div>
            </div>
            <div class="card text-right bg-white">
                <div class="card-header text-right" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse4" aria-expanded="true" aria-controls="collapseOne" style="color: #333;">
                        من چک از خودم ندارم میشه از شخص دیگری چک بگیرم؟
                        </button>
                    </h2>
                </div>
                <div id="collapse4" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <div class="card-body">
                    بله هم میتونید از اشخاص دیگر چک بگیرید و هم اون شخص میتونه در کنار شما از خدمات استفاده کنه و از خدمتی که مخصوص شما لاله ای هاست بهره مند بشه به این نحو کمکی هم به خودش شده نسبت فامیلی و آشنایی هم اهمیتی در کل پرونده نداره و میتونید از دوستان یا همکاران یا همسایگانتون چک بگیرید فقط نباید چک برگشتی داشته باشند.
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="banner position-relative">
            <img class="img-fluid banner-img" src="{{ $service->thumb }}" alt="{{ $service->title }}">
        </div>

        <div class="section py-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-5 py-5 my-auto mr-auto">
                        {!! $service->txt_box_1 !!}
                    </div>
                    <div class="d-none d-md-block col-md-6 pl-0 text-left">
                        <img src="{{ $service->img_box_3 }}" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
        <div class="video-section py-5 bg-gray">
            <div class="container">
                <div class="row py-5">
                    <div class="col-12">
                        {!! $service->content !!}
                    </div>
                </div>
            </div>
        </div>

		<h6 class="text-center mt-3">تصاویر</h6>
        <div class="col-12 row mb-4">
        @if(isset($service->workSamples))
            @foreach($service->workSamples as $work_sample)
                <div class="col-md-2 p-2">
                    <a href="{{ asset($work_sample->image) }}" target="_blank">
                        <img src="{{ $work_sample->image }}" class="card-img-top" height="250px">
                    </a>
                </div>
            @endforeach
        @endif
        </div>
    </div>
   @include('site.services.questions')
@endsection
@section('js')
    <script src="/assets/site/js/fix-number.js?v=1"></script>
    <script src="/assets/site/js/get-service.js?v=1.1"></script>
@endsection
