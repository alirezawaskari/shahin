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
        <table
            class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col">خدمات گیرنده</th>
                    <th scope="col">ضامن اول</th>
                    <th scope="col">ضامن دوم</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                </tr>
            </tbody>
        </table>
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

                تبصره 1 : نظر به این مهم که معمولا اجرای خدمات درمانی در بازه زمانی طولانی تر از ۳ ماه با افزایش بهای واحد
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
                با احتساب کارمزد 4 % سالیانه به میزان {{ isset($item['wage']) ? number_format($item['wage']): '...'
                }}ریال و مالیات به میزان {{ isset($item['wage']) ? number_format($item['wage'] * 0.09): '...' }}
                ریال&nbsp; &nbsp;که جمع اصل
                و کارمزد آن به مبلغ {{ isset($item['loan']) ? number_format($item['loan']) : '...' }} ریال می باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-2 : مبلغ مذکور بسته
                به افزایش یا کاهش میزان خدمات دریافتی و یا خارج شدن بخشی از خدمات از فاکتوربه علت
                &nbsp;نبود تجهیزات یا مواد یا خدمات دهنده خاص که
                امکان دارد در طی زمان اجرا به هر علت موجود نباشد، یا به واسطه تغییر فاکتور اصلی قابل تغییر است ،
                &nbsp;لذا ملاک محاسبه مبلغ کامل دیون خدمات گیرنده،
                آخرین فاکتور وهزینه نهایی خدمات اجرا شده می باشد که بعنوان پیوست شماره 2 قرارداد ، قرار داده خواهد شد و
                مورد تأیید و توافق طرفین است .<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-3 : موضوع قرارداد
                شامل اصل مبلغ تسهیلات و کارمزد به میزان 4 %&nbsp;
                کل دیون می باشد و خدمات گیرنده به موجب این قرارداد ملزم و متعهد گردید که
                جمع اصل و کارمزد خدمات اقساطی را که مبلغ {{ isset($item['loan']) ? number_format($item['loan']) : '...'
                }} ریال میباشد به اقساط
                مساوی و متوالی طی {{ isset($item['installments_num']) ? $item['installments_num'] : '...' }} ماه از قرار
                هر قسط {{ isset($item['installments_price']) ? number_format($item['installments_price']) : '...' }}
                ریال به شماره کارتی که از طریق درگاه الکترونیکی به خدمات گیرنده اعلام می شود ، &nbsp;پرداخت نماید.
                به موجب این قرارداد ، خدمات گیرنده اجازه ارسال پیامک حاوی لینک درگاه الکترونیکی
                معتبر و دارای مجوز را به شرکت صادر نموده و جهت پرداخت اقساط مذکور و تمامی هزینه
                های مرتبط با قرارداد اعم از اختلاف حساب و سایر موارد اعم از هزینه های تشکیل
                پرونده و غیره را صرفاً از طریق لینک مذکور با دقت نظر در تطبیق نام شرکت و درگاه الکترونیکی و صرفاً از
                سامانه پیامکی که نخستین بار پیامک ثبت نام و تشکیل پرونده و موفقیت در پرداخت ثبت نام را به خدمات گیرنده
                ابلاغ نموده است پرداخت نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">تبصره 2 : در
                صورتی که به واسطه نبود اینترنت ، نداشتن اعتبار تلفن ، یا قطع غیر مترقبه در ارسال پیامکهای مربوطه اختلالی
                ایجاد گردد ،
            </span>
        </p>
        <table
            class="table table-contract table-bordered border-contract table-striped table-responsive-md mb-2 text-center">
            <thead>
                <tr>
                    <th scope="col">خدمات گیرنده</th>
                    <th scope="col">ضامن اول</th>
                    <th scope="col">ضامن دوم</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="border-contract page-break p-6" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">
                متقاضی ( خدمات گیرنده ) مکلف به واریز مبلغ اقساط یاد شده
                از طریق ورود به وبسایت رسمی لاله کارت ، قسمت پرونده های من ، پرداخت اقساط یا به شماره کارت و حساب اعلام
                شده از طریق شماره تماس صاحب امضای مجاز در سررسید خود بدون اتلاف زمان و تطویل اوقات به بهانه قطع و اختلال
                در سیستم های الکترونیکی به صورت کارت به کارت یا واریز از حساب به کارت یا ارائه نقدی وجه اقساط به شرکت (
                صرفاً صاحب امضاء یاد شده) بوده و مکلف است رسید وجه پرداختی را نگهداری و تصویر یا رونوشت آن را جهت درج در
                پرونده به حساب کاربری خود منعکس یا به شرکت تحویل نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">تبصره 3 : خدمات گیرنده حق واریز اقساط به هیچ حسابی غیر از آنچه که
                از طریق درگاه الکترونیکی و درگاه موجود در وبسایت رسمی شرکت اعلام می شود را ندارد و در صورت واریز هرگونه
                وجه ، پرداخت نقدی آن و دیگر مواردی که به حساب رسمی یاد شده واریز نشده یا به سایر افراد عیر از مرجع اصلی
                و طرف قرارداد باشد آن قسط یا وجه ، دریافت نشده طلقی و قابل پیگرد از سوی شرکت میگردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-4 : خدمات گیرنده / ضامن / ضامنین متعهد شدند جهت جهت هرگونه عدم
                پرداخت دیون قرض الحسنه به تصور بدون سود و بهره بودن تسهیلات و فرار از دین مربوطه ، وجه التزام بدین شرح
                تعیین میگردد ، در صورت تاخیرغیر مجاز و بدون هماهنگی و دریافت تأیید کتبی از طرف قرارداد و باقی ماندن
                اقساط یا هرگونه دیون مرتبط با قرارداد ، مبلغ بستانکاری شرکت ضرب در {{ isset($item['percent_fines']) ?
                $item['percent_fines'] : '...'}} % و ضرب در تعداد روزهای تاخیر شده گردیده و به همراه اصل مطالبات به
                عنوان بستانکاری شرکت از سوی خدمات گیرنده/ضامن یا ضامنین مشخص شده میبایست پرداخت گردد .<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-5 : خدمات گیرنده / ضامن / ضامنین متعهد شدند اقساط را هرماه و در
                سررسید تعیین شده، به حساب اعلام شده شرکت و از طریق درگاه الکترونیکی واریز نمایند و خدمات گیرنده موظف به
                نگهداری و ارائه ی اسناد پرداختی برای تسویه حساب و دریافت تضامین و مدارک خود میباشد و عذر عدم اطلاع از
                این حیث پذیرفته نیست در صورت تاخیر و منظور شدن جریمه ، هر گونه پرداخت وجه صرفاً از بدهی نامبرده کاسته و
                تا پرداخت کامل بدهی گذشته به جرایم وی افزوده میگردد و خدمات گیرنده حق هر گونه اعتراض را از خود سلب و
                ساقط نمود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">
            </span>
            <span lang="FA" style="line-height: 150%;">
                3-6 : پرداخت از طریق فاکتور الکترونیکی در سایت لاله کارت (www.lalecard.ir) و یا پیامک ارسالی هرماهه از
                طرف شرکت صورت می پذیرد. خدمات گیرنده با اطلاع از سایت رسمی و درگاه اینترنتی به نام شرکت جانبازان آینده
                ساز و با تشکیل پرونده الکترونیکی و ثبت نام از همان طریق یاد شده جهت پرداخت مابقی هزینه ها عذر عدم دسترسی
                به راه های پرداختی را از خود سلب و ساقط نموده است .<br>
                <o:p></o:p>
            </span>
        </p>
        <table
            class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col">خدمات گیرنده</th>
                    <th scope="col">ضامن اول</th>
                    <th scope="col">ضامن دوم</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 140px;"></td>
                    <td style="height: 140px;"></td>
                    <td style="height: 140px;"></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="border-contract page-break p-6" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">3-7 : در صورت عدم پرداخت دو قسط متوالی و یا سه قسط متناوب توسط
                خدمات گیرنده، شرکت مخیر است تمامی دیون موجل مربوطه را تبدیل به حال نموده و مطالبات خود را بابت بهای
                موضوع قرارداد و جرایم و تأخیر تادیه آن به همراه هزینه دادرسی یا حق الوکاله وکیل و سایر موارد احتمالی را
                ازخدمات گیرنده/ضامن/ضامنین وی مطالبه نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">
                3-8 : فاکتور منظور شده برای خدمات گیرنده با گواهی امضاء ذیل آن و پذیرش کامل مفاد الحاقیه تعهدات خدمات
                دهندگان و اطلاعیه های آن (پیوست شماره 1) و در صورت تغییر جزئیات اجرایی و یا نرخ ها و یا موارد دیگر
                (پیوست شماره 2 ) قرارداد و قرارداد بین خدمات دهنده و شرکت به عنوان (پیوست شماره 3) قرارداد و از اجزاء
                لاینفک قرارداد حاضر میباشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-9 : امکان تغییرهزینه های مندرج در فاکتور نهایی به علل متفاوت از
                قبیل تغییر در شیوه و نوع خدمات ومبالغ آن وجود دارد و خدمات گیرنده با آگاهی کامل نسبت به موضوع ، اقدام به
                امضاء قرارداد حاضر نموده است در صورت کاهش هزینه از میزان برآورد اولیه شرکت یا خدمات دهنده از میزان بدهی
                و کارکرد متعلقه خدمات گیرنده کسر و از اقساط یا باقی بدهی کسر میگردد و در صورت افزایش هزینه از میزان
                برآورد اولیه صورت گرفته خدمات دهنده مکلف به پرداخت نقدی تمام هزینه مازاد میباشد و شرکت مخیر است در صورت
                عدم همکاری خدمات گیرنده طرح درمان و یا نوعیت درمان را به میزان پرداخت شده انجام و مابقی خدمات را ارائه
                ننماید و یا به شکل مورد تأیید ناظرین قرارداد تغییر در طرح درمان بیمار دهد در هر صورت در صورتی که از محل
                عدم پرداخت هزینه های اجرای کار که بدواً براورد اولیه آن احتمال مغایرت دارد تاخیر در اجرای خدمات صورت
                بگیرد خدمات گیرنده حق اعتراض ندارد و شرکت مخیر به قطع ارائه خدمات میباشد و در صورت اتمام اجرا و تمام شدن
                مراحل خدمات و ارائه آن میتواند از طریق این قرارداد و فاکتور ارائه شده مطالبات خود را با تاخیر و تادییه
                احتمالی پیگیری نماید. </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;">3-10 : شرکت تنها زمانی متعهد به صدور دستور اجرای خدمات به خدمات
                دهنده یا خدمات دهندگان طرف با خدمات گیرنده میباشد که دیون مادی و اسناد تضامنی و مدارک خدمات گیرنده ارائه
                ، ثبت و تأیید گردد ، سپس شرکت از طریق سامانه الکترونیکی مجوز آغاز خدمات دهی به خدمات گیرنده را ابلاغ
                نموده و تأیید پرونده نیز به خدمات گیرنده ابلاغ میگردد ، لذا امضای قرارداد حاضر بدون پرداخت مبیع یا ارائه
                تأیید اسناد برای خدمات گیرنده هیچ قابلیتی نداشته و صرفاً پس از تأیید الکترونیکی از سوی شرکت قابلیت اجرا
                و پیگیری دارد. </span>
            <br>
            <span lang="FA" style="line-height: 150%;">3-11 : چنانچه متقاضی به واسطه عواملی خارج از تعهدات شرکت و یا به
                هر علت دیگر ، از بهره برداری از اعتبار صادره و اعطاء شده جهت اجرای موضوع قرارداد امتناع ورزد ، با توجه
                به کارسازی چکهای ارائه شده و عدم امکان استرداد وجه یا اسناد مذکور ، متقاضی صرفا می تواند شخصا و یا با
                معرفی مرتبطین یا بستگان و نزدیکان خود از اعتبار تخصیص یافته استفاده کرده و بهره مند گردد . </span>
            <br>
        </p>
        <table
            class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col">خدمات گیرنده</th>
                    <th scope="col">ضامن اول</th>
                    <th scope="col">ضامن دوم</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                </tr>
            </tbody>
        </table>
    </div>
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
        <table
            class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col">خدمات گیرنده</th>
                    <th scope="col">ضامن اول</th>
                    <th scope="col">ضامن دوم</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                </tr>
            </tbody>
        </table>
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
                در 9 ماده و 3 نسخه، به همراه یک الحاقیه و پیوست ها،
                تنظیم گردیده و هرکدام از موارد مذکور جزئی غیرقابل تجزیه بوده و در صورت اختلاف،
                صرفاً استناد به همه ی موارد ذکر شده نافذ می باشد.
            </span>
        </p>
        <table
            class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col">خدمات گیرنده</th>
                    <th scope="col">ضامن اول</th>
                    <th scope="col">ضامن دوم</th>
                    <th scope="col">مدیر امور مالی شرکت</th>
                    <th scope="col">مدیر امور قراردادها</th>
                    <th scope="col">مدیرعامل شرکت</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                    <td style="height: 160px;"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
