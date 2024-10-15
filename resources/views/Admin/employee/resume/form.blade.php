<form method="post" action="{{route('employee.resume.update' , [ $employee->id])}}" enctype="multipart/form-data">
    @csrf
    @include('Admin.layouts.errors')
    <div class="col-12 mt-4">
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark">مشخصات شخصی</div>
            </div>
            <div class="col-md-3 mb-2">
                <label for="">ایمیل</label>
                <input type="text" value="{{$employee->user->email}}" name="email" placeholder="ایمیل"
                    class="form-control">
            </div>
            <br>
            <div class="col-md-3 mb-2">
                <label>*شماره شناسنامه</label>
                <input value="{{$employee->user->shenasnamecode}}" name="shenasnamecode" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
                <label>*تاریخ تولد</label>
                <input
                    value="{{isset($employee->resume_detail['date_of_birth']) ? $employee->resume_detail['date_of_birth'] : ''}}"
                    name="date_of_birth" class="form-control date_of_birth" required>
            </div>
            <div class="col-md-3 mb-2">
                <label>*محل صدور</label>
                <input
                    value="{{isset($employee->resume_detail['birth_certificate_issuing_place']) ? $employee->resume_detail['birth_certificate_issuing_place'] : ''}}"
                    name="birth_certificate_issuing_place" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
                <label>*کد ملی :</label>
                <input value="{{$employee->user->melicode}}" name="melicode" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
                <label>*مذهب :</label>
                <input
                    value="{{isset($employee->resume_detail['religion']) ? $employee->resume_detail['religion'] : ''}}"
                    name="religion" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
                <label>*ملیت :</label>
                <input
                    value="{{isset($employee->resume_detail['nationality']) ? $employee->resume_detail['nationality'] : ''}}"
                    name="nationality" class="form-control" required>
            </div>
            <div class="col-md-3 mb-2">
                <label>تلفن تماس (ثابت):</label>
                <input value="{{$employee->user->call}}" name="call" class="form-control">
            </div>
            <div class="col-md-6 mb-2">
                <label>*سابقه بیمه</label>
                <input
                    value="{{isset($employee->resume_detail['bime_history']) ? $employee->resume_detail['bime_history'] : ''}}"
                    type="number" name="bime_history" class="form-control" min="0" value="0"
                    placeholder="سابقه بیمه(درصورت داشتن بیمه براساس ماه)" required>
            </div>
            <div class="col-md-6 mb-2">
                <label>نوع شغل درخواستی</label>
                <select class="form-control select2" name="recruitment_notice_id">
                    @foreach ($recruitment_notices as $recruitment_notice)
                    <option value="{{$recruitment_notice->id}}" {{$recruitment_notice_selected==$recruitment_notice->id
                        ? 'selected' : ''}}>{{$recruitment_notice->title}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mb-2">
                <label>در صورتی که توضیحی دارید در کادر ذیل وارد نمایید:</label>
                <textarea name="text"
                    value="{{isset($employee->resume_detail['text']) ? $employee->resume_detail['text'] : ''}}"
                    class="form-control " rows="6" placeholder="توضیحات مربوطه"></textarea>
            </div>
            <div class="col-md-2 form-check form-check-inline mb-2">
                <p>*وضعیت تاهل :</p>
                <input type="radio" id="" name="marital_status" value="مجرد" class="form-check-input"
                    {{isset($employee->resume_detail['marital_status']) &&
                $employee->resume_detail['marital_status'] == "مجرد" ? 'checked' : ''}} required>
                <label for="html">مجرد</label><br>
                <input type="radio" id="" name="marital_status" value="متاهل" class="form-check-input"
                    {{isset($employee->resume_detail['marital_status']) &&
                $employee->resume_detail['marital_status'] == "متاهل" ? 'checked' : ''}} required>
                <label for=" ">متاهل</label><br>

            </div>
            <div class="col-md-2 form-check form-check-inline mb-2">
                <p>*جنسیت :</p>
                <input class="form-check-input" type="radio" id=" " name="sex" value="0" {{$employee->user->sex
                ? '' : 'checked'}} required>
                <label for="html">آقا</label><br>
                <input class="form-check-input" type="radio" id="" name="sex" value="1" {{$employee->user->sex ?
                'checked' : ''}} required>
                <label for=" ">خانم</label><br>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark">آدرس </div>
            </div>
            <div class="col-md-12 mb-2">
                <label>*نشانی محل سکونت :</label>
                <input value="{{$employee->user->address}}" name="address" class="form-control" required>
            </div>
            <div class="col-md-3 form-check form-check-inline mb-2">
                <p>*وضعیت مسکن :</p>
                <input type="radio" id="" name="housing_situation" value="شخصی"
                    {{isset($employee->resume_detail['housing_situation']) &&
                $employee->resume_detail['housing_situation'] == "شخصی" ? 'checked' : ''}} required>
                <label for="html">شخصی</label><br>
                <input type="radio" id="" name="housing_situation" value="اجاره ای"
                    {{isset($employee->resume_detail['housing_situation']) &&
                $employee->resume_detail['housing_situation'] == "اجاره ای" ? 'checked' : ''}} required>
                <label for=" ">اجاره ای</label><br>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark">*خدمت وظیفه عمومی(ورود اطلاعات برای آقایان
                    الزامی است ) </div>
            </div>
            <div class="col-md-2">
                <p>انجام خدمت وظیفه عمومی :</p>
                <input type="radio" id="" name="military_service_status" value="بله"
                    {{isset($employee->resume_detail['military_service_status']) &&
                $employee->resume_detail['military_service_status'] == "بله" ? 'checked' : ''}}>
                <label for="html">بله</label><br>
                <input type="radio" id="" name="military_service_status" value="خیر"
                    {{isset($employee->resume_detail['military_service_status']) &&
                $employee->resume_detail['military_service_status'] == "خیر" ? 'checked' : ''}}>
                <label for=" ">خیر</label><br>
            </div>
            <div class="col-md-2">
                <p>معافیت سربازی :</p>
                <input type="radio" id=" " name="exemption_military_service " value="بله"
                    {{isset($employee->resume_detail['exemption_military_service']) &&
                $employee->resume_detail['exemption_military_service'] == "بله" ? 'checked' : ''}}>
                <label for="html">بله</label><br>
                <input type="radio" id="" name="exemption_military_service " value="خیر"
                    {{isset($employee->resume_detail['exemption_military_service']) &&
                $employee->resume_detail['exemption_military_service'] == "خیر" ? 'checked' : ''}}>
                <label for=" ">خیر</label><br>
            </div>
            <div class="col-md-2">
                <label>نوع معافیت:</label>
                <input name="exemption_military_service_type"
                    {{isset($employee->resume_detail['exemption_military_service_type']) ?
                $employee->resume_detail['exemption_military_service_type'] : ''}} class="form-control">
            </div>
            <div class="col-md-2">
                <label>تاریخ اتمام معافیت:</label>
                <input name="exemption_military_service_end_date" type="text"
                    class="form-control exemption_military_service_end_date"
                    {{isset($employee->resume_detail['exemption_military_service_end_date']) ?
                $employee->resume_detail['exemption_military_service_end_date'] : ''}}>
            </div>
            <div class="col-md-2">
                <label>محل خدمت:</label>
                <input name="military_service_location" class="form-control"
                    {{isset($employee->resume_detail['military_service_location']) ?
                $employee->resume_detail['military_service_location'] : ''}}>
            </div>
            <div class="col-md-2">
                <label>تاریخ خاتمه خدمت:</label>
                <input name="military_service_end_date" class="form-control military_service_end_date"
                    {{isset($employee->resume_detail['military_service_end_date']) ?
                $employee->resume_detail['military_service_end_date'] : ''}}>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark">*آخرین مدرک تحصیلی </div>
            </div>
            <div class="col-md-2">
                <label>*مدرک تحصیلی:</label>
                <input name="degree_of_education" class="form-control"
                    {{isset($employee->resume_detail['degree_of_education']) ?
                $employee->resume_detail['degree_of_education'] : ''}} required>
            </div>
            <div class="col-md-2">
                <label>*رشته تحصیلی:</label>
                <input name="field_of_Study" class="form-control" {{isset($employee->resume_detail['field_of_Study']) ?
                $employee->resume_detail['field_of_Study'] : ''}}>
            </div>
            <div class="col-md-2">
                <label>معدل کل:</label>
                <input name="total_average" class="form-control" {{isset($employee->resume_detail['total_average']) ?
                $employee->resume_detail['total_average'] : ''}}>
            </div>
            <div class="col-md-4">
                <label>نام و آدرس محل دریافت مدرک تحصیلی :</label>
                <input name="education_location" class="form-control"
                    {{isset($employee->resume_detail['education_location']) ?
                $employee->resume_detail['education_location'] : ''}}>
            </div>
            <div class="col-md-2">
                <label>تاریخ اخذ مدرک:</label>
                <input name="education_end_date" class="form-control education_end_date"
                    {{isset($employee->resume_detail['education_end_date']) ?
                $employee->resume_detail['education_end_date'] : ''}}>
            </div>
        </div>
        <hr>

        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark">نحوه همکاری </div>
            </div>
            <div class="col-md-2">
                <p>*تمایل به همکاری به صورت :</p>
                <input type="radio" id=" " name="cooperation_type" value="تمام وقت"
                    {{isset($employee->resume_detail['cooperation_type']) &&
                $employee->resume_detail['cooperation_type'] == "تمام وفت" ? 'checked' : ''}} required>
                <label for="html">تمام وقت</label><br>
                <input type="radio" id="" name="cooperation_type" value="پاره وقت "
                    {{isset($employee->resume_detail['cooperation_type']) &&
                $employee->resume_detail['cooperation_type'] == "پاره وقت" ? 'checked' : ''}} required>
                <label for="">پاره وقت</label><br>
            </div>
            <div class="col-md-10">
                <p>در صورت تمایل به کار پاره وقت ساعات و روزهای همکاری را دقیقا اعلام
                    فرمایید :</p>
                <textarea class="form-control" {{isset($employee->resume_detail['description_half_time']) ? $employee->resume_detail['description_half_time'] : ''}} name="description_half_time">

        </textarea>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark">سوابق شغلی </div>
            </div>
            <div class="col-md-12">
                <label>*سوابق شغلی</label>
                <textarea name="work_history" {{isset($employee->resume_detail['work_history']) ? $employee->resume_detail['work_history'] : ''}} class="form-control font-14" rows="6"
                            placeholder="سوابق شغلی (با ذکر نام محل کار و سمت شغلی مربوطه)" required></textarea>

            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark"> زبان </div>
            </div>
            <div class="col-md-6 form-check form-check-inline row">
                <div class="col-md-12"> *تسلط به زبان انگلیسی:</div>
                <input class="form-check-inline" type="radio" id=" " name="en_lang_quality" value="ضعیف"
                    {{isset($employee->resume_detail['en_lang_quality']) &&
                $employee->resume_detail['en_lang_quality'] == "ضعیف" ? 'checked' : ''}} required>
                <label for="html">ضعیف</label><br>
                <input class="form-check-inline" type="radio" id="" name="en_lang_quality" value="متوسط"
                    {{isset($employee->resume_detail['en_lang_quality']) &&
                $employee->resume_detail['en_lang_quality'] == "متوسط" ? 'checked' : ''}} required>
                <label for=" ">متوسط</label><br>
                <input class="form-check-inline" type="radio" id="" name="en_lang_quality" value="خوب"
                    {{isset($employee->resume_detail['en_lang_quality']) &&
                $employee->resume_detail['en_lang_quality'] == "خوب" ? 'checked' : ''}} required>
                <label for=" ">خوب</label><br>
                <input class="form-check-inline" type="radio" id="" name="en_lang_quality" value="عالی"
                    {{isset($employee->resume_detail['en_lang_quality']) &&
                $employee->resume_detail['en_lang_quality'] == "عالی" ? 'checked' : ''}} required>
                <label for=" ">عالی</label><br>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark"> آشنایی به کامپیوتر </div>
            </div>
            <div class="col-md-10">
                <p>در صورتی که به نرم افزار های آفیس و یا هر نرم افزار دیگری تسلطی دارید
                    نام برده و میزان تسلط خود را ذکر فرمایید :</p>
                <textarea class="form-control" {{isset($employee->resume_detail['computer_skill']) ? $employee->resume_detail['computer_skill'] : ''}} name="computer_skill">
        </textarea>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark"> جزئیات درخواست </div>
            </div>
            <div class="col-md-6 form-check form-check-inline">
                <label>*در صورت انتقال یا ماموریت حاضر به رفتن به شهرستانها و شعبات دیگر
                    هستید ؟</label>
                <input class="form-check-inline" type="radio" id=" " name="transfer_job" value="بله"
                    {{isset($employee->resume_detail['transfer_job']) &&
                $employee->resume_detail['transfer_job'] == "بله" ? 'checked' : ''}} required>
                <label for="html">بله</label><br>
                <input class="form-check-inline" type="radio" id=" " name="transfer_job" value="خیر"
                    {{isset($employee->resume_detail['transfer_job']) &&
                $employee->resume_detail['transfer_job'] == "خیر" ? 'checked' : ''}} required>
                <label for="html">خیر</label><br>
            </div>
            <div class="col-md-6 form-check form-check-inline">
                <label>*اکنون مشغول به کار هستید ؟</label>
                <input class="form-check-inline" type="radio" id=" " name="busy_now" value="بله"
                    {{isset($employee->resume_detail['busy_now']) &&
                $employee->resume_detail['busy_now'] == "بله" ? 'checked' : ''}} required>
                <label for="html">بله</label><br>
                <input class="form-check-inline" type="radio" id=" " name="busy_now" value="خیر"
                    {{isset($employee->resume_detail['busy_now']) &&
                $employee->resume_detail['busy_now'] == "خیر" ? 'checked' : ''}} required>
                <label for="html">خیر</label><br>
            </div>
            <div class="col-md-12 form-check form-check-inline">
                <label>*در صورت انعقاد قرارداد آیا توانایی ارائه ضمانت نامه معتبر دارید
                    ؟</label>
                <input class="form-check-inline" type="radio" id=" " name="can_guarantee" value="بله"
                    {{isset($employee->resume_detail['can_guarantee']) &&
                $employee->resume_detail['can_guarantee'] == "بله" ? 'checked' : ''}} required>
                <label for="html">بله</label><br>
                <input class="form-check-inline" type="radio" id=" " name="can_guarantee" value="خیر"
                    {{isset($employee->resume_detail['can_guarantee']) &&
                $employee->resume_detail['can_guarantee'] == "خیر" ? 'checked' : ''}} required>
                <label for="html">خیر</label><br>
            </div>
            <div class="col-md-12 form-check form-check-inline">
                <label>*نوع ضمانت ؟</label>
                <input class="form-check-inline" type="radio" id=" " name="guarantee" value="چک"
                    {{isset($employee->resume_detail['guarantee']) &&
                $employee->resume_detail['guarantee'] == "چک" ? 'checked' : ''}} required>
                <label for="html">چک</label><br>
                <input class="form-check-inline" type="radio" id=" " name="guarantee" value="سفته"
                    {{isset($employee->resume_detail['guarantee']) &&
                $employee->resume_detail['guarantee'] == "سفته" ? 'checked' : ''}} required>
                <label for="html">سفته</label><br>
                <input class="form-check-inline" type="radio" id=" " name="guarantee" value="ضمانت محضری"
                    {{isset($employee->resume_detail['guarantee']) &&
                $employee->resume_detail['guarantee'] == "ضمانت محضری" ? 'checked' : ''}} required>
                <label for="html">ضمانت محضری</label><br>
            </div>
            <div class="col-md-12">
                <label>*تاریخی که میتوانید مشغول به کار گردید:</label>
                <input name="start_work_date" {{isset($employee->resume_detail['start_work_date']) ?
                $employee->resume_detail['start_work_date'] : ''}} class="form-control
                start_work_date" required>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark"> آشنایی با کارکنان :</div>
            </div>
            <div class="col-md-12 form-check form-check-inline">
                <label>*آیا آشنایی نسبی با هیچ یک از کارکنان این شرکت دارید؟</label>
                <input class="form-check-inline" type="radio" id=" " name="relative_familiarity" value="بله"
                    {{isset($employee->resume_detail['relative_familiarity']) &&
                $employee->resume_detail['relative_familiarity'] == "بله" ? 'checked' : ''}} required>
                <label for="html">بله</label><br>
                <input class="form-check-inline" type="radio" id=" " name="relative_familiarity" value="خیر"
                    {{isset($employee->resume_detail['relative_familiarity']) &&
                $employee->resume_detail['relative_familiarity'] == "خیر" ? 'checked' : ''}} required>
                <label for="html">خیر</label><br>
            </div>
            <div class="col-md-4">
                <label>نسبت:</label>
                <input name="relative_family_type" {{isset($employee->resume_detail['relative_family_type']) ?
                $employee->resume_detail['relative_family_type'] : ''}} class="form-control">
            </div>
            <div class="col-md-4">
                <label>نام:</label>
                <input name="relative_family_name" {{isset($employee->resume_detail['relative_family_name']) ?
                $employee->resume_detail['relative_family_name'] : ''}} class="form-control">
            </div>
            <div class="col-md-4">
                <label>قسمت:</label>
                <input name="relative_family_part" {{isset($employee->resume_detail['relative_family_part']) ?
                $employee->resume_detail['relative_family_part'] : ''}} class="form-control">
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="col-md-12">
                <div class="alert alert-dark"> تایید صحت اطلاعات وارده: </div>
            </div>
            <div class="col-md-12 row form-check form-check-inline">
                <div class="col-md-12">
                    <p style="text-align: justify">با دادن اطلاعات مندرج این پرسشنامه درخواست کار در شرکت را
                        نموده و
                        متعهد میشوم که به کلیه سوالات فوق پاسخ صحیح و با اطلاع کامل از مفهوم
                        آنها داده ام و در صورتی که کذب هر یک از جوابها معلوم شود ، به منزله
                        استعفا من از شرکت تلقی خواهد شد و همچنین به این شرکت اجازه میدهم هر
                        نوع تحقیقاتی را که لازم بداند ، درباره اینجانب انجام دهد . ضمنا در
                        صورت استحدام کلیه مقررات آیین نامه های استخدامی این شرکت را اعم از
                        فعلی یا آتی قبول دارم. </p>
                </div>
                <div class="col-md-12">
                    <label>*تایید میکنم:</label>
                    <input name="accept_rules" {{isset($employee->resume_detail['accept_rules']) ? 'checked' : ''}}
                    value="بله" type="checkbox" required class="form-check-input">
                </div>
            </div>
        </div>
        <hr>
        <input type="submit" value="ثبت" class="btn btn-success mr-2 send-resume-btn">
    </div>
</form>
