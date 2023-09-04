@extends('site.master')
@section('title')استخدام نیروی کار@endsection
@section('description')استخدام شبکه لاله | استخدام لاله کارت | فرصت های شغلی شبکه لاله | فرصت های شغلی لاله کارت@endsection

@section('content')

    <div class="service p-md-4">
        <div class="register-section py-4" id="one-section">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-12 py-5 my-auto mr-auto">
                        <div class="card">
                            <div class="card-header">مراحل ارسال رزومه:</div>
                            <div class="card-body">
                                <p>1.شغل مورد نظر خود را انتخاب کنید.</p>
                                <p>2.متن پیام و درخواست خود را یادداشت کنید.</p>
                                <p>3.ارسال رزومه و اطلاعات فردی و سوابق شغلی الزامیست پس از بارگزاری آن کلید سبز رنگ ارسال درخواست را کلیک کنید.</p>
                                <p>توجه :  کاربر گرامی لطفا رزومه را با فرمت pdf,jpg,jpeg آپلود کنید.</p>
								<p>راهنمای ثبت نام :</p>
								<p>متخصص محترم و کارجوی عزیز

شبکه لاله در سطوح مختلف از رشته های متعدد و سطوح مختلف دعوت به همکاری مینماید.لطفا رشته مورد نظر خود را ابتدا انتخاب و اطلاعات مرتبط را تکمیل نمائید</p>

                            </div>

                        </div>
                        <h5 class="mt-5">همکاری و استخدام</h5>
                        <div class="row">
                            <div class="col-md-4 alert alert-dark">
                                لطفا ابتدا استان مورد نظر خود را انتخاب نمایید .
                            </div>
                        </div>

                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger" role="alert">
                                {{ $error }}
                            </div>
                        @endforeach
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                        @foreach($recruitment_notices as $province_id => $province_recruitment_notices)
                                <li class="nav-item  flex-fill text-center nav-item-service">
                                    <a class="nav-link  " id="firstData-tab" data-toggle="tab" href="#province_{{$province_id}}" role="tab"
                                       aria-controls="home" aria-selected="true"> {{'استان '. \App\Models\Province::find($province_id)->name}}</a>
                                </li>
                        @endforeach
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            @php($i=1)
                        @foreach($recruitment_notices as $province_id => $province_recruitment_notices)
                            <div class="tab-pane fade {{$i == 1?"show active":''}}" id="province_{{$province_id}}" role="tabpanel" aria-labelledby="firstData-tab">
                                @php($i++)
                            @foreach($province_recruitment_notices as $recruitment_notice )
                            <button class="accordion text-right mb-2">{{ $recruitment_notice->title .' - '. $recruitment_notice->province->name}}</button>
                            <div class="panel">
                                <div class="card-body overflow-auto" >
                                    {!! $recruitment_notice->description !!}
                                </div>
                                <form action="/send_resume"  method="post" enctype="multipart/form-data" class="form-resume">
                                    @csrf
                                    <input type="hidden" name="recruitment_notice_id" value="{{ $recruitment_notice->id }}">
                                    <div class="col-md-12">
                                        <span class="alert alert-warning">
                                            ورود اطلاعات برای موارد ستاره دار (*) الزامی است .
                                        </span>
                                    </div>
                                    <div class="col-12 mt-4">
                                        <div class="form-row">
                                            <div class="col-md-12">
                                                <div class="alert alert-dark">مشخصات شخصی</div>
                                            </div>
                                            <div class="col-md-3">
                                                <label>* نام</label>
                                                <input type="text" name="name" placeholder="نام" class="form-control" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="">*نام خانوادگی</label>
                                                <input type="text" name="family" placeholder=" نام خانوادگی" class="form-control " required>
                                            </div>
                                            <div class="col-md-3">
                                                <label>*تلفن همراه</label>
                                                <input type="text" name="phone" placeholder="تلفن همراه" class="form-control" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="">ایمیل</label>
                                                <input type="text" name="email" placeholder="ایمیل" class="form-control" >
                                            </div>
                                            <div class="col-md-2">
                                                <label>*شماره شناسنامه</label>
                                                <input name="number_shenasname" class="form-control" required >
                                            </div>
                                            <div class="col-md-2">
                                                <label>*محل صدور</label>
                                                <input name="birth_certificate_issuing_place" class="form-control" required >
                                            </div>
                                            <div class="col-md-2">
                                                <label>*کد ملی :</label>
                                                <input name="national_number" class="form-control" required >
                                            </div>
                                            <div class="col-md-2">
                                                <label>*مذهب :</label>
                                                <input name="religion" class="form-control" required >
                                            </div>
                                            <div class="col-md-2">
                                                <label>*ملیت :</label>
                                                <input name="nationality" class="form-control" required >
                                            </div>
                                            <div class="col-md-2 form-check form-check-inline">
                                                <p>*وضعیت تاهل :</p>
                                                <input type="radio" id=" " name="marital_status" value="مجرد" class="form-check-input" required>
                                                <label   for="html">مجرد</label><br>
                                                <input   type="radio" id="" name="marital_status" value="متاهل" class="form-check-input" required>
                                                <label for=" ">متاهل</label><br>

                                            </div>
                                            <div class="col-md-2 form-check form-check-inline">
                                                <p>*جنسیت :</p>
                                                <input class="form-check-input" type="radio" id=" " name="sex" value="آقا" required>
                                                <label for="html">آقا</label><br>
                                                <input class="form-check-input" type="radio" id="" name="sex" value="خانم" required>
                                                <label for=" ">خانم</label><br>

                                            </div>
                                        </div>
                                        <div class="form-row mb-2">
                                            <div class="col-md-6">
                                                <label>*سابقه بیمه</label>
                                                <input type="number" name="bime_history" class="form-control" min="0" value="0" placeholder="سابقه بیمه(درصورت داشتن بیمه براساس ماه)"  required>
                                            </div>
                                            <div class="col-md-6">
                                                <label>نوع شغل درخواستی</label>
                                                <input type="text" name="work_type" value="{{ $recruitment_notice->title }}" class="form-control " readonly placeholder="نوع شغل درخواستی" required>
                                            </div>
                                        </div>
                                        <div class="form-row mb-2">
                                            <div class="col-md-12">
                                                <label>در صورتی که توضیحی دارید در کادر ذیل وارد نمایید:</label>
                                                <textarea  name="text" class="form-control " rows="6" placeholder="توضیحات مربوطه"  ></textarea>
                                            </div>
                                        </div>
                                        <div class="form-row mb-2">

                                            <div class="col-md-12">
                                                <div class="alert alert-dark">آدرس </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label>*نشانی محل سکونت :</label>
                                                <input name="address" class="form-control" required >
                                            </div>
                                            <div class="col-md-3 form-check form-check-inline">
                                                <p>*وضعیت مسکن  :</p>
                                                <input type="radio" id=" " name="housing_situation" value="شخصی" required>
                                                <label for="html">شخصی</label><br>
                                                <input type="radio" id="" name="housing_situation" value="اجاره ای" required>
                                                <label for=" ">اجاره ای</label><br>
                                            </div>
                                            <div class="col-md-2">
                                                <label>تلفن تماس (ثابت):</label>
                                                <input name="telephone" class="form-control" >

                                            </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark">*خدمت وظیفه عمومی(ورود اطلاعات برای آقایان الزامی است ) </div>
                                            </div>
                                                <div class="col-md-2">
                                                    <p>انجام خدمت وظیفه عمومی  :</p>
                                                    <input type="radio" id=" " name="military_service_status" value="بله">
                                                    <label for="html">بله</label><br>
                                                    <input type="radio" id="" name="military_service_status" value="خیر">
                                                    <label for=" ">خیر</label><br>
                                                </div>
                                                <div class="col-md-2">
                                                    <p>معافیت سربازی :</p>
                                                    <input type="radio" id=" " name="exemption_military_service " value="بله">
                                                    <label for="html">بله</label><br>
                                                    <input type="radio" id="" name="exemption_military_service " value="خیر">
                                                    <label for=" ">خیر</label><br>
                                                </div>
                                                <div class="col-md-2">
                                                    <label>نوع معافیت:</label>
                                                    <input name="exemption_military_service_type" class="form-control" >
                                                </div>
                                                <div class="col-md-2">
                                                    <label>تاریخ اتمام معافیت:</label>
                                                    <input name="exemption_military_service_end_date" type="text" class="form-control  " >
                                                </div>
                                                <div class="col-md-2">
                                                    <label>محل خدمت:</label>
                                                    <input name="military_service_location" class="form-control" >
                                                </div>
                                                <div class="col-md-2">
                                                    <label>تاریخ خاتمه خدمت:</label>
                                                    <input name="military_service_end_date" class="form-control  " >
                                                </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark">*آخرین مدرک تحصیلی </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label>*مدرک تحصیلی:</label>
                                                <input name="degree_of_education" class="form-control" required >
                                            </div>
                                            <div class="col-md-2">
                                                <label>*رشته تحصیلی:</label>
                                                <input name="field_of_Study" class="form-control" >
                                            </div>
                                            <div class="col-md-2">
                                                <label>معدل کل:</label>
                                                <input name="total_average" class="form-control" >
                                            </div>
                                            <div class="col-md-4">
                                                <label>نام و آدرس محل دریافت مدرک تحصیلی :</label>
                                                <input name="education_location" class="form-control" >
                                            </div>
                                            <div class="col-md-2">
                                                <label>تاریخ اخذ مدرک:</label>
                                                <input name="education_end_date" class="form-control" >
                                            </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark">نحوه همکاری  </div>
                                            </div>
                                            <div class="col-md-2">
                                                <p>*تمایل به همکاری به صورت  :</p>
                                                <input type="radio" id=" " name="cooperation_type" value="تمام وقت" required>
                                                <label for="html">تمام وقت</label><br>
                                                <input type="radio" id="" name="cooperation_type" value="پاره وقت " required>
                                                <label for="">پاره وقت</label><br>
                                            </div>
                                        <div class="col-md-10">
                                                <p>در صورت تمایل به کار پاره وقت ساعات و روزهای همکاری را دقیقا اعلام فرمایید  :</p>
                                            <textarea class="form-control" name="description_half_time">

                                            </textarea>
                                        </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark">سوابق شغلی </div>
                                            </div>
                                        <div class="col-md-12">
                                                <label>*سوابق شغلی</label>
                                                <textarea name="work_history" class="form-control  font-14" rows="6" placeholder="سوابق شغلی (با ذکر نام محل کار و سمت شغلی مربوطه)" required></textarea>

                                        </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark">  زبان  </div>
                                            </div>
                                            <div class="col-md-6 form-check form-check-inline row">
                                                <div class="col-md-12"> *تسلط به زبان انگلیسی:</div>
                                                <input class="form-check-inline" type="radio" id=" " name="en_lang_quality" value="ضعیف" required>
                                                <label for="html">ضعیف</label><br>
                                                <input class="form-check-inline" type="radio" id="" name="en_lang_quality" value="متوسط" required>
                                                <label for=" ">متوسط</label><br>
                                                <input class="form-check-inline" type="radio" id="" name="en_lang_quality" value="خوب" required>
                                                <label for=" ">خوب</label><br>
                                                <input class="form-check-inline" type="radio" id="" name="en_lang_quality" value="عالی" required>
                                                <label for=" ">عالی</label><br>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark">  آشنایی به کامپیوتر  </div>
                                            </div>
                                            <div class="col-md-10">
                                                <p>در صورتی که به نرم افزار های آفیس و یا هر نرم افزار دیگری تسلطی دارید نام برده و میزان تسلط خود را ذکر فرمایید   :</p>
                                                <textarea class="form-control" name="computer_skill" >
                                            </textarea>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark"> جزئیات درخواست </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label>*تاریخی که میتوانید مشغول به کار گردید:</label>
                                                <input name="start_work_date" class="form-control " required >
                                            </div>
                                            <div class="col-md-6">
                                                <label>*حقوق درخواستی (ریال):</label>
                                                <input name="rights_requested" type="number" class="form-control" required >
                                            </div>
                                            <div class="col-md-6 form-check form-check-inline">
                                                <label>*در صورت انتقال یا ماموریت حاضر به رفتن به شهرستانها و شعبات دیگر هستید ؟</label>
                                                <input class="form-check-inline" type="radio" id=" " name="transfer_job" value="بله" required>
                                                <label for="html">بله</label><br>
                                                <input class="form-check-inline" type="radio" id=" " name="transfer_job" value="خیر" required>
                                                <label for="html">خیر</label><br>
                                            </div>
                                            <div class="col-md-6 form-check form-check-inline">
                                                <label>*اکنون مشغول به کار هستید ؟</label>
                                                <input class="form-check-inline" type="radio" id=" " name="busy_now" value="بله" required>
                                                <label for="html">بله</label><br>
                                                <input class="form-check-inline" type="radio" id=" " name="busy_now" value="خیر" required>
                                                <label for="html">خیر</label><br>
                                            </div>
                                            <div class="col-md-12 form-check form-check-inline">
                                                <label>*در صورت انعقاد قرارداد آیا توانایی ارائه ضمانت نامه معتبر دارید ؟</label>
                                                <input class="form-check-inline" type="radio" id=" " name="can_guarantee" value="بله" required>
                                                <label for="html">بله</label><br>
                                                <input class="form-check-inline" type="radio" id=" " name="can_guarantee" value="خیر" required>
                                                <label for="html">خیر</label><br>
                                            </div>
                                            <div class="col-md-12 form-check form-check-inline">
                                                <label>*نوع ضمانت ؟</label>
                                                <input class="form-check-inline" type="radio" id=" " name="guarantee" value="چک" required>
                                                <label for="html">چک</label><br>
                                                <input class="form-check-inline" type="radio" id=" " name="guarantee" value="سفته" required>
                                                <label for="html">سفته</label><br>
                                                <input class="form-check-inline" type="radio" id=" " name="guarantee" value="ضمانت محضری" required>
                                                <label for="html">ضمانت محضری</label><br>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="alert alert-dark"> آشنایی با کارکنان :</div>
                                            </div>
                                            <div class="col-md-12 form-check form-check-inline">
                                                <label>*آیا آشنایی نسبی با هیچ یک از کارکنان این شرکت دارید؟</label>
                                                <input class="form-check-inline" type="radio" id=" " name="relative_familiarity" value="بله" required>
                                                <label for="html">بله</label><br>
                                                <input class="form-check-inline" type="radio" id=" " name="relative_familiarity" value="خیر" required>
                                                <label for="html">خیر</label><br>
                                            </div>
                                            <div class="col-md-4">
                                                <label>نسبت:</label>
                                                <input name="relative_family_type" class="form-control" >
                                            </div>
                                            <div class="col-md-4">
                                                <label>نام:</label>
                                                <input name="relative_family_name" class="form-control" >
                                            </div>
                                            <div class="col-md-4">
                                                <label>قسمت:</label>
                                                <input name="relative_family_part" class="form-control" >
                                            </div>
                                            <div class="col-md-12">
                                                <div class="alert alert-dark"> تایید صحت اطلاعات وارده: </div>
                                            </div>
                                            <div class="col-md-12 row form-check form-check-inline">
                                                <p>با دادن اطلاعات مندرج این پرسشنامه درخواست کار در شرکت را نموده و متعهد میشوم که به کلیه سوالات فوق پاسخ صحیح و با اطلاع کامل از مفهوم آنها داده ام و در صورتی که کذب  هر  یک از جوابها معلوم شود ، به منزله استعفا من از شرکت تلقی خواهد شد و همچنین  به این شرکت اجازه میدهم هر نوع تحقیقاتی را که لازم بداند ، درباره اینجانب انجام دهد . ضمنا در صورت استحدام کلیه مقررات آیین نامه های استخدامی این شرکت را اعم از فعلی یا آتی قبول دارم. </p>
                                                    <div class="col-md-12">
                                                        <label>*تایید میکنم:</label>
                                                        <input name="accept_rules" value="بله" type="checkbox" required class="form-check-input" >
                                                    </div>
                                            </div>


                                        </div>

                                        <div class="col-md-12">
                                            <div class="alert alert-dark">   فایل رزومه: </div>
                                        </div>

                                        <div class="form-row mb-2">
                                            <input type="file" name="resume" id="file" accept=".pdf,.jpg,.jpeg">
                                            <div class="col-md-12">
                                                <p>توجه : کاربر گرامی در صورت داشتن فایل رزومه جداگانه   لطفا رزومه را با فرمت pdf,jpg,jpeg آپلود کنید.</p>
                                            </div>
                                            <input type="submit" value="ارسال درخواست" class="btn btn-success mr-2 send-resume-btn">
                                        </div>
                                    </div>
                                </form>
                            </div>
                            @endforeach
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(session()->get('resume_success')=='success')
        <span id="contact_success"></span>
    @elseif(session()->get('resume_success')=='error')
        <span id="contact_error"></span>
    @endif
@endsection
@section('js')
    <script>
        var acc = document.getElementsByClassName("accordion");
        var i;
        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.display === "block") {
                    panel.style.display = "none";
                } else {
                    panel.style.display = "block";
                }
            });
        }
    </script>
    <script src="{{asset('assets/script/ckeditor/ckeditor.js')}}"></script>
    <script>
        //to do move script to single file
        $(document).ready(function () {
            $("form.form-resume").submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '/send_resume',
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        if (data.status=='success'){
                            swal({
                                text: "با موفقیت ارسال شد .",
                                icon: "success",
                                button: "تایید",
                                timer:4000,
                                allowOutsideClick: true
                            });
                            setTimeout(location.reload(), 4000);
                        }else{
                            swal("خطا!",  data.messages.join('\n'),'error')
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });
        })
    </script>
@endsection
