@extends('site.master')

@section('title')
    دریافت کارگزاری و همکاری
@endsection

@section('content')
    <div class="article">
        {{--banner--}}
        @component('site.layouts.breadcrumb',['title'=>'دریافت کارگزاری و همکاری','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        <div class="article-section agency pb-5 bg-gray">
            <div class="container">
                <div class="row">
                    <div class="card shadow w-100 bg-white article-box overflow-hidden border-0">
                        <div class="card-body">
                            <div class="col-12 col-md-12 py-5 my-auto mr-auto">
                                {!! $service->left_content !!}
                            </div>
                            <div class="col-12 col-md-12 py-5 my-auto mr-auto">
                                    @if(auth()->check())
                                        @include('site.layouts.add_to_cart' , ['service' => $service , 'representation' => 0 , 'callcenter' => $callcenter->id])
                                    @else
                                    <form action="/fastregister" method="POST" class="my-4" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <input class="form-control my-2" type="text" name="name" required placeholder="نام" value="{{ old('name') }}">
                                                @if ($errors->has('name'))
                                                    <span class="d-block text-red text-left">{{ $errors->first('name') }}</span>
                                                @endif
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <input class="form-control my-2" type="text" name="family" required placeholder="نام خانوادگی" value="{{ old('family') }}">
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
                                                    @foreach($service->service_items as $item)
                                                        <option value="{{ $item->id }}">{{ $item->title }}</option>
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
                                        <input type="hidden" name="callcenter_id" value="{{ $callcenter->id }}">
                                        <input type="hidden" name="type_service" value="100">
                                    </form>
                                    @endif
                            </div>
                            <div class="col-12 col-md-12 py-5 my-auto mr-auto">
                                <h6 style="text-align: center;">گالری عکس ها</h6>
								 <div class="container col-md-8">
                                @foreach($images as $image)
                                    <div class="mySlides">
                                        <img src="{{url($image->value)}}" width="100%">
                                    </div>
                                @endforeach

                                <div class="caption-container">
                                    <p id="caption"></p>
                                </div>

                                <div class="row">
                                    <?php $count = 1;?>
                                    @foreach($images as $image)
                                        <div class="column">
                                            <img class="demo cursor" src="{{url($image->value)}}" style="width:100%" onclick="currentSlide({{ $count }})">
                                        </div>
                                        <?php $count ++;?>
                                    @endforeach
                                </div>
                            </div>

							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var slideIndex = 1;
        showSlides(slideIndex);

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function currentSlide(n) {
            showSlides(slideIndex = n);
        }

        function showSlides(n) {
            var i;
            var slides = document.getElementsByClassName("mySlides");
            var dots = document.getElementsByClassName("demo");
            var captionText = document.getElementById("caption");
            if (n > slides.length) {slideIndex = 1}
            if (n < 1) {slideIndex = slides.length}
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";
            }
            for (i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            slides[slideIndex-1].style.display = "block";
            dots[slideIndex-1].className += " active";
            captionText.innerHTML = dots[slideIndex-1].alt;
        }
    </script>

@endsection

