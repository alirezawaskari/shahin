<div class="col-12 row">
    <form class="form-horizontal" role="form" method="post"
          action="{{ route('purchaseInvoice.confirm' , [$purchaseInvoice->id ]) }}"
          enctype="multipart/form-data">
        @csrf

        @include('Admin.layouts.errors')
        @role('mali|superadmin|supervisor')
        @if($purchaseInvoice->status == 1)
            <p class="text-warning col-12 mt-2">مدیر مالی محترم تایید نهایی فاکتور به منظور تایید
                فاکتور خرید از جانب شما و ارسال فاکتور خرید برای تایید به مدیرعامل می‌باشد.</p>
            <button class="btn btn-success ml-2">تایید و ارجاع به مدیرعامل</button>
        @endif
        @endrole
        @role('superadmin')
        @if($purchaseInvoice->status == 2)
            <button class="btn btn-success ml-2">تایید نهایی مدیر</button>
        @endif
        @endrole
        @role('mali-representation')
        @if($purchaseInvoice->status == 3)
            <p class="text-warning col-12">مدیر مالی محترم تایید نهایی فاکتور به منظور تایید
                پرونده از جانب شما و ارسال پرونده برای تایید مدیر عاملیت می‌باشد.</p>
            <button class="btn btn-success ml-2">تایید و ارجاع به مدیر عاملیت</button>
        @endif
        @endrole
        @role('superadmin-representation')
        @if($purchaseInvoice->status == 4)
            <button class="btn btn-success ml-2">تایید نهایی مدیر عاملیت</button>
        @endif
        @endrole
        @role('warehouse-keeper' || 'superadmin')
        @if($purchaseInvoice->status == 6)
            <p class="text-warning col-md-12">مدیریت محترم : اینجانب {{ $user->name.' '.$user->family }} با کد
                کاربری {{ $user->id}} اقلام فاکتور شماره {{ $purchaseInvoice->id }} در
                تاریخ {{ verta(date("Y/m/d"))->format('Y/m/d') }} صحیح و سالم تحویل گرفته و با
                بررسی کامل سلامت کالاهای فوق رو تایید می‌نمایم.</p>
            <button class="btn btn-success ml-2">تایید دریافت کالا</button>
            <input type="hidden" name="status" value="7">
            <div class="btn btn-danger ml-2" data-toggle="modal" data-target="#commentModal">گزارش
                خرابی کالا
            </div>
        @endif
        @endrole
    </form>
    <form class="form-horizontal" role="form" method="post"
          action="{{route('purchaseInvoice.changeStatus' , [$purchaseInvoice->id ])}}"
          enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="status" value="1">
        @role('superadmin')
        @if($purchaseInvoice->status == 2)
            <button class="btn btn-danger ml-2">ارجاع مجدد به مالی</button>
        @endif
        @if($purchaseInvoice->status == 0)
            <button class="btn btn-danger ml-2">ارجاع به مالی</button>
        @endif
        @endrole
    </form>
    <form class="form-horizontal" role="form" method="post"
          action="{{route('purchaseInvoice.changeStatus' , [$purchaseInvoice->id ])}}"
          enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="status" value="8">
        @role('superadmin')
        <button class="btn btn-dark">بستن فاکتور</button>
        @endrole
    </form>
</div>
