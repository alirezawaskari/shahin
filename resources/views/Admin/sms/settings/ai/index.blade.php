@extends('Admin.master')

@section('title', 'تنظیمات مدل‌های هوش مصنوعی (LLM) - شاهین')

@section('content')
<div class="container-fluid px-3 py-2">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">
                <i class="fa fa-magic text-warning me-2"></i> تنظیمات مدل‌های هوش مصنوعی (LLM)
            </h3>
            <p class="text-muted small mb-0">
                پیکربندی اتصال مستقیم به سرویس‌های هوش مصنوعی (Gemini, DeepSeek, OpenAI, OpenRouter, Ollama) برای استودیو کمپین پیامکی
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 text-white">
                <i class="fa fa-shield me-1"></i> پنل نظارت مدیر ارشد
            </span>
        </div>
    </div>

    <!-- Navigation -->
    @include('Admin.sms.settings.navigation')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="fa fa-check-circle fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Configuration Form Column -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-sliders text-primary me-2"></i> پیکربندی ارائه‌دهنده و مدل فعال
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.sms.settings.ai.update') }}" method="POST" id="aiSettingsForm">
                        @csrf

                        <!-- 1. Provider Selection Cards -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2">۱. انتخاب ارائه‌دهنده سرویس (Provider) <span class="text-danger">*</span></label>
                            <div class="row g-3" id="providerCardsContainer">
                                @foreach($providers as $pKey => $pInfo)
                                    @php
                                        $isSelected = ($setting->provider ?? 'gemini') === $pKey;
                                    @endphp
                                    <div class="col-12 col-sm-6 col-md-4">
                                        <div class="provider-card p-3 rounded border text-center cursor-pointer transition-all {{ $isSelected ? 'border-primary bg-light-primary text-primary shadow-sm' : 'border-secondary-subtle bg-white text-dark' }}" 
                                             data-provider="{{ $pKey }}"
                                             data-default-base-url="{{ $pInfo['default_base_url'] }}"
                                             style="cursor: pointer; transition: all 0.2s ease;">
                                            <div class="fs-3 mb-2">
                                                <i class="fa {{ $pInfo['icon'] ?? 'fa-cube' }}"></i>
                                            </div>
                                            <div class="fw-bold small">{{ $pInfo['name'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <input type="hidden" name="provider" id="selectedProviderInput" value="{{ old('provider', $setting->provider ?? 'gemini') }}">
                        </div>

                        <!-- 2. Model Selection & Presets -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-7">
                                <label class="form-label fw-bold mb-2" for="modelSelect">
                                    ۲. نام مدل هوش مصنوعی (Model ID) <span class="text-danger">*</span>
                                </label>
                                <select class="form-select mb-2" id="modelSelect">
                                    <!-- Populated dynamically via JS -->
                                </select>
                                <input type="text" 
                                       name="model" 
                                       id="customModelInput" 
                                       class="form-control" 
                                       value="{{ old('model', $setting->model ?? 'google/gemini-2.5-pro-preview') }}" 
                                       placeholder="یا شناسه مدل دلخواه را وارد کنید..."
                                       required>
                                <div class="form-text text-muted small">
                                    می‌توانید مدل را از لیست بالا انتخاب کرده یا شناسه دقیق مدل دلخواه را دستی تایپ کنید.
                                </div>
                            </div>

                            <div class="col-12 col-md-5">
                                <label class="form-label fw-bold mb-2" for="apiKeyInput">
                                    ۳. کلید دسترسی (API Key)
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           name="api_key" 
                                           id="apiKeyInput" 
                                           class="form-control" 
                                           placeholder="{{ !empty($setting->api_key) ? $setting->masked_api_key : 'کلید API را وارد کنید...' }}"
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleApiKeyBtn">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text text-muted small">
                                    @if(!empty($setting->api_key))
                                        <span class="text-success"><i class="fa fa-lock me-1"></i> کلید فعلی محفوظ است (برای تغییر، مقدار جدید وارد کنید).</span>
                                    @else
                                        برای سرورهای محلی (Ollama) می‌توانید خالی بگذارید.
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 3. Base URL & Endpoints -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2" for="baseUrlInput">
                                ۴. آدرس ریشه اندپوینت (Base URL)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fa fa-globe"></i></span>
                                <input type="url" 
                                       name="base_url" 
                                       id="baseUrlInput" 
                                       class="form-control" 
                                       value="{{ old('base_url', $setting->base_url) }}" 
                                       placeholder="https://api.openai.com/v1"
                                       dir="ltr">
                            </div>
                            <div class="form-text text-muted small">
                                برای OpenRouter: <code dir="ltr">https://openrouter.ai/api/v1</code> | برای DeepSeek: <code dir="ltr">https://api.deepseek.com/v1</code> | برای Ollama: <code dir="ltr">http://localhost:11434/v1</code>
                            </div>
                        </div>

                        <!-- 4. Generation Hyperparameters -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-2 d-flex justify-content-between" for="temperatureInput">
                                    <span>دمای تولید (Temperature)</span>
                                    <span class="badge bg-secondary" id="tempBadge">{{ number_format((float)old('temperature', $setting->temperature ?? 0.70), 2) }}</span>
                                </label>
                                <input type="range" 
                                       name="temperature" 
                                       id="temperatureInput" 
                                       class="form-range" 
                                       min="0.0" 
                                       max="1.5" 
                                       step="0.05" 
                                       value="{{ old('temperature', $setting->temperature ?? 0.70) }}">
                                <div class="d-flex justify-content-between text-muted small">
                                    <span>۰.۰ (دقیق و ساختاریافته)</span>
                                    <span>۰.۷ (تعادل بهینه)</span>
                                    <span>۱.۵ (خلاقانه)</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label class="form-label fw-bold mb-2" for="maxTokensInput">
                                    سقف توکن خروجی (Max Tokens)
                                </label>
                                <input type="number" 
                                       name="max_tokens" 
                                       id="maxTokensInput" 
                                       class="form-control" 
                                       value="{{ old('max_tokens', $setting->max_tokens ?? 2000) }}" 
                                       min="1" 
                                       max="128000" 
                                       step="1"
                                       required>
                                <div class="form-text text-muted small">برای تولید پیامک‌های کوتاه و فیلترهای هدف، ۲۰۰۰ توکن ایده‌آل است.</div>
                            </div>
                        </div>

                        <!-- 5. System Prompt / Tone -->
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-2" for="rolePromptTextarea">
                                ۵. پرامپت سیستمی و پرسونای بازاریابی (System Instructions)
                            </label>
                            <textarea name="role" 
                                      id="rolePromptTextarea" 
                                      rows="4" 
                                      class="form-control" 
                                      placeholder="دستورالعمل‌های سبک نگارش، رعایت اصول زبان فارسی و روان‌شناسی پیامک...">{{ old('role', $setting->role) }}</textarea>
                            <div class="form-text text-muted small">
                                این دستورالعمل به تمامی درخواست‌های تولید پیامک و تفکیک مخاطب در استودیو هوش مصنوعی تزریق می‌گردد.
                            </div>
                        </div>

                        <!-- 6. Active Toggle -->
                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   id="isActiveSwitch" 
                                   {{ old('is_active', $setting->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark" for="isActiveSwitch">
                                فعال‌سازی اتصال زنده به مدل (در صورت غیرفعال بودن، سیستم از موتور پشتیبان هوشمند داخلی استفاده خواهد کرد)
                            </label>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                            <button type="button" class="btn btn-outline-info px-4 py-2" id="testConnectionBtn">
                                <i class="fa fa-wifi me-1"></i> تست اتصال زنده به مدل
                            </button>
                            <button type="submit" class="btn btn-primary px-5 py-2">
                                <i class="fa fa-save me-1"></i> ذخیره تنظیمات هوش مصنوعی
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Side: Status & Live Test Output Column -->
        <div class="col-12 col-xl-4">
            <!-- Active Model Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-info-circle text-info me-2"></i> وضعیت مدل در حال استفاده
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <span class="text-muted small">وضعیت ماژول:</span>
                        @if($setting->is_active ?? true)
                            <span class="badge bg-success px-2 py-1"><i class="fa fa-check me-1"></i> متصل و فعال</span>
                        @else
                            <span class="badge bg-secondary px-2 py-1"><i class="fa fa-pause me-1"></i> متوقف (حالت آفلاین)</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <span class="text-muted small">ارائه‌دهنده:</span>
                        <span class="fw-bold text-dark">{{ $providers[$setting->provider ?? 'gemini']['name'] ?? $setting->provider }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <span class="text-muted small">شناسه مدل:</span>
                        <span class="badge bg-light text-dark border font-monospace px-2 py-1" dir="ltr">{{ $setting->model ?? 'google/gemini-2.5-pro-preview' }}</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small">کلید احراز هویت:</span>
                        @if(!empty($setting->api_key))
                            <span class="text-success small fw-bold"><i class="fa fa-key me-1"></i> تنظیم شده</span>
                        @else
                            <span class="text-warning small"><i class="fa fa-exclamation-triangle me-1"></i> ثبت نشده (یا آفلاین)</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Live Test Feedback Box -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-terminal text-primary me-2"></i> نتیجه ارزیابی اتصال زنده
                    </h6>
                </div>
                <div class="card-body p-3" id="testResultContainer">
                    <div class="text-center text-muted py-4" id="testPlaceholder">
                        <i class="fa fa-plug fs-1 mb-2 text-secondary-subtle"></i>
                        <p class="small mb-0">
                            روی دکمه «تست اتصال زنده به مدل» کلیک کنید تا ارتباط رفت‌وبرگشت و زمان پاسخ‌دهی (Latency) ارزیابی شود.
                        </p>
                    </div>

                    <div id="testLoading" class="text-center py-4 d-none">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="small text-muted mb-0">در حال ارسال پرامپت ارزیابی به سرور هوش مصنوعی...</p>
                    </div>

                    <div id="testSuccess" class="alert alert-success d-none mb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa fa-check-circle fs-5"></i>
                            <strong class="small">اتصال موفقیت‌آمیز بود!</strong>
                            <span class="badge bg-success ms-auto font-monospace" id="latencyBadge">-- ms</span>
                        </div>
                        <p class="small mb-1 text-dark">پاسخ نمونه دریافت‌شده از مدل:</p>
                        <div class="p-2 bg-white rounded border small text-dark" id="sampleResponseText"></div>
                    </div>

                    <div id="testError" class="alert alert-danger d-none mb-0">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fa fa-times-circle fs-5"></i>
                            <strong class="small" id="errorMessageTitle">خطا در برقراری ارتباط</strong>
                        </div>
                        <div class="p-2 bg-white rounded border small text-danger font-monospace text-break" id="errorDetailsText" dir="ltr"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    const providersData = @json($providers);

    document.addEventListener('DOMContentLoaded', function () {
        const providerCards = document.querySelectorAll('.provider-card');
        const providerInput = document.getElementById('selectedProviderInput');
        const modelSelect = document.getElementById('modelSelect');
        const customModelInput = document.getElementById('customModelInput');
        const baseUrlInput = document.getElementById('baseUrlInput');
        const apiKeyInput = document.getElementById('apiKeyInput');
        const toggleApiKeyBtn = document.getElementById('toggleApiKeyBtn');
        const tempInput = document.getElementById('temperatureInput');
        const tempBadge = document.getElementById('tempBadge');

        // Populate models for active provider
        function updateProviderModels(providerKey, preserveCustom = true) {
            const provider = providersData[providerKey];
            if (!provider) return;

            modelSelect.innerHTML = '';
            
            // Add models options
            Object.entries(provider.models || {}).forEach(([mId, mLabel]) => {
                const opt = document.createElement('option');
                opt.value = mId;
                opt.textContent = mLabel + ' (' + mId + ')';
                modelSelect.appendChild(opt);
            });

            // If custom model input matches an option, select it; else pick first
            const currentVal = customModelInput.value.trim();
            if (provider.models[currentVal]) {
                modelSelect.value = currentVal;
            } else if (!preserveCustom || !currentVal) {
                const firstKey = Object.keys(provider.models)[0];
                if (firstKey) {
                    modelSelect.value = firstKey;
                    customModelInput.value = firstKey;
                }
            }
        }

        // Provider card click
        providerCards.forEach(card => {
            card.addEventListener('click', function () {
                const selectedP = this.getAttribute('data-provider');
                const defaultUrl = this.getAttribute('data-default-base-url');

                providerCards.forEach(c => {
                    c.classList.remove('border-primary', 'bg-light-primary', 'text-primary', 'shadow-sm');
                    c.classList.add('border-secondary-subtle', 'bg-white', 'text-dark');
                });
                this.classList.remove('border-secondary-subtle', 'bg-white', 'text-dark');
                this.classList.add('border-primary', 'bg-light-primary', 'text-primary', 'shadow-sm');

                providerInput.value = selectedP;
                if (!baseUrlInput.value.trim() || baseUrlInput.getAttribute('data-auto-filled') === 'true') {
                    baseUrlInput.value = defaultUrl;
                    baseUrlInput.setAttribute('data-auto-filled', 'true');
                }

                updateProviderModels(selectedP, false);
            });
        });

        // Model dropdown change
        modelSelect.addEventListener('change', function () {
            customModelInput.value = this.value;
        });

        // Initialize models
        updateProviderModels(providerInput.value, true);

        // Temperature slider change
        tempInput.addEventListener('input', function () {
            tempBadge.textContent = parseFloat(this.value).toFixed(2);
        });

        // Toggle API key visibility
        toggleApiKeyBtn.addEventListener('click', function () {
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
                this.innerHTML = '<i class="fa fa-eye-slash"></i>';
            } else {
                apiKeyInput.type = 'password';
                this.innerHTML = '<i class="fa fa-eye"></i>';
            }
        });

        // Test Connection AJAX
        const testBtn = document.getElementById('testConnectionBtn');
        const testPlaceholder = document.getElementById('testPlaceholder');
        const testLoading = document.getElementById('testLoading');
        const testSuccess = document.getElementById('testSuccess');
        const testError = document.getElementById('testError');
        const latencyBadge = document.getElementById('latencyBadge');
        const sampleResponseText = document.getElementById('sampleResponseText');
        const errorMessageTitle = document.getElementById('errorMessageTitle');
        const errorDetailsText = document.getElementById('errorDetailsText');

        testBtn.addEventListener('click', function () {
            testPlaceholder.classList.add('d-none');
            testSuccess.classList.add('d-none');
            testError.classList.add('d-none');
            testLoading.classList.remove('d-none');
            testBtn.disabled = true;

            const payload = {
                _token: '{{ csrf_token() }}',
                provider: providerInput.value,
                model: customModelInput.value.trim(),
                api_key: apiKeyInput.value.trim(),
                base_url: baseUrlInput.value.trim(),
            };

            fetch('{{ route("admin.sms.settings.ai.test") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                testLoading.classList.add('d-none');
                testBtn.disabled = false;

                if (data.success) {
                    latencyBadge.textContent = (data.latency_ms || 0) + ' ms';
                    sampleResponseText.textContent = data.sample || data.message;
                    testSuccess.classList.remove('d-none');
                } else {
                    errorMessageTitle.textContent = data.message || 'خطا در اتصال به مدل';
                    errorDetailsText.textContent = data.error || 'جزئیاتی از سرور دریافت نشد.';
                    testError.classList.remove('d-none');
                }
            })
            .catch(err => {
                testLoading.classList.add('d-none');
                testBtn.disabled = false;
                errorMessageTitle.textContent = 'خطا در ارسال درخواست ارزیابی';
                errorDetailsText.textContent = err.toString();
                testError.classList.remove('d-none');
            });
        });
    });
</script>
<style>
    .bg-light-primary {
        background-color: #eff6ff !important;
    }
</style>
@endsection
