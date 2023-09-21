<div class="row col-12 mt-5">
    <form class="form-horizontal" role="form" method="post"
        action="{{ route('totalFactorItem.update', [$totalFactorItem->id]) }}">
        @csrf
        @method('PATCH')
        <input type="hidden" name="factorItem_id" value="{{ $item['id'] }}">
        <input type="hidden" name="type" value="confirm">
        @role('superadmin')
        @if ($item['status'] == 8)
        <button class="btn btn-success ml-2">تایید نهایی مدیر</button>
        @endif
        @endrole
        @role('mali|consulting_contract_affairs|superadmin')
        @if ($item['status'] == 5)
        <p class="text-warning col-12">مدیر مالی محترم تایید نهایی فاکتور به منظور تایید
            پرونده از جانب شما و ارسال پرونده برای تایید مدیرعامل می‌باشد.</p>
        <button class="btn btn-success">تایید و ارجاع به امورقراردادها</button>
        @endif
        @endrole
        @role('consulting_contract_affairs|superadmin')
        @if ($item['status'] == 13)
        <button class="btn btn-success">تایید و ارجاع به مدیرعامل</button>
        @endif
        @endrole
        @role('mali-representation')
        @if ($item['status'] == 11)
        <p class="text-warning col-12">مدیر مالی محترم تایید نهایی فاکتور به منظور تایید
            پرونده از جانب شما و ارسال پرونده برای تایید مدیرنمایندگی می‌باشد.</p>
        <button class="btn btn-success">تایید و ارجاع به مدیرنمایندگی</button>
        @endif
        @endrole
        @role('superadmin-representation')
        @if ($item['status'] == 12)
        <button class="btn btn-success">تایید نهایی مدیر نمایندگی</button>
        @endif
        @endrole
        @role('filing|mali|consulting_contract_affairs|superadmin')
        @if ($item['status'] == 9)
        <button class="btn btn-success">تایید و ارجاع به مدیر مالی</button>
        @endif
        @endrole
    </form>
    <form action="/dashboard/totalFactorItem/change-status" method="POST"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="5">
        @role('consulting_contract_affairs|superadmin')
        @if ($item['status'] == 13)
        <button class="btn btn-danger mr-2">ارجاع مجدد به مالی</button>
        @endif
        @endrole
    </form>
    <form action="/dashboard/totalFactorItem/change-status" method="POST"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="13">
        @role('superadmin')
        @if ($item['status'] == 8)
        <button class="btn btn-danger mr-2">ارجاع مجدد به امورقراردادها</button>
        @endif
        @endrole
    </form>
    <form action="/dashboard/totalFactorItem/change-status" method="POST"
        enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="item[id]" value="{{ $item['id'] }}">
        <input type="hidden" name="status_factor" value="14">
        @role('filing|mali')
        @if ($item['status'] == 9)
        <button class="btn btn-danger mr-2">ارجاع مجدد به پشتیبانی</button>
        @endif
        @endrole
    </form>
</div>
