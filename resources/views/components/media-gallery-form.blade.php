@php
    $deskImages = $galleries->where('type', 1)->values();

    $numberImage = 4;

    if ($ghorfeTemplet->type == 'service') {
        $numberImage = 6;
    }

    $bigDeskIndex = $numberImage;
@endphp


<div class="col-md-12 mb-3">
    <h4>عکس های گالری *</h4>
    <span>تمامی عکس ها الزامی هستند.</span><br/>
    <span>نمایش در موبایل و دسکتاپ</span><br/>
    <span>{{ $numberImage }} عکس کوچک و 1 عکس بزرگ</span>
</div>

{{-- ================= DESKTOP ================= --}}
<div class="row">

    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
        عکس های دسکتاپ
    </div>


    @for($i = 0; $i < $numberImage; $i++)
        @php
            $image = $deskImages[$i] ?? null;
        @endphp

        <div class="card col-md-6 p-2 text-center mb-3">

            <input type="text"
                   name="deskImage[{{$i}}][file_title]"
                   value="{{ $image->title ?? '' }}"
                   class="form-control mb-2"
                   placeholder="عنوان">


            <div class="input-group mb-2">
                <input id="desk_{{$i}}" name="desk_file_{{$i}}"
                       class="d-none form-control file-upload file_upload_ajax" type="file"
                       accept="image/*"/>
                <label for="desk_{{$i}}"
                       class="btn btn-primary custom-file-upload border border-1">
                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                </label>
            </div>

            <input type="hidden" id="desk_{{$i}}_path" class="path" name="deskImage[{{$i}}][file]" value="">
            <div id="desk_{{$i}}_src" class="show disInline"></div>

        </div>
    @endfor

    {{-- ================= BIG DESKTOP IMAGE ================= --}}
    @php
        $bigDesk = $deskImages[$numberImage] ?? null;
    @endphp

    <div class="col-md-12 card p-3 shadow-sm text-center">

        <input type="text"
               name="deskImage[4][file_title]"
               value="{{ $bigDesk->title ?? '' }}"
               class="form-control mb-2"
               placeholder="عنوان عکس بزرگ">

        <div class="input-group mb-2">
            <input id="desk_4" name="desk_file_4"
                   class="d-none form-control file-upload file_upload_ajax" type="file"
                   accept="image/*"/>
            <label for="desk_4"
                   class="btn btn-primary custom-file-upload border border-1">
                <i class="fa fa-cloud-upload"></i> انتخاب تصویر
            </label>
        </div>

        <input type="hidden" id="desk_4_path" class="path" name="deskImage[4][file]" value="">
        <div id="desk_4_src" class="show disInline"></div>

    </div>

</div>

{{-- ================= MOBILE ================= --}}
<div class="row mt-4">

    <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
        عکس های موبایل
    </div>

    @php
        $mobileImages = $galleries->where('type', 0)->values();
    @endphp

    @for($i = 0; $i < $numberImage; $i++)
        @php
            $image = $mobileImages[$i] ?? null;
        @endphp

        <div class="card col-md-6 p-2 text-center mb-3">

                <input type="text"
                       name="{{ $input }}[{{ $i }}][file_title]"
                       value="{{ $media?->getCustomProperty('title') }}"
                       class="form-control mb-2"
                       placeholder="{{ $isBig ? 'عنوان عکس بزرگ' : 'عنوان' }}">

                {{-- multilingual titles + parent_id kept as media custom properties (galleries table stays droppable) --}}
                <input type="hidden" name="{{ $input }}[{{ $i }}][title_en]"   value="{{ $media?->getCustomProperty('title_en') }}">
                <input type="hidden" name="{{ $input }}[{{ $i }}][title_ar]"   value="{{ $media?->getCustomProperty('title_ar') }}">
                <input type="hidden" name="{{ $input }}[{{ $i }}][title_turk]" value="{{ $media?->getCustomProperty('title_turk') }}">
                <input type="hidden" name="{{ $input }}[{{ $i }}][parent_id]"  value="{{ $media?->getCustomProperty('parent_id') }}">

                <div id="{{ $fieldId }}_current" data-box-h="{{ $boxH }}">
                    @if($media)
                        <div class="div_remove_btn d-inline-block mb-2" id="media_{{ $media->id }}">
                            <img src="{{ $media->getFullUrl() }}"
                                 class="rounded border"
                                 style="height:{{ $boxH }}px;width:100%;object-fit:contain;background:#f8f9fa;"
                                 alt="">
                            {{-- No ✕ on the LAST image while the legacy `galleries` table still holds
                                 rows for this collection: emptying it lets the next save re-adopt them
                                 all, so the delete would undo itself. See
                                 AdoptsLegacyMedia::galleryMediaIsRemovable(). --}}
                            @if(! $owner || ! method_exists($owner, 'galleryMediaIsRemovable') || $owner->galleryMediaIsRemovable($collection))
                            <a data-id="{{ $media->id }}" title="حذف عکس"
                               class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>
                            @endif
                        </div>
                    @else
                        <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2"
                             style="height:{{ $boxH }}px;background:#f8f9fa;">بدون تصویر</div>
                    @endif
                </div>

            <div class="input-group mb-2">
                <input id="mobile_{{$i}}" name="mobile_file_{{$i}}"
                       class="d-none form-control file-upload file_upload_ajax" type="file"
                       accept="image/*"/>
                <label for="mobile_{{$i}}"
                       class="btn btn-primary custom-file-upload border border-1">
                    <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                </label>
            </div>

            <input type="hidden" id="mobile_{{$i}}_path" class="path" name="mobileImage[{{$i}}][file]" value="">
            <div id="mobile_{{$i}}_src" class="show disInline"></div>

        </div>
    @endfor

    @php
        $bigMobile = $mobileImages[$numberImage] ?? null;
    @endphp

    <div class="col-md-12 card p-3 shadow-sm text-center">

        <input type="text"
               name="mobileImage[4][file_title]"
               value="{{ $bigMobile->title ?? '' }}"
               class="form-control mb-2"
               placeholder="عنوان عکس بزرگ">

        <img id="preview_mobile_4"
             src="{{ $bigMobile ? asset($bigMobile->image) : 'https://via.placeholder.com/150x250' }}"
             class="img-fluid mb-2 rounded"
             style="height:180px;object-fit:cover">

        <div class="input-group mb-2">
            <input id="mobile_4" name="mobile_file_4"
                   class="d-none form-control file-upload file_upload_ajax" type="file"
                   accept="image/*"/>
            <label for="mobile_4"
                   class="btn btn-primary custom-file-upload border border-1">
                <i class="fa fa-cloud-upload"></i> انتخاب تصویر
            </label>
        </div>

        <input type="hidden" id="mobile_4_path" class="path" name="mobileImage[4][file]" value="">
        <div id="mobile_4_src" class="show disInline"></div>

    </div>
@endforeach

{{-- Legacy galleries-table fallback (read-only) — hidden once this owner has Spatie gallery media --}}
@include('Admin.partials.legacyGalleryFallback', [
    'model'       => $owner,
    'collections' => collect($sections)->pluck('collection')->all(),
])
