@extends('Admin.master')

@section('title', 'افزودن پنل پیامک جدید')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">افزودن پنل پیامک جدید</h3>
            <p class="text-muted small mb-0">تعریف درگاه یا ارائه‌دهنده جدید برای ارسال و دریافت پیامک</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.sms.settings.panels.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-right me-1"></i> بازگشت به فهرست
            </a>
        </div>
    </div>

    @include('Admin.sms.settings.navigation')

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.sms.settings.panels.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- General Settings -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">اطلاعات کلی پنل</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">نام پنل (شناسه ارائه‌دهنده) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="مثال: IranSms, NeginSms, ParsaSms" value="{{ old('name') }}" required>
                            <small class="text-muted">این نام در خطوط اختصاصی برای انتساب درگاه استفاده می‌شود.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">نامک (Slug)</label>
                            <input type="text" name="slug" class="form-control" placeholder="مثال: iran-sms" value="{{ old('slug') }}">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">نوع سرویس <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="normal-advertising" {{ old('type') === 'normal-advertising' ? 'selected' : '' }}>عادی / تبلیغاتی</option>
                                    <option value="otp" {{ old('type') === 'otp' ? 'selected' : '' }}>کد تایید (OTP)</option>
                                    <option value="all" {{ old('type') === 'all' ? 'selected' : '' }}>چندمنظوره / سراسری</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">وضعیت اولیه <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>فعال</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">توضیحات</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="یادداشت‌ها و توضیحات تکمیلی...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Connection Info -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">اطلاعات اتصال و احراز هویت (API)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">نوع وب‌سرویس / درایور <span class="text-danger">*</span></label>
                            <select name="connection_info[driver]" id="panelDriverSelect" class="form-select">
                                <option value="iransms" {{ old('connection_info.driver', 'iransms') === 'iransms' ? 'selected' : '' }}>ایران پیامک / پارسا پیامک (REST API - ApiKey)</option>
                                <option value="neginsms" {{ old('connection_info.driver') === 'neginsms' ? 'selected' : '' }}>نگین پیامک / هما سیستم (SOAP WSDL - PortalCode)</option>
                                <option value="custom" {{ old('connection_info.driver') === 'custom' ? 'selected' : '' }}>سایر / سفارشی</option>
                            </select>
                            <small class="text-muted">پروتکل و درایور اتصال سیستم به ارائه‌دهنده را مشخص کنید.</small>
                        </div>

                        <div class="mb-3" id="portalCodeGroup">
                            <label class="form-label fw-bold">کد پرتال (Portal Code)</label>
                            <input type="text" name="connection_info[portal_code]" class="form-control font-monospace" placeholder="مثال: 10002239" value="{{ old('connection_info.portal_code') }}">
                            <small class="text-muted">مخصوص وب‌سرویس نگین پیامک (Homais SOAP).</small>
                        </div>

                        <div class="mb-3" id="apiKeyGroup">
                            <label class="form-label fw-bold">کلید وب‌سرویس (API Key / Token)</label>
                            <input type="password" name="connection_info[api_key]" class="form-control font-monospace" placeholder="apikey or token" value="{{ old('connection_info.api_key') }}">
                            <small class="text-muted">مخصوص وب‌سرویس ایران پیامک / پارسا پیامک (REST API).</small>
                        </div>

                        <div class="row g-3 mb-3" id="userPassGroup">
                            <div class="col-6">
                                <label class="form-label fw-bold">نام کاربری</label>
                                <input type="text" name="connection_info[username]" class="form-control font-monospace" placeholder="username" value="{{ old('connection_info.username') }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">کلمه عبور</label>
                                <input type="password" name="connection_info[password]" class="form-control font-monospace" placeholder="password" value="{{ old('connection_info.password') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">شماره خط اختصاصی در وب‌سرویس (اختیاری)</label>
                            <input type="text" name="connection_info[line_number]" class="form-control font-monospace" placeholder="مثال: 10002622" value="{{ old('connection_info.line_number') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">آدرس وب‌سرویس (Base URL / Endpoint / WSDL)</label>
                            <input type="url" name="connection_info[base_url]" class="form-control font-monospace" placeholder="http://api.parsasms.com یا https://dev.homais.com/..." value="{{ old('connection_info.base_url') }}">
                            <small class="text-muted">در صورت خالی بودن، آدرس رسمی پیش‌فرض درایور فراخوانی می‌شود.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Send / Receive / Delivery Settings -->
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">تنظیمات ارسال، دریافت و وضعیت تحویل</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <h6 class="fw-bold text-primary mb-2">تنظیمات ارسال (Send)</h6>
                                <div class="mb-3">
                                    <label class="form-label small">شماره خط فرستنده پیش‌فرض</label>
                                    <input type="text" name="send_settings[default_sender]" class="form-control font-monospace" placeholder="3000..." value="{{ old('send_settings.default_sender') }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">مهلت زمانی درخواست (Timeout ثانیه)</label>
                                    <input type="number" name="send_settings[timeout]" class="form-control" value="{{ old('send_settings.timeout', 15) }}">
                                </div>
                                <div class="form-text small text-muted">
                                    عبارت الزامی «لغو 11» طبق ضوابط اپراتورها همواره به انتهای پیامک‌های تبلیغاتی افزوده می‌شود و قابل غیرفعال‌سازی نیست.
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6 class="fw-bold text-success mb-2">تنظیمات دریافت (Receive)</h6>
                                <div class="mb-3">
                                    <label class="form-label small">آدرس وب‌هوک دریافت پیام (Webhook URL)</label>
                                    <input type="url" name="receive_settings[webhook_url]" class="form-control font-monospace" placeholder="https://lalecard.ir/api/sms/webhook" value="{{ old('receive_settings.webhook_url') }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6 class="fw-bold text-info mb-2">تنظیمات تحویل (Delivery Report)</h6>
                                <div class="mb-3">
                                    <label class="form-label small">آدرس بازگشتی وضعیت تحویل (Callback URL)</label>
                                    <input type="url" name="delivery_settings[callback_url]" class="form-control font-monospace" placeholder="https://lalecard.ir/api/sms/delivery" value="{{ old('delivery_settings.callback_url') }}">
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="delivery_settings[auto_check]" value="1" class="form-check-input" id="autoCheck" {{ old('delivery_settings.auto_check', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="autoCheck">بررسی خودکار وضعیت تحویل (Polling / Callback)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-check me-1"></i> ذخیره و ثبت پنل
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
