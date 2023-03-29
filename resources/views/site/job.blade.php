@extends('site.master')

@section('content')

    <div class="service">
        <div class="register-section py-4" id="one-section">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-2">
                    </div>
                    <div class="col-12 col-md-8 py-5 my-auto mr-auto">
                        <div class="card">
                            <div class="card-header">معرفی شرکت</div>
                            <div class="card-body">متخصص محترم و کارجوی عزیز 

شبکه لاله در سطوح مختلف از رشته های متعدد و سطوح مختلف دعوت به همکاری مینماید.</div>
                        </div>
                        <h5 class="mt-5">فرصت های شغلی</h5>
                        <form action="/savecontact" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-body">
                                    <img src="{{ asset('assets/site/images/478345.png') }}" width="25px">طراح گرافیست و انیماتور 
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <img src="{{ asset('assets/site/images/49128.svg') }}" width="25px">  اپراتور مرکز تماس
                                </div>
                            </div>
							  <div class="card">
                                <div class="card-body">
                                    <img src="{{ asset('assets/site/images/developer.jpg') }}" width="25px">  برنامه نویس لاراول
                                </div>
                            </div>
							  <div class="card">
                                <div class="card-body">
                                    <img src="{{ asset('assets/site/images/accountants.png') }}" width="25px">  حسابدار 
                                </div>
                            </div>
							 <div class="card">
                                <div class="card-body">
                                    <img src="{{ asset('assets/site/images/868-34-512.png') }}" width="25px">  پشتیبان سایت
                                </div>
                            </div>
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
                                    <input type="text" name="email" placeholder="ایمیل" class="form-control ml-2 mb-2 bg-light custom-input font-14" required>
                                </div>

                                <div class="form-group">
                                    <textarea required name="text" class="form-control no-resize bg-light custom-input font-14" rows="6" placeholder="توضیحات"></textarea>
                                </div>
                                <div class="form-group">
                                    <input type="submit" value="ارسال درخواست" class="btn btn-orange btn-block text-white font-14">
                                </div>
                            </div>
                        </form>

                    </div>
                    <div class="col-md-2">
                    </div>

                </div>
            </div>
        </div>
    </div>
 @if(session()->get('contact_success')=='success')
        <span id="contact_success"></span>
    @elseif(session()->get('contact_error')=='success')
        <span id="contact_error"></span>
    @endif
@endsection

