@if($financial_plans_types)
    <div class="card mb-3">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">فرم تایید شرایط و مقررات :</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12 alert alert-success">
                    <strong>
                        مرحله اول :
                    </strong>
                    مطالعه توضیحات
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <div class="form-check" >
                            <input style="cursor: pointer" class="form-check-input" type="checkbox" value="" id="service-description-check" required>
                            <label style="cursor: pointer" class="form-check-label mr-3" for="service-description-check">
                                توضیحات خدمت را مطالعه نمودم
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 alert alert-success" id="step-two">
                    <strong>
                        مرحله دوم :
                    </strong>
                    انتخاب گزینه مورد نظر
                </div>
                <div class="row" id="step-two-box" style="position: relative ;width: 100%">
                    @if( request()->segment(1)!= 'lagestic' &&  request()->segment(2)!= 'business-agency'&&  request()->segment(2)!= 'Permanent-employment' )
                        <div class="col-md-12">
                            <div class="alert alert-danger m-5" role="alert">
                                کاربر گرامی لطفا توجه فرمائید، که در صورت انتخاب یکی از طرح های زیر و
                                <span class="badge badge-success">"تکمیل فرایند ثبت نام"</span>
                                فاکتور رسمی سجا با شناسه اقتصادی و نماد اعتماد الکترونیکی صادر میگردد لذا در صورتی که نیاز به اخذ خدمات قرض الحسنه بدون سود، بهره و بدون ضامن ندارید، با توجه به این مهم که هم اینک مردم عزیز به این خدمات نیاز ضروری دارند و سهمیه ها محدود است اقدام به تکمیل فرآیند ثبت نام ننمائید.
                                اگر نیاز به پرسش و راهنمایی جهت اقدام دارید از طریق
                                <a href="/contact">تماس با ما</a>
                                مطرح نمائید، دقت نمائید در صورت تکمیل فرایند ثبت نام و عدم پرداخت فاکتور صادر شده جهت افتتاح عضویت و تشکیل پرونده اقساطی جهت جلوگیری از تضییع حقوق نیازمندان به تسهیلات
                                <ul>
                                    <li>در مرحله اول تا دو ماه از دریافت هر گونه تسهیلات سلب امتیاز شده و (ممنوع دریافت خدمت) گردیده</li>
                                    <li>در صورت تکرار صدور فاکتور و عدم پرداخت، از تمامی خدمات سامانه سجا در سطح کشور محروم خواهید شد</li>
                                </ul>
                                لذا در تکمیل فرآیند ثبت نام و صدور فاکتور خود دقت فرمائید.
                            </div>
                        </div>

                    @endif
                    <div class="" id="step-two-cover" style="position: absolute;top:0;left: -13px;width: 100%;height: 100%;z-index: 9999;background-color: rgba(0, 0, 0, 0.5);"></div>
                    @foreach($financial_plans_types as $item)
                        <div class="col-12 col-md-8 mx-auto mb-3">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body d-flex flex-column justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">{{ $item->title }}</h5>
                                        <p class="card-text text-success">{{ number_format($item->price)}} ریال</p>
                                    </div>
                                    <button class="btn btn-success font-12 py-1 add-to-cart" data-href="{{URL::to('/')}}"
                                            data-service_id="{{ $service->id }}" data-id="{{ $item->id }}"
                                            data-financial_plans_type_id="{{ $item->id }}"
                                            data-representation="{{ $representation }}"
                                            data-task_id="{{isset($task) && !is_null($task)?$task->id:''}}"
                                            data-callcenter="{{ $callcenter }}"
                                            data-factor="{{ isset($factor)?$factor:'' }}"
                                            data-type="item" {{ !$item->status ? 'disabled' : '' }}>
                                        <i class="fas fa-cart-plus"></i>
                                        @if(in_array(trim($service->id) , App\Models\AgencyType::pluck('service_id')->toArray()))
                                            جهت اعلام آمادگی اینجا کلیک کنید
                                        @else
                                            تکمیل فرایند ثبت نام
                                        @endif
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
@else
    <button class="btn btn-orange px-4 my-3 text-white add-to-cart" data-href="{{URL::to('/')}}"
            data-service_id="{{ $service->id }}" data-id="{{ $service->id }}"
            data-representation="{{ $representation }}" data-callcenter="{{ $callcenter }}" data-type="service">
        <i class="fas fa-cart-plus"></i> ثبت نام
    </button>
@endif
