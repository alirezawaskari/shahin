{{-- Stylesheet link instead of <style>: the page body is Vue-compiled, which drops <style>/<script> tags. --}}
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/sms-nav.css') }}">

<div class="card mb-3 shadow-sm border-0 sms-logs-nav">
    <div class="card-body p-2">
        <ul class="nav nav-pills d-flex flex-row flex-nowrap overflow-x-auto gap-2 py-1" style="white-space: nowrap; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.logs.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.logs.index') }}">
                    <i class="fa fa-list ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> لاگ پیامک‌های ارسالی و دریافتی
                </a>
            </li>
            @if(Route::has('admin.sms.single.index'))
                <li class="nav-item flex-shrink-0">
                    <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.single.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.single.index') }}">
                        <i class="fa fa-paper-plane ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ارسال تکی
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.bulk.index'))
                <li class="nav-item flex-shrink-0">
                    <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.bulk.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.bulk.index') }}">
                        <i class="fa fa-users ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> ارسال انبوه
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.financial.otp.index'))
                <li class="nav-item flex-shrink-0">
                    <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.financial.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()) ? 'admin.sms.financial.otp.index' : 'admin.sms.financial.advertising.index') }}">
                        <i class="fa fa-money ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> امور مالی و موجودی
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.reports.otp.index'))
                <li class="nav-item flex-shrink-0">
                    <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.reports.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()) ? 'admin.sms.reports.otp.index' : 'admin.sms.reports.advertising.index') }}">
                        <i class="fa fa-bar-chart ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> گزارشات آماری
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.settings.index'))
                <li class="nav-item flex-shrink-0">
                    <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.settings.index') }}">
                        <i class="fa fa-cogs ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> تنظیمات پیامک
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
