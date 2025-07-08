@extends('Admin.master')

@section('css')

@stop
@section('content')

    <div class="card position-relative">
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-12 centerBox mb-3"><br/><br/>
                    <h5>تخفیف های تعیین شده بر روی کالاهای انتخابی شما اعمال شد</h5>
                    <p>خلاصه ی وضعیت تخفیف ها و پروموشن ها و پوییش های شرکت داده شده به شرح زیر می باشد:</p>
                </div>
            </div>

            <div class="row centerBox mb-3">
                <div class="col-4 disInline padT40 borderLeft">
                    <a class="iconSmartDiscount mb-1"><i class="fa fa-percent" aria-hidden="true"></i></a><br/><br/>
                    <h5>{{$coupon_count}}</h5>
                    <h5>تنوع کالا با تخفیف نمایش داده می شوند</h5>
                    <p class="colorGray">این کالاها با قیمت خط خورده و با درصد تخفیف در وب سایت لاله نمایش داده می شوند.</p>
                </div>
                <div class="col-4 disInline padT40 borderLeft">
                    <a class="iconSmartDiscount mb-1"><i class="fa fa-bullhorn" aria-hidden="true"></i></a><br/><br/>
                    <h5>0</h5>
                    <h5>تنوع کالا به پروموشن فروش ویژه اضافه شدند.</h5>
                    <p class="colorGray">این کالاها علاوه بر نمایش به صورت تخفیف خورده در صورت تایید به لیست کالاهای فروش ویژه نیز اضافه
                        شدند.</p>
                </div>
                <div class="col-4 disInline padT40">
                    <a class="iconSmartDiscount mb-1"><i class="fa fa-star-o" aria-hidden="true"></i></a><br/><br/>
                    <h5>0</h5>
                    <h5>تنوع کالا به پوییش های دیجی کالا اضافه شدند.</h5>
                    <p class="colorGray">این کالاها علاوه بر نمایش به صورت تخفیف خورده در صورت تایید به پوییش های دیجی کالا نیز اضافه می
                        شوند.</p>
                </div>
            </div>

            <div class="row centerBox mb-3">
                <a class="btn btn-success marginAuto" href="/dashboard/periodicPrices">مشاهده تنوع ها با تخفیف هوشمند</a>
            </div>

        </div>

    </div>

@stop
@section('js')

@stop
