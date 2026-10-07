<div class="card mb-4 shadow-sm border-0">
    <div class="card-body p-2">
        <ul class="nav nav-pills flex-column flex-sm-row gap-2">
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.settings.index') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.index') }}">
                    <i class="fa fa-dashboard me-1"></i> پیش‌خوان تنظیمات
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.settings.panels.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.panels.index') }}">
                    <i class="fa fa-server me-1"></i> پنل‌های پیامک
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.settings.senders.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.senders.index') }}">
                    <i class="fa fa-phone me-1"></i> خطوط اختصاصی
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.settings.templates.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.templates.index') }}">
                    <i class="fa fa-file-text-o me-1"></i> الگوهای پیامک
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.settings.groups.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.groups.index') }}">
                    <i class="fa fa-folder-o me-1"></i> گروه‌بندی پیام‌ها
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-2 px-3 {{ request()->routeIs('admin.sms.settings.contracts.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.contracts.index') }}">
                    <i class="fa fa-file-pdf-o me-1"></i> قراردادها و تعهدنامه‌ها
                </a>
            </li>
        </ul>
    </div>
</div>
