@extends('site.master')

@section('title')
    سازمان های طرف قرارداد
@endsection

@section('content')

    <div class="contract">
        {{-- banner --}}
        @component('site.layouts.breadcrumb',['title'=>'سازمان های طرف قرارداد','background'=>asset('/assets/site/images/services-header.jpg')]) @endcomponent

        {{-- contract --}}
        <div class="contract-section py-5 bg-gray">
            <div class="container">
                <div class="row">
                   <div class="col-6 col-md-3 mb-4">

                           <div class="contract-box">
                               <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s4.png") }}" alt="">
                               <h6 class="text-center text-black-50 mt-3">اداره کل زندانهای استان تهران</h6>
                           </div>

                   </div>
                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s3.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">نیروی هوایی جمهوری اسلامی ایران</h6>
                            </div>

                    </div>
                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s2.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">آموزش و پرورش تهران بزرگ</h6>
                            </div>

                    </div>
                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s1.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">سازمان اتوبوسرانی</h6>
                            </div>

                    </div>
                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s8.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">بنیاد شهید جمهوری اسلامی ایران</h6>
                            </div>

                    </div>
                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s7.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">سازمان تامین اجتماعی تهران بزرگ</h6>
                            </div>

                    </div>
                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s6.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">پست بانک ایران</h6>
                            </div>

                    </div>
                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s5.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">بیمارستان تریتا</h6>
                            </div>

                    </div>


                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s11.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">سازمان تامین اجتماعی نیروهای مسلح</h6>
                            </div>

                    </div>

                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s12.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">شرکت پست جمهوری اسلامی ایران</h6>
                            </div>

                    </div>

                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s10.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">دادگستری تهران بزرگ</h6>
                            </div>

                    </div>

                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s9.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">نیروی زمینی جمهوری اسلامی ایران</h6>
                            </div>

                    </div>


                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s14.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">سازمان هواشناسی کشور</h6>
                            </div>

                    </div>


                    <div class="col-6 col-md-3 mb-4">

                            <div class="contract-box">
                                <img class="img-fluid rounded-circle border" src="{{ asset("/img/org/Laleh Web Design-new-Finalllll_s13.png") }}" alt="">
                                <h6 class="text-center text-black-50 mt-3">وزارت دفاع و پشتیبانی نیروهای مسلح</h6>
                            </div>

                    </div>


                </div>
            </div>
        </div>

    </div>
@endsection
