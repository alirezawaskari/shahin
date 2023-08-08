<div id="printableAreaContract" class="bg-white">
    <div class="border-contract pr-5 pb-4 mb-4">
        <div class="row">
            <div class="col-md-3 mt-4">
                <p> تاریخ : {{ $item[ 'start_date' ] }} </p>
                <p> شماره قرارداد : {{ $totalFactorItem->id }} </p>
            </div>
            <div class="col-md-6 mt-4 text-center">
                <h3> خدمات رفاهی لاله</h3>
                <h5>قرارداد مالی و اصلی بین خدمات گیرنده و شرکت</h5>
            </div>
            <div class="col-md-3 mt-3">
                <img src="{{ asset('assets/dashboard/images/logo.png') }}" width="150px">
            </div>
        </div>
    </div>
    <div class="border-contract page-break-first p-6">
        <p></p>
        <p>
        <ul></ul>
        <ul></ul>
        </p>
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;font-weight: 700;">&nbsp;ماده 1 : طرفین قرارداد :</span></br>
            <span lang="FA" style="line-height: 150%;">1-1 : شرکت جانبازان
                آینده ساز(سهامی خاص) به شماره ی ثبت شده 478000 به نشانی کرج ، 45 متری گلشهر ،
                پونه غربی ساختمان دارا ُ (جنب فروشگاه بهشتی ) طبقه 8 واحد ۱۵ با نام تجاری (شبکه خدمات رفاهی لاله با پروانه
                ی فعالیت شماره 521122 صادره از اتحادیه کسب و کارهای مجازی با موضوع فروش و عرضه
                کالا و خدمات )به نمایندگی آقای سیدمصطفی میرحسینی که به موجب اساسنامه و تصمیم مجمع عمومی صاحبان سهام
                به سمت مدیرعامل، رئیس هیئت مدیره و دارای حق امضا انتخاب شده و از این پس در این
                قرارداد" شرکت" نامیده می شود.
                <br>
            </span>

            <span lang="FA" style="line-height: 150%;">1-2 : {{ $user::getGenderTitle($user->sex) }} {{ $user->name.' '.$user->family.' ، ' }} فرزند: {{ $user->father }} با
                کدملی: {{ $user->melicode ? $user->melicode : '.......' }} به نشانی محل سکونت: {{ isset($user->address) ? $user->address : '.......' }} به
                نشانی محل کار: {{ isset($user->work_address) ? $user->work_address : '.......' }} شماره ی تلفن همراه {{ $user->phone }} شماره
                ی تلفن محل سکونت {{ isset($user->home_phone) ? $user->home_phone : '.......' }} شماره ی تلفن محل کار {{ isset($user->work_phone) ? $user->work_phone : '...' }} که
                از این پس در این قرارداد "خدمات گیرنده" نامیده می شوند.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">1-3 : ضامنِ خدمات
                گیرنده، {{ isset($item['contract_values']['gender']) ? $user::getGenderTitle($item['contract_values']['gender']) : 'خانم/آقای' }} {{ isset($item['contract_values']['guarantorname']) ? $item['contract_values']['guarantorname'] : '...' }} {{ isset($item['contract_values']['guarantorfamily']) ? $item['contract_values']['guarantorfamily'] : '...' }} فرزند: {{ isset($item['contract_values']['fathername']) ? $item['contract_values']['fathername'] : '...' }} با کد ملی {{ isset($item['contract_values']['guarantormelicode']) ? $item['contract_values']['guarantormelicode'] : '...' }} به
                نشانی محل سکونت {{ isset($item['contract_values']['addresshome']) ? $item['contract_values']['addresshome'] : '...' }} به نشانی محل کار {{ isset($item['contract_values']['addresswork']) ? $item['contract_values']['addresswork'] : '...' }} شماره
                ی تلفن همراه {{ isset($item['contract_values']['mobile']) ? $item['contract_values']['mobile'] : '...' }} شماره ی تلفن محل سکونت {{ isset($item['contract_values']['phonehome']) ? $item['contract_values']['phonehome'] : '...' }} شماره
                ی تلفن محل کار {{ isset($item['contract_values']['phonework']) ? $item['contract_values']['phonework'] : '...' }} نوع ضمانت {{ $totalFactorItem::GuaranteeTYPE[$item['guarantee_type']] }} که
                از این پس در این قرارداد ضامن نامیده می شوند.<br>
            </span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 2 : موضوع قرارداد :<br></span>
            <span lang="FA" style="line-height: 150%;">عبارت است از معرفی
                خدمات گیرنده برای دریافت خدمات {{ App\Models\totalFactorItem::setFactorItemable($totalFactorItem) }} از مراکز طرف قرارداد با شرکت که با
            </span>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md mb-3 text-center">
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
    <div class="border-contract page-break p-6 mt-4">
        <p></p>
        <p>
        <ul></ul>
        <ul></ul>
        </p>
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">
                دریافت تسهیلات مندرج در ماده 3 زیر و خدمات مالی از شرکت به صورت اقساطی از خدمات دریافتی مورد
                نیاز ( فاکتور میزان خدمات مورد نیاز با گواهی امضاء خدمات گیرنده و خدمات
                دهنده ضمیمه قرارداد است) بهره مند می گردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">این قرارداد
                دارای یک الحاقیه به شماره ی {{ $contract_number ? $contract_number : '......'}}
                راجع به تعهدات خدمات دهندگان می باشد که جزء لاینفک قرارداد است.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 3 : مبلغ
                قرارداد و &nbsp;شرایط پرداخت :<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-1&nbsp; : مبلغ قرارداد : {{ number_format($totalFactorItem->total_price - $item['prepayment']['price']) }} ریال
                با احتساب کارمزد 4 % سالیانه به میزان {{ isset($item['wage']) ? number_format($item['wage']): '...' }}ریال و مالیات به میزان {{ isset($item['wage']) ? number_format($item['wage'] * 0.09): '...' }} ریال&nbsp; &nbsp;که جمع اصل
                و کارمزد آن به مبلغ {{ isset($item['loan']) ? number_format($item['loan']) : '...' }} ریال می باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-2 : مبلغ مذکور بسته
                به افزایش یا کاهش میزان خدمات دریافتی و یا خارج شدن بخشی از خدمات از فاکتوربه علت
                &nbsp;نبود تجهیزات یا مواد یا خدمات دهنده خاص که
                امکان دارد در طی زمان اجرا به هر علت موجود نباشد، متغیر است ، &nbsp;لذا ملاک محاسبه مبلغ کامل دیون خدمات گیرنده،
                آخرین فاکتور وهزینه نهایی خدمات اجرا شده می باشد که بعنوان پیوست شماره 2 قرارداد ، قرار داده خواهد شد و مورد تائید و توافق طرفین است .<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-3 : موضوع قرارداد
                شامل اصل مبلغ تسهیلات و کارمزد به میزان 4 %&nbsp;
                کل دیون می باشد و خدمات گیرنده به موجب این قرارداد ملزم و متعهد گردید که
                جمع اصل و کارمزد خدمات اقساطی را که مبلغ {{ isset($item['loan']) ? number_format($item['loan']) : '...' }} ریال میباشد به اقساط
                مساوی و متوالی طی {{ isset($item['installments_num']) ? $item['installments_num'] : '...' }} ماه از قرار هر قسط {{ isset($item['installments_price']) ? number_format($item['installments_price']) : '...' }} ریال به شماره کارتی که از طریق درگاه الکترونیکی به خدمات گیرنده اعلام می شود ، &nbsp;پرداخت نماید.
                به موجب این قرارداد ، خدمات گیرنده اجازه ارسال پیامک حاوی لینک درگاه الکترونیکی
                معتبر و دارای مجوز را به شرکت صادر نموده و جهت پرداخت اقساط مذکور و تمامی هزینه
                های مرتبط با قرارداد اعم از اختلاف حساب و سایر موارد اعم از هزینه های تشکیل
                پرونده و غیره را میبایست صرفاً از طریق لینک مذکور که ماهیانه به صورت پیامک
                ارسال میگردد با دقت نظر در تطبیق نام شرکت و درگاه الکترونیکی و صرفاً از سامانه
                پیامکی که نخستین بار پیامک ثبت نام و تشکیل پرونده و موفقیت در پرداخت ثبت نام را
                به خدمات گیرنده ابلاغ نموده است پرداخت نمائید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">تبصره 1 : در
                صورتی که به واسطه نبود اینترنت ، نداشتن اعتبار تلفن ، یا قطع غیر مترقبه یا
            </span>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md mb-2 text-center">
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
    <div class="border-contract page-break p-6 mt-4">
        <p></p>
        <p>
        <ul></ul>
        <ul></ul>
        </p>
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">
                اختلال در ارسال پیامکهای مربوطه ایجاد گردد ، متقاضی ( خدمات گیرنده ) مکلف به واریز مبلغ
                اقساط یاد شده به شماره کارت اعلام شده از طریق درگاه الکترونیکی در سررسید خود بدون اتلاف زمان و
                تطویل اوقات به بهانه قطع و اختلال در سیستم های الکترونیکی به صورت کارت به کارت
                یا واریز از حساب به کارت یا ارائه نقدی وجه اقساط به شرکت ( صرفاً صاحب امضاء یاد
                شده)  بوده و مکلف است رسید وجه پرداختی را نگهداری و تصویر یا رونوشت آن را
                جهت درج در پرونده به حساب کاربری خود منعکس یا به شرکت تحویل نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">تبصره 2 :
                خدمات گیرنده حق واریز اقساط به هیچ حسابی غیر از آنچه که از طریق درگاه الکترونیکی اعلام می شود را ندارد و در صورت واریز هرگونه وجه ، تقدیم نقدی
                آن و دیگر مواردی که به حساب رسمی یاد شده واریز نشده باشد آن قسط یا وجه ، دریافت
                نشده طلقی میگردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;"> 3-4 : خدمات گیرنده / ضامن / ضامنین متعهد شدند در صورت تاخیر
                در پرداخت اقساط، میزان {{ isset($item['percent_fines']) ? $item['percent_fines'] : '...'}} % از باقیمانده ی اقساط به صورت مبلغ قسط ضرب در {{ isset($item['percent_fines']) ? $item['percent_fines'] : '...'}} % و ضرب
                در تعداد روزهای تاخیر شده را به شرکت بپردازند .<br>
            </span>
            <span lang="FA" style="line-height: 150%;"> 3-5 : خدمات گیرنده / ضامن / ضامنین متعهد شدند اقساط را هرماه
                و در سررسید تعیین شده، به حساب اعلام شده شرکت در درگاه الکترونیکی واریز نمایند و خدمات گیرنده موظف به نگهداری و ارائه
                ی اسناد پرداختی برای تسویه حساب و دریافت تضامین و مدارک خود میباشد و عذر عدم
                اطلاع از این حیث پذیرفته نیست در صورت تاخیر و منظور شدن جریمه ، هر گونه پرداخت
                وجه صرفاً از بدهی نامبرده کاسته و تا پرداخت کامل بدهی گذشته به جرایم وی افزوده
                میگردد و خدمات گیرنده حق هر گونه اعتراض را از خود سلب و ساقط نمود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">
            </span>
            <span lang="FA" style="line-height: 150%;">
                3-6 : پرداخت از
                طریق فاکتور الکترونیکی در سایت لاله (www.lalecard.ir)و یا پیامک ارسالی از طرف شرکت صورت می پذیرد. خدمات گیرنده با اطلاع از سایت رسمی
                و درگاه اینترنتی به نام شرکت جانبازان آینده ساز جهت پرداخت هزینه صرفاً از طریق دستگاه کارتخوان منصوبه عذر عدم
                دسترسی به راههای پرداختی را از خود سلب و ساقط نموده است&nbsp; .<br>
                <o:p></o:p>
            </span>
            <span lang="FA" style="line-height: 150%;">3-7 : در صورت عدم
                پرداخت دو قسط متوالی و یا سه قسط متناوب توسط خدمات گیرنده، شرکت مخیر است تمامی دیون
                موجل مربوطه را تبدیل به حال نموده و مطالبات خود را بابت بهای موضوع قرارداد و جرایم
                آن ازخدمات گیرنده/ضامن/ضامنین وی مطالبه نماید.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-8 : فاکتور منظور
            </span>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
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
    <div class="border-contract page-break p-6 mt-4">
        <p></p>
        <p>
        <ul></ul>
        <ul></ul>
        </p>
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">
                شده برای خدمات گیرنده با گواهی امضاء ذیل آن و پذیرش کامل مفاد الحاقیه تعهدات خدمات دهندگان و اطلاعیه های آن &nbsp;(پیوست شماره 1) و&nbsp; در صورت تغییر جزئیات اجرایی و یا نرخ ها و یا
                موارد دیگر (پیوست شماره 2 ) قرارداد و قرارداد بین خدمات دهنده و شرکت به عنوان
                (پیوست شماره 3) قرارداد و از اجزاء لاینفک قرارداد حاضر میباشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">3-9 : امکان
                تغییرهزینه های مندرج در فاکتور نهایی به علل متفاوت از قبیل تغییر در شیوه و نوع
                خدمات ومبالغ آن وجود دارد و خدمات گیرنده با آگاهی کامل نسبت به موضوع ، اقدام به امضاء قرارداد حاضر نموده است.</span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 4 : مدت قرارداد :<br></span>
            <span lang="FA" style="line-height: 150%;">مدت قرار داد از
                تاریخ انعقاد قرارداد به مدت {{ isset($item['installments_num']) ? $item['installments_num'] : '...' }} ماه است که در تاریخ {{ isset($item['end_date']) ? $item['end_date'] : '...' }}به شرط تسویه
                ی کامل دیون خاتمه می یابد انقضاء مدت ذکر شده رافع تعهدات خدمات گیرنده به پرداخت تمامی دیون اعم از اصل بدهی ، جرائم احتمالی متعلقه در زمان تاخیر و یا کارمزد و یا هر موضوعی که در قرارداد و منضمات به آن اشاره شده نمیباشد. <br>
            </span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 5 : ضمانت و شرایط مربوط به آن :<br></span>
            @if($item['guarantee_type'] == 'check')
            @if(isset($item['contract_values']['check_type']) && $item['contract_values']['check_type'] == 'چک صیاد 2')
            <span lang="FA" style="line-height: 150%;">5-1 : خدمات گیرنده
                به منظور تضمین پرداخت اقساط خود، یک برگ چک به مبلغ {{ isset($item['contract_values']['checkprice']) ?
                $item['contract_values']['checkprice'] : '...' }} ریال و شماره
                {{ isset($item['contract_values']['checknumber']) ? $item['contract_values']['checknumber'] : '...' }}
                نزد بانک {{ isset($item['contract_values']['checkbank']) ? $item['contract_values']['checkbank'] : '...'
                }} در تاریخ {{ isset($item['contract_values']['check_date']) ? $item['contract_values']['check_date'] :
                '...' }} و در وجه {{ isset($item['contract_values']['recipient_check']) ? $item['contract_values']['recipient_check'] :
                '...' }}
                تحویل امور مالی شرکت نمود.<br>
            </span>
            @else
            <span lang="FA" style="line-height: 150%;">5-1 : خدمات گیرنده
                به منظور تضمین پرداخت اقساط خود، یک برگ چک به مبلغ {{ isset($item['contract_values']['checkprice']) ? $item['contract_values']['checkprice'] : '...' }} ریال به شماره
                {{ isset($item['contract_values']['checknumber']) ? $item['contract_values']['checknumber'] : '...' }} نزد بانک {{ isset($item['contract_values']['checkbank']) ? $item['contract_values']['checkbank'] : '...' }} بدون درج تاریخ و در وجه شرکت جانبازان آینده
                ساز به امور مالی شرکت تحویل نمود.<br>
            </span>
            @endif

            <span lang="FA" style="line-height: 150%;">5-2 : اگر دیون خدمات
                گیرنده از سقف مبلغ چک بیشتر گردد، حق طرح دعوای مطالبه ی وجه برای شرکت وجود دارد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : خدمات گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند اسناد ضمانتی خود را دریافت نماید.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            @elseif($item['guarantee_type'] == 'certificate')
            <span lang="FA" style="line-height: 150%;">5-1 : خدمات گیرنده به منظور تضمین پرداخت اقساط، گواهی کسر از حقوق با مهر و امضای معتبر به شماره {{ isset($item['contract_values']['certificate_num']) ? $item['contract_values']['certificate_num'] : '...' }}
                تاریخ {{ isset($item['contract_values']['certificate_date']) ? $item['contract_values']['certificate_date'] : '...' }} صادره از {{ isset($item['contract_values']['certificate_location']) ? $item['contract_values']['certificate_location'] : '...'}} ، تسلیم امور مالی شرکت نمود. بدیهی است درصورت کشف خلاف و یا عدم ایفای تعهدات
                از سوی خدمات گیرنده، شرکت مخیر است اقدامات قانونی لازم را انجام دهد و از طریق اداره مربوطه اقدام به دریافت اقساط ماهیانه نماید.<br>
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
                به منظور تضمین پرداخت اقساط خود، یک برگ سفته به مبلغ {{ isset($item['contract_values']['promissorynoteprice']) ? $item['contract_values']['promissorynoteprice'] : '...' }} ریال به شماره
                {{ isset($item['contract_values']['promissorynotenumber']) ? $item['contract_values']['promissorynotenumber'] : '...' }} بدون درج تاریخ
                 به امور مالی شرکت تحویل نمود.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">مشخصات صاحب سفته(دراین قرارداد صاحب سفته شخص خدمات گیرنده است.):&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br> </span>
            <span lang="FA" style="line-height: 150%;">5-2 : اگر دیون خدمات
                گیرنده از سقف مبلغ سفته بیشتر گردد، حق طرح دعوای مطالبه ی وجه برای شرکت وجود دارد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">5-3 : خدمات گیرنده
                تنها پس از دریافت برگه ی تسویه حساب کامل میتواند اسناد ضمانتی خود را دریافت نماید.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<br>
            </span>
            @endif
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
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
    <div class="border-contract page-break p-6 mt-4">
        <p></p>
        <p>
        <ul></ul>
        <ul></ul>
        </p>
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 6 : داوری :<br></span>
            <span lang="FA" style="line-height: 150%;">6-1 : در صورت بروز
                هرگونه اختلاف بین طرفین اعم از اینکه مربوط به اجرای تعهدات موضوع قرارداد یا مربوط
                به تفسیر و تعبیر هر یک از مواد قرارداد و پیوست های آن و یا تخلفی در خصوص تعهدات این قرارداد باشد و
            </span>
            <span lang="FA" style="line-height: 150%;"> همچنین سایر احتمالات از قبیل فسخ، بطلان، تعیین خسارات، وصول مطالبات ، اختلافات مالی ، اختلافات خدماتی
                و غیره ، پرونده و موارد مربوط به داور که از طرف شرکت تعیین گردیده به "واحد حقوقی شرکت" ارجاع می گردد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">رای داور برای طرفین این قرارداد قطعی
                و لازم الاجرا است.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-2 : شرط داوری حاضر ،
                مستقل از این قرارداد بوده و حتی در فرض بطلان، فسخ یا اختتام آن نیز معتبر می باشد.<br></span>
            <span lang="FA" style="line-height: 150%;">6-3 : وفق بند 2 و
                4 ماده ی 469 از قانون آیین دادرسی مدنی، خدمات گیرنده به قبول داوری "واحد حقوقی شرکت"
                اقرار نمود و امضای قرارداد حاضر توسط طرفین مبنی بر قبول واحد حقوقی شرکت، بعنوان داور به منزله ی امضاء در حضور داور است  .<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-4 : ابلاغیه ها توسط
                اظهارنامه ی رسمی توسط داور برای طرفین ارسال می گردد و نیازی به ابلاغ توسط دادگاه و یا اقدام
                دیگری نمی باشد.<br>
            </span>
            <span lang="FA" style="line-height: 150%;">6-5 : ابتدای مدت داوری
                روزی است که موضوع به داوری ارجاع می شود و مدت آن 6ماه می باشد که قابل تمدید است.<br></span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 7 : سایر موارد :<br></span>
            <span lang="FA" style="line-height: 150%;">7-1 : خدمات گیرنده
                متعهد به پرداخت هزینه های تبعی و جنبی قرارداد از قبیل بیمه،
                مالیات، عوارض و مالیات بر ارزش افزوده وغیره می باشد که شخصاً پس از اعلام شرکت نسبت به پرداخت اقدام می نماید.<br></span>
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
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md text-center">
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
        </p>
    </div>
    <div class="border-contract page-break p-6 mt-4">
        <p></p>
        <p>
        <ul></ul>
        <ul></ul>
        </p>
        <p style="line-height:50px;font-size:18px;font-weight: 500;">
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 8 : اقامتگاه طرفین :<br></span>
            <span lang="FA" style="line-height: 150%;">به شرح مذکور در
                ماده 1 قرارداد حاضر می باشد و خدمات گیرنده و ضامن وی، موظف هستند حداکثر ظرف یک هفته
                پس از تغییر نشانی، یا تغییر تلفن ثابت یا تلفن همراه، نشانی و یا تلفن های جدید خود را
            </span>
            <span lang="FA" style="line-height: 150%;">    به طرف مقابل اعلام نمایند، در غیر اینصورت کلیه اوراق و مکاتبات به نشانی مذکور
                صدر این قرارداد ارسال خواهد شد و مرسل الیه نمی تواند به عذر عدم اطلاع معترض گردد.<br></span>
            <span lang="FA" style="line-height: 150%;">&nbsp;<br></span>
            <span lang="FA" style="line-height: 150%;font-weight: 700;">ماده 9 : تعداد نسخ، پیوست ها و ملحقات :<br></span>
            </span>
            <span lang="FA" style="line-height: 150%;">این قرارداد
                در 9 ماده و 4 نسخه، به همراه یک الحاقیه به شماره ی {{ $resnumber }} مورخ {{ $item['date_first_pay'] }} ،
                به همراه 2پیوست دیگر (فاکتوراولیه و نهایی-آموزش های مربوط به استفاده از خدمات )،
                تنظیم گردیده و هرکدام از موارد مذکور جزئی غیرقابل تجزیه بوده و در صورت اختلاف،
                صرفاً استناد به همه ی موارد ذکر شده نافذ می باشد.
            </span>
        </p>
        <table class="table table-contract table-bordered border-contract table-striped table-responsive-md mb-5 text-center">
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
