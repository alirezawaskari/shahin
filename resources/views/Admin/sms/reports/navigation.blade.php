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

<style>
    /* RTL Icon Spacing */
    .sms-reports-nav .nav-link i.fa,
    .nav-pills .nav-link i.fa {
        margin-left: 0.45rem !important;
        margin-right: 0 !important;
    }
</style>

<div class="card mb-3 shadow-sm border-0 sms-reports-nav">
    <div class="card-body p-2">
        <ul class="nav nav-pills d-flex flex-row flex-nowrap overflow-x-auto gap-2 py-1" style="white-space: nowrap; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.reports.otp.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.reports.otp.index', $businessParam) }}">
                    <i class="fa fa-shield ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> گزارش پیامک‌های اعتبارسنجی (OTP)
                </a>
            </li>
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.reports.advertising.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.reports.advertising.index', $businessParam) }}">
                    <i class="fa fa-bullhorn ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> گزارش تبلیغاتی و کمپین‌ها
                </a>
            </li>
            @if($isSuper)
                <li class="nav-item flex-shrink-0 ms-auto">
                    <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.reports.superadmin.*') ? 'active bg-danger text-white' : 'text-danger' }}" 
                       href="{{ route('admin.sms.reports.superadmin.index') }}">
                        <i class="fa fa-globe ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> گزارش جامع بین‌سازمانی (مدیر کل)
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
