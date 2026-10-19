@extends('Admin.master')

@section('title', 'ویرایش الگوی پیامک: ' . $template->title)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">ویرایش الگوی پیامک: {{ $template->title }}</h3>
            <p class="text-muted small mb-0">هرگونه ویرایش متن به صورت خودکار در تاریخچه نگارش‌ها ثبت می‌شود.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.sms.settings.templates.history', $template) }}" class="btn btn-outline-info btn-sm">
                <i class="fa fa-history me-1"></i> مشاهده تاریخچه و مقایسه
            </a>
            <a href="{{ route('admin.sms.settings.templates.index') }}" class="btn btn-outline-secondary btn-sm">
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

    <form action="{{ route('admin.sms.settings.templates.update', $template) }}" method="POST" id="templateForm">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <!-- Left Side: Form Details -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">اطلاعات الگو</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">عنوان الگو <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $template->title) }}" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">نوع سرویس <span class="text-danger">*</span></label>
                                <select name="service_type" class="form-select" required>
                                    @if(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()) || $template->service_type === 'normal')
                                    <option value="normal" {{ old('service_type', $template->service_type) === 'normal' ? 'selected' : '' }}>عادی</option>
                                    @endif
                                    @if(app(\App\Services\Sms\SmsAccessTierService::class)->canUseOtp(auth()->user()))
                                    <option value="otp" {{ old('service_type', $template->service_type) === 'otp' ? 'selected' : '' }}>کد تایید (OTP)</option>
                                    @endif
                                    <option value="advertising" {{ old('service_type', $template->service_type) === 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">وضعیت <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="active" {{ old('status', $template->status) === 'active' ? 'selected' : '' }}>فعال</option>
                                    <option value="inactive" {{ old('status', $template->status) === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                                </select>
                            </div>
                        </div>

                        @if(!empty($otpCategories))
                        <div class="mb-3" id="otpCategoryWrap" style="display:none;">
                            <label class="form-label fw-bold">دسته‌بندی سیستمی OTP <span class="text-danger">*</span></label>
                            <select name="otp_category" id="otpCategorySelect" class="form-select @error('otp_category') is-invalid @enderror" disabled>
                                <option value="">انتخاب کنید...</option>
                                @foreach($otpCategories as $slug => $label)
                                    <option value="{{ $slug }}" {{ old('otp_category', $template->otp_category) === $slug ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('otp_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">یکی از چهار دسته‌بندی ثابت سیستم؛ فقط برای الگوهای OTP.</div>
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-bold">گروه / زیرگروه پیامک</label>
                            <select name="sms_message_group_id" class="form-select">
                                <option value="">— بدون گروه —</option>
                                @foreach($groupOptions as $id => $title)
                                    <option value="{{ $id }}" data-promotional="{{ in_array($id, $promotionalGroupIds ?? []) ? '1' : '0' }}" {{ old('sms_message_group_id', $template->sms_message_group_id) == $id ? 'selected' : '' }}>
                                        {{ $title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <!-- Parameter tokens picker -->
                        <div class="mb-2">
                            <label class="form-label fw-bold small text-muted">درج متغیرهای پویا (با کلیک درج می‌شود):</label>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-token" data-token="{نام کاربر}">{نام کاربر}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-token" data-token="{نام خانوادگی}">{نام خانوادگی}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-token" data-token="{تاریخ}">{تاریخ}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-token" data-token="{ساعت}">{ساعت}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-token" data-token="{شناسه فاکتور}">{شناسه فاکتور}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-token" data-token="{لینک پرداخت}">{لینک پرداخت}</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary insert-token" data-token="{شناسه درخواست}">{شناسه درخواست}</button>
                                <button type="button" class="btn btn-sm btn-outline-info insert-token" data-token="{company_name}">{company_name}</button>
                                <button type="button" class="btn btn-sm btn-outline-info insert-token" data-token="{phone}">{phone}</button>
                                <button type="button" class="btn btn-sm btn-outline-info insert-token" data-token="{address}">{address}</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">متن الگو <span class="text-danger">*</span></label>
                            <textarea name="content" id="templateContent" class="form-control font-sans-serif" rows="6" required>{{ old('content', $template->content) }}</textarea>
                            <div class="d-flex justify-content-between text-muted small mt-1">
                                <span>تعداد کاراکتر: <span id="charCount">0</span></span>
                                <span>تعداد بخش پیامک: <span id="smsPages">1</span></span>
                            </div>
                        </div>

                        <div class="text-end pt-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fa fa-check me-1"></i> به‌روزرسانی الگو
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Live Dynamic Preview Frame -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fa fa-mobile fa-lg text-primary me-1"></i> پیش‌نمایش زنده الگو
                        </h5>
                        <span class="badge bg-light text-dark border small" id="previewStatus">زنده</span>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center align-items-center bg-light p-4">
                        <div class="p-3 bg-white border rounded-4 shadow-sm w-100" style="max-width: 320px; min-height: 280px; position: relative;">
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-3 text-muted small">
                                <span><i class="fa fa-commenting-o text-primary"></i> پیامک سیستم</span>
                                <span class="font-monospace">الان</span>
                            </div>

                            <div class="p-3 bg-light rounded-3 text-dark mb-3" style="font-size: 0.95rem; line-height: 1.7; white-space: pre-wrap; word-break: break-word;" id="previewBubble">
                                متن پیامک پس از درج متغیرها اینجا نمایش داده می‌شود...
                            </div>

                            <div class="border-top pt-2 mt-auto">
                                <small class="text-muted d-block mb-1 fw-bold">متغیرهای استفاده شده:</small>
                                <div id="placeholdersList" class="d-flex flex-wrap gap-1">
                                    <span class="text-muted small fst-italic">هیچ متغیری استفاده نشده است</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

{{-- Scripts live outside the Vue-compiled #app root (inline <script> inside it is dropped). --}}
@section('scripts')
<script>
                            (function () {
                                var type = document.querySelector('select[name="service_type"]');
                                var wrap = document.getElementById('otpCategoryWrap');
                                var sel = document.getElementById('otpCategorySelect');
                                function sync() {
                                    var on = type && type.value === 'otp';
                                    wrap.style.display = on ? '' : 'none';
                                    sel.disabled = !on;
                                    sel.required = on;
                                }
                                if (type) { type.addEventListener('change', sync); }
                                sync();
                            })();
                        </script>
                                                <script>
                            (function () {
                                // Promotional / normal templates only support the promotional subject groups.
                                var type = document.querySelector('select[name="service_type"]');
                                var sel = document.querySelector('select[name="sms_message_group_id"]');
                                function syncGroups() {
                                    if (!sel) return;
                                    var otp = type && type.value === 'otp';
                                    Array.prototype.forEach.call(sel.options, function (o) {
                                        if (o.value === '') return;
                                        var blocked = !otp && o.getAttribute('data-promotional') !== '1' && !o.defaultSelected;
                                        o.disabled = blocked;
                                        o.hidden = blocked;
                                    });
                                    if (sel.selectedOptions.length && sel.selectedOptions[0].disabled) { sel.value = ''; }
                                }
                                if (type) { type.addEventListener('change', syncGroups); }
                                syncGroups();
                            })();
                        </script>
<script src="{{ asset('assets/dashboard/js/sms-param-chips.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('templateContent');
        const previewBubble = document.getElementById('previewBubble');
        const charCount = document.getElementById('charCount');
        const smsPages = document.getElementById('smsPages');
        const placeholdersList = document.getElementById('placeholdersList');
        const previewStatus = document.getElementById('previewStatus');
        const tokenButtons = document.querySelectorAll('.insert-token');

        // Dynamic parameter tokens are inserted as styled, non-text-editable
        // chip elements instead of raw "{...}" brace text.
        SmsParamChips.attach(textarea, tokenButtons);

        let debounceTimer;

        function updatePreview() {
            const content = textarea.value;
            charCount.textContent = content.length;
            smsPages.textContent = Math.max(1, Math.ceil(content.length / 70));

            clearTimeout(debounceTimer);
            previewStatus.textContent = 'در حال پردازش...';

            debounceTimer = setTimeout(() => {
                fetch("{{ route('admin.sms.settings.templates.preview') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ content: content })
                })
                .then(res => res.json())
                .then(data => {
                    previewStatus.textContent = 'به‌روز شد';
                    if (data.success && data.data) {
                        previewBubble.textContent = data.data.preview || 'متن پیامک خالی است...';
                        
                        placeholdersList.innerHTML = '';
                        if (data.data.placeholders && data.data.placeholders.length > 0) {
                            data.data.placeholders.forEach(p => {
                                const badge = document.createElement('span');
                                badge.className = 'badge bg-primary bg-opacity-10 text-primary border';
                                badge.textContent = p;
                                placeholdersList.appendChild(badge);
                            });
                        } else {
                            placeholdersList.innerHTML = '<span class="text-muted small fst-italic">هیچ متغیری استفاده نشده است</span>';
                        }
                    }
                })
                .catch(() => {
                    previewStatus.textContent = 'خطا در ارتباط';
                });
            }, 300);
        }

        textarea.addEventListener('input', updatePreview);
        updatePreview();
    });
</script>
@endsection
