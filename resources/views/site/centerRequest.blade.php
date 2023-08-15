@extends('site.master')

@section('title')
همکاری باما
@endsection

@section('content')

<div class="contact">
    {{-- contact --}}
    <div class="section position-relative">
        <div class="overlay position-absolute"></div>
        <div class="container py-4 col-12 col-sm-9 col-md-9">
            <div class="row">
                <div class="col-12 pt-5">
                    <h2 class="text-orange text-center mb-4">همکاری باما</h2>
                    <h6>
                        حمایت 360 درجه سجا از تولید کنندگان ، فروشندگان و خدمات دهندگان کالا و خدمات ( نه تنها هزینه ای
                        دریافت نمیشود بلکه از شما حمایت مادی و معنوی قابل توجهی خواهد شد)
                        برای اولین بار و با همت مدیران دلسوز در بخش غیر دولتی ستاد جانبازان ، به این اندیشیده ایم در
                        صورتی که به تولید کننده/خدمات دهنده/فروشنده منصف کمکهای مادی و معنوی انجام شود بخش اعظمی از
                        هزینه های زندگی را میتوانیم از دوش مردم برداریم.
                    </h6>
                    <p>
                        1- اعطاء فضای کاری در ساختمانهای مجلل و مجهز که مخصوص شما طراحی و ساخته شده که هزینه
                        اجاره/رهن/خرید فروشگاه از دوش شما برداشته شود.
                    </p>
                    <p>
                        2- حمایت معنوی و قانونی از شما در مقابل رقبای تجاری یا افرادی که با هرگونه امکانات منحصر به فرد
                        بازار را از دست تولید کنندگان و خدمات دهندگان شایسته ربوده اند.
                    </p>
                    <p>
                        3- اتصال سامانه شاهین برای پرداخت هزینه های تولید و فروش شما به صورت نقدی در لحظه
                    </p>
                    <p>
                        4- معرفی میلیونها نفر به مراکزی که در اختیار شما قرارداده شده و کمک به معرفی شما بدون هزینه گزاف
                        تبلیغاتی
                    </p>
                    <p>
                        5- حمایت تمام قد دستگاهها و عوامل دولتی
                    </p>
                    <p>
                        6- کمک به ایجاد ساختارهای سازمانی / کمک به فروش / جذب نیرو/ آموزش نیرو و ....
                    </p>
                    <h6>لطفا فرم زیر را به دقت کامل نمائید.</h6>
                </div>

                <div class="col-12 my-auto pt-4">
                    <div class="card">
                        <div class="card-body">
                            <form class="form-horizontal" role="form" method="post"
                                action="{{ url('centerRequest/store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="col-12">
                                    @include('Admin.layouts.errors')
                                    <div class="form-row">
                                        <div class="col-12 mb-3">
                                            <h5>1 - انتخاب نوع دسته بندی همکاری</h5>
                                            <br>
                                            <br>
                                            فروش کالا <input type="radio" name="cat_type" value="category"
                                                onclick="catType(this.value)" checked>
                                            ارائه خدمات <input type="radio" name="cat_type" value="service"
                                                onclick="catType(this.value)">
                                        </div>
                                        <div class="col-12 mb-5">
                                            <select class="form-control" name="service" id="service">
                                                <option selected disabled>انتخاب خدمت مربوطه</option>
                                                @foreach ($services as $service)
                                                <option value="{{ $service->id }}">{{ $service->title }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <select class="form-control" name="category" id="category">
                                                <option selected disabled>انتخاب دسته بندی مربوطه</option>
                                                @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->title }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="col-12 mb-5">
                                            <h5>2 - انتخاب جایگاه شغلی</h5>
                                            <br>
                                            <br>
                                            فروشنده <input type="radio" value="seller" name="type" checked>
                                            تولید کننده <input type="radio" value="producer" name="type">
                                            تامین کننده <input type="radio" value="supplier" name="type">
                                            خدمات دهنده <input type="radio" value="service_provider" name="type">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <h5> 3 - سایر اطلاعات</h5>
                                        <br>
                                        <br>
                                        <div class="form-row">
                                            <div class="col-12 col-md-6 mb-3">
                                                <label>نام </label>
                                                <input type="text" name="name" required placeholder="نام "
                                                    class="form-control">
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label>  نام خانوادگی</label>
                                                <input type="text" name="family" required placeholder="نام خانوادگی"
                                                    class="form-control">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label> جنسیت</label>
                                                <select class="form-control my-2" type="int" name="gender"
                                                        id="gender" required>
                                                    <option value="" disabled selected>جنسیت</option>
                                                    <option value="0">آقا</option>
                                                    <option value="1">خانم</option>
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6 mb-3">
                                                <label>شماره موبایل</label>
                                                <input type="number" name="phone" placeholder="شماره موبایل"
                                                    class="form-control">
                                            </div>
                                        </div>

                                        <div class="form-row">
                                            <div class="col-12 col-md-6">
                                                <select class="form-control my-2" type="int" name="province_id"
                                                    id="province_id" required>
                                                    <option value="">انتخاب استان</option>
                                                    @foreach(\App\Models\Province::all() as $province)
                                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <select class="form-control my-2" type="int" name="city_id" id="city_id"
                                                    required>
                                                    <option value="">انتخاب شهر</option>
                                                    @foreach(\App\Models\City::all() as $city)
                                                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label>موضوع</label>
                                                <input name="subject" required class="form-control"
                                                    placeholder="موضوع شغل خود را وارد نمایید">
                                            </div>

                                            <div class="col-12 col-md-6">
                                                <label>نحوه آشنایی (طریقی که با این مجموعه آشنا شدید)</label>
                                                <input name="connections" class="form-control"
                                                    placeholder="نحوه آشنایی خود با شرکت را وارد نمائید">
                                            </div>

                                            <div class="col-12 mt-3">
                                                <label>توضیحات</label>
                                                <textarea name="description" class="form-control no-resize" rows="6"
                                                    placeholder="توضیحات کاملی راجع به امتیازات و توانایی های شغل و محل ارائه خدمات خود را بیان نمائید"></textarea>
                                            </div>

                                            <div class="col-12">
                                                <div id="addnew" class='file file--upload'>
                                                    <label for='file'>
                                                        <img class="upload-file" src="{{asset('img/upload.jpg')}}"
                                                            class="img-fluid">
                                                        <h6>بارگذاری تصاویر</h6>
                                                    </label>
                                                    <input name="file[]" type="file" id="file"
                                                        class="filestyle file-upload file_upload_ajax file-up"
                                                        data-buttonname="btn-white">
                                                </div>
                                                <div id="file_newlink" class="col-12 row">
                                                    <div>
                                                    </div>
                                                </div>
                                                <div id="file_newlinktpl">
                                                    <div id="file_src" class="show"></div>
                                                    <input type="hidden" id="file" class="path" name="urlfile[]">
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <input type="submit" value="ثبت درخواست"
                                            class="btn btn-orange btn-block text-white font-14">
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

@if (session()->get('center_success') == 'success')
    <span id="contact_success"></span>
@elseif(session()->get('center_error')=='success')
     <span id="contact_error"></span>
    @endif

<script>
        document.getElementById('service').style.display = 'none';
        function catType(catType) {
            if (catType == 'category') {
                document.getElementById('category').style.display = 'block';
                document.getElementById('service').style.display = 'none';
            } else {
                document.getElementById('category').style.display = 'none';
                document.getElementById('service').style.display = 'block';
            }
        }
</script>

@stop
@section('js')
<script src="{{asset('assets/dashboard/js/site/adminJs.js')}}"></script>
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
@endsection
