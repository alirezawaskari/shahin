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
                        @foreach($errors->all() as $error)
                            <div class="alert alert-danger" role="alert">
                                {{ $error }}
                            </div>
                        @endforeach
                        @foreach($recruitment_notices as $recruitment_notice)
                            <button class="accordion text-right mb-2">{{ $recruitment_notice->title .' - '. $recruitment_notice->province->name}}</button>
                            <div class="panel">
                                <div class="card-body overflow-auto" >
                                    {!! $recruitment_notice->description !!}
                                </div>


                                <form action="/send_resume" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="recruitment_notice_id" value="{{ $recruitment_notice->id }}">
                                    <div class="col-12 mt-4">
                                        <div class="form-row">
                                            <div class="col-6">
                                                <input type="text" name="name" placeholder="نام و نام خانوادگی" class="form-control ml-2 mb-2 bg-light custom-input font-14" required>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" name="phone" placeholder="تلفن همراه" class="form-control no-snip mb-2 bg-light custom-input font-14" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <input type="text" name="email" placeholder="ایمیل" class="form-control ml-2 mb-2 bg-light custom-input font-14">
                                        </div>
                                        <div class="form-group">
                                            <textarea name="text" class="form-control no-resize bg-light custom-input font-14" rows="6" placeholder="توضیحات" required></textarea>
                                        </div>
                                        <div class="form-row">
											<label> آپلود رزومه :</label>
											<br>
                                            <input type="file" name="resume" id="file" accept=".pdf,.jpg,.jpeg">
                                            <div class="col-md-12">
                                                <p>توجه :  کاربر گرامی لطفا رزومه را با فرمت pdf,jpg,jpeg آپلود کنید.</p>
                                            </div>
                                            <input type="submit" value="ارسال درخواست" class="btn btn-success mr-2">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endforeach
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

@endsection

