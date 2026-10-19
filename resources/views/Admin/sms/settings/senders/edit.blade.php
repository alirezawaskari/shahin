@extends('Admin.master')

@section('title', 'ویرایش خط اختصاصی: ' . $senderNumber->number)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">ویرایش خط اختصاصی: {{ $senderNumber->number }}</h3>
            <p class="text-muted small mb-0">به‌روزرسانی تنظیمات خط فرستنده و کسب‌وکارهای متصل</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.sms.settings.senders.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-right me-1"></i> بازگشت به فهرست
            </a>
        </div>
    </div>

    @include('Admin.sms.settings.navigation')

    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            <h6 class="fw-bold mb-2"><i class="fa fa-exclamation-triangle me-1"></i> خطا در ویرایش خط فرستنده:</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">مشخصات خط فرستنده</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sms.settings.senders.update', $senderNumber) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">شماره خط (Sender Number) <span class="text-danger">*</span></label>
                        <input type="text" name="number" class="form-control font-monospace" value="{{ old('number', $senderNumber->number) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">نوع خط <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" id="senderTypeSelect" required>
                            <option value="advertising" {{ old('type', $senderNumber->type) === 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                            <option value="otp" {{ old('type', $senderNumber->type) === 'otp' ? 'selected' : '' }}>کد تایید (OTP - احراز هویت سراسری)</option>
                        </select>
                        <div class="form-text text-warning small mt-1" id="otpNotice" style="display: none;">
                            <i class="fa fa-info-circle me-1"></i> توجه: در کل سامانه تنها یک خط OTP سراسری مجاز است.
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">پنل / درگاه پیامک <span class="text-danger">*</span></label>
                        <input type="text" name="sms_panel" class="form-control" list="panelOptions" value="{{ old('sms_panel', $senderNumber->sms_panel) }}" required>
                        <datalist id="panelOptions">
                            @foreach($panels as $p)
                                <option value="{{ $p->name }}">{{ $p->name }} ({{ $p->type }})</option>
                            @endforeach
                            <option value="IranSms">IranSms</option>
                            <option value="NeginSms">NeginSms</option>
                            <option value="ParsaSms">ParsaSms</option>
                        </datalist>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">وضعیت خط <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" {{ old('status', $senderNumber->status) === 'active' ? 'selected' : '' }}>فعال</option>
                            <option value="inactive" {{ old('status', $senderNumber->status) === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold">وضعیت تعهدنامه / قرارداد خط</label>
                        @if(auth()->user()?->isSuperAdmin())
                            <select name="contract_status" class="form-select">
                                <option value="تایید شده" {{ old('contract_status', $senderNumber->contract_status) === 'تایید شده' ? 'selected' : '' }}>تایید شده (فعال برای ارسال)</option>
                                <option value="در انتظار تایید تعهدنامه" {{ old('contract_status', $senderNumber->contract_status) === 'در انتظار تایید تعهدنامه' ? 'selected' : '' }}>در انتظار تایید تعهدنامه</option>
                                <option value="رد شده / منقضی" {{ old('contract_status', $senderNumber->contract_status) === 'رد شده / منقضی' ? 'selected' : '' }}>رد شده / منقضی</option>
                            </select>
                            <small class="text-muted"><i class="fa fa-shield text-primary me-1"></i> کنترل انحصاری توسط مدیریت ارشد سامانه.</small>
                        @else
                            @php
                                $isApproved = ($senderNumber->contract_status === 'تایید شده');
                            @endphp
                            <div class="p-2 bg-light rounded border d-flex align-items-center justify-content-between">
                                <span class="badge {{ $isApproved ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2">
                                    <i class="fa {{ $isApproved ? 'fa-check-circle' : 'fa-clock-o' }} me-1"></i>
                                    {{ $senderNumber->contract_status ?: 'در انتظار تایید تعهدنامه' }}
                                </span>
                                <small class="text-muted"><i class="fa fa-lock me-1"></i> غیرقابل تغییر توسط کاربر (کنترل منحصراً توسط مدیریت ارشد)</small>
                            </div>
                        @endif
                    </div>

                    <div class="col-12 col-md-6" id="businessAssignmentGroup">
                        <label class="form-label fw-bold">تخصیص اشتراکی به کسب‌وکارها</label>
                        <select name="business_ids[]" class="form-select" multiple size="5">
                            @foreach($businesses as $b)
                                <option value="{{ $b->id }}" {{ in_array($b->id, (array) old('business_ids', $assignedBusinessIds)) ? 'selected' : '' }}>
                                    {{ $b->title }} (کد {{ $b->id }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">کسب‌وکارهای مجاز برای استفاده مشترک یا اختصاصی از این خط.</small>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">توضیحات</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $senderNumber->description) }}</textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-check me-1"></i> به‌روزرسانی خط فرستنده
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

{{-- Scripts live outside the Vue-compiled #app root (inline <script> inside it is dropped). --}}
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('senderTypeSelect');
        const otpNotice = document.getElementById('otpNotice');
        const bizGroup = document.getElementById('businessAssignmentGroup');

        function toggleTypeView() {
            if (typeSelect.value === 'otp') {
                if (otpNotice) otpNotice.style.display = 'block';
                if (bizGroup) bizGroup.style.display = 'none';
            } else {
                if (otpNotice) otpNotice.style.display = 'none';
                if (bizGroup) bizGroup.style.display = 'block';
            }
        }

        typeSelect.addEventListener('change', toggleTypeView);
        toggleTypeView();
    });
</script>
@endsection
