<div id="printableAreaContract" class="bg-white">
    <div class="border-contract pr-5 pb-4 mb-4">
        <div class="row">
            <div class="col-md-3 mt-4">
                <p> تاریخ : {{ verta(date("Y/m/d H:i"))->format('%d / %m / %Y - H:i') }} </p>
                <p> شماره قرارداد : {{ $totalFactorItem->id }} </p>
            </div>
            <div class="col-md-6 mt-4 text-center">
                <h3> خدمات رفاهی لاله</h3>
                <h5>قرارداد مالی و اصلی بین وام گیرنده و شرکت</h5>
            </div>
            <div class="col-md-3 mt-3">
                <img src="{{ asset('assets/dashboard/images/logo.png') }}" width="150px">
            </div>
        </div>
    </div>
    <div class="border-contract page-break-first p-6" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;font-weight: 700;">&nbsp;ماده 1 : طرفین قرارداد :</span><br>
            <span lang="FA" style="line-height: 150%;">1-1 : شرکت جانبازان
                آینده ساز(سهامی خاص) به شماره ی ثبت شده 478000 به نشانی کرج ، 45 متری گلشهر ،
                پونه شرقی ساختمان دارا ُ (جنب فروشگاه بهشتی ) طبقه 10 با برند تجاری (شبکه خدمات رفاهی لاله با پروانه
                ی فعالیت شماره 521122 صادره از اتحادیه کسب و کارهای مجازی با موضوع فروش و عرضه
                کالا و خدمات )به نمایندگی آقای سیدمصطفی میرحسینی که به موجب اساسنامه و تصمیم مجمع
                به سمت مدیرعامل، رئیس هیئت مدیره و دارای حق امضا انتخاب شده اند و از این پس در این
                قرارداد" شرکت" نامیده می شوند.
                <br>
                در این قرارداد خانم سید فاطمه میرحسینی دهنه سری به عنوان مسئول پیگیری امور مالی شرکت و پیگیری کننده
                موضوعات مالی و پرداخت ها به وام گیرنده معرفی می گردد.
                <br>
            </span>

            <span lang="FA" style="line-height: 150%;">1-2 : آقای/خانم {{ $user->name.' '.$user->family }} فرزند: {{
                $user->father }} با
                کدملی: {{ $user->melicode }} به نشانی محل سکونت: {{ isset($user->address) ? $user->address : '.......'
                }} به
                نشانی محل کار: {{ isset($user->work_address) ? $user->work_address : '...' }} شماره ی تلفن همراه {{
                $user->phone }} شماره
                ی تلفن محل سکونت {{ isset($user->home_phone) ? $user->home_phone : '...' }} شماره ی تلفن محل کار {{
                isset($user->work_phone) ? $user->work_phone : '...' }} که
                در این قرارداد "خدمات گیرنده" نامیده می شوند.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">1-3 : ضامنِ خدمات
                گیرنده، آقای/خانم {{ isset($item['contract_values']['guarantorname']) ?
                $item['contract_values']['guarantorname'] : '...' }} {{
                isset($item['contract_values']['guarantorfamily']) ? $item['contract_values']['guarantorfamily'] : '...'
                }} فرزند: {{ isset($item['contract_values']['fathername']) ? $item['contract_values']['fathername'] :
                '...' }} با کد ملی {{ isset($item['contract_values']['guarantormelicode']) ?
                $item['contract_values']['guarantormelicode'] : '...' }} به
                نشانی محل سکونت {{ isset($item['contract_values']['addresshome']) ?
                $item['contract_values']['addresshome'] : '...' }} به نشانی محل کار {{
                isset($item['contract_values']['addresswork']) ? $item['contract_values']['addresswork'] : '...' }}
                شماره
                ی تلفن همراه {{ isset($item['contract_values']['mobile']) ? $item['contract_values']['mobile'] : '...'
                }} شماره ی تلفن محل سکونت {{ isset($item['contract_values']['phonehome']) ?
                $item['contract_values']['phonehome'] : '...' }} شماره
                ی تلفن محل کار {{ isset($item['contract_values']['phonework']) ? $item['contract_values']['phonework'] :
                '...' }} نوع ضمانت {{ $totalFactorItem::GuaranteeTYPE[$item['guarantee_type']] }} که
                در این قرارداد ضامن نامیده می شوند.<br>
            </span>
        </p>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md mb-3 text-center">
            <thead>
                <tr>
                    <th scope="col"> وام گیرنده</th>
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
    <div class="border-contract page-break p-6 mt-4"style="text-align: justify;" >
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 2 : موضوع قرارداد :<br></span>
            <span lang="FA" style="line-height: 150%;">
                عبارت است از دریافت وام قرض الحسنه دارای کارمزد شرعی و دیگر موازین قانونی طبق عقود مرابهه جهت خرید کالا
                و خدماتی که شرکت ندارد و لازم است به متقاضی تسهیلات نقدی ارائه دهد که خود تهیه
            </span>
            <span lang="FA" style="line-height: 150%;">
                نماید بدون ربا و بهره صرفاً با کارمزد حداقلی جهت پوشش دادن هزینه های اجرایی قرارداد مذکور با شرایط
                <br>
            </span>
            <span lang="FA" style="line-height: 150%;">قرارداد مذکور
                و توافقات ذیل حسب حمایت از اقشار تحت حمایت شرکت، که طی مراحل مندرج در اساسنامه به عضویت شرکت درآمده اند.
            </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 3 : مبلغ
                تسهیلات و &nbsp;شرایط پرداخت :<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-1&nbsp; : میزان مبلغ قرارداد : 230,000,000 ریال با احتساب
                کارمزد 12 % سالیانه به میزان 27,600,000 ریال &nbsp;جمع اصل
                و کارمزد تسهیلات به مبلغ 257,600,000 ریال می باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-2 : موضوع قرارداد
                شامل اصل مبلغ تسهیلات و کارمزد به میزان 12 %&nbsp;
                کل دیون می باشد و وام گیرنده به موجب این قرارداد ملزم و متعهد گردید که
                جمع اصل و کارمزد وام را که مبلغ 257,600,000 ریال میباشد به اقساط
                مساوی و متوالی طی {{ isset($item['installments_num']) ? $item['installments_num'] : '...' }}ماه از قرار
                هر قسط 21,466,667 ریال به شماره کارت
                5047061090044005&nbsp; نزد بانک شهر، شعبه جنت آباد
                شمالی در وجه شرکت جانبازان آینده ساز و از طریق درگاه الکترونیکی منظور شده و
                اعلام شده به وام گیرنده &nbsp;پرداخت نماید.
                به موجب این قرارداد ، وام گیرنده اجازه ارسال پیامک حاوی لینک درگاه الکترونیکی
                معتبر و دارای مجوز را به شرکت صادر نموده و جهت پرداخت اقساط مذکور و تمامی هزینه
                های مرتبط با قرارداد اعم از اختلاف حساب و سایر موارد اعم از هزینه های تشکیل
                پرونده و غیره را می بایست صرفاً از طریق لینک مذکور که ماهیانه به صورت پیامک
                ارسال میگردد با دقت نظر در تطبیق نام شرکت و درگاه الکترونیکی و صرفاً از سامانه
                پیامکی که نخستین بار پیامک ثبت نام و تشکیل پرونده و موفقیت در پرداخت ثبت نام را
                به وام گیرنده ابلاغ نموده است.<br>
            </span>
            <span lang="FA" style="line-height: 200%;">تبصره 1 : در
                صورتی که به واسطه نبود اینترنت ، نداشتن اعتبار تلفن ، یا قطع غیر مترقبه یا
            </span>
            <span lang="FA" style="line-height: 200%;">
                اختلال در ارسال پیامکهای مربوطه متقاضی ( وام گیرنده ) مکلف به واریز مبلغ اقساط یاد شده به شماره کارت ذکر
                شده در ماده 3-3
            </span>
            <span lang="FA" style="line-height: 200%;">
                در سررسید خود بدون اتلاف زمان و
                تطویل اوقات به بهانه قطع و اختلال در سیستم های الکترونیکی به صورت کارت به کارت
                یا واریز از حساب به کارت یا
            </span>
        </p>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md mb-2 text-center">
            <thead>
                <tr>
                    <th scope="col"> وام گیرنده</th>
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
    <div class="border-contract page-break p-6 mt-4" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 200%;">
                ارائه نقدی وجه اقساط به شرکت ( صرفاً صاحب امضاء یاد
            </span>
            <span lang="FA" style="line-height: 200%;">
                شده) پرداخت نموده و مکلف است رسید وجه پرداختی را نگهداری و
            </span>
            <span lang="FA" style="line-height: 150%;">
                تصویر یا رونوشت آن جهت درج در پرونده به حساب کاربری خود منعکس یا به شرکت تحویل نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">تبصره 2 :
                وام گیرنده حق واریز اقساط به حساب هیچ یک از افراد غیر از نماینده رسمی شرکت که
                در صدر ماده نخست از آن یاده شده ندارد ، ودر صورت واریز هرگونه وجه ، تقدیم نقدی
                آن و دیگر مواردی که به حساب رسمی یاد شده واریز نشده باشد آن قسط یا وجه دریافت
                نشده طلقی میگردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;"> 3-3 : در صورت تاخیر
                در پرداخت اقساط، میزان {{ isset($item['percent_fines']) ? $item['percent_fines'] : '...'}} % از
                باقیمانده ی اقساط به صورت مبلغ قسط ضرب در {{ isset($item['percent_fines']) ? $item['percent_fines'] :
                '...'}} % و ضرب
                در تعداد روزهای تاخیر شده می باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;"> 3-4 : اقساط باید هرماه
                و در سررسید تعین شده، به حساب شرکت واریز گردد و وام گیرنده موظف به نگهداری و ارائه
                ی اسناد پرداختی برای تسویه حساب و دریافت تضامین و مدارک خود میباشد و عذر عدم
                اطلاع از این حیث پذیرفته نیست در صورت تاخیر و منظور شدن جریمه هر گونه پرداخت
                وجه صرفاً از بدهی نامبرده کاسته و تا پرداخت کامل بدهی گذشته به جرایم وی افزوده
                میگردد و وام گیرنده حق هر گونه اعتراض را از خود سلب و ساقط نمود<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-5 : پرداخت از
                طریق فاکتور الکترونیکی در سایت لاله
            </span>
            <span style="line-height: 150%;"><a href="http://www.lalecard.ir/">
                    <span dir="LTR">(www.lalecard.ir)</span>
                </a>
            </span>
            <span dir="RTL"></span>
            <span dir="RTL"></span>
            <span lang="FA" style="line-height: 150%;">
                <span dir="RTL"></span>
                <span dir="RTL"></span>
                و یا پیامک ارسالی از طرف شرکت صورت می پذیرد وام گیرنده با اطلاع از سایت رسمی
                و درگاه اینترنتی به نام شرکت جانبازان آینده ساز و شماره کارت شرکت و یا نشانی
                محل شعب شرکت جهت پرداخت هزینه صرفاً از طریق دستگاه کارتخوان منصوبه عذر عدم
                دسترسی به راههای پرداختی را از خود سلب و ساقط نموده است&nbsp; .<br>
                <o:p></o:p>
            </span>

            <span lang="FA" style="line-height: 150%;">3-6 : وام گیرنده
            </span>
            <span lang="FA" style="line-height: 150%;">
                مبلغی به حساب شرکت به عنوان سپرده حسن انجام تعهد و همینطور جهت بهره برداری در راستای ارائه کالا و خدمات
                یا تولید واریز نموده به صورت قرض الحسنه بوده و در ازای آن سود یا بهره تعلق نگرفته و به همین میزان شرکت
                نیز دوبرابر مبلغ مذکور را به وام گیرنده وام داده و حداقل کارمزد را محاسبه نموده است ، مبلغ تودیع شده و
                به صورت قرض الحسنه در پایان اقساط قابل برداشت بوده یا پس از واریز 6 قسط متوالی امکان خرید کردن ( نه
                برداشت به صورت نقدی صرفاً خرید از کالا ها و خدمات شرکت) را خواهد داشت.
                <br>
            </span>
        </p>
        <table class="table table-contract table-bordered border-contract table-st<riped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col"> وام گیرنده</th>
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
    <div class="border-contract page-break p-6 mt-4" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;">
                3-8: اعضاء مبلغ وام ( وجه پرداختی به متقاضی) پس از گذشت 3 ماه بعد از تکمیل مدارک و تشکیل پرونده است.
                <br>
            </span>

            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 4 : مدت قرارداد :<br></span>
            <span lang="FA" style="line-height: 150%;">مدت قرار داد از
                تاریخ انعقاد قرارداد به مدت {{ isset($item['installments_num']) ? 15 : '...' }} ماه است که در تاریخ {{
                isset($item['end_date']) ? $item['end_date'] : '...' }}به شرط تسویه
                ی کامل دیون خاتمه می یابد انقضاء مدت ذکر شده رافع تعهدات وام گیرنده به پرداخت تمامی دیون اعم از اصل بدهی
                ، جرائم احتمالی متعلقه در زمان تاخیر و یا کارمزد و هر موضوعی که در قرارداد و منضمات آن به اشاره شده
                نمیباشد. <br>
            </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 5 : ضمانت و شرایط مربوط به آن :<br></span>
            @if($item['guarantee_type'] == 'check')
            @if(isset($item['contract_values']['check_type']) && $item['contract_values']['check_type'] == 'چک صیاد 2')
            <span lang="FA" style="line-height: 150%;">5-1 : وام گیرنده
                به منظور تضمین پرداخت اقساط خود، چک به مبلغ {{ isset($item['contract_values']['checkprice']) ?
                $item['contract_values']['checkprice'] : '...' }} ریال و شماره
                {{ isset($item['contract_values']['checknumber']) ? $item['contract_values']['checknumber'] : '...' }}
                نزد بانک {{ isset($item['contract_values']['checkbank']) ? $item['contract_values']['checkbank'] : '...'
                }} در تاریخ {{ $item['start_date'] }} و در وجه سیده فاطمه میرحسینی دهنه سری
                تحویل امور مالی شرکت نمود.<br>
            </span>
            @else
            <span lang="FA" style="line-height: 150%;">5-1 : وام گیرنده
                به منظور تضمین پرداخت اقساط خود، چک به مبلغ {{ isset($item['contract_values']['checkprice']) ?
                $item['contract_values']['checkprice'] : '...' }} ریال و شماره
                {{ isset($item['contract_values']['checknumber']) ? $item['contract_values']['checknumber'] : '...' }}
                نزد بانک {{ isset($item['contract_values']['checkbank']) ? $item['contract_values']['checkbank'] : '...'
                }} بدون درج تاریخ و در وجه شرکت جانبازان آینده
                ساز تحویل امور مالی شرکت نمود.<br>
            </span>
            @endif
            <span lang="FA" style="line-height: 150%;">مشخصات صاحب
                چک:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-2 : در صورت عدم
                پرداخت دو قسط متوالی و یا سه قسط متناوب توسط وام گیرنده، شرکت مخیر است تمامی دیون
                مربوطه را تبدیل به حال نموده و مطالبات خود را بابت بهای موضوع قرارداد و جرایم
                آن وام گیرنده/ضامن/ضامنین وی مطالبه نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : وام گیرنده/
                ضامن/ ضامنین متعهد شدند در صورت تاخیر در پرداخت اقساط، ضمن تسویه حساب کامل اصل بدهی،
                خسارت تأخیر را طبق قرارداد(جریمه دیرکرد مانده بدهی بصورت روزشمار {{ $item['percent_fines'] }} % ) به
                شرکت بپردازند.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-4 : اگر دیون وام
                گیرنده از سقف مبلغ چک بیشتر گردد، حق طرح دعوای مطالبه ی وجه برای شرکت وجود دارد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-5 : وام گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند اسناد ضمانتی خود را دریافت
                نماید.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            @elseif($item['guarantee_type'] == 'certificate')
            <span lang="FA" style="line-height: 150%;">5-1 : وام گیرنده به منظور تضمین پرداخت اقساط، گواهی کسر از حقوق
                با مهر و امضای معتبر به شماره {{ isset($item['contract_values']['certificate_num']) ?
                $item['contract_values']['certificate_num'] : '...' }}
                تاریخ {{ isset($item['contract_values']['certificate_date']) ?
                $item['contract_values']['certificate_date'] : '...' }} صادره از {{
                isset($item['contract_values']['certificate_location']) ?
                $item['contract_values']['certificate_location'] : '...'}} ، تسلیم امور مالی شرکت نمود. بدیهی است درصورت
                کشف خلاف و یا عدم ایفای تعهدات
                از سوی وام گیرنده، شرکت مخیر است اقدامات قانونی لازم را انجام دهد و از طریق اداره مربوطه اقدام به دریافت
                اقساط ماهیانه نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-2 : درصورت عدم همکاری
                اداره ی مربوطه و یا در صورت عدم وصول مطالبات به هر دلیل، شرکت حق و اختیار طرح دعوای
                حقوقی را خواهد داشت.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : وام گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند گواهی کسر از حقوق خود را دریافت
                نماید.<br>
            </span>
            @else
            <span lang="FA" style="line-height: 150%;">5-1 : وام گیرنده
                به منظور تضمین پرداخت اقساط خود، سفته به مبلغ {{ isset($item['contract_values']['promissorynoteprice'])
                ? $item['contract_values']['promissorynoteprice'] : '...' }} ریال و شماره
                {{ isset($item['contract_values']['promissorynotenumber']) ?
                $item['contract_values']['promissorynotenumber'] : '...' }} بدون درج تاریخ
                تحویل امور مالی شرکت نمود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">مشخصات صاحب
                سفته:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-2 : در صورت عدم
                پرداخت دو قسط متوالی و یا سه قسط متناوب توسط وام گیرنده، شرکت مخیر است تمامی دیون
                مربوطه را تبدیل به حال نموده و مطالبات خود را بابت بهای موضوع قرارداد و جرایم
                آن وام گیرنده/ضامن/ضامنین وی مطالبه نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : وام گیرنده/
                ضامن/ ضامنین متعهد شدند در صورت تاخیر در پرداخت اقساط، ضمن تسویه حساب کامل اصل بدهی،
                خسارت تأخیر را طبق قرارداد(جریمه دیرکرد مانده بدهی بصورت روزشمار {{ $item['percent_fines'] }} % ) به
                شرکت بپردازند.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-4 : اگر دیون خدمات
                گیرنده از سقف مبلغ سفته بیشتر گردد، حق طرح دعوای مطالبه ی وجه برای شرکت وجود دارد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-5 : وام گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند اسناد ضمانتی خود را دریافت
                نماید.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            @endif
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 6 : شرط داوری :<br></span>
            <span lang="FA" style="line-height: 150%;">6-1 : در صورت بروز
                هرگونه اختلاف بین طرفین اعم از اینکه مربوط به اجرای تعهدات موضوع قرارداد یا مربوط
                به تفسیر و تعبیر هر یک از مواد و پیوست های آن و یا تخلفی در خصوص تعهدات این قرارداد
            </span>
        </p>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col"> وام گیرنده</th>
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
    <div class="border-contract page-break p-6 mt-4" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;">در صورت بروز
                باشد و همچنین سایر اختلافات احتمالی از قبیل فسخ، بطلان، تعیین خسارات، وصول مطالبات
                و غیره به یک نفر داور مورد تعیین از طرف "واحد حقوقی شرکت" ارجاع می گردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">رای داور قطعی
                و لازم الاجرا است.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-2 : شرط داوری حاضر
                مستقل از این قرارداد بوده و حتی در فرض بطلان، فسخ یا اختتام آن نیز معتبر می باشد.<br></span>
            <span lang="FA" style="line-height: 150%;">6-3 : وفق بند 2 و
                4 ماده ی 469 از قانون آیین دادرسی مدنی، وام گیرنده به داوری "واحد حقوقی شرکت"
                اقرار نمود و امضای قرارداد حاضر توسط طرفین و واحد حقوقی شرکت، به منزله ی "قبولی
                هر 2 طرف" می باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-4 : ابلاغیه ها توسط
                اظهارنامه ی رسمی به طرفین ارسال می گردد و نیازی به ابلاغ توسط دادگاه و یا اقدام
                دیگری نمی باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-5 : ابتدای مدت داوری
                روزی است که موضوع به داوری ارجاع می شود و مدت آن 6ماه می باشد که قابل تمدید است.<br></span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 7 : سایر موارد :<br></span>
            <span lang="FA" style="line-height: 150%;">7-1 : وام گیرنده
                متعهد است نسبت به پرداخت هزینه ها و کسوراتی که به تسهیلات تعلق می گیرد از قبیل بیمه،
                مالیات، عوارض ارزش افزوده وغیره شخصاً و پس از اعلام شرکت اقدام نماید.<br></span>
            <span lang="FA" style="line-height: 150%;">7-2 : این قرارداد
                براساس اصل آزادی قراردادها و ماده 10 قانون مدنی، توافق طرفین و قوانین مصوب کشور
                جمهوری اسلامی ایران منعقد شده است که طرفین آن را پذیرفته و هرگونه ایراد و اعتراض
                نسبت به اجرای آن را از خود سلب و ساقط نمودند.<br></span>
            <span lang="FA" style="line-height: 150%;">7-3 : امضا کنندگان
                این قرارداد اقرار نمودند که جهت امضا و انعقاد آن، اطلاع کامل از مفاد قرارداد داشته
                و در کمال صحت عقلانی آن را منعقد کرده اند و ادعایی خلاف قرارداد پذیرفته نخواهد بود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">7-4 : وام گیرنده
                با علم و آگاهی کامل از این قرارداد، کلیه خیارات خصوصاً خیار غبن فاحش و افحش را از
                خود سلب و ساقط نمود.<br>
            </span>
        </p>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
            <thead>
                <tr>
                    <th scope="col"> وام گیرنده</th>
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
    <div class="border-contract page-break p-6 mt-4" style="text-align: justify;">
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 8 : اقامتگاه طرفین :<br></span>
            <span lang="FA" style="line-height: 150%;">به شرح مذکور در
                ماده 1 قرارداد حاضر می باشد و وام گیرنده و ضامن وی، موظف هستند حداکثر ظرف یک هفته
                پس از تغییر نشانی، یا تغییر تلفن ثابت یا همراه، نشانی و یا تلفن های جدید خود را
            </span>
            <span lang="FA" style="line-height: 150%;">به شرح مذکور به طرف مقابل اعلام نمایند، در غیر اینصورت کلیه اوراق
                و مکاتبات به نشانی مذکور در
                صدر این قرارداد ارسال خواهد شد و مرسل الیه نمی تواند به عذر عدم اطلاع معترض گردد<br></span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 9 : تعداد نسخ ، پیوست ها و ملحقات
                :<br></span>
            <span lang="FA" style="line-height: 150%;">
                این قرارداد در 9 ماده و 3 نسخه، مورخ تنظیم گردیده و هرکدام از موارد مذکور جزئی غیرقابل تجزیه بوده و در
                صورت اختلاف، صرفاً استناد به همه ی موارد ذکر شده نافذ می باشد.
            </span>
        </p>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md mb-5 text-center">
            <thead>
                <tr>
                    <th scope="col"> وام گیرنده</th>
                    <th scope="col">ضامن اول</th>
                    <th scope="col">ضامن دوم</th>
                    <th scope="col">مدیر امور مالی شرکت</th>
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
                </tr>
            </tbody>
        </table>
    </div>
</div>
