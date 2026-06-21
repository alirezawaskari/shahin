@extends('Admin.master')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="x_title">
            <h4>ثبت کالا</h4>
        </div>

        <form class="form-horizontal" method="post" action="{{route('products.store')}}" enctype="multipart/form-data">
            {{csrf_field()}}
            <section class="signup-step-container">
                <div class="container">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-12">
                            <div class="wizard">
                                <div class="wizard-inner">
                                    <div class="connecting-line"></div>
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li role="presentation"
                                            class="active  @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step1" data-toggle="tab" aria-controls="step1" role="tab"
                                                aria-expanded="true"> <i>قدم اول</i><small>گروه کالا</small></a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step2" data-toggle="tab" aria-controls="step2" role="tab"
                                                aria-expanded="false"> <i>قدم دوم</i><small>اطلاعات کالا</small></a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step3" data-toggle="tab" aria-controls="step3" role="tab"><i>قدم
                                                    سوم</i><small>درج ویژگی های کالا</small></a>
                                        </li>
                                        <li role="presentation"
                                            class="disabled @if($center_id != '' ) width-Nav-tabs @endif">
                                            <a href="#step4" data-toggle="tab" aria-controls="step4" role="tab"><i>قدم
                                                    چهارم</i><small>بارگذاری تصاویر</small></a>
                                        </li>
                                        @if($center_id =='')
                                        <li role="presentation" class="disabled">
                                            <a href="#step5" data-toggle="tab" aria-controls="step5" role="tab"><i>قدم
                                                    پنجم</i><small>لوکیشن کالا</small></a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                                <div class="tab-content" id="main_form">
                                    <div class="tab-pane active" role="tabpanel" id="step1">
                                        <h5 class="text-center">انتخاب دسته بندی کالا</h5>
                                        @include('Admin.step.step_one')
                                        <br />
                                        <button type="button"
                                            class="badge badge-info next-step float-right">ادامه</button>
                                    </div>
                                    <div class="tab-pane" role="tabpanel" id="step2">
                                        <h5 class="text-center">اطلاعات کالا</h5><br />
                                        @include('Admin.step.step_two',['internal_use'=>true])
                                        <hr />
                                        <button type="button"
                                            class="badge badge-info prev-step float-left">برگشت</button>
                                        <button type="button"
                                            class="badge badge-info next-step float-right">ادامه</button>
                                    </div>
                                    <div class="tab-pane" role="tabpanel" id="step3">
                                        <h5 class="text-center">درج ویژگی‌های کالا</h5>
                                        <div class="col-md-12 col-sm-12 col-xs-12 createAttributesBox">

                                        </div>
                                        <hr />
                                        <button type="button"
                                            class="badge badge-info prev-step float-left">برگشت</button>
                                        <button type="button"
                                            class="badge badge-info next-step float-right">ادامه</button>
                                    </div>
                                    <div class="tab-pane" role="tabpanel" id="step4">
                                        <h5 class="text-center">بارگذاری تصاویر</h5>
                                        <br /><br />

                                        <ul>
                                            <li>ابعاد تصویر بایستی در بازه 600*600 تا 2500*2500 و حجم آن باید کمتر
                                                از 6 مگا بایت باشد.
                                            </li>
                                            <li>کالا باید 85% تصویر را در برگیرد و پس زمینه تصویر اصلی باید کاملا
                                                سفید باشد.
                                            </li>
                                            <li>تصویر باید فقط کالایی که قصد فروش آن را دارید نمایش دهد و بدون هیچ
                                                لوگو ، نوشته و یا واترمارکی باشد.
                                            </li>
                                            <li>تصویر شما باید مربعی باشد یا ابعاد یک در یک داشته باشد.</li>
                                            <li>فرمت تصاویر بایستی JPG باشد.</li>
                                            <li>ثبت یک عکس ضروری می باشد و در صورت ثبت یک عکس می توانید کالا را ثبت
                                                کنید.
                                            </li>
                                            <li>پرچم: برای مشخص کردن تصویر اصلی کالا، علامت پرچم را انتخاب کنید.
                                            </li>
                                            <li>سطل زباله: اگر تصویر را به اشتباه انتخاب کرده‌اید، روی علامت سطل
                                                زباله کلیک کنید تا تصویر مورد نظر حذف شود
                                            </li>
                                        </ul>
                                        <br /><br />

                                        <div class="form-group">
                                            <div class="col-md-6 col-sm-6 col-xs-12">
                                                <label for="file-upload" class="custom-file-upload">
                                                    <i class="fa fa-cloud-upload"></i> بارگذاری تصاویر
                                                </label>
                                                <input id="file-upload" type="file" onchange="readURL(this);" />

                                                <div>
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="imagesList">
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <br /><br />
                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت
                                                </button>
                                            </li>
                                            @if($center_id == null)
                                            <li>
                                                <button type="button" class="badge badge-info next-step">ادامه</button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="tab-pane" role="tabpanel" id="step5">
                                        <div class="disTable">
                                            <input class="type_good_selected disInline" type="radio"
                                                name="type_good_selected" title="غرفه من" value="1" checked><span
                                                class="lblRadioBtn font-weight-bold font16">غرفه من </span>
                                        </div>

                                        <div class="goodStockBox" style="display: none;"><br /><br />
                                            @include('Admin.partials.productStoreRoom',['arrayName'=>'good','stock'=>false])
                                        </div>
                                        <hr />
                                        <ul class="list-inline pull-right">
                                            <li>
                                                <button type="button" class="badge badge-info prev-step">برگشت
                                                </button>
                                            </li>
                                            <li>
                                                <button type="submit"
                                                    class="btn btn-success submitGood floatLeft mrgT20"
                                                    style="display:none">ثبت و درج کالا بعدی</button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </form>

    </div>
</div>
@stop
@section('css')
<style>
    input[type="file"] {
        display: none;
    }

    .width-Nav-tabs {
        width: 25% !important;
    }
</style>
<link rel="stylesheet" href="{{asset('assets/dashboard/css/site/product/stepWizard.css')}}">
@stop
@section('js')
<script src="{{ asset('/assets/script/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/categoryJs.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/product/stepWizard.js') }}"></script>
<script src="{{ asset('assets/dashboard/js/site/product/createGood.js') }}"></script>
<script>
    $(document).ready(function () {
            CKEDITOR.replace('description_lbl',ck_uploader) ;
        })
</script>
@stop
