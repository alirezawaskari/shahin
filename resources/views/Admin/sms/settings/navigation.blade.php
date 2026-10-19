{{-- Stylesheet link instead of <style>: the page body is Vue-compiled, which drops <style>/<script> tags. --}}
<link rel="stylesheet" href="{{ asset('assets/dashboard/css/sms-settings.css') }}">

<div class="card mb-3 shadow-sm border-0 sms-settings-nav">
    <div class="card-body p-2">
        <ul class="nav nav-pills d-flex flex-row flex-nowrap overflow-x-auto gap-2 py-1" style="white-space: nowrap; -webkit-overflow-scrolling: touch; scrollbar-width: none;">
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.index') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.index') }}">
                    <i class="fa fa-dashboard ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> پیش‌خوان تنظیمات
                </a>
            </li>
            @if(auth()->user()?->isSuperAdmin())
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.panels.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.panels.index') }}">
                    <i class="fa fa-server ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> پنل‌های پیامک
                </a>
            </li>
            @endif
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.senders.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.senders.index') }}">
                    <i class="fa fa-phone ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> خطوط اختصاصی
                </a>
            </li>
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.templates.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.templates.index') }}">
                    <i class="fa fa-file-text-o ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> الگوهای پیامک
                </a>
            </li>
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.auto-responders.*') ? 'active bg-primary text-white' : 'text-dark' }}"
                   href="{{ route('admin.sms.settings.auto-responders.index') }}">
                    <i class="fa fa-reply ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> پاسخ‌گوی خودکار
                </a>
            </li>
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.groups.*') ? 'active bg-primary text-white' : 'text-dark' }}" 
                   href="{{ route('admin.sms.settings.groups.index') }}">
                    <i class="fa fa-folder-o ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> گروه‌بندی پیام‌ها
                </a>
            </li>
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.contracts.*', 'admin.sms.settings.content.*', 'admin.sms.contract.*') ? 'active bg-primary text-white' : 'text-dark' }}"
                   href="{{ route('admin.sms.settings.contracts.index') }}">
                    <i class="fa fa-balance-scale ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> قراردادها، تعهدنامه و ضوابط حقوقی
                </a>
            </li>
            @if(auth()->user()?->isSuperAdmin())
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.access-tiers.*') ? 'active bg-primary text-white' : 'text-dark' }}"
                   href="{{ route('admin.sms.settings.access-tiers.index') }}">
                    <i class="fa fa-shield ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> سطوح دسترسی کاربران
                </a>
            </li>
            <li class="nav-item flex-shrink-0">
                <a class="nav-link py-2 px-3 text-nowrap {{ request()->routeIs('admin.sms.settings.ai.*') ? 'active bg-primary text-white' : 'text-dark' }}"
                   href="{{ route('admin.sms.settings.ai.index') }}">
                    <i class="fa fa-magic ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> مدل‌های هوش مصنوعی (LLM)
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>
