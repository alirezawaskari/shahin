@extends('site.representation.master')
@section('title')
    {{$representation->fa_name}}
@endsection
@section('content')
    <div class="container">
        <div class="service">
            <div class="header-representation">
                <div class="container-fluid">
                    <div class="row pt-3">
                        <div class="col-6 d-flex align-items-center">
                            <a href=""><img class="mr-md-5 d-none d-md-block" src="{{ asset('assets/site/images/footer-logo.png') }}" width="150px" alt="شبکه خدمات رفاهی لاله"></a>
                            @auth
                                <a href="/cart" class="btn btn-warning position-relative ml-2">
                                    <i class="fas fa-shopping-cart font-16 ml-2"></i>
                                    سبدخرید
                                    <span class="cart-count d-flex justify-content-center align-items-center position-absolute font-12 text-dark" id="cart-count">{{ session()->has('cart.total.count') ? session()->get('cart.total.count') : 0 }}</span>
                                </a>
                            @endauth
                        </div>
                        <div class="col-6 d-flex justify-content-end">
                            <a href="{{ $representation->path() }}">
                            <span class="ml-md-5 text-dark font-16 d-flex align-items-center">
                            برگشت
                            <i class="fas fa-arrow-left text-orange font-18 m-2"></i>
                        </span>
                            </a>
                        </div>
                    </div>
                    @if(!auth()->check())
                        <h3 class="mb-5 text-danger text-center alert alert-warning">جهت دریافت اطلاعات کامل ابتدا ورود یا ثبت نام نمایید </h3>
                    @endif
                    @if(auth()->check())
                        <div class="row">
                            <div class="col-12 col-md-5 py-5 my-auto mr-auto">

                                {!! $service->left_content !!}


                                @if($service->special == 1)
                                    <h6 class="text-green my-2 font-16">این خدمات ویژه شهر های تهران و کرج می باشد</h6>
                                @endif
                                @if(!is_null($required))
                                    @if($required == false)
                                        <div class="rol2">
                                            <p>برای خرید این خدمت باید ابتدا خدمات زیر را خریداری نمایید.</p>
                                            @foreach($requireServices as $ser)
                                                <a href="/{{$representation->slug}}/{{$ser->slug}}" class="btn btn-orange text-white mb-2">
                                                    {{ $ser->title }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => $representation->id, 'callcenter' => $callcenter->id])
                                    @endif
                                @else
                                    @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => $representation->id, 'callcenter' => $callcenter->id])
                                @endif
                            </div>
                            <div class="d-none d-md-block col-md-6 pl-0 text-left">
                                <img src="{{asset('/assets/site/images/fastregister-img.png')}}" class="img-fluid">
                            </div>
                        </div>

                    @else
                        {!! $service->left_content !!}

                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger my-3" role="alert">
                                {{ $error }}
                            </div>
                        @endforeach
                        <div class="row">
                            <div class="col-12 col-md-6 border">
                                <p class="text-orange font-18 text-center">
                                    <strong>ورود</strong>
                                </p>
                                <p class="text-black-50 font-14 text-center">اگر قبلا ثبت نام کرده اید لطفا وارد حساب خود شوید</p>
                                <form action="{{ route('login') }}" method="POST" class="my-4 px-0 px-md-5">
                                    @csrf
                                    <input class="form-control my-2" type="number" name="phone" required placeholder="شماره موبایل"/>
                                    <input class="form-control my-2" type="password" required name="password" placeholder="رمز عبور"/>
                                    <input class="form-control my-2" type="hidden" required name="representation"/>
                                    <input class="btn btn-orange text-white font-16 btn-block my-2" type="submit" value="ورود"/>
                                </form>
                                <a href="{{ route('reset_pass_form') }}" class="text-black-50 border-0 font-14 bold mr-2 mt-2">رمز عبور خود را فراموش کرده اید؟</a>
                            </div>
                            <div class="col-12 col-md-6 border">
                                <p class="text-orange font-18 text-center">
                                    <strong>ثبت نام</strong>
                                </p>
                                <p class="text-black-50 font-14 text-center">اگر حساب کاربری ندارید لطفا ثبت نام نمایید</p>
                                <form action="/fastregister" method="POST" class="my-4">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <input class="form-control my-2" type="text" name="name" required placeholder="نام"
                                                   value="{{ old('name') }}">
                                            @if ($errors->has('name'))
                                                <span class="d-block text-red text-left">{{ $errors->first('name') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <input class="form-control my-2" type="text" name="family" required
                                                   placeholder="نام خانوادگی" value="{{ old('family') }}">
                                            @if ($errors->has('family'))
                                                <span class="d-block text-red text-left">{{ $errors->first('family') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <input class="form-control my-2" type="text" name="phone" required
                                                   placeholder="شماره موبایل" value="{{ old('phone') }}">
                                            @if ($errors->has('phone'))
                                                <span class="d-block text-red text-left">{{ $errors->first('phone') }}</span>
                                            @endif
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <select class="form-control my-2" type="int" name="service_id" id="service_id" required>
                                                @foreach($service->service_items->where('isShow' , true) as $item)
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
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-orange text-white px-3 mt-2 btn-block">ثبت نام
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="representation_id" value="{{ $representation->id }}">
                                    <input type="hidden" name="callcenter_id" value="{{ $callcenter->id }}">
                                </form>

                            </div>
                        </div>
                    @endif

                </div>
            </div>

            <div class="section py-4">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 col-md-5 py-5 my-auto mr-auto">
                            {!! $service->txt_box_1 !!}
                        </div>
                        <div class="d-none d-md-block col-md-6 pl-0 text-left">
                            <img src="{{ $service->img_box_1 }}" class="img-fluid" style="max-width: 100%;height: 350px;margin-top:80px;">
                        </div>
                    </div>
                </div>
            </div>
            {{--section--}}
            <div class="video-section py-5 bg-gray">
                <div class="container">
                    <div class="row py-5">
                        <div class="col-12">
                            {!! $service->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
    <script>
        $('#date').persianDatepicker({
            format: 'YYYY/MM/D',
            autoClose: true,
        });
    </script>
@endsection

