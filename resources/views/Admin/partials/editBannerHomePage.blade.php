<div class="col-md-12 col-sm-12 col-xs-12 mb-3 ">
    <h4>عکس های صفحه اصلی *</h4>
    <span>تمامی عکس های صفحه اصلی الزامی هستند.</span><br/>
    <span>2 عکس افقی و 2 عکس عمودی در pc نمایش داده می شوند.</span><br/>
    <span>4 عکس افقی در موبایل نمایش داده می شوند.</span><br/>
</div>

<div class="col-12 col-md-12 mb-5 row">

    {{-- ================= HORIZONTAL ================= --}}
    <div class="col-md-6">
        <p>عکس های افقی : </p>
        @for($i = 0; $i < 4; $i++)
            @php
                $slot = method_exists($model, 'gallerySlot') ? $model->gallerySlot('gallery_horizontal', $i) : null;
            @endphp
            <div class="col-md-12 border rounded p-2 mb-2 d-flex align-items-center">
                <input type="text" placeholder="عنوان عکس" name="horizontalImages[{{$i}}][file_title]"
                       value="{{ $slot ? $slot->getCustomProperty('title') : '' }}"
                       class="form-control disInline width30"/>

                <label for="horizontalImages_{{$i}}" class="btn btn-primary custom-file-upload border border-1 mb-0 mx-2">
                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                </label>
                <input id="horizontalImages_{{$i}}" name="horizontalImages_{{$i}}" class="d-none file-upload file_upload_ajax" type="file" accept="image/*"/>
                <input type="hidden" id="horizontalImages_{{$i}}_path" class="path" name="horizontalImages[{{$i}}][file]" value="">

                <div id="horizontalImages_{{$i}}_current" data-box-h="80" style="width:100px" class="mx-2">
                    @if($slot)
                        <div class="div_remove_btn d-inline-block mb-2" id="media_{{$slot->id}}">
                            <img class="rounded border" style="height:80px;width:100%;object-fit:contain;background:#f8f9fa;"
                                 src="{{ $slot->getFullUrl() }}" alt="{{ $slot->getCustomProperty('title') }}">
                            <a data-id="{{ $slot->id }}" title="حذف عکس" class="btn btn-sm remove_media_btn">
                                <i class="fa fa-close" aria-hidden="true"></i>
                            </a>
                        </div>
                    @else
                        <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2"
                             style="height:80px;background:#f8f9fa;">بدون تصویر</div>
                    @endif
                </div>

                <div id="horizontalImages_{{$i}}_src" class="show disInline mx-2"></div>
            </div>
        @endfor
    </div>

    {{-- ================= VERTICAL ================= --}}
    <div class="col-md-6">
        <p>عکس های عمودی : </p>
        @for($i = 0; $i < 2; $i++)
            @php
                $slot = method_exists($model, 'gallerySlot') ? $model->gallerySlot('gallery_vertical', $i) : null;
            @endphp
            <div class="col-md-12 border rounded p-2 mb-2 d-flex align-items-center">
                <input type="text" placeholder="عنوان عکس" name="verticalImages[{{$i}}][file_title]"
                       value="{{ $slot ? $slot->getCustomProperty('title') : '' }}"
                       class="form-control disInline width30"/>

                <label for="verticalImages_{{$i}}" class="btn btn-primary custom-file-upload border border-1 mb-0 mx-2">
                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                </label>
                <input id="verticalImages_{{$i}}" name="verticalImages_{{$i}}" class="d-none file-upload file_upload_ajax" type="file" accept="image/*"/>
                <input type="hidden" id="verticalImages_{{$i}}_path" class="path" name="verticalImages[{{$i}}][file]" value="">

                <div id="verticalImages_{{$i}}_current" data-box-h="80" style="width:100px" class="mx-2">
                    @if($slot)
                        <div class="div_remove_btn d-inline-block mb-2" id="media_{{$slot->id}}">
                            <img class="rounded border" style="height:80px;width:100%;object-fit:contain;background:#f8f9fa;"
                                 src="{{ $slot->getFullUrl() }}" alt="{{ $slot->getCustomProperty('title') }}">
                            <a data-id="{{ $slot->id }}" title="حذف عکس" class="btn btn-sm remove_media_btn">
                                <i class="fa fa-close" aria-hidden="true"></i>
                            </a>
                        </div>
                    @else
                        <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2"
                             style="height:80px;background:#f8f9fa;">بدون تصویر</div>
                    @endif
                </div>

                <div id="verticalImages_{{$i}}_src" class="show disInline mx-2"></div>
            </div>
        @endfor
    </div>

    {{-- Legacy galleries-table fallback (read-only) — hidden once this model has Spatie gallery media --}}
    @include('Admin.partials.legacyGalleryFallback', [
        'model'       => $model,
        'collections' => ['gallery_horizontal', 'gallery_vertical'],
    ])

</div>
