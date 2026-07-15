@extends('site.master')

@section('title')
پویش 360
@endsection

@section('content')

<div class="contact">
    {{-- contact --}}
    <div class="section position-relative">
        <div class="overlay position-absolute"></div>
        <div class="container py-4 col-12 col-sm-9 col-md-9">
            <div class="row">
                <div class="col-12 pt-5">
                    <h2 class="text-orange text-center mb-4">پویش 360 | حمایت سجا</h2>
                    <p><span style="font-size:14px"><strong>برگزارکننده: شبکه خدمات درمانی و رفاهی لاله</strong> </span>
                    </p>

                    <p>شرایط اقتصادی کنونی به&zwnj;گونه&zwnj;ای است که بسیاری از تولیدکنندگان و فروشندگان با کاهش چشمگیر
                        فروش روبه&zwnj;رو شده&zwnj;اند و ما برآنشدیم ، با ایجاد حرکت بزرگ مردمی تحت حمایت سجا کل کشور
                        تولیدات و خدمات و کالا هاتان را در اختیار مردم عزیز کشور بی واسطه قراردهیم.</p>

                    <p>&nbsp;<span style="color:#c0392b">پویش ۳۶۰ | حمایت سجا</span> توسط شبکه خدمات درمانی و رفاهی لاله
                        راه&zwnj;اندازی شده تا شما را مستقیماً به ۹ میلیون بازنشسته و کارکنان سراسر کشور متصل کند و با
                        حمایت همه&zwnj;جانبه، رونق دوباره به کسب&zwnj;وکارتان ببخشد.</p>

                    <p>⸻</p>

                    <p><strong>چرا باید در پویش ۳۶۰ شرکت کنید؟</strong></p>

                    <p>&bull; دسترسی بی&zwnj;واسطه به ۹ میلیون مخاطب: معرفی کالا ها شما به جمعیتی بزرگ و خرید نقدی از
                        شما</p>

                    <p>&bull; امکانات منحصر به فرد و مناسب در تمامی حوزه ها : متناسب با حجم و شرایط تولید یا ارائه خدمات
                        شما</p>

                    <p>&bull; تبلیغات گسترده سراسری: پوشش رسانه&zwnj;ای در فضای مجازی، پیامک، بیلبورد و خبرگزاری&zwnj;ها
                    </p>

                    <p>&bull; حمایت قانونی و معنوی: پشتیبانی شما از سوی ۱۳۰ سازمان و نهاد مرجع&nbsp;</p>

                    <p>&bull; &nbsp;ارائه فضای فروش رایگان: فروشگاه&zwnj;های مجلل و مجهز در نقاط کلیدی شهر بدون هزینه
                        اجاره یا رهن</p>

                    <p>&bull; سیستم پرداخت آنی &laquo;شاهین&raquo;: تضمین نقدینگی شما در لحظه انجام تراکنش</p>

                    <p>⸻</p>

                    <p><strong>سایر خدمات پویش ۳۶۰ در شهرهای منتخب</strong></p>

                    <p>1. اختصاص فضای کاری و فروشگاه رایگان</p>

                    <p>2. پشتیبانی حقوقی و معنوی</p>

                    <p>3. اتصال به سامانه پرداخت آنی شاهین</p>

                    <p>4. معرفی رایگان به میلیون&zwnj;ها مخاطب</p>

                    <p>5. همراهی دستگاه&zwnj;های دولتی و حمایتی</p>

                    <p>6. مشاوره در حوزه توسعه سازمانی، فروش و جذب نیروی انسانی</p>

                    <p>⸻</p>

                    <p>🔹 فرصت <span style="color:#c0392b">محدود </span>است!</p>

                    <p>همین حالا فرم زیر را پر کنید و جزو اولین گروهی باشید که از ظرفیت بی&zwnj;نظیر <span
                            style="color:#c0392b">پویش ۳۶۰ | حمایت سجا</span> بهره&zwnj;مند می&zwnj;شوند.</p>

                    <p>با شرکت در این پویش، آینده کسب&zwnj;وکار خود را تضمین کنید!</p>

                    <p>🔗 <strong>فرم ثبت&zwnj;نام پویش ۳۶۰</strong></p>

                    <p>&nbsp;</p>
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
                                            <h5>1 - انتخاب نوع دسته بندی</h5>
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

    This is a GUEST page. site-upload.js (site/master.blade.php) already serves the
    upload widget above, and @yield('js') renders AFTER it — so anything loaded here
    overrides it. layouts.js redefines new_link() to emit .remove_media_btn, whose
    adminJs.js handler posts to /dashboard/removeMedia (Authenticate:web +
    CheckAdminAuthenticated) — a guest is bounced and the remove silently fails.
    site-upload.js's new_link() correctly uses the public /upload/removeTemp.

    The province -> city cascade this page needs is already in site/js/app.js;
    layouts.js only duplicated it.
--}}
