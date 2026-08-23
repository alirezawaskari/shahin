<head>
       <style>
        .seo-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .06);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
        }

        .form-text {
            font-size: 12px;
        }

        .url-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 12px;
            direction: ltr;
            text-align: left;
            word-break: break-all;
        }

        .preview-box {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 18px;
        }

        .google-title {
            color: #1a0dab;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .google-url {
            color: #188038;
            font-size: 13px;
            direction: ltr;
            text-align: left;
        }

        .google-description {
            color: #555;
            font-size: 13px;
            margin-top: 5px;
        }

        .char-counter {
            font-size: 12px;
            color: #6c757d;
        }

        .char-warning {
            color: #dc3545;
        }

        .char-success {
            color: #198754;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1"> مدیریت SEO </h4>
                <div class="text-muted small"> خدمت: <strong>{{ $service->title }}</strong> </div>
            </div>
            <div class="d-flex gap-2"> <a href="{{ route('services.show', $service->slug) }}" target="_blank"
                    class="btn btn-outline-primary"> مشاهده صفحه </a> <button type="submit" form="seoForm"
                    class="btn btn-primary seo-content-submit"> ذخیره تغییرات </button> </div>
        </div>
            <form action="{{route('service.update' , [$service->slug ])}}" id="seo-content-form">
            <div class="row g-4">
                <!-- ========================= -->
                <!-- SEO Settings -->
                <!-- ========================= -->
                <input type="hidden" value="seo_content" name="update_type">
                <div class="col-lg-8">
                    <div class="card seo-card mb-4">
                        <div class="card-body">
                            <div class="section-title"> تنظیمات اصلی SEO </div> <!-- SEO Title -->
                            <div class="mb-4"> <label class="form-label required"> عنوان SEO </label> <input type="text"
                                    name="seo_title" id="seo_title" class="form-control"
                                    value="{{ old('seo_title', $service->seoContent?->seo_title ?? $service->title) }}"
                                    maxlength="70" placeholder="عنوان سئو خدمت را وارد کنید">
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="form-text"> در صورت خالی بودن، عنوان اصلی خدمت استفاده می‌شود. </div>
                                    <span id="seoTitleCounter" class="char-counter"> 0 / 70 </span>
                                </div>
                            </div> <!-- Meta Description -->
                            <div class="mb-4"> <label class="form-label required"> Meta Description </label> <textarea
                                    name="meta_description" id="meta_description" class="form-control" rows="4"
                                    maxlength="170"
                                    placeholder="توضیحات کوتاه و مناسب برای موتورهای جستجو">{{ old('meta_description', $service->seoContent?->meta_description) }}</textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="form-text"> توضیح مختصر و مرتبط با محتوای خدمت. </div> <span
                                        id="descriptionCounter" class="char-counter"> 0 / 170 </span>
                                </div>
                            </div> <!-- Robots -->
                            <div class="mb-4"> <label class="form-label"> وضعیت ایندکس </label> <select name="robots"
                                    class="form-select">
                                    <option value="index, follow" {{ old('robots', $service->seoContent?->robots ?? 'index, follow')
                                        == 'index, follow' ? 'selected' : '' }}> Index, Follow </option>
                                    <option value="noindex, follow" {{ old('robots', $service->seoContent?->robots) == 'noindex,
                                        follow' ? 'selected' : '' }}> Noindex, Follow </option>
                                </select>
                                <div class="form-text"> حالت پیش‌فرض برای خدمات جدید Index, Follow است. </div>
                            </div> <!-- Canonical -->
                            <div class="mb-4"> <label class="form-label"> Canonical URL </label> <input type="url"
                                    name="canonical_url" class="form-control" dir="ltr"
                                    value="{{ old('canonical_url', $service->seoContent?->canonical_url) }}"
                                    placeholder="در صورت خالی بودن URL اصلی استفاده می‌شود">
                                <div class="form-text"> اگر این فیلد خالی باشد، Canonical بهصورت خودکار از Route واقعی
                                    خدمت ساخته می‌شود. </div>
                            </div>
                        </div>
                    </div> <!-- ========================= -->
                    <!-- Redirect -->
                    <!-- ========================= -->
                    <div class="card seo-card mb-4">
                        <div class="card-body">
                            <div class="section-title"> Redirect 301 </div>
                            <div class="mb-3"> <label class="form-label"> URL قدیمی </label> <input type="text"
                                    name="redirect_from" class="form-control" dir="ltr"
                                    value="{{ old('redirect_from', $service->seoContent?->redirect_from) }}"
                                    placeholder="/old-service-url"> </div>
                            <div class="mb-3"> <label class="form-label"> URL مقصد </label> <input type="text"
                                    name="redirect_to" class="form-control" dir="ltr"
                                    value="{{ old('redirect_to', $service->seoContent?->redirect_to) }}"
                                    placeholder="/new-service-url"> </div>
                            <div class="form-check form-switch"> <input class="form-check-input" type="checkbox"
                                    name="redirect_enabled" value="1" id="redirect_enabled" {{
                                    $service->seoContent?->redirect_enabled ? 'checked' : '' }}> <label class="form-check-label"
                                    for="redirect_enabled"> فعال‌سازی Redirect 301 </label> </div>
                            <div class="alert alert-warning mt-3 mb-0"> <strong>توجه:</strong> قبل از ذخیره Redirect،
                                سیستم باید مقصد را بررسی کند تا Redirect Loop ایجاد نشود. </div>
                        </div>
                    </div> <!-- ========================= -->
                    <!-- Open Graph -->
                    <!-- ========================= -->
                    <div class="card seo-card mb-4">
                        <div class="card-body">
                            <div class="section-title"> Open Graph </div> <!-- OG Title -->
                            <div class="mb-3"> <label class="form-label"> OG Title </label> <input type="text"
                                    name="og_title" class="form-control"
                                    value="{{ old('og_title', $service->seoContent?->og_title) }}"
                                    placeholder="در صورت خالی بودن SEO Title استفاده می‌شود"> </div>
                            <!-- OG Description -->
                            <div class="mb-3"> <label class="form-label"> OG Description </label> <textarea
                                    name="og_description" class="form-control" rows="3"
                                    placeholder="در صورت خالی بودن Meta Description استفاده می‌شود">{{ old('og_description', $service->seoContent?->og_description) }}</textarea>
                            </div>
                            <div class="mb-3"> <label class="form-label"> نام سایت </label> <input type="text"
                                    name="og_site_name" class="form-control"
                                    value="{{ old('og_site_name', config('app.name')) }}"> </div>
                        </div>
                    </div> <!-- ========================= -->
                    <!-- URL -->
                    <!-- ========================= -->
                    <div class="card seo-card mb-4">
                        <div class="card-body">
                            <div class="section-title"> آدرس صفحه خدمت </div>
                            <div class="url-box mb-3" id="serviceUrl"> {{ route('services.show', $service->slug) }} </div>
                            <div class="d-flex gap-2"> <a href="{{ route('services.show', $service->slug) }}"
                                    target="_blank" class="btn btn-primary"> مشاهده صفحه </a> <button type="button"
                                    class="btn btn-outline-secondary" onclick="copyServiceUrl()"> کپی URL </button>
                            </div>
                            <div id="copyMessage" class="text-success small mt-2" style="display:none;"> URL با موفقیت
                                کپی شد. </div>
                        </div>
                    </div>
                    <button type="submit" form="seoForm"
                    class="btn btn-primary seo-content-submit"> ذخیره تغییرات </button>
                </div> <!-- ========================= -->
                <!-- Sidebar -->
                <!-- ========================= -->
                <div class="col-lg-4">
                    <!-- Service Info -->
                    <div class="card seo-card mb-4">
                        <div class="card-body">
                            <div class="section-title"> اطلاعات خدمت </div>
                            <div class="mb-3"> <small class="text-muted"> عنوان خدمت </small>
                                <div class="fw-bold"> {{ $service->title }} </div>
                            </div>
                            <div class="mb-3"> <small class="text-muted"> وضعیت </small>
                                <div> @if($service->isShow) <span class="badge bg-success"> منتشر شده </span>
                                    @else <span class="badge bg-secondary"> پیش‌نویس </span> @endif </div>
                            </div>
                            <div> <small class="text-muted"> آخرین بروزرسانی </small>
                                <div> {{  verta($service->updated_at)->format('Y/m/d') }} </div>
                            </div>
                        </div>
                    </div> <!-- Google Preview -->
                    <div class="card seo-card mb-4">
                        <div class="card-body">
                            <div class="section-title"> پیش‌نمایش Google </div>
                            <div class="preview-box">
                                <div id="googlePreviewTitle" class="google-title"> عنوان خدمت </div>
                                <div class="google-url"> {{ route('services.show', $service->slug) }} </div>
                                <div id="googlePreviewDescription" class="google-description"> توضیحات متای خدمت </div>
                            </div>
                        </div>
                    </div> <!-- OG Preview -->
                    <div class="card seo-card">
                        <div class="card-body">
                            <div class="section-title"> اطلاعات خروجی Open Graph </div>
                            <div class="small">
                                <div class="mb-2"> <strong>og:type:</strong> website </div>
                                <div class="mb-2"> <strong>og:locale:</strong> fa_IR </div>
                                <div class="mb-2"> <strong>og:url:</strong>
                                    <div class="text-muted mt-1" dir="ltr"> {{ route('services.show', $service->slug) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        const seoTitle = document.getElementById('seo_title'); const metaDescription = document.getElementById('meta_description'); const titleCounter = document.getElementById('seoTitleCounter'); const descriptionCounter = document.getElementById('descriptionCounter'); const previewTitle = document.getElementById('googlePreviewTitle'); const previewDescription = document.getElementById('googlePreviewDescription'); function updateCounters() { const titleLength = seoTitle.value.length; const descriptionLength = metaDescription.value.length; titleCounter.innerText = titleLength + ' / 70'; descriptionCounter.innerText = descriptionLength + ' / 170'; titleCounter.classList.remove( 'char-warning', 'char-success' ); descriptionCounter.classList.remove( 'char-warning', 'char-success' ); if (titleLength >= 30 && titleLength <= 60) { titleCounter.classList.add('char-success'); } else { titleCounter.classList.add('char-warning'); } if (descriptionLength >= 120 && descriptionLength <= 160) { descriptionCounter.classList.add('char-success'); } else { descriptionCounter.classList.add('char-warning'); } previewTitle.innerText = seoTitle.value  'عنوان خدمت'; previewDescription.innerText = metaDescription.value  'توضیحات متای خدمت'; } seoTitle.addEventListener( 'input', updateCounters ); metaDescription.addEventListener( 'input', updateCounters ); updateCounters(); function copyServiceUrl() { const url = document.getElementById('serviceUrl').innerText.trim(); navigator.clipboard.writeText(url) .then(function () { document.getElementById('copyMessage').style.display = 'block'; setTimeout(function () { document.getElementById('copyMessage').style.display = 'none'; }, 2000); }); }
    </script>

