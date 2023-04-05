@extends('Admin.master')
@section('title')کارتابل روزانه@endsection

@section('content')

<div class="page-header">
    <div>
        <h3>کارتابل روزانه</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">داشبورد</a></li>
                <li class="breadcrumb-item active" aria-current="page">کارتابل روزانه</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row col-12">
    @role('warehouse-keeper')
    <div class="col-12 col-lg-4 col-md-4">
        <div class="card overflow-hidden {{ $item['count_product_storeRoom_expiring'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد محصول درحال انقضا : {{$item['count_product_storeRoom_expiring']}}</h5>
                    <a href="/dashboard/storeRoom/list-product-expiring" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-4">
        <div class="card overflow-hidden {{ $item['count_product_storeRoom_expiring'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد درخواست ها خروج از انبار : {{$item['count_request']}}</h5>
                    <a href="/dashboard/request/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    @role('filing')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_filing'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تایید : {{$item['count_factoritem_filing']}}</h5>
                    <a href="/dashboard/totalFactorItem/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    @role('legal')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_mali_legal'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت اقدام ضمانت : {{$item['count_factoritem_mali_legal']}}</h5>
                    <a href="/dashboard/totalFactorItem/reports/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    @role('consulting_contract_affairs')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_consulting_contract_affairs'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تایید : {{$item['count_factoritem_consulting_contract_affairs']}}</h5>
                    <a href="/dashboard/totalFactorItem/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_mali_consulting_contract_affairs'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت اقدام ضمانت : {{$item['count_factoritem_mali_consulting_contract_affairs']}}</h5>
                    <a href="/dashboard/totalFactorItem/reports/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    @role('mali')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_mali'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تایید : {{$item['count_factoritem_mali']}}</h5>
                    <a href="/dashboard/totalFactorItem/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_mali_mali'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تسویه حساب : {{$item['count_factoritem_mali_mali']}}</h5>
                    <a href="/dashboard/totalFactorItem/reports/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factor_mali'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد فاکتور ها خرید جدید : {{$item['count_factor_mali']}}</h5>
                    <a href="{{ route('purchaseInvoice.desktop') }}" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_tickets'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد تیکت جدید جهت بررسی : {{ $item['count_tickets'] }}</h5>
                    <a href="{{ route('desktop.tickets') }}" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @role('superadmin')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_superadmin'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تایید : {{$item['count_factoritem_superadmin']}}</h5>
                    <a href="/dashboard/totalFactorItem/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_mali_superadmin'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تسویه حساب : {{$item['count_factoritem_mali_superadmin']}}</h5>
                    <a href="/dashboard/totalFactorItem/reports/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factor_superadmin'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد فاکتور ها خرید جدید : {{$item['count_factor_superadmin']}}</h5>
                    <a href="{{ route('purchaseInvoice.desktop') }}" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    @role('mali-representation')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_mali_representation'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تایید : {{$item['count_factoritem_mali_representation']}}</h5>
                    <a href="/dashboard/totalFactorItem/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factor_mali_representation'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد فاکتور ها خرید جدید : {{$item['count_factor_mali_representation']}}</h5>
                    <a href="{{ route('purchaseInvoice.desktop') }}" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    @if(auth()->user()->supplier)
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factor_supplier'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد درخواست ها فروش جدید : {{$item['count_factor_supplier']}}</h5>
                    <a href="{{ route('purchaseInvoice.desktop') }}" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endif
    @role('superadmin-representation')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_superadmin_representation'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها جدید جهت تایید : {{$item['count_factoritem_superadmin_representation']}}</h5>
                    <a href="/dashboard/totalFactorItem/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factor_superadmin_representation'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد فاکتور ها خرید جدید : {{$item['count_factor_superadmin_representation']}}</h5>
                    <a href="{{ route('purchaseInvoice.desktop') }}" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endrole
    @if(auth()->user()->center)
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_factoritem_confirm'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پرونده ها آماده اجرا : {{$item['count_factoritem_confirm']}}</h5>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endif
    @permission('contact|contact-detail')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_contact'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد پیام ها جدید جهت پاسخ : {{$item['count_contact']}}</h5>
                    <a href="{{ route('contact.desktop') }}" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endpermission
    @permission('recruitment_notice')
    <div class="col-12 col-lg-4 col-md-12">
        <div class="card overflow-hidden {{ $item['count_resume'] > 0 ? 'bg-danger' : '' }}">
            <div class="card-body text-center">
                <div class="m-b-10">
                    <h5 class="alert alert-warning"> تعداد رزومه ها جدید جهت پیگیری : {{$item['count_resume']}}</h5>
                    <a href="/dashboard/resume/desktop" class="btn btn-warning text-white mt-3">مشاهده</a>
                </div>
            </div>
            <div class="sparkline-demo4" style="margin: 0 -2px -3px -2px"></div>
        </div>
    </div>
    @endpermission
    @role('superadmin')
    <div class="container bg-white">
        <div class="row justify-content-center">
            <div class="col-md-5 mt-5">
                <div class="card">
                <div class="card-header">
                    <h3>لیست تمدید ها :</h3>
                </div>
                    <div class="card-body">
                        <table class="table  ">
                            <tr class="bg-info">
                                <td class="text-right">
                                    سرورها
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>سرور مجازی </td>
                                <td>{{verta($expire_dates['vps_expire_date'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr class="bg-info">
                                <td class="text-right"> پنل ها</td>
                                <td></td>

                            </tr>
                            <tr>
                                <td>پنل پلسک</td>
                                <td>{{verta($expire_dates['plesk_expire_date'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr class="bg-info">
                                <td class="text-right">دامنه ها</td>
                                <td></td>

                            </tr>
                            <tr>
                                <td>lalecard.ir </td>
                                <td>{{verta($expire_dates['domain_expire_date_lalecard.ir'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr>
                                <td>lalecard.com</td>
                                <td>{{verta($expire_dates['domain_expire_date_lalecard.com'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr>
                                <td>ghorfe-online.ir </td>
                                <td>{{verta($expire_dates['domain_expire_date_ghorfe-online.ir'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr>
                                <td>lalehkart.ir  </td>
                                <td>{{verta($expire_dates['domain_expire_date_lalehkart.ir'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr>
                                <td>lalekart.ir  </td>
                                <td>{{verta($expire_dates['domain_expire_date_lalekart.ir'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr>
                                <td>lalemarket.ir </td>
                                <td>{{verta($expire_dates['domain_expire_date_lalemarket.ir'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr>
                                <td>qorfe-online.ir </td>
                                <td>{{verta($expire_dates['domain_expire_date_qorfe-online.ir'])->format('Y/m/d')}}</td>
                            </tr>
                            <tr>
                                <td>lalebank.com  </td>
                                <td>{{verta($expire_dates['domain_expire_date_lalebank.com'])->format('Y/m/d')}}</td>
                            </tr>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endrole
</div>
@stop

@section('js')
<script>
    function openNav() {
    if(document.getElementById("myNav").style.width == "4%")
    {
        document.getElementById("myNav").style.width = "14%";
        document.getElementById("app").style.width = "86%";
    }
    else
    {
        document.getElementById("myNav").style.width = "4%";
        document.getElementById("app").style.width = "96%";
    }
}
</script>
@endsection
