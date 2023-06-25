@extends('site.master')

@section('title')
    اعطاء مجوز همکاری به مراکز و فروشگاهها
@endsection
@section('content')
    <div>
        {{--banner--}}
        @component('site.layouts.breadcrumb',['title'=>'اعطاء مجوز همکاری به مراکز و فروشگاهها','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent
        {{--article--}}
        @if(!auth()->check())
            <div class="col-12 mt-4">
                <h6>مخاطب محترم : مجوزات شبکه خدمات رفاهی لاله در ذیل همین صفحه درج شده و قابل بازدید می باشد. جهت تماس با کارشناسان یا دریافت اطلاعات بیشتر فرم زیر را تکمیل نمایید.</h6>
            </div>
            <div class="col-12 col-md-5 m-auto">
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
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary text-white px-3 mt-2 btn-block">ثبت نام
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="callcenter_id" value="{{ $callcenter->id }}">
                    <input type="hidden" name="type_service" value="100">
                </form>
            </div>
        @else
            <div class="row">
                <div class="col-md-6 mr-5" style="text-align: right">
                    <br>
                    <video width="100%" height="100%" controls>
                        <source src="https://lalecard.ir/upload/ads_videos/ash1.mp4" type="video/mp4">
                    </video>
                </div>
            </div>
            <div class="row mt-4 m-4">
                <div class="col-md-8">
                    {!! $service->left_content !!}
                    @if(auth()->check())
                        @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => 0 , 'callcenter' => $callcenter->id])
                    @endif
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
                    <div class="section py-4">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-md-12 py-5 my-auto mr-auto">
                                    {!! $service->content !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/mosahebe1.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/poshak.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/agency.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/b1.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/dandanpezeshki.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/company.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/b2.mp4" type="video/mp4">
                                    </video>
                                </div>>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/mobl.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="col-md-7 mr-5" style="text-align: right">
                                    <br>
                                    <video width="100%" height="100%" controls>
                                        <source src="https://lalecard.ir/upload/ads_videos/lebas1.mp4" type="video/mp4">
                                    </video>
                                    <br>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    @foreach($images as $image)
                        <a class="lightbox" href="#dog">
                            <img src="{{url($image->value)}}" width="100%" onclick="linkImage(this.src)">
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="lightbox-target" id="dog">
                <img src="{{url($image->value)}}" width="100%" class="mb-3" id="image">
                <a class="lightbox-close" href="#"></a>
            </div>
        @endif
    </div>
@endsection
@section('js')
    <script src="/assets/site/js/fix-number.js?v=1"></script>
    <script src="/assets/site/js/get-service.js?v=1"></script>
@endsection
