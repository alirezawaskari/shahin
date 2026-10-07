<div class="card mb-4 shadow-sm border-0">
    <div class="card-body p-2">
        <ul class="nav nav-pills flex-column flex-sm-row gap-2">
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.logs.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.logs.index') }}">
                    <i class="fa fa-list me-1"></i> لاگ پیامک‌های ارسالی و دریافتی
                </a>
            </li>
            @if(Route::has('admin.sms.single.index'))
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.single.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.single.index') }}">
                        <i class="fa fa-paper-plane me-1"></i> ارسال تکی
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.bulk.index'))
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.bulk.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.bulk.index') }}">
                        <i class="fa fa-users me-1"></i> ارسال انبوه
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.financial.otp.index'))
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.financial.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.financial.otp.index') }}">
                        <i class="fa fa-money me-1"></i> امور مالی و موجودی
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.reports.otp.index'))
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.reports.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.reports.otp.index') }}">
                        <i class="fa fa-bar-chart me-1"></i> گزارشات آماری
                    </a>
                </li>
            @endif
            @if(Route::has('admin.sms.settings.index'))
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.settings.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                       href="{{ route('admin.sms.settings.index') }}">
                        <i class="fa fa-cogs me-1"></i> تنظیمات پیامک
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
