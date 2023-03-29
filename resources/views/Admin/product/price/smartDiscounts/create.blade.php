@extends('Admin.master')

@section('css')

@stop
@section('content')

    <div class="page-header">
        <div>
            <h3> تخفیف های هوشمند </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                    <li class="breadcrumb-item active" aria-current="page">
                        <span> تخفیف های هوشمند | در این قسمت می توانید برای کالاهای خود تخفیف تعیین کنید . </span>
                    </li>
                </ol>
            </nav>
        </div>

    </div>

    <div class="card position-relative">
        <div class="card-body">
            <h4>راهنمای تخفیف های هوشمند</h4>
            <p>شما می توانید برای هر یک از کالاهای موجود خود به میزان دلخواه تخفیف تعیین کنید.</p>
            <p>کالاهای انتخابی بر مبنای کیفیت تخفیفی که برای آن تعیین کرده اید به صورت هوشمند به پروموشن ها / کمپین های
                لاله اضافه می شوند.</p>
            <p>کالاها پس از بررسی و اضافه شدن به پروموشن ها و کمپین های مختلف به یکی از شکل های زیر در سایت نمایش داده
                می شوند:</p>
            <div class="sameRow mb-4">
                <div class="column">
                    <h4>تخفیف عادی</h4>
                    <p>نمایش قیمت بصورت خط خورده با درصد تخفیف</p>
                </div>
                <div class="column">
                    <h4>پروموشن فروش ویژه </h4>
                    <p>نمایش قیمت بصورت خط خورده با درصد تخفیف</p>
                    <p>امکان قرارگیری پشت بنرهای لاله</p>
                </div>
                <div class="column">
                    <h4>تخفیف عادی</h4>
                    <p>نمایش قیمت بصورت خط خورده با درصد تخفیف</p>
                    <p>امکان قرارگیری پشت بنرهای لاله و صفحات پروموشن</p>
                    <p>احتمال قرارگیری کالا در شبکه های اجتماعی لاله</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-md-12 mb-5">
                    <h4 class="text-center">انتخاب کالاها از پنل</h4>
                    <p class="text-center">کالا ها را به صورت دستی از میان کالاهای مجاز انتخاب و قیمت گذاری کنید.</p>
                    <form method="post" action="{{route('periodicPrices.store')}}">
                        {{csrf_field()}}
                        <div class="col-12 col-md-12 mb-3 centerProductLists">
                            @if (count($product_attributes) > 0)
                                <section class="productAttribute">
                                    @include('Admin.product.price.smartDiscounts.productsAttributeList')
                                </section>
                            @endif
                        </div>
                        <button type="submit" class="btn btn-success">افزودن به لیست تخفیف ها</button>
                    </form>
                </div>
            </div>

        </div>

    </div>

@stop
@section('js')

    <script type="text/javascript">

        $(function () {
            $('body').on('click', '.pagination a', function (e) {
                e.preventDefault();

                $('#load a').css('color', '#dfecf6');
                $('#load').append('<img style="position: absolute; left: 0; top: 0; z-index: 100000;" src="/upload/demo_wait.gif" />');

                var url = $(this).attr('href');
                getArticles(url);
                window.history.pushState("", "", url);
            });

            function getArticles(url) {
                $.ajax({
                    url: url
                }).done(function (data) {
                    $('.productAttribute').html(data);
                });
            }
        });

    </script>

@stop
