<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="google-site-verification" content="2LiK03QLZtoxWosDgNx_gXH9ffVfCJmTiMduAI643K8" />
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-133477056-1"></script>
    <link rel="stylesheet" href="{{asset('assets/site/css/app.css')}}?ver=6">
    <link rel="stylesheet" href="{{asset('assets/dashboard/css/persian-datepicker.min.css')}}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Toastr -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <!-- Toastr -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <!-- Styles -->
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
        }

        .hero-image {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url("images/photographer.jpg");
            height: 80%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
        }

        .hero-text {
            text-align: center;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
        }

        .hero-text button {
            border: none;
            outline: 0;
            display: inline-block;
            padding: 10px 25px;
            color: black;
            background-color: #ddd;
            text-align: center;
            cursor: pointer;
        }

        .hero-text button:hover {
            background-color: #555;
            color: white;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        h1 {
            font-weight: normal;
        }

        li {
            display: inline-block;
            list-style-type: none;
            padding: 2px 1em;
            text-transform: uppercase;
            border: 3px solid wheat;
            font-size: 100%;
            color: #0D0A0A;
            margin-top: 40px;
        }

        li span {
            display: block;
            color: wheat;
            font-family: cursive;
            font-weight: 700;
        }
        .container {
            color: #333;
            margin: 0 auto;
            text-align: center;
        }
    </style>
</head>

<body>
<div class="col-12 hero-image">
    <div class="col-12 hero-text mt-5">
        <h1 style="font-size:50px;font-weight: 600;">حدیثه و بهروز</h1>
        <h4>پنجم فروردین منتظرتان هستیم</h4>
        <div class="col-12 container">
            <ul class="col-12">
                <li><span id="days"></span>روز</li>
                <li><span id="hours"></span>ساعت</li>
                <li><span id="minutes"></span>دقیقه</li>
                <li><span id="seconds"></span>ثانیه</li>
            </ul>
            <h3 style="color: white; font-weight: 600;margin-top: 10px;margin-bottom: 10px;">مانده تا شروع مراسم</h3>
        </div>
    </div>
</div>
<section>
    <h1 style="font-size:30px;font-weight: 600;color: #3d4852;" class="text-center mt-4"><img src="images/women.png" width="10%">آقای رضا مقامی با خانواده<img src="images/men.png" width="10%"></h1>
    <div class="row mt-3">
        <div class="col-6">
            <p style="text-align: justify;padding-right: 20px;">از دورترین فاصله ها به هم رسیدیم و تا اوج بودن با همیم
                بهای عشق چیست بجز عشق
                به هم رسیدن یعنی آغاز
                با هم ماندن یعنی زندگی
                زندگی با عشق یعنی کامیابی

                پایان هر رفتن رسیدن است

                و ما اکنون به نقطه‌اي رسیده‌ایم که آغاز یک رفتن است

                دراین آغاز همسفر ما باشید</p>
        </div>
        <div class="col-6">
            <img src="images/weeding.jpg" width="100%" style="border: 3px dashed;border-radius: 5px;padding-right: 20px;">
            <h1 style="font-size:100%;font-weight: 600;color: #3d4852;" class="text-center mt-4">حدیثه صفری و بهروز مقامی</h1>
        </div>
    </div>
</section>
<div class="col-12 hero-image" style="height: 50%">
    <div class="col-12 hero-text mt-5">
        <p style="font-size: 20px;">دست در دست هم نهاده‌ایم و سفره دوستی را در

            دشت آرزوها گسترانده‌ایم تا هر خاطره‌ای را به

            زیبایی در دلها بیاوریم

            كه زندگی تنها یادگاری از محبت است

            شوق فشردن دستان شما برایمان انتظاری است شیرین

        </p>
    </div>
</div>
<section>
    <h1 class="text-center">موقعیت تالار</h1>
    <div class="social-section col-12" style=" width: 99%;">
        <iframe width="100%" height="250" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.openstreetmap.org/export/embed.html?bbox=32.56274386269267%2C50.3699569702148438%2C50.246360778808594%2C32.61980121473449&amp;layer=mapnik&amp;marker=32.56274386269267%2C50.3699569702148438"></iframe><br/><small>
            <a href="https://www.openstreetmap.org/?mlat=32.5627&amp;mlon=50.3700#map=12/53.5626/-2.3698"></a></small>
    </div>
    <div class="col-12">
        <h3>تالار باربد</h3>
        <p>آدرس تالار : کرج کمالشهر</p>
    </div>
</section>
</body>
<script>
    const second = 1000,
        minute = second * 60,
        hour = minute * 60,
        day = hour * 24;

    let countDown = new Date('Mar 24, 2020 00:00:00').getTime(),
        x = setInterval(function() {

            let now = new Date().getTime(),
                distance = countDown - now;

            document.getElementById('days').innerText = Math.floor(distance / (day)),
                document.getElementById('hours').innerText = Math.floor((distance % (day)) / (hour)),
                document.getElementById('minutes').innerText = Math.floor((distance % (hour)) / (minute)),
                document.getElementById('seconds').innerText = Math.floor((distance % (minute)) / second);

        }, second)
</script>
</html>
