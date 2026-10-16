{{-- The send icon next to «فیلتر کن». Only rendered for users who may send group SMS. --}}
@php($smsToFilterCan = auth()->user() && (auth()->user()->isSuperAdmin() || auth()->user()->isAbleTo('send-group-sms')))
@if($smsToFilterCan)
    <button type="button" class="btn btn-outline-success py-2 px-3 me-1" id="open_filter_sms"
            title="ارسال پیامک به نتیجه فیلتر" data-bs-toggle="tooltip">
        <i class="fa fa-envelope-o"></i> ارسال پیامک
    </button>
@endif
