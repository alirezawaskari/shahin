@extends('Admin.master')

@section('title', 'ویرایش پنل پیامک: ' . $panel->name)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">ویرایش پنل پیامک: {{ $panel->name }}</h3>
            <p class="text-muted small mb-0">به‌روزرسانی تنظیمات اتصال و درگاه پیامک</p>
        </div>
        <a href="{{ route('admin.sms.settings.panels.index') }}" class="btn btn-outline-secondary">
            <i class="fa fa-arrow-right me-1"></i> بازگشت به فهرست
        </a>
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

    <form action="{{ route('admin.sms.settings.panels.update', $panel) }}" method="POST">
        @csrf
        @method('PUT')
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
                            <input type="text" name="name" class="form-control" value="{{ old('name', $panel->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">نامک (Slug)</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $panel->slug) }}">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">نوع سرویس <span class="text-danger">*</span></label>
                                <select name="type" class="form-select" required>
                                    <option value="normal-advertising" {{ old('type', $panel->type) === 'normal-advertising' ? 'selected' : '' }}>عادی / تبلیغاتی</option>
                                    <option value="otp" {{ old('type', $panel->type) === 'otp' ? 'selected' : '' }}>کد تایید (OTP)</option>
                                    <option value="all" {{ old('type', $panel->type) === 'all' ? 'selected' : '' }}>چندمنظوره / سراسری</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">وضعیت <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="active" {{ old('status', $panel->status) === 'active' ? 'selected' : '' }}>فعال</option>
                                    <option value="inactive" {{ old('status', $panel->status) === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">توضیحات</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $panel->description) }}</textarea>
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
                            <label class="form-label fw-bold">کلید وب‌سرویس (API Key / Token)</label>
                            <input type="password" name="connection_info[api_key]" class="form-control font-monospace" value="{{ old('connection_info.api_key', $panel->getConnectionSetting('api_key')) }}">
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">نام کاربری</label>
                                <input type="text" name="connection_info[username]" class="form-control font-monospace" value="{{ old('connection_info.username', $panel->getConnectionSetting('username')) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">کلمه عبور</label>
                                <input type="password" name="connection_info[password]" class="form-control font-monospace" value="{{ old('connection_info.password', $panel->getConnectionSetting('password')) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">آدرس وب‌سرویس (Base URL / Endpoint)</label>
                            <input type="url" name="connection_info[base_url]" class="form-control font-monospace" value="{{ old('connection_info.base_url', $panel->getConnectionSetting('base_url')) }}">
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
                                    <input type="text" name="send_settings[default_sender]" class="form-control font-monospace" value="{{ old('send_settings.default_sender', $panel->getSendSetting('default_sender')) }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small">مهلت زمانی درخواست (Timeout ثانیه)</label>
                                    <input type="number" name="send_settings[timeout]" class="form-control" value="{{ old('send_settings.timeout', $panel->getSendSetting('timeout', 15)) }}">
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="send_settings[auto_append_cancel]" value="1" class="form-check-input" id="appendCancel" {{ old('send_settings.auto_append_cancel', $panel->getSendSetting('auto_append_cancel', 1)) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="appendCancel">افزودن خودکار عبارت «لغو 11» به پیام‌های تبلیغاتی</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6 class="fw-bold text-success mb-2">تنظیمات دریافت (Receive)</h6>
                                <div class="mb-3">
                                    <label class="form-label small">آدرس وب‌هوک دریافت پیام (Webhook URL)</label>
                                    <input type="url" name="receive_settings[webhook_url]" class="form-control font-monospace" value="{{ old('receive_settings.webhook_url', $panel->getReceiveSetting('webhook_url')) }}">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <h6 class="fw-bold text-info mb-2">تنظیمات تحویل (Delivery Report)</h6>
                                <div class="mb-3">
                                    <label class="form-label small">آدرس بازگشتی وضعیت تحویل (Callback URL)</label>
                                    <input type="url" name="delivery_settings[callback_url]" class="form-control font-monospace" value="{{ old('delivery_settings.callback_url', $panel->getDeliverySetting('callback_url')) }}">
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="delivery_settings[auto_check]" value="1" class="form-check-input" id="autoCheck" {{ old('delivery_settings.auto_check', $panel->getDeliverySetting('auto_check', 1)) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="autoCheck">بررسی خودکار وضعیت تحویل (Polling / Callback)</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fa fa-check me-1"></i> به‌روزرسانی تنظیمات پنل
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
