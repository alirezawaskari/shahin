<div class="col-12">
            @role('warehouse-keeper')
            @if($count_product_storeRoom_expiring > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_product_storeRoom_expiring}} محصول درحال انقضا در انبار موجود می باشد لطفا بررسی نمایید</span>
                    <a href="/dashboard/storeRoom/list-product-expiring" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @role('filing')
            @if($count_factoritem_filing > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_filing}} پرونده جدید جهت تایید در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @role('legal')
            @if($count_factoritem_legal > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_legal}} پرونده جدید جهت تایید در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @if($count_factoritem_mali_legal > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_mali_legal}} پرونده جدید جهت اقدام برای ضمانت در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factoritem/reports" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @role('consulting_contract_affairs')
            @if($count_factoritem_mali_consulting_contract_affairs > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_mali_consulting_contract_affairs}} پرونده جدید جهت اقدام برای ضمانت در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factoritem/reports" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @role('mali')
            @if($count_factoritem_mali > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_mali}} پرونده جدید جهت تایید در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @if($count_factoritem_mali_mali > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_mali_mali}} پرونده جدید جهت تایید تسویه حساب در بخش گزارش مالی پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factoritem/desktop-reports" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @if($count_factor_mali > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factor_mali}} فاکتور خرید جدید جهت تایید در بخش فاکتورهای خرید دارید، لطفا بررسی نمایید</span>
                    <a href="{{ route('purchaseInvoice.index') }}" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @if($count_tickets > 0)
            <div class="alert alert-warning text-dark d-flex justify-content-between">
                <span> {{$count_tickets}} پیام جدید در قسمت اتوماسیون اداری دارید لطفا بررسی نمایید.</span>
                <a href="{{ route('desktop.tickets') }}" class="btn btn-warning text-white">مشاهده</a>
            </div>
            @endif
            @role('superadmin')
            @if($count_factoritem_superadmin > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_superadmin}} پرونده جدید جهت تایید در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @if($count_factoritem_mali_superadmin > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_mali_superadmin}} پرونده جدید جهت تایید تسویه حساب در بخش گزارش مالی پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factoritem/desktop-reports" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @if($count_factor_superadmin > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factor_superadmin}} فاکتور خرید جدید جهت تایید در بخش فاکتور های خرید دارید، لطفا بررسی نمایید</span>
                    <a href="{{ route('purchaseInvoice.index') }}" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @role('mali-representation')
            @if($count_factoritem_mali_representation > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_mali_representation }} پرونده جدید جهت تایید در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @if($count_factor_mali_representation > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factor_mali_representation }} فاکتور خرید جدید جهت تایید در بخش فاکتور های خرید دارید، لطفا بررسی نمایید</span>
                    <a href="{{ route('purchaseInvoice.index') }}" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @if($count_factor_supplier > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factor_supplier }} درخواست جدید جهت تایید در بخش درخواست ها دارید، لطفا بررسی نمایید</span>
                    <a href="{{ route('purchaseInvoice.desktop') }}" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @role('superadmin-representation')
            @if($count_factoritem_superadmin_representation > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_superadmin_representation }} پرونده جدید جهت تایید در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @if($count_factor_superadmin_representation > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factor_superadmin_representation }} فاکتور خرید جدید جهت تایید در بخش فاکتورهای خرید دارید، لطفا بررسی نمایید</span>
                    <a href="{{ route('purchaseInvoice.index') }}" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endrole
            @if($count_factoritem_confirm > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_factoritem_confirm}} پرونده جدید تایید شده جهت اجرا در بخش پرونده ها دارید، لطفا بررسی نمایید</span>
                    <a href="/dashboard/factorItem/desktop" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @permission('contact|contact-detail')
            @if($count_contact > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_contact}} پیام جدید در بخش تماس با ما دارید، لطفا بررسی نمایید</span>
                    <a href="{{ route('contact.index') }}" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endpermission

            @permission('recruitment_notice')
            @if($count_resume > 0)
                <div class="alert alert-warning text-dark d-flex justify-content-between">
                    <span> {{$count_resume}} پیام جدید در بخش رزومه ها دارید، لطفا بررسی نمایید</span>
                    <a href="{{ route('resume.index') }}" class="btn btn-warning text-white">مشاهده</a>
                </div>
            @endif
            @endpermission
        </div>
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
