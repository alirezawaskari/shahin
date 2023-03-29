@extends('Admin.master')
@section('title')گزارشات مالی@endsection

@section('content')
    <script src="../../assets/script/canvasjs.min.js"></script>
    <div class="row page-header">
        @if(auth()->user()->isAbleTo('view-sales-revenue'))
        <div class="col-lg-3 col-md-12">
            <div class="seller-box card overflow-hidden">
                <div class="card-body text-center">
                    <div class="m-b-10">
                        <p> تعهد فروش شما : 130</p>
                        <p> تعداد فروش انجام شده : {{ $totalMonthFactorPaidCount }}</p>
                        <p> درصد موفقیت از فروش : {{ round(($totalMonthFactorPaidCount/130)*100).' %' }}</p>
                    </div>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-12">
            <div class="request-box card overflow-hidden">
                <div class="card-body text-center">
                    <div class="m-b-10">
                        <p> تعداد کل تقاضا : {{ $totalMonthFactorCount }}</p>
                        <p> تعداد فروش انجام شده : {{ $totalMonthFactorPaidCount }}</p>
                        <p> تعداد فروش انجام نشده : {{ $totalMonthFactorCount - $totalMonthFactorPaidCount }}</p>
                    </div>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        @else
        <div class="col-lg-3 col-md-12">
            <div class="user-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">اطلاعات کاربری</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <span>{{ auth()->user()->name . ' ' .auth()->user()->family }} {{ auth()->user()->roles->first()->display_name }} عزیز به شاهین خوش آمدید  </span>
                    </div>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        @if(auth()->user()->admin_organization)
        <div class="col-lg-3 col-md-12">
            <div class="link-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">اعتبار باقی مانده سازمان</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10 font-size-18">
                        <p>{{ number_format(auth()->user()->admin_organization->credit).'(ریال)' }}</p>
                    </div>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        @else
        <div class="col-lg-3 col-md-12">
            <div class="link-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">تعداد کاربران کل شاهین</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10 font-size-22">
                        <p>{{ number_format($totalUser) }}</p>
                    </div>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        @endif
        @endif
        <div class="col-lg-3 col-md-12">
            <div class="data-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">لینک دعوت شما</h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                       <input class="form-control" id="link" value="{{ $link }}" name="link">
                    </div>
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <select class="form-control" name="service" id="service" onchange="linkUser()">
                            <option value="all">همه</option>
                          @foreach($services as $service)
                            <option value="{{ $service->slug }}">{{ $service->title }}</option>
                          @endforeach
                       </select>
                       <button onclick="myFunction()" class="text-center btn btn-dark">کپی</button>
                    </div>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        <div class="col-lg-3 col-md-12">
            <div class="calendar-box card overflow-hidden">
                <div class="card-body text-center">
                    <h5 class="title-box">امروز {{ verta(now())->formatWord('l') }}<i class="fas fa-calculator"></i></h5>
                    <div class="d-flex align-items-center justify-content-center m-b-10 font-size-18">
                        <p>{{ verta(now())->format('%d %B ، %Y - H:i:s')}}</p>
                    </div>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
    </div>
    @permission('view-sales-revenue|view-sales-revenue-detail')
    <div class="row">
        <div class="col-lg-4 col-md-12">
            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalDayFactorPrice) }}</h2>
                    </div>
                    <p>درآمد روزجاری (ریال-نقدی)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalDayFactorPriceOrgan) }}</h2>
                    </div>
                    <p>درآمد روزجاری (ریال-اعتبار)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalYesterdayFactorPrice) }}</h2>
                    </div>
                    <p>درآمد روزقبل (ریال)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalWeekFactorPrice) }}</h2>
                    </div>
                    <p>درآمد هفته جاری (ریال)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>

        </div>
        <div class="col-lg-4 col-md-12">

            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalMonthFactorPrice) }}</h2>
                    </div>
                    <p>درآمد ماه جاری (ریال)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>

        </div>
        <div class="col-lg-4 col-md-12">

            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalBeforeMonthFactorPrice) }}</h2>
                    </div>
                    <p>درآمد ماه قبل (ریال)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>

        </div>
        <div class="col-lg-4 col-md-12">

            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalSixMonthFactorPrice) }}</h2>
                    </div>
                    <p>درآمد6 ماه جاری (ریال)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>

        </div>
        <div class="col-lg-4 col-md-12">

            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalYearFactorPrice) }}</h2>
                    </div>
                    <p>درآمد سال جاری (ریال)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>

        </div>
        <div class="col-lg-4 col-md-12">

            <div class="card overflow-hidden">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-server"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalAllFactorPrice) }}</h2>
                    </div>
                    <p>درآمد کل (ریال)</p>
                </div>
                <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
            </div>

        </div>
        <div class="col-lg-4 col-sm-12">
            <div class="card overflow-hidden card-primary">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-primary icon-block-floating m-l-20">
                            <i class="ti-user"></i>
                        </div>
                        <h2 class="m-b-0 text-primary font-weight-100 primary-font">{{ number_format($totalUser ) }}</h2>
                    </div>
                    <p>کل کاربران</p>
                </div>
                <div class="sparkline-demo1" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">

            <div class="card overflow-hidden card-danger">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-danger icon-block-floating m-l-20">
                            <i class="ti-user"></i>
                        </div>
                        <h2 class="m-b-0 text-danger font-weight-800 primary-font">{{ number_format($totalDayUser) }}</h2>
                    </div>
                    <p>کاربر جدید روز جاری</p>
                </div>
                <div class="sparkline-demo2" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12">
            <div class="card overflow-hidden card-warning">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center m-b-10">
                        <div class="icon-block icon-block-outline-warning icon-block-floating m-l-20">
                            <i class="ti-user"></i>
                        </div>
                        <h2 class="m-b-0 text-warning font-weight-800 primary-font">{{ number_format($totalMonthUser) }}</h2>
                    </div>
                    <p>کاربر جدید ماه جاری</p>
                </div>
                <div class="sparkline-demo3" style="margin: 0 -2px -3px -2px"></div>
            </div>
        </div>
		<div class="col-lg-12 col-md-12">
            <div class="card" style="padding: 20px;">
                <div id="chartContainer" style="height: 300px; width: 100%;direction: initial;"></div>
            </div>
        </div>
    </div>
    <chart :label_month="{{ json_encode($label_month) }}" :label_week="{{ json_encode($label_week) }}" :factor_month="{{ json_encode($factor_month) }}" :factor_week="{{ json_encode($factor_week) }}" :user_month="{{ json_encode($user_month) }}" :user_week="{{ json_encode($user_week) }}"></chart>
    @endpermission
@stop
@section('js')
    <script>
        function myFunction() {
            var copyText = document.getElementById("link");
            copyText.select();
            copyText.setSelectionRange(0, 99999)
            document.execCommand("copy");
        }
        function linkUser() {
        service = document.getElementById("service").value;
        $.ajax({
            type: "get",
            url: '/dashboard/set-link/'+service,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 800000,
            success: function(result) {
                document.getElementById("link").value = result["link"];
                toastr.success(result["message"]);
            },
            error: function(e) {
            }
        });
        }
        window.onload = function () {

            var chart = new CanvasJS.Chart("chartContainer", {
                exportEnabled: true,
                animationEnabled: true,
                title:{
                    text: "گزارش کل خدمات"
                },
                legend:{
                    cursor: "pointer",
                    itemclick: explodePie
                },
                data: [{
                    type: "pie",
                    showInLegend: true,
                    toolTipContent: "{name}: <strong>{y}</strong>",
                    indexLabel: "{y} - {name}",
                    dataPoints: [
                            @for($i = 0 ; $i< count($service_name) ; $i++)
                        { y: '{{ $service_count[$i] }}', name: '{{ $service_name[$i] }}'},
                        @endfor
                    ]
                }]
            });
            chart.render();
        }
        function explodePie (e) {
            if(typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
                e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
            } else {
                e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
            }
            e.chart.render();

        }
    </script>
@endsection

