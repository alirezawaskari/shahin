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
                    <h6 class="text-center">لطفا فرم زیر را به دقت کامل نمائید</h6>
                </div>

                <div class="col-12 my-auto">
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
                                                <label> نام خانوادگی</label>
                                                <input type="text" name="family" required placeholder="نام خانوادگی"
                                                    class="form-control">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label> جنسیت</label>
                                                <select class="form-control my-2" type="int" name="gender" id="gender"
                                                    required>
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
                                                <select class="form-control my-2" type="int" name="province"
                                                    id="province_id" required>
                                                    <option value="">انتخاب استان</option>
                                                    @foreach(\App\Models\Province::all() as $province)
                                                    <option value="{{ $province->id }}">{{ $province->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <select class="form-control my-2" type="int" name="city" id="city_id"
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
                                                <div id="addnew" class="mt-4">
                                                    <label for='file' class="btn btn-primary custom-file-upload border border-1">
                                                        <i class="fa fa-upload ml-1" aria-hidden="true"></i>بارگذاری تصاویر
                                                    </label>
                                                    <input name="file[]" type="file" id="file"
                                                        class="d-none filestyle file-upload file_upload_ajax file-up"
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
                <div class="col-12 my-auto pt-4 text-center">
                    <h6>صفحه لاله را در روبیکا دنبال کنید</h6>
                    <a href=" https://rubika.ir/lalecardofficial"><img
                            src="data:image/png;base64,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"
                            width="60px"></a>
                    <hr>
                </div>
                <div class="col-12 my-auto pt-4">
                    <h6>
                        در نظر داریم با حمایت 360 درجه سجا و سازمانهای متصل و متعامل به این سامانه ده ها هزار نفر بازدید
                        کننده که نیازهایشان مشخص شده است را به شما متصل نمائیم .
                        از شما در هر سطح شغلی ، درآمدی با هر امکانات پایه ای که دارید با اتکا به دانش ایجاد شده در
                        سامانه های مبتنی بر هوش مصنوعی شاهین کمک شایان توجهی مینمائیم.
                        توجه نمائید سامانه های مطب ، مغازه و دفتر کار دیجیتال شاهین یکی از توانمند ترین ساختارهای ایجاد
                        شده توسط اداره کل فناوری و ارتباطات سجا با بیش از 8 سال سابقه بهره برداری و اجرا میباشد ، این
                        پلتفرم خلاف مسیر تمام پلتفرمهای موجود کشور که نهایتاً فضایی محدود برای آگهی یا استفاده کوتاه
                        برای نمایش دادن یک یا چند محصول یا خدمت شما فراهم می آورند ، به شما یک فضای کسب و کار کامل با
                        کمترین نیاز به هزینه های تبلیغاتی و نیروی انسانی میدهد که بتوانید مادام العمر مالک دامنه ، نشانی
                        و اطلاعات درون این سامانه باشید و حتی در انتخاب رنگ، تم و نوع چیدمان شعبه خود کاملا سلیقه مدار و
                        مختار خواهید بود ، با اتصال شما به بانک بزرگ مصرف کنندگانی که هر روز و هر لحظه بیشتر میشوند و
                        بهره برداری از دانش هوش مصنوعی در رسیدن خدمت یا کالا به مشتری واقعی نه فقط بازدید کننده محض فروش
                        و درامد شما چندین و چند برابر میشود.
                    </h6>
                    <h6>سایر حمایت های سجا در برخی از شهرها از شما تولید کنندگان کالا و خدمات:</h6>
                    <p>
                        1- اعطاء فضای کامل شغلی که هزینه اجاره/رهن/خرید فروشگاه ، مطب یا دفتر کار از دوش شما برداشته
                        شود.
                    </p>
                    <p>
                        2- حمایت معنوی و قانونی از شما در مقابل رقبای تجاری یا افرادی که با هرگونه امکانات منحصر به فرد
                        بازار را از دست تولید کنندگان و خدمات دهندگان شایسته ربوده اند.
                    </p>
                    <p>
                        3- اتصال سامانه شاهین برای پرداخت هزینه های تولید و فروش شما به صورت نقدی در لحظه
                    </p>
                    <p>
                        4- معرفی هزاران نفر به مراکز دیجیتال که در اختیار شما قرارداده شده و معرفی شما بدون هزینه های
                        گزاف تبلیغاتی
                    </p>
                    <p>
                        5- حمایت تمام قد دستگاهها و عوامل دولتی و سازمانهای متعامل سجا از شما
                    </p>
                    <p>
                        6- کمک به ایجاد ساختارهای سازمانی / کمک به فروش / اعطاء نیروی دور کار جهت فروش ، بازاریابی/
                        آموزش نیرو / تولید محتوا/ درج نشر تبلیغات با هزینه های بسیار نازل /و ....
                    </p>
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
{{--
    Deliberately NO dashboard bundle here (adminJs.js / layouts.js).

    This is a public, guest-facing page. site.master already loads site-upload.js, which
    provides everything this form needs (the .file_upload_ajax handler, new_link, and the
    remove control) and posts to the PUBLIC endpoints.

    The dashboard bundle would be loaded after site-upload.js (master -> @yield('js')) and
    override it: both define new_link() and both bind change.fileUploadAjax, so the admin
    versions would win. Its remove button posts to /dashboard/removeMedia, which requires
    Authenticate:web + CheckAdminAuthenticated — a guest is bounced to login, so the temp
    file is never deleted and the row never even disappears. site-upload.js instead uses
    /upload/removeTemp (public + throttled, scoped to temp holders).
--}}
