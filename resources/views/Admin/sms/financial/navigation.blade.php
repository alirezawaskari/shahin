<div class="card mb-4 shadow-sm border-0">
    <div class="card-body p-2">
        <ul class="nav nav-pills flex-column flex-sm-row gap-2">
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.financial.otp.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.financial.otp.index') }}">
                    <i class="fa fa-shield me-1"></i> اعتبار و تراکنش‌های OTP
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.financial.advertising.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.financial.advertising.index') }}">
                    <i class="fa fa-bullhorn me-1"></i> اعتبار خطوط تبلیغاتی
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.reports.otp.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.reports.otp.index') }}">
                    <i class="fa fa-list-alt me-1"></i> گزارش پیامک‌های OTP
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.reports.advertising.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.reports.advertising.index') }}">
                    <i class="fa fa-pie-chart me-1"></i> گزارش تبلیغاتی و کمپین‌ها
                </a>
            </li>
            @if(auth()->user()?->isSuperAdmin())
                <li class="nav-item">
                    <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.reports.superadmin.*') ? 'active bg-danger text-white' : 'text-danger' }}" 
                       href="{{ route('admin.sms.reports.superadmin.index') }}">
                        <i class="fa fa-globe me-1"></i> گزارش جامع بین‌سازمانی (مدیر کل)
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
