@extends('site.master')
@section('title')
فاکتور: {{ $factor->resnumber }}
@endsection
@section('content')
<div class="factor">
    <div class="factor-section bg-white py-5 position-relative">
        <div class="overlay position-absolute"></div>
        <div class="container">
            <div class="row">
            </div>
            <div class="factor-box col-12 rounded py-5 position-relative overflow-hidden">
                <div class="overlay position-absolute"></div>
                <div class="logo d-flex align-items-center flex-column">
                    <img src="/assets/site/images/factor-logo.png" alt="">
                    <h5 class="mt-3">شرکت جانبازان آینده ساز</h5>
                    <p class="my-0">سهامی خاص (478000)</p>
                    @if(!is_null($factor->representation))
                    <p>{{ $factor->representation->fa_name }}</p>
                    @else
                    <p>دفتر مرکزی </p>
                    @endif
                </div>
                @if(now() > $factor->expired_at && $factor->status != 1 )
                <p class="text-danger font-18 text-center">فاکتور: {{ $factor->resnumber }} منقضی شده است و
                    امکان پرداخت وجود ندارد، لطفا سفارش جدید ثبت نمایید یا با پشتیبانی تماس بگیرید.</p>
                <p class="font-18 text-center text-dark">پشتیبانی: 09366342145</p>
                @else
                <div class="col-12">
                    <div class="row">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <img class="w-50" src="/assets/site/images/footer-logo.png" alt="">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            @if(isset($factor->factorItems[0]))
                            <h5 class="my-0">صورتحساب قطعی فروش کالا و خدمات</h5>
                            @else
                            <h5 class="my-0">قبض پرداخت اقساط و دیون</h5>
                            @endif
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>تاریخ: {{ verta(\Carbon\Carbon::now())->format('%d %B ، %Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-5">
                    <p class="text-center font-18 bold">مشخصات فروشنده</p>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>نام حقیقی و حقوقی: شرکت جانبازان آینده ساز</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره اقتصادی: 411495546645</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره ثبت: 478000</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>استان: البرز / شهرستان: کرج</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>کد پستی: 1478654579</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره تماس: {{ $setting->call }} / فکس: 43851905</span>
                        </div>
                    </div>
                    @if(!is_null($factor->representation))
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره تماس نمایندگی: {{ $factor->representation->call }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>آدرس نمایندگی: {{ $factor->representation->address }}</span>
                        </div>
                    </div>
                    @else
                    <div class="row mb-3">
                        <div class="col-12  d-flex align-items-center">
                            <span>نشانی کامل: {{ $setting->address }}</span>
                        </div>
                    </div>
                    @endif

                </div>
                <div class="col-12 mt-5">
                    <p class="text-center font-18 bold">مشخصات خریدار</p>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>نام حقیقی و حقوقی: {{ $factor->user->name }} {{ $factor->user->family }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره اقتصادی / کد ملی: {{ $factor->user->melicode }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>استان: {{ $factor->user->province_id != null ?
                                \App\Models\Province::find($factor->user->province_id)->name : '' }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شهرستان: {{$factor->user->city_id != null ?
                                \App\Models\City::find($factor->user->city_id)->name : '' }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>کد پستی: {{ $factor->user->postalcode }}</span>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center justify-content-center">
                            <span>شماره تماس: {{ $factor->user->phone }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12  d-flex align-items-center">
                            <span>آدرس: {{ $factor->user->address }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-5">
                    @if(isset($factor->factorItems[0]))
                    <p class="text-center font-18 bold">مشخصات کالا یا خدمات مورد معامله</p>
                    <div class="table-responsive">
                        <table class="table bg-white rounded">
                            <thead>
                                <tr class="bg-gray text-orange">
                                    <th scope="col">خدمت</th>
                                    <th scope="col">مبلغ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($factor->factorItems as $item)
                                <tr>
                                    @if(!is_null($item->financialPlansType))
                                    <th>
                                        {{-- {{ $item->financialPlansType->title }}--}}
                                        {{ $item->factoritemable->factor_title.'-'.$item->financialPlansType->title}}
                                    </th>
                                    <th class="pay-invoice">
                                        {{ number_format($item->price) }} ریال
                                    </th>
                                    @else
                                    <th>
                                        {{ $item->factoritemable->title.'-'. $item->factoritemable->factor_title}}
                                    </th>
                                    <th class="pay-invoice">
                                        {{ number_format($item->price) .' ریال' }}
                                        {{ $item->price == 0 ? '(هدیه)' : '' }}
                                    </th>
                                    @endif
                                </tr>
                                <tr>
                                    <th>
                                        {{-- {{ number_format($item->factoritemable->tax) }}--}}
                                        {{number_format($factor->tax)}}
                                        درصد مالیات بر ارزش
                                        افزوده

                                    </th>
                                    <th>{{ number_format($factor->tax) }} ریال</th>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <p>ملاحظات : </p>
                        {{-- <p>--}}
                            {{-- در پرداخت فاکتور فوق دقت فرمائید این فاکتور پس از پرداخت قابلیت انصراف و عودت--}}
                            {{-- وجه ندارد.</p>--}}
                        <p>پس از پرداخت این فاکتور صفحه سایت را ترک ننمائید و جهت ادامه روند پرونده فرم اطلاعات یا
                            بارگذاری مدارک را کامل نمائید.</p>
                        @if($factor->status == 1)
                        <div class="alert alert-success">این فاکتور با موفقیت پرداخت شده است .</div>
                        @if((auth()->check() && auth()->user()->id == $factor->user_id ) || ! auth()->check() || (
                        auth()->check() && !is_null( $factor->factor_item) && !is_null(
                        $factor->factor_item->supervisor_id) && auth()->user()->id ==
                        $factor->factor_item->supervisor_id) )
                        <a href="/checkout/{{$factor->resnumber}}">
                            <span id="upload-docs" class="btn btn-primary">بارگذاری مدارک
                                <i class="fa fa-mouse-pointer"></i>
                            </span>
                        </a>
                        @endif
                        @endif
                        @foreach($factor->factorItems as $item)
                        @if(in_array(trim($item->factoritemable->id) ,
                        App\Models\AgencyType::pluck('service_id')->toArray()) )
                        <p>پرداخت این فاکتور تائید یا رد شرایط مالی شما نبوده و طبق تعاریف مالی و حقوقی مندرج در پرونده
                            زیر نظر واحد مربوطه بایست همکاری گردد.</p>
                        @endif
                        @endforeach
                        @elseif(isset($factor->factorItem))
                        <p class="text-center font-18 bold">مشخصات کالا یا خدمات مورد معامله</p>
                        <div class="table-responsive">
                            <table class="table bg-white rounded">
                                <thead>
                                    <tr class="bg-gray text-orange">
                                        <th scope="col">خدمت</th>
                                        <th scope="col">مبلغ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th>
                                            {{ $factor->factorItem->factoritemable->title.' - باقیمانده هزینه '.
                                            $debt_price .' ریال (با احتساب پرداخت این فاکتور) ' }}
                                        </th>
                                        <th class="pay-invoice">
                                            {{ number_format($factor->price) .' ریال' }}
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                            <p>ملاحظات : </p>

                            <p>1 - پس از پرداخت این فاکتور صفحه سایت را ترک ننمائید و جهت ادامه روند پرونده فرم اطلاعات
                                یا بارگذاری مدارک را کامل نمائید.</p>
                            @if(in_array(trim($factor->factorItem->factoritemable->id) ,
                            App\Models\AgencyType::pluck('service_id')->toArray()) )
                            <p>2 - پرداخت این فاکتور تائید یا رد شرایط مالی شما نبوده و طبق تعاریف مالی و حقوقی مندرج در
                                پرونده زیر نظر واحد مربوطه بایست همکاری گردد.</p>
                            @endif
                            @elseif(isset($factor->totalFactorItem))
                            <p class="text-center font-18 bold">مشخصات کالا یا خدمات مورد معامله</p>
                            <div class="table-responsive">
                                <table class="table bg-white rounded">
                                    <thead>
                                        <tr class="bg-gray text-orange">
                                            <th scope="col">خدمت</th>
                                            <th scope="col">مبلغ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th>
                                                @foreach ($factor->totalFactorItem->factorItems as $item)
                                                {{ $item->factoritemable->title .' - '}}
                                                @endforeach
                                                {{ ' باقیمانده هزینه '. $debt_price .' ریال (با احتساب پرداخت این
                                                فاکتور) '}}
                                            </th>
                                            <th class="pay-invoice">
                                                {{ number_format($factor->price) .' ریال' }}
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                                <p>ملاحظات : </p>

                                <p> پس از پرداخت این فاکتور صفحه سایت را ترک ننمائید و جهت ادامه روند پرونده فرم اطلاعات
                                    یا بارگذاری مدارک را کامل نمائید.</p>
                                @endif
                                {{-- <div class="alert alert-danger">
                                    <p>کاربر گرامی ؛</p>
                                    <p>
                                        به دلایل اختلال در سرعت اینترنت قبل از ورود به درگاه بانکی از بسته
                                        بودن فیلتر شکن خود مطئمن شده و در صورت هرگونه اختلال در پرداخت صورتحساب ها <a
                                            href="/contact" class="">تماس با ما</a> حاصل نمائید.
                                        مجموعه سجا جهت حفظ امنیت اطلاعات و منابع مالی شما عزیزان خواهشمند است جهت واریز
                                        هرگونه وجه مانند ، هزینه ثبت نام و تشکیل پرونده ، پیش پرداخت و یا اقساط خدمات یا
                                        هزینه تشکیل پرونده مناقصات و مزایدات رسمی از درگاه اینترنتی تعبیه شده در خدمات
                                        استفاده نموده و از پرداخت هرگونه وجه به حساب اشخاص خودداری نموده و‌در صورت
                                        مشاهده با ما تماس حاصل نمائید.
                                    </p>
                                </div> --}}
                                <div class="alert alert-primary">
                                    <h6>قرارداد الکترونیکی :</h6>
                                    <p>
                                        حسب قوانین مصوب مقنن محترم جمهوری اسلامی ایران
                                        وفق باب اول الی ششم قانون تجارت الکترونیکی با رعایت اصول قانونی ، شرعی و با حفظ
                                        آثار و قوت توافقات پیش از این قرارداد بین خریدار و فروشنده به صورت الکترونیکی
                                        منعقد گردید ؛
                                    </p>
                                    <p>
                                        ۱- هزینه پرداختی فاکتور فوق صرف نظر از خرید هرگونه کالا ، خدمات ، یا سایرمعاملات
                                        صرفا به مدت ۷ روز (یک هفته کاری) قابل انصراف و استرداد به طریق مشروح قوانین
                                        تجارت الکترونیکی بوده و پس از انقضاء آن به هیچ عنوان قابلیت استرداد و انصراف
                                        ندارد.
                                    </p>
                                    <p>
                                        ۲- وجوهات پرداختی پس از هفت روز (۷ روز ) صرف اجرای تعهدات ، خرید یا تولید کالا ،
                                        خرید یا تولید مواد اولیه ، خرید یا تولید خدمات مورد نیاز خریدار طبق توافق صرف
                                        گردیده و در صورت تعلق گرفتن کسورات قانونی ائم از مالیات ارزش افزوده یا هرگونه
                                        مواردی که قانون بر تراکنش و پرداخت دیون فوق ایراد ایجاد نماید ، قانون کشور ارجع
                                        برقوانین قرارداد و‌توافق حاضر بوده و متعاقب اعلام شرکت خریدار مکلف به پرداخت
                                        دیون میباشد.
                                    </p>
                                    <p>
                                        ۳- شرکت تمامی پرداختهای فوق ائم از فاکتور رسمی فوق و سایر پرداختهای مشتری را در
                                        قسمت پرونده های من ذیل صفحات سایت مطابق با قانون تجارت الکترونیکی بارگزاری و‌در
                                        معرض دید خریدار ، سازمانهای ناظر قرارداده و خریدار مکلف به بررسی تمامی تراکنش
                                        های خود و پرداختها میباشد.
                                    </p>
                                    <p>
                                        ۴- خریدار تعهد نموده مشمول قوانین منع دریافت خدمات از تشکل ها ، اصناف و واحد های
                                        خدماتی نبوده و با کارت یا حساب اصلی خود پرداخت به صورت ارز داخلی (ریال ایران) و
                                        بدور از شبهات پول سیاه، پول شویی و سایر شقوق منع شده در قانون نسبت به معامله فوق
                                        اقدام نموده است.
                                    </p>
                                    <p>
                                        ۵- در صورت بروز هرگونه اختلاف ، ائم از فنی ، اجرایی، مالی بین خریدار و فروشنده
                                        یا خدمات دهنده یا فروشنده معرفی شده از سوی شرکت که طبق مجوزات قابلیت این اقدام
                                        را دارا میباشد یا هرگونه تفسیر یا تعبیر این قرارداد که منجر به اختلاف شود بدوا
                                        از طریق مکاتبه الکترونیکی در قسمت تماس با ما در قسمت سایت با واحد حقوقی و در
                                        صورت عدم حل و فصل موضوع ، مراتب جهت تعیین داور یا کمیسیون داوری متشکل از یک یا
                                        چند نفر داور متخصص به انتخاب و تشخیص واحد حقوقی سجا ارجاع میگردد.
                                    </p>
                                    <p>
                                        ۶- این قرارداد و اجزای جدا ناپذیر آن ائم از مدارک و مستندات مندرج در پرونده ،
                                        فاکتور هزینه ها ، فیش واریزی و سایر مندرجات طبق قوانین تجارت الکترونیکی در درگاه
                                        www.lalecard.ir در قسمت پرونده های من برای کاربر و در دسترسی کارشناس پشتیبان و
                                        مسئول فنی مرتبط قراردارد و تمامی داده ها ائم از مادی و معنوی با رضایت کاربر ثبت
                                        و ذخیره گردیده است.
                                    </p>
                                </div>
                                <div
                                    class="col-12 d-flex justify-content-center justify-content-md-end align-items-center my-3">
                                    @if(!is_null($factor->coupon))
                                    <span class="bold font-14 my-0 py-0 mx-2">کل: {{
                                        number_format($factor->price_without_discount) }}
                                        ریال</span>
                                    <span class="bold font-13 my-0 py-0 mx-2 text-danger">تخفیف: {{
                                        number_format($factor->price_without_discount - $factor->price) }}
                                        ریال</span>

                                    <span class="bold font-13 my-0 py-0 mx-2">قابل پرداخت: {{
                                        number_format($factor->price) }}
                                        ریال</span>
                                    @else
                                    <span class="bold font-14 my-0 py-0 mx-2">کل: {{ number_format($factor->price) }}
                                        ریال</span>
                                    @endif

                                    <span class="bold font-12 my-0 py-0 text-red" id="offprice"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-md-3 justify-content-end align-items-center">
                                    <div class="form-group row mr-1">
                                        @if(!is_null($factor->coupon_id))
                                        <span class="font-13 text-success">کد تخفیف {{ $factor->coupon->discount }} %
                                            اعمال شد</span>
                                        @elseif($factor->status != 1)
                                        <form action="{{ route('coupon') }}" method="POST" class="d-flex">
                                            @csrf
                                            <input required class="form-control col-8" type="text" name="code"
                                                placeholder="کد تخفیف">
                                            <input type="hidden" name="factor_id" value="{{ $factor->id }}">
                                            <input type="submit" class="btn btn-primary font-12 col-3 mx-1"
                                                value="اعمال کد">
                                        </form>
                                        @foreach($errors->all() as $error)
                                        <span class="text-danger font-12">{{ $error }}</span>
                                        @endforeach
                                        @endif
                                        <a target="_blank" href="{{ asset('images/IMG-20191230-WA0000.jpg') }}">
                                            <p class="mt-3">مشاهده گواهی ارزش افزوده</p>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 justify-content-end align-items-center">
                                    <div class="row d-flex align-items-center justify-content-center">
                                        <div
                                            class="col-12 d-flex justify-content-center flex-column align-items-center">
                                            <form action="{{route('pay')}}" method="post"
                                                class="d-flex flex-column justify-content-center align-items-center">
                                                @csrf
                                                <input name="coupon" id="couponinputwallet" type="hidden" value="">
                                                <input name="factor_id" type="hidden" value="{{ $factor->id }}">
                                                {{-- --}}{{-- <span class="alert alert-primary bold">--}}
                                                    {{-- با توجه به اختلالات الکترونیکی پیش آمده لطفا مبلغ--}}
                                                    {{-- {{number_format($factor->price)}}--}}
                                                    {{-- ریال را به شماره کارت--}}
                                                    {{-- '5047061065198174'--}}
                                                    {{-- به نام سید مصطفی میرحسینی واریز نموده و فیش واریزی را از طریق
                                                    واتس اپ امور مالی ارسال فرمایید .--}}
                                                    {{-- </span> --}}
                                                @if( $factor->status != 1 )
                                                <div class="card" id="payments-box">
                                                    <div class="card-header">
                                                        <h5 class="mb-0">انتخاب روش پرداخت:</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="btn-group btn-group-toggle mt-3"
                                                            data-toggle="buttons">
                                                            <br>
                                                            <label
                                                                class="rounded btn btn-outline-warning text-dark active mx-2 d-flex align-items-center justify-content-center pointer">
                                                                <input type="radio" name="payment" id="option1"
                                                                    value="online" autocomplete="off" checked>
                                                                <span class="font-14">پرداخت از طریق کارت شتاب</span>
                                                            </label>
                                                            {{-- پرداخت کارت به کارت --}}
                                                            {{-- <label
                                                                class="rounded btn btn-outline-warning text-dark active mx-2 d-flex align-items-center justify-content-center pointer">
                                                                <input type="radio" name="payment" value="card"
                                                                    id="option3" autocomplete="off" checked>
                                                                <span class="font-14">پرداخت کارت به کارت</span>
                                                            </label> --}}
                                                            @auth
                                                            @if($factor->user->wallet >= $factor->price)
                                                            <label
                                                                class="rounded btn btn-outline-warning text-dark mx-2 d-flex flex-column align-items-center justify-content-center pointer">
                                                                <input type="radio" name="payment" value="wallet"
                                                                    id="option2" autocomplete="off">
                                                                <span class="font-12">کسر {{
                                                                    number_format($factor->price) }} ریال از کیف
                                                                    شما</span>
                                                                <span class="font-12">موجودی شما: {{
                                                                    number_format($factor->user->wallet) }} ریال</span>
                                                            </label>
                                                            @else
                                                            <button type="button"
                                                                class="rounded btn btn-secondary disabled d-flex flex-column align-items-center justify-content-center"
                                                                disabled>
                                                                <span class="font-12">کسر {{
                                                                    number_format($factor->price) }} ریال از کیف
                                                                    شما</span>
                                                                <span class="font-12">موجودی شما: {{
                                                                    number_format($factor->user->wallet) }} ریال</span>
                                                            </button>
                                                            @endif
                                                            @endauth
                                                            {{-- <label
                                                                class="rounded btn btn-outline-warning text-dark mx-2 d-flex flex-column align-items-center justify-content-center pointer">
                                                                <input type="radio" name="payment" value="card"
                                                                    id="option3" autocomplete="off">
                                                                <span class="font-14">پرداخت کارت به کارت</span>
                                                            </label> --}}
                                                        </div>
                                                        <input id="btn_factor" type="submit"
                                                            value="جهت پرداخت اینجا کلیک کنید"
                                                            class="btn btn-success font-14 my-2 btn-block">
                                                        {{-- <input id="btn_factor" type="submit"
                                                            value=" پرداخت , دریافت شماره کارت جهت پرداخت"
                                                            class="btn btn-success font-14 my-2 btn-block"> --}}

                                                    </div>
                                                </div>
                                                @endif
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3 d-flex justify-content-end align-items-center">

                                    @if(!is_null($factor->callcenter_id))
                                    <span class="mx-2">
                                        مسئول صدور : {{ \App\Models\User::find($factor->callcenter_id)->name }} {{
                                        \App\Models\User::find($factor->callcenter_id)->family }}
                                        این فاکتور را برای شما ایجاد کرده است.</span>
                                    @endif

                                    <span>مهر و امضا فروشنده</span>
                                    @if(!is_null($factor->representation))
                                    @if(!is_null($factor->representation->stamp))
                                    <img src="{{ asset($factor->representation->stamp) }}" alt="">
                                    @else
                                    <img src="/assets/site/images/mohr.png" alt="">
                                    @endif
                                    @else
                                    <img src="/assets/site/images/mohr.png" alt="">
                                    @endif
                                </div>
                            </div>
                        </div>

                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endsection
        @section('js')
        <script>
            $(document).ready(function() {
                    $('html, body').animate({
                        scrollTop: $('#upload-docs').offset().top
                    }, 1000);
                });
                $(document).ready(function () {
                    $('#accept-rules').prop('checked', false);

                    $('#accept-rules').change(function () {
                        if (this.checked) {
                            $('#payments-box').slideDown();
                        } else {
                            $('#payments-box').slideUp();
                        }
                    });
                });
        </script>
        @endsection
