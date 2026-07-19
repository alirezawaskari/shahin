@extends('site.master')
@section('title')استخدام نیروی کار@endsection
@section('description')استخدام شبکه لاله | استخدام لاله کارت | فرصت های شغلی شبکه لاله | فرصت های شغلی لاله
کارت@endsection
@section('content')

<div class="service p-md-4">
    <div class="register-section py-4" id="one-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-12 py-5 my-auto mr-auto">
                    <div class="card">
                        <div class="card-header">مراحل ارسال رزومه:</div>
                        <div class="card-body">
                            <p>1.استان مورد نظر خود را انتخاب کنید.</p>
                            <p>2.شغل مورد نظر خود را انتخاب کنید.</p>
                            <p>توجه : کاربر گرامی لطفا رزومه را با فرمت pdf,jpg,jpeg آپلود کنید.</p>
                            <p>راهنمای ثبت نام :</p>
                            <p>متخصص محترم و کارجوی عزیز

                                شبکه لاله در سطوح مختلف از رشته های متعدد و سطوح مختلف دعوت به همکاری مینماید.لطفا رشته
                                مورد نظر خود را ابتدا انتخاب و اطلاعات مرتبط را تکمیل نمائید</p>

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
                            <a class="nav-link  " id="firstData-tab" data-toggle="tab" href="#province_{{$province_id}}"
                                role="tab" aria-controls="home" aria-selected="true"> {{$province_id ? 'استان '.
                                \App\Models\Province::find($province_id)->name : 'استخدام کشوری (دورکاری)'}}</a>
                        </li>
                        @endforeach
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        @php($i=1)
                        @foreach($recruitment_notices as $province_id => $province_recruitment_notices)
                        <div class="tab-pane fade {{$i == 1?" show active":''}}" id="province_{{$province_id}}"
                            role="tabpanel" aria-labelledby="firstData-tab">
                            @php($i++)
                            @foreach($province_recruitment_notices as $recruitment_notice )
                            <button class="accordion text-right mb-2">{{ $recruitment_notice->title .' -
                                '.($recruitment_notice->province ? $recruitment_notice->province->name : 'استخدام کشوری
                                (دورکاری)')}}</button>
                            <div class="panel">
                                <div class="card-body overflow-auto">
                                    {!! $recruitment_notice->description !!}
                                </div>
                                <form action="/send_resume" method="post" enctype="multipart/form-data"
                                    class="form-resume">
                                    @csrf
                                    <input type="hidden" name="recruitment_notice_id"
                                        value="{{ $recruitment_notice->id }}">
                                    <div class="col-md-12">
                                        <span class="alert alert-warning">
                                            ورود اطلاعات برای موارد ستاره دار (*) الزامی است .
                                        </span>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-12">
                                            <div class="alert alert-dark">مشخصات شخصی</div>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>* نام</label>
                                            <input type="text" name="name" placeholder="نام" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label for=""> * نام خانوادگی</label>
                                            <input type="text" name="family" placeholder=" نام خانوادگی"
                                                class="form-control " required>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label> * انتخاب استان</label>
                                            <select class="form-control province_id" type="int" name="province"
                                                id="province_id" required>
                                                <option value="">انتخاب استان</option>
                                                @foreach(\App\Models\Province::all() as $province)
                                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label> * انتخاب شهر</label>
                                            <select class="form-control city_id" type="int" name="city" id="city_id"
                                                required>
                                                <option value="">انتخاب شهر</option>
                                                @foreach(\App\Models\City::all() as $city)
                                                <option value="{{ $city->id }}">{{ $city->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label> * تلفن همراه</label>
                                            <input type="text" name="phone" placeholder="تلفن همراه"
                                                class="form-control" required>
                                        </div>

                                        <div class="col-md-4 mb-2">
                                            <label> * جنسیت :</label>
                                            <select class="form-control" name="gender">
                                                <option value="0">
                                                    آقا
                                                </option>
                                                <option value="1">
                                                    خانم
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label>توضیحات (با ذکر آخرین مدرک تحصیلی و سوابق شغلی مربوطه)</label>
                                            <textarea name="work_history" class="form-control" required></textarea>
                                        </div>

                                        <div class="col-12 m2">
                                            <label> فایل رزومه: </label>
                                            <div class="input-group mb-2">
                                                <input id="resume_{{$recruitment_notice->id}}" class="d-none form-control file-upload file_upload_ajax" type="file" name="resume" accept=".pdf,.jpg,.jpeg"/>
                                                <label for="resume_{{$recruitment_notice->id}}" class="btn btn-primary custom-file-upload border border-1">
                                                    <i class="fa fa-cloud-upload"></i> انتخاب فایل
                                                </label>
                                            </div>
                                            <input type="hidden" id="resume_{{$recruitment_notice->id}}_path" class="path" name="resume_path" value="">
                                            <div id="resume_{{$recruitment_notice->id}}_src" class="show disInline"></div>
                                        </div>
                                        <div class="col-md-12">
                                            <p>توجه : کاربر گرامی در صورت داشتن فایل رزومه جداگانه لطفا رزومه را با
                                                فرمت pdf,jpg,jpeg آپلود کنید.</p>
                                        </div>
                                        <input type="submit" value="ارسال درخواست"
                                            class="btn btn-success mr-2 send-resume-btn">
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
<script src="{{ asset('/assets/dashboard/js/layouts.js') }}"></script>
<script src="{{asset('assets/script/ckeditor/ckeditor.js')}}"></script>
<script>
    //to do move script to single file
            $(document).ready(function () {
                // $('.date_of_birth,.military_service_end_date ,.exemption_military_service_end_date , .start_work_date , .education_end_date').mobiscroll().pDatepicker({
                //     // initialValueType: "gregorian",
                //     format: "YYYY/MM/DD",
                //     onSelect: "year",
                //     timePicker: {
                //         enabled: false,
                //         meridiem: {
                //             enabled: true
                //         }
                //     }
                // });
                // $('.date_of_birth,.military_service_end_date ,.exemption_military_service_end_date , .start_work_date , .education_end_date').val('');
                $("form.form-basic-information").submit(function(e) {
                    e.preventDefault();
                    var formData = new FormData(this);
                    $.ajax({
                        url: '/registerBasicInformation',
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
            //select province
$('.province_id').on('change', function () {
    var token = $('meta[name="csrf-token"]').attr('content');
    var id = $(this).val();
    $.ajax({
        url: '/city/select',
        type: 'post',
        dataType: 'json',
        data: {
            "_token": token,
            "id": id,
        },
        success: function (data) {
            $('.city_id')
                .empty()
                .append('<option value="all">انتخاب شهر</option>')
            ;
            $.each(data.cities, function (i, d) {
                $('.city_id').append('<option value="' + d.id + '">' + d.name + '</option>');
            });
        }
      });
});
</script>
@endsection
