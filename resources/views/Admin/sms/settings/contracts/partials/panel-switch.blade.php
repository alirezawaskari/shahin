{{-- Super admin only (the caller checks). Disables or enables ONE business's SMS panel: $biz, $panelType (otp|advertising). --}}
@php($panelState = app(\App\Services\SmsSettings\SmsPanelAccess::class)->state($biz, $panelType))
@if($panelState['disabled'])
    <span class="badge bg-danger align-self-center me-1" title="{{ $panelState['reason'] }}">پنل غیرفعال</span>
    <form action="{{ route('admin.sms.contract.panels.enable', ['business' => $biz->id, 'type' => $panelType]) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-success" title="فعال‌سازی دوباره این پنل برای کسب‌وکار">
            <i class="fa fa-play"></i> فعال‌سازی پنل
        </button>
    </form>
@else
    <form action="{{ route('admin.sms.contract.panels.disable', ['business' => $biz->id, 'type' => $panelType]) }}" method="POST" class="d-inline"
          onsubmit="var r = window.prompt('دلیل غیرفعال‌سازی پنل {{ \App\Services\SmsSettings\SmsPanelAccess::label($panelType) }} این کسب‌وکار (الزامی):', ''); if (r === null || r.trim() === '') { return false; } this.elements['reason'].value = r.trim(); return true;">
        @csrf
        <input type="hidden" name="reason" value="">
        <button type="submit" class="btn btn-outline-danger" title="غیرفعال‌سازی این پنل برای کسب‌وکار">
            <i class="fa fa-ban"></i> غیرفعال‌سازی پنل
        </button>
    </form>
@endif
