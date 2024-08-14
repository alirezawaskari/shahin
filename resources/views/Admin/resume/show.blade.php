@extends('Admin.master')

@section('content')

    <div class="page-header">
        <div>
            <h3>مشاهده رزومه</h3>

        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('resume.create') }}">رزومه جدید</a>
        </div>
    </div>
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm mb-5">
                    <div class="card-body">
                        <p><strong>نام و نام خانوادگی:</strong> {{ $resume->name }}</p>
                        <p><strong>ایمیل:</strong> {{ $resume->email }}</p>
                        <p><strong>تلفن:</strong> {{ $resume->phone }}</p>
                        <p><strong>شغل درخواستی:</strong> {{ $resume->work_type }}</p>
                        <p><strong>توضیحات متقاضی:</strong> {{ $resume->text }}</p>
                        <hr>

                        {{-- <h5 class="mb-4 alert alert-primary">اطلاعات شخصی</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item"><strong>شماره شناسنامه: </strong>{{ $resume->detail['number_shenasname'] ??''}}</li>
                            <li class="list-group-item"><strong>تاریخ تولد: </strong>{{ $resume->detail['date_of_birth'] ??''}}</li>
                            <li class="list-group-item"><strong>محل صدور شناسنامه: </strong>{{ $resume->detail['birth_certificate_issuing_place'] ??''}}</li>
                            <li class="list-group-item"><strong>کد ملی: </strong>{{ $resume->detail['national_number']??'' }}</li>
                            <li class="list-group-item"><strong>دین: </strong>{{ $resume->detail['religion']??'' }}</li>
                            <li class="list-group-item"><strong>تابعیت: </strong>{{ $resume->detail['nationality']??'' }}</li>
                            <li class="list-group-item"><strong>سابقه بیمه (ماه): </strong>{{ $resume->detail['bime_history']??'' }}</li>
                            <li class="list-group-item"><strong>وضعیت تأهل: </strong>{{ $resume->detail['marital_status'] ??''}}</li>
                            <li class="list-group-item"><strong>جنسیت: </strong>{{ $resume->detail['sex']??'' }}</li>
                            <li class="list-group-item"><strong>آدرس: </strong>{{ $resume->detail['address']??'' }}</li>
                            <li class="list-group-item"><strong>وضعیت مسکن: </strong>{{ $resume->detail['housing_situation']??''}}</li>
                            <li class="list-group-item"><strong>تلفن: </strong>{{ $resume->detail['telephone'] }}</li>
                            <li class="list-group-item"><strong>وضعیت خدمت سربازی: </strong>{{ $resume->detail['military_service_status']??'' }}</li>
                            <li class="list-group-item"><strong>معافیت از خدمت سربازی: </strong>{{ $resume->detail['exemption_military_service'] ??''}}</li>
                            <li class="list-group-item"><strong>نوع معافیت از خدمت سربازی: </strong>{{ $resume->detail['exemption_military_service_type']??'' }}</li>
                            <li class="list-group-item"><strong>تاریخ پایان معافیت از خدمت سربازی: </strong>{{ $resume->detail['exemption_military_service_end_date']??'' }}</li>
                            <li class="list-group-item"><strong>محل خدمت سربازی: </strong>{{ $resume->detail['military_service_location']??'' }}</li>
                            <li class="list-group-item"><strong>تاریخ پایان خدمت سربازی: </strong>{{ $resume->detail['military_service_end_date'] ??''}}</li>
                        </ul> --}}

                        {{-- <h5 class="mb-4 alert alert-primary">سابقه کاری</h5>
                        <p>{{ $resume->work_history }}</p>
                        <hr> --}}

                        {{-- <h5 class="mb-4 alert alert-primary">مهارت‌ها و تحصیلات</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item">
                                <strong>
                                    مدرک تحصیلی:
                                </strong>
                                {{ $resume->detail['degree_of_education']??'' }}</li>
                            <li class="list-group-item">
                                <strong>
                                    رشته تحصیلی:
                                </strong>
                                {{ $resume->detail['field_of_Study']??'' }}</li>
                            <li class="list-group-item">
                                <strong>
                                    معدل کل:
                                </strong>
                                {{ $resume->detail['total_average']??'' }}</li>
                            <li class="list-group-item">
                                <strong>
                                    تسلط به زبان انگلیسی:
                                </strong>
                                {{ $resume->detail['en_lang_quality']??'' }}</li>
                            <li class="list-group-item">
                                <strong>
                                    مهارت کامپیوتری:
                                </strong>
                                {{ $resume->detail['computer_skill'] ??''}}</li>
                            <li class="list-group-item">
                                <strong>
                                    تاریخ شروع کار:
                                </strong>
                                {{ $resume->detail['start_work_date'] ??''}}</li>
                        </ul>
                        <h5 class="mb-4 alert alert-primary">نوع همکاری</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item">
                                <strong>
                                    نوع همکاری:
                                </strong>
                                {{ $resume->detail['cooperation_type']??'' }}</li>
                            <li class="list-group-item">
                                <strong>
                                    شرح پاره وقت:
                                </strong>
                                {{ $resume->detail['description_half_time']??'' }}</li>
                            <li class="list-group-item">
                                <strong>
                                حقوق درخواستی:
                                </strong>
                                {{ $resume->detail['rights_requested'] ?? ''}}</li>

                        </ul> --}}

                        {{-- <h5 class="mb-4 alert alert-primary">سایر اطلاعات</h5>
                        <ul class="list-group mb-4">
                            <li class="list-group-item"><strong>آیا در حال حاضر مشغول به کار هستید؟:</strong> {{ $resume->detail['busy_now']?? ''}}</li>
                            <li class="list-group-item"><strong>در صورت انتقال یا ماموریت حاضر به رفتن به شهرستانها و شعبات دیگر هستید؟:</strong> {{ $resume->detail['transfer_job']?? ''}}</li>
                            <li class="list-group-item"><strong>آیا می‌توانید ضمانت ارائه بدهید؟:</strong> {{ $resume->detail['can_guarantee']?? '' }}</li>
                            <li class="list-group-item"><strong>نوع ضمانت:</strong> {{ $resume->detail['guarantee'] }}</li>
                            <li class="list-group-item"><strong>آیا آشنایی نسبتی با هیچ یک از کارکنان این شرکت دارید؟:</strong> {{ $resume->detail['relative_familiarity'] ?? '' }}</li>
                            <li class="list-group-item"><strong>نوع نسبت:</strong> {{ $resume->detail['relative_family_type']?? '' }}</li>
                            <li class="list-group-item"><strong>نام و نام خانوادگی فرد معرف:</strong> {{ $resume->detail['relative_family_name']?? '' }}</li>
                            <li class="list-group-item"><strong>قسمت فرد معرف:</strong> {{ $resume->detail['relative_family_part']?? '' }}</li>
                        </ul> --}}
                    </div>
                </div>
            </div>
        </div>

    </div>

@stop

{{--@section('js')--}}
{{--    <script src="/assets/dashboard/js/resume.js?v=1.0"></script>--}}
{{--@endsection--}}
