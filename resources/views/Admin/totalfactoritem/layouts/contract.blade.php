<div id="printableAreaContract" class="bg-white">
    <div class="border-contract pr-5 pb-4 mb-4">
        <div class="row">
            <div class="col-3 mt-4">
                <p> تاریخ : {{ $item[ 'start_date' ] }} </p>
                <p> شماره قرارداد : {{ $totalFactorItem->id }} </p>
            </div>
            <div class="col-6 mt-4 text-center">
                <h3> خدمات رفاهی لاله</h3>
                <h5>قرارداد مالی و اصلی بین خدمات گیرنده و شرکت</h5>
            </div>
            <div class="col-3 mt-3">
                <img src="{{ asset('assets/dashboard/images/laleh_icon.png') }}" width="150px">
            </div>
        </div>
    </div>
    <div class="border-contract page-break-first p-6" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;font-weight: 700;">&nbsp;ماده 1 : طرفین قرارداد :</span></br>
            <span lang="FA" style="line-height: 150%;">1-1 : شرکت جانبازان آینده ساز(سهامی خاص) به شماره ی ثبت شده
                478000 به نشانی اتوبان تهران کرج ، ابتدای جاده محمد شهر، ورودی بازار میوه و تره بار البرز ، ساختمان
                پارسیان طبقه 1 با کد پستی 3177635715 با نام تجاری (شبکه خدمات رفاهی لاله با پروانه ی فعالیت شماره 521122
                صادره از اتحادیه کسب و کارهای مجازی با موضوع فروش و عرضه کالا و خدمات )به نمایندگی آقای سیدمصطفی
                میرحسینی که به موجب اساسنامه و تصمیم مجمع عمومی صاحبان سهام به سمت مدیرعامل، رئیس هیئت مدیره و دارای حق
                امضا انتخاب شده و از این پس در این قرارداد" شرکت" نامیده می شود.
                <br>
            </span>

            <span lang="FA" style="line-height: 150%;">1-2 : {{ $user::getGenderTitle($user->sex) }} {{ $user->name.'
                '.$user->family.' ، ' }} فرزند: {{ $user->father }} با
                کدملی: {{ $user->melicode ? $user->melicode : '.......' }} به نشانی محل سکونت: {{ isset($user->address)
                ? $user->address : '.......' }} به
                نشانی محل کار: {{ isset($user->work_address) ? $user->work_address : '.......' }} شماره ی تلفن همراه {{
                $user->phone }} شماره
                ی تلفن محل سکونت {{ isset($user->home_phone) ? $user->home_phone : '.......' }} شماره ی تلفن محل کار {{
                isset($user->work_phone) ? $user->work_phone : '...' }} که
                از این پس در این قرارداد "خدمات گیرنده" نامیده می شوند.<br>
            </span>
            @if ($totalFactorItem->guarantor_checked)
            <span lang="FA" style="line-height: 150%;">1-3 : ضامنِ خدمات
                گیرنده، {{ isset($item['contract_values']['gender']) ?
                $user::getGenderTitle($item['contract_values']['gender']) : 'خانم/آقای' }} {{
                isset($item['contract_values']['guarantorname']) ? $item['contract_values']['guarantorname'] : '...' }}
                {{ isset($item['contract_values']['guarantorfamily']) ? $item['contract_values']['guarantorfamily'] :
                '...' }} فرزند: {{ isset($item['contract_values']['fathername']) ?
                $item['contract_values']['fathername'] : '...' }} با کد ملی {{
                isset($item['contract_values']['guarantormelicode']) ? $item['contract_values']['guarantormelicode'] :
                '...' }} به
                نشانی محل سکونت {{ isset($item['contract_values']['addresshome']) ?
                $item['contract_values']['addresshome'] : '...' }} به نشانی محل کار {{
                isset($item['contract_values']['addresswork']) ? $item['contract_values']['addresswork'] : '...' }}
                شماره
                ی تلفن همراه {{ isset($item['contract_values']['mobile']) ? $item['contract_values']['mobile'] : '...'
                }} شماره ی تلفن محل سکونت {{ isset($item['contract_values']['phonehome']) ?
                $item['contract_values']['phonehome'] : '...' }} شماره
                ی تلفن محل کار {{ isset($item['contract_values']['phonework']) ? $item['contract_values']['phonework'] :
                '...' }} نوع ضمانت {{ $totalFactorItem::GuaranteeTYPE[$item['guarantee_type']] }} که
                از این پس در این قرارداد ضامن نامیده می شوند.<br>
            </span>
            @else
            <span lang="FA" style="line-height: 150%;">1-3 : ضامنِ خدمات
                گیرنده
            </span>
            <span lang="FA" style="line-height: 150%;">با توافق طرفین در این قرارداد ضامن خدمات گیرنده شخص خدمات گیرنده
                با مشخصات مندرج فوق میباشد.</span>
            @endif
        </p>
    </div>
    <div class="border-contract page-break p-6" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 2 : موضوع قرارداد :<br></span>
            <span lang="FA" style="line-height: 150%;">عبارت است از معرفی
                خدمات گیرنده برای دریافت خدمات {{ App\Models\totalFactorItem::setFactorItemable($totalFactorItem) }} از
                مراکز طرف قرارداد با شرکت که با
            </span>
            <span lang="FA" style="line-height: 150%;">
                دریافت تسهیلات مندرج در ماده 3 و خدمات مالی از شرکت به صورت اقساطی از خدمات دریافتی مورد
                نیاز بهره مند می گردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">این قرارداد
                دارای یک الحاقیه به شماره ی {{ $contract_number ? $contract_number : ''}}
                راجع به تعهدات خدمات دهندگان به همراه فاکتور فروش به شماره ی {{ $resnumber }} مورخ {{
                $item['date_first_pay'] }} می باشد که جزء لاینفک قرارداد است.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">

                تبصره 1 : نظر به این مهم که معمولا اجرای خدمات درمانی در بازه زمانی طولانی تر از ۳ ماه با افزایش بهای
                واحد
                کالا و خدمات مصرفی مواجه گردیده و تورم ناشی از آن بر نرخ کلی خدمات ارائه شده اثر گذار و موجب افزایش بهای
                تمام شده میگردد لذا با اعلام نظر موافق خدمات گیرنده و تراضی و آگاهی وی در صورت افزایش بها در طول مدت
                اجرای خدمات، در انتهای اجرای خدمات امور انجام شده بازبینی گردیده و خدمات گیرنده مکلف به تسویه دیون باقی
                مانده به طریق مشروح در قرارداد میباشد ، در این خصوص فاکتور و تصاویر مستندات در انتهای کار ضمیمه قرارداد
                و آخرین حکایت مالی بین طرفین و جایگزین فاکتور اولیه است.
                <br>
            </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;</span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 3 : مبلغ
                قرارداد و &nbsp;شرایط پرداخت :<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-1&nbsp; : مبلغ قرارداد : {{
                number_format($totalFactorItem->total_price - $item['prepayment']['price']) }} ریال
                با احتساب کارمزد به میزان {{ isset($item['wage']) ? number_format($item['wage']): '...'
                }}
                ریال&nbsp; &nbsp;که جمع اصل
                و کارمزد آن به مبلغ {{ isset($item['loan']) ? number_format($item['loan']) : '...' }} ریال می باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-2 : مبلغ فوق ، نظر به امکان کاهش و یا افزایش میزان خدمات
                دریافتی و در نتیجه تغییر فاکتور نهایی خدمات، قابل اصلاح و تغییر است . لذا ملاک محاسبه مبلغ کامل دیون
                خدمات گیرنده، آخرین فاکتور و هزینه نهایی خدمات اجرا شده می باشد که به عنوان پیوست شماره 2 قرارداد ،
                ضمیمه خواهد شد .<br>
            </span>
        </p>
    </div>
    @if($item['is_pay_installment_by_check'])
    @include('Admin.totalfactoritem.layouts.contract.payInstallmetCheck')
    @else
    @include('Admin.totalfactoritem.layouts.contract.payInstallmetOnline')
    @endif
    <div class="border-contract page-break p-6" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 4 : مدت قرارداد :<br></span>
            <span lang="FA" style="line-height: 150%;">مدت قرار داد از
                تاریخ انعقاد قرارداد به مدت {{ isset($item['installments_num']) ? $item['installments_num'] : '...' }}
                ماه است که در تاریخ {{ isset($item['end_date']) ? $item['end_date'] : '...' }}به شرط تسویه
                ی کامل دیون خاتمه می یابد انقضاء مدت ذکر شده رافع تعهدات خدمات گیرنده به پرداخت تمامی دیون اعم از اصل
                بدهی ، جرائم احتمالی متعلقه در زمان تاخیر و یا کارمزد و یا هر موضوعی که در قرارداد و منضمات به آن اشاره
                شده نمیباشد. <br>
            </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;</span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 5 : ضمانت و شرایط مربوط به آن :<br></span>
            @if($item['guarantee_type'] == 'check')
            @if(isset($item['contract_values']['check_type']) && $item['contract_values']['check_type'] == 'چک صیاد 2')
            <span lang="FA" style="line-height: 150%;">5-1 : خدمات گیرنده
                به منظور تضمین پرداخت اقساط خود، یک برگ چک به مبلغ {{ isset($item['contract_values']['checkprice']) ?
                $item['contract_values']['checkprice'] : '...' }} ریال و شماره
                {{ isset($item['contract_values']['checknumber']) ? $item['contract_values']['checknumber'] : '...' }}
                نزد بانک {{ isset($item['contract_values']['checkbank']) ? $item['contract_values']['checkbank'] : '...'
                }} در تاریخ {{ isset($item['contract_values']['check_date']) ? $item['contract_values']['check_date'] :
                '...' }} و در وجه {{ isset($item['contract_values']['recipient_check']) ?
                $item['contract_values']['recipient_check'] :
                '...' }}
                تحویل امور مالی شرکت نمود.<br>
            </span>
            @else
            <span lang="FA" style="line-height: 150%;">5-1 : خدمات گیرنده
                به منظور تضمین پرداخت اقساط خود، یک برگ چک به مبلغ {{ isset($item['contract_values']['checkprice']) ?
                $item['contract_values']['checkprice'] : '...' }} ریال به شماره
                {{ isset($item['contract_values']['checknumber']) ? $item['contract_values']['checknumber'] : '...' }}
                نزد بانک {{ isset($item['contract_values']['checkbank']) ? $item['contract_values']['checkbank'] : '...'
                }} بدون درج تاریخ و در وجه شرکت جانبازان آینده
                ساز به امور مالی شرکت تحویل نمود.<br>
            </span>
            @endif

            <span lang="FA" style="line-height: 150%;">5-2 : اگر دیون خدمات
                گیرنده از سقف مبلغ چک بیشتر گردد، حق طرح دعوای مطالبه ی وجه برای شرکت وجود دارد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : خدمات گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند اسناد ضمانتی خود را دریافت
                نماید.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            @elseif($item['guarantee_type'] == 'certificate')
            <span lang="FA" style="line-height: 150%;">5-1 : خدمات گیرنده به منظور تضمین پرداخت اقساط، گواهی کسر از حقوق
                با مهر و امضای معتبر به شماره {{ isset($item['contract_values']['certificate_num']) ?
                $item['contract_values']['certificate_num'] : '...' }}
                تاریخ {{ isset($item['contract_values']['certificate_date']) ?
                $item['contract_values']['certificate_date'] : '...' }} صادره از {{
                isset($item['contract_values']['certificate_location']) ?
                $item['contract_values']['certificate_location'] : '...'}} ، تسلیم امور مالی شرکت نمود. بدیهی است درصورت
                کشف خلاف و یا عدم ایفای تعهدات
                از سوی خدمات گیرنده، شرکت مخیر است اقدامات قانونی لازم را انجام دهد و از طریق اداره مربوطه اقدام به
                دریافت اقساط ماهیانه نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-2 : درصورت عدم همکاری
                اداره ی مربوطه و یا در صورت عدم وصول مطالبات به هر دلیل، شرکت حق و اختیار طرح دعوای
                حقوقی را خواهد داشت.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : خدمات گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند گواهی کسر از حقوق خود را دریافت
                نماید.<br>
            </span>
            @else
            <span lang="FA" style="line-height: 150%;">5-1 : خدمات گیرنده
                به منظور تضمین پرداخت اقساط خود، سفته ها به مبلغ {{
                isset($item['contract_values']['promissorynoteprice']) ? $item['contract_values']['promissorynoteprice']
                : '...' }} ریال به شماره
                {{ isset($item['contract_values']['promissorynotenumber']) ?
                $item['contract_values']['promissorynotenumber'] : '...' }} بدون درج تاریخ
                به امور مالی شرکت تحویل نمود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">مشخصات صاحب سفته(دراین قرارداد صاحب سفته شخص خدمات گیرنده
                است.):&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-2 : اگر دیون خدمات
                گیرنده از سقف مبلغ سفته بیشتر گردد، حق طرح دعوای مطالبه ی وجه برای شرکت وجود دارد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : خدمات گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند اسناد ضمانتی خود را دریافت
                نماید.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            @endif
            <span lang="FA" style="line-height: 150%;">&nbsp;</span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 6 : داوری :<br></span>
            <span lang="FA" style="line-height: 150%;">6-1 : در صورت بروز
                هرگونه اختلاف بین طرفین اعم از اینکه مربوط به اجرای تعهدات موضوع قرارداد یا مربوط
                به تفسیر و تعبیر هر یک از مواد قرارداد و پیوست های آن و یا تخلفی در خصوص تعهدات این قرارداد باشد و
            </span>
            <span lang="FA" style="line-height: 150%;"> همچنین سایر احتمالات از قبیل فسخ، بطلان، تعیین خسارات، وصول
                مطالبات ، اختلافات مالی ، اختلافات خدماتی
                و غیره ، پرونده و موارد مربوط به داور که از طرف شرکت تعیین گردیده به "واحد حقوقی شرکت" ارجاع می
                گردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">رای داور برای طرفین این قرارداد قطعی
                و لازم الاجرا است.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-2 : شرط داوری حاضر ،
                مستقل از این قرارداد بوده و حتی در فرض بطلان، فسخ یا اختتام آن نیز معتبر می باشد.<br></span>
            <span lang="FA" style="line-height: 150%;">6-3 : وفق بند 2 و
                4 ماده ی 469 از قانون آیین دادرسی مدنی، خدمات گیرنده به قبول داوری "واحد حقوقی شرکت"
                اقرار نمود و امضای قرارداد حاضر توسط طرفین مبنی بر قبول واحد حقوقی شرکت، بعنوان داور به منزله ی امضاء در
                حضور داور است .<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-4 : ابلاغیه ها توسط
                اظهارنامه ی رسمی توسط داور برای طرفین ارسال می گردد و نیازی به ابلاغ توسط دادگاه و یا اقدام
                دیگری نمی باشد.<br>
            </span>
        </p>
    </div>
    <div class="border-contract page-break p-6" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">6-5 : ابتدای مدت داوری
                روزی است که موضوع به داوری ارجاع می شود و مدت آن 6ماه می باشد که قابل تمدید است.<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 7 : سایر موارد :<br></span>
            <span lang="FA" style="line-height: 150%;">7-1 : خدمات گیرنده
                متعهد به پرداخت هزینه های تبعی و جنبی قرارداد از قبیل بیمه،
                مالیات، عوارض و مالیات بر ارزش افزوده وغیره می باشد که شخصاً پس از اعلام شرکت نسبت به پرداخت اقدام می
                نماید.<br></span>
            <span lang="FA" style="line-height: 150%;">7-2 : این قرارداد
                براساس اصل آزادی قراردادها و ماده 10 قانون مدنی، توافق طرفین و قوانین مصوب کشور
                جمهوری اسلامی ایران منعقد شده است که طرفین آن را پذیرفته و هرگونه ایراد و اعتراض
                نسبت به اجرای آن را از خود سلب و ساقط نمودند.<br></span>
            <span lang="FA" style="line-height: 150%;">7-3 : امضا کنندگان
                این قرارداد اقرار نمودند که جهت امضا و انعقاد آن، اطلاع کامل از مفاد قرارداد داشته
                و در کمال صحت جسمانی و عقلانی آن را امضاء و منعقد کرده اند و ادعایی خلاف قرارداد پذیرفته نخواهد بود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">7-4 : خدمات گیرنده
                با علم و آگاهی کامل از این قرارداد، کلیه خیارات خصوصاً خیار غبن فاحش و افحش را از
                خود سلب و ساقط نمود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;</span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 8 : اقامتگاه طرفین :<br></span>
            <span lang="FA" style="line-height: 150%;">به شرح مذکور در
                ماده 1 قرارداد حاضر می باشد و خدمات گیرنده و ضامن وی، موظف هستند حداکثر ظرف یک هفته
                پس از تغییر نشانی، یا تغییر تلفن ثابت یا تلفن همراه، نشانی و یا تلفن های جدید خود را
            </span>
            <span lang="FA" style="line-height: 150%;"> به طرف مقابل اعلام نمایند، در غیر اینصورت کلیه اوراق و مکاتبات
                به نشانی مذکور
                صدر این قرارداد ارسال خواهد شد و مرسل الیه نمی تواند به عذر عدم اطلاع معترض گردد.<br></span>
            <span lang="FA" style="line-height: 150%;">&nbsp;</span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 9 : تعداد نسخ، پیوست ها و ملحقات
                :<br>
            </span>
            <span lang="FA" style="line-height: 150%;">این قرارداد
                در 9 ماده و 4 نسخه، به همراه یک الحاقیه و پیوست ها،
                تنظیم گردیده و هرکدام از موارد مذکور جزئی غیرقابل تجزیه بوده و در صورت اختلاف،
                صرفاً استناد به همه ی موارد ذکر شده نافذ می باشد.
            </span>
        </p>
        @include('Admin.totalfactoritem.layouts.contract.histories')
    </div>
</div>
