<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted"         => ":attribute باید پذیرفته شده باشد.",
    "active_url"       => "آدرس :attribute معتبر نیست",
    "after"            => ":attribute باید تاریخی بعد از :date باشد.",
    "alpha"            => ":attribute باید شامل حروف الفبا باشد.",
    "string"            => ":attribute باید شامل حروف الفبا باشد.",
    "alpha_dash"       => ":attribute باید شامل حروف الفبا و عدد و خظ تیره(-) باشد.",
    "alpha_num"        => ":attribute باید شامل حروف الفبا و عدد باشد.",
    "array"            => ":attribute باید شامل آرایه باشد.",
    "before"           => ":attribute باید تاریخی قبل از :date باشد.",
    "between"          => array(
        "numeric" => ":attribute باید بین :min و :max باشد.",
        "file"    => ":attribute باید بین :min و :max کیلوبایت باشد.",
        "string"  => ":attribute باید بین :min و :max کاراکتر باشد.",
        "array"   => ":attribute باید بین :min و :max آیتم باشد.",
    ),
    "boolean"          => "The :attribute field must be true or false",
    "confirmed"        => ":attribute با تاییدیه مطابقت ندارد.",
    "date"             => ":attribute یک تاریخ معتبر نیست.",
    "date_format"      => ":attribute با الگوی :format مطاقبت ندارد.",
    "different"        => ":attribute و :other باید متفاوت باشند.",
    "digits"           => ":attribute باید :digits رقم باشد.",
    "digits_between"   => ":attribute باید بین :min و :max رقم باشد.",
    "email"            => "فرمت :attribute معتبر نیست.",
    "exists"           => ":attribute انتخاب شده، معتبر نیست.",
    "image"            => ":attribute باید تصویر باشد.",
    "in"               => ":attribute انتخاب شده، معتبر نیست.",
    "integer"          => ":attribute باید نوع داده ای عددی (integer) باشد.",
    "ip"               => ":attribute باید IP آدرس معتبر باشد.",
    "max"              => array(
        "numeric" => ":attribute نباید بزرگتر از :max باشد.",
        "file"    => ":attribute نباید بزرگتر از :max کیلوبایت باشد.",
        "string"  => ":attribute نباید بیشتر از :max کاراکتر باشد.",
        "array"   => ":attribute نباید بیشتر از :max آیتم باشد.",
    ),
    "mimes"            => ":attribute باید یکی از فرمت های :values باشد.",
    "min"              => array(
        "numeric" => ":attribute نباید کوچکتر از :min باشد.",
        "file"    => ":attribute نباید کوچکتر از :min کیلوبایت باشد.",
        "string"  => ":attribute نباید کمتر از :min کاراکتر باشد.",
        "array"   => ":attribute نباید کمتر از :min آیتم باشد.",
    ),
    "not_in"           => ":attribute انتخاب شده، معتبر نیست.",
    "numeric"          => ":attribute باید شامل عدد باشد.",
    "regex"            => ":attribute یک فرمت معتبر نیست",
    "required"         => "فیلد :attribute الزامی است",
    "required_if"      => "فیلد :attribute هنگامی که :other برابر با :value است، الزامیست.",
    "required_with"    => ":attribute الزامی است زمانی که :values موجود است.",
    "required_with_all"=> ":attribute الزامی است زمانی که :values موجود است.",
    "required_without" => ":attribute الزامی است زمانی که :values موجود نیست.",
    "required_without_all" => ":attribute الزامی است زمانی که :values موجود نیست.",
    "same"             => ":attribute و :other باید مانند هم باشند.",
    "size"             => array(
        "numeric" => ":attribute باید برابر با :size باشد.",
        "file"    => ":attribute باید برابر با :size کیلوبایت باشد.",
        "string"  => ":attribute باید برابر با :size کاراکتر باشد.",
        "array"   => ":attribute باسد شامل :size آیتم باشد.",
    ),
    "timezone"         => "The :attribute must be a valid zone.",
    "unique"           => "شما قبلا با این :attribute ثبت نام شده اید با ورود به سایت خدمات مورد نظر را انتخاب کنید",
    "url"              => "فرمت آدرس :attribute اشتباه است.",
    "exists_code"      => "کد ارسالی در سیستم وجود ندارد",
    "expire_code"      => "اعتبار کد ارسالی به پایان رسیده است",
    "used"             => "این کد قبلا مورد استفاده قرار گرفته است",
    "exists_phone"     => "چنین شماره ای در سیستم ثبت نشده است",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => array(),

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'attributes' => array(
        "name" => "نام",
        "username" => "نام کاربری",
        "email" => "ایمیل",
        "first_name" => "نام",
        "last_name" => "نام خانوادگی",
        "password" => "رمز عبور",
        "password_confirmation" => "تاییدیه ی رمز عبور",
        "city" => "شهر",
        "country" => "کشور",
        "address" => "نشانی",
        "phone" => "شماره موبایل",
        "mobile" => "تلفن همراه",
        "age" => "سن",
        "sex" => "جنسیت",
        "gender" => "جنسیت",
        "day" => "روز",
        "month" => "ماه",
        "year" => "سال",
        "hour" => "ساعت",
        "minute" => "دقیقه",
        "second" => "ثانیه",
        "title" => "عنوان",
        "text" => "متن",
        "content" => "محتوا",
        "description" => "توضیحات",
        "excerpt" => "گلچین کردن",
        "date" => "تاریخ",
        "time" => "زمان",
        "available" => "موجود",
        "size" => "اندازه",
        "body" => "متن",
        "imageUrl" => "تصویر",
        "videoUrl" => "آدرس ویدیو",
        "slug" => "نامک",
        "tags" => "تگ ها",
        "category" => "دسته",
        "story" => "داستان",
        'number' => 'شماره قسمت',
        'price' => 'قیمت',
        'course_id' => 'دوره مورد نظر',
        'fileUrl' => 'آدرس فایل',
        'enSlug' => 'نامک انگلیسی',
        'percent' => 'درصد',
        'images' => 'تصویر',
        'melicode' => 'کد ملی',
        'family' => 'نام خانوادگی',
        'company_name' => 'نام شرکت',
        'reagent' => 'نام معرف',
        'birth' => 'تاریخ تولد',
        'melicode' => 'کد ملی',
        'postalcode' => 'کد پستی',
        'shenasnamecode' => 'شماره شناسنامه',
        'call' => 'تلفن ثابت',
        'night' => 'شب های اقامت',
        'internet' => 'سرویس اینترنت',
        'code' => 'کد تخفیف',
        'task_date' => 'تاریخ',
        'meli_code' => 'کد ملی',
        'expire' => 'اعتبار',
        'service' => 'خدمات',
        'reason' => 'دلیل',
        'callcenter' => 'کالسنتر',
        'province' => 'استان',
        'user' => 'کاربر',
        'startDate' => 'تاریخ شروع',
        'endDate' => 'تاریخ پایان',
        'startUser' => 'کالسنتر مبدا',
        'endUser' => 'کالسنتر مقصد',
        'count' => 'تعداد',
        'id' => 'انتخاب از جدول',
        'type' => 'روش',
        'default' => 'پیش فرض',
        'storage_num' => 'شماره محل نگهداری',
        'row_num' => 'شماره سطر',
        'col_num' => 'شماره ستون',
        'purchase_price' => 'قیمت خرید',
		'stock' => 'موجودی',
        'card_id' => 'واحد مربوطه',
        'resume' => 'فایل رزومه',
        'agent_id' => 'انتخاب پزشک',
        'cat_type' => 'نوع دسته بندی همکاری',
        'product_category' => 'دسته بندی محصول مربوطه',
        'service_category' => 'دسته بندی خدمت مربوطه',
        'category' => 'فروش کالا',
        'service' => 'ارائه خدمات',
        'pay_to' => 'در وجه',
        'check_front_image' => 'تصویر روی چک',
        'check_back_image' => 'تصویر پشت چک',
        'promissory_front_image' => 'تصویر روی سفته',
        'promissory_back_image' => 'تصویر پشت سفته',
        'exporter' => 'صادر کننده',
        'amount' => 'مبلغ',
        'receiver_phone'=>'شماره موبایل دریافت کننده',
        'receiver_name'=>'نام دریافت کننده',
        'receiver_family'=>'نام خانوادگی دریافت کننده',
        'receiver_id'=>'شناسه دریافت کننده',
        'exporter_city_id'=>'شهر صادر کننده',
        'exporter_phone'=>'شماره موبایل  صادر کننده',
        'exporter_name'=>'نام  صادر کننده',
        'exporter_family'=>'نام خانوادگی  صادر کننده',
        'exporter_id'=>'شناسه  صادر کننده',
        'exporter_province_id'=>'استان  صادر کننده',
        'pay_to_city_id'=>'شهر گیرنده ',
        'pay_to_phone'=>'شماره موبایل  گیرنده ',
        'pay_to_name'=>'نام  گیرنده ',
        'pay_to_family'=>'نام خانوادگی  گیرنده ',
        'pay_to_id'=>'شناسه  گیرنده ',
        'pay_to_province_id'=>'استان  گیرنده ',
        'category_id'=>'دسته بندی',
        'end_representation' => 'نمایندگی مقصد',
    ),
);
