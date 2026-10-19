@php
    $isSuper = auth()->user()?->isSuperAdmin();
    $targetBusinessId = request('business_id') ?: request('business');
    if (!$targetBusinessId && $isSuper) {
        if (isset($business) && $business?->id) {
            $targetBusinessId = $business->id;
        } elseif (isset($businesses) && $businesses instanceof \Illuminate\Support\Collection && $businesses->isNotEmpty()) {
            $targetBusinessId = $businesses->first()->id;
        } elseif (isset($all_businesses) && $all_businesses instanceof \Illuminate\Support\Collection && $all_businesses->isNotEmpty()) {
            $targetBusinessId = $all_businesses->first()->id;
        } else {
            $targetBusinessId = \App\Models\GhorfeOnlineList::query()->value('id');
        }
    }
    $businessParam = ($isSuper && $targetBusinessId) ? ['business_id' => $targetBusinessId] : [];
@endphp

{{-- Stylesheet link instead of <style>: the page body is Vue-compiled, which drops <style>/<script> tags. --}}
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/sms-nav.css') }}">

<div class="card mb-3 shadow-sm border-0 sms-financial-nav">
    <div class="card-body p-2">
        <ul class="nav nav-pills d-flex flex-row flex-nowrap overflow-x-auto gap-2 py-1" style="white-space: nowrap; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
            @if(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()))
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.financial.otp.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.financial.otp.index', $businessParam) }}">
                    <i class="fa fa-shield ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> اعتبار و تراکنش‌های OTP
                </a>
            </li>
            @endif
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.financial.advertising.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.financial.advertising.index', $businessParam) }}">
                    <i class="fa fa-bullhorn ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> اعتبار خطوط تبلیغاتی
                </a>
            </li>
        </ul>
    </div>
</div>
