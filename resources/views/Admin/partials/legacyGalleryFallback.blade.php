@php
    /**
     * Read-only strip of legacy `galleries`-table images for a model whose gallery was
     * migrated to Spatie Media Library. Shown ONLY when the model has NO new-system
     * (Spatie) media in the given collection(s) — i.e. "Spatie if any, else legacy".
     * Existing galleries keep displaying here; re-uploading through the form above moves
     * them into Spatie and this strip disappears. New uploads only ever go to Spatie.
     *
     * @param  mixed         $model        HasMedia model exposing a galleries() morphMany
     * @param  array|string  $collections  gallery collection name(s) to check for new media
     */
    $collections = isset($collections) ? (array) $collections : ['gallery'];

    $legacyGalleries = collect();
    if (isset($model) && $model
        && method_exists($model, 'getMedia')
        && method_exists($model, 'galleries')) {

        $hasNewGallery = false;
        foreach ($collections as $__c) {
            if ($model->getMedia($__c)->isNotEmpty()) {
                $hasNewGallery = true;
                break;
            }
        }
        $legacyGalleries = $hasNewGallery ? collect() : $model->galleries;
    }
@endphp

@if($legacyGalleries->isNotEmpty())
    <div class="col-12 col-md-12 mt-3">
        <div class="alert alert-warning py-2 mb-2">
            <i class="fa fa-info-circle"></i>
             تصاویر قدیمی گالری (سیستم قبلی). برای انتقال به  سیستم جدید، تصاویر را دوباره از فرم بالا بارگذاری کنید یا برای انتقال تصاویر قدیمی به سیتم جدید یک بار دکمه ذخیره را کلیک کنید
        </div>
        <div class="row">
            @foreach($legacyGalleries as $legacyImage)
                @continue(empty($legacyImage->image))
                <div class="col-6 col-md-2 p-2 text-center">
                    <img src="{{ asset($legacyImage->image) }}"
                         class="rounded border"
                         style="height:120px;width:100%;object-fit:contain;background:#f8f9fa;"
                         alt="{{ $legacyImage->title }}">
                    @if($legacyImage->title)
                        <small class="d-block text-muted mt-1">{{ $legacyImage->title }}</small>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
