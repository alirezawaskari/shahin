@php
$numberImage = 2;
$ghorfeTemplet = $owner->parent->en_title ?? $owner->en_title;
if ($ghorfeTemplet == 'service') {
$numberImage = 4;
}
$bigDeskIndex = $numberImage;
@endphp
<div class="col-md-12 mb-3">
    <h4>عکس های گالری *</h4>
    <span>تمامی عکس ها الزامی هستند.</span><br/>
    <span>نمایش در موبایل و دسکتاپ</span><br/>
    <span>{{ $numberImage }} عکس کوچک و 1 عکس بزرگ</span>
</div>

@foreach($sections as $section)
    @php
        $collection = $section['collection'];
        $input      = $section['input'];
    @endphp

    <div class="row mt-4">

        <div class="card-header bg-dark-gradient col-md-12 border border-right-0 border-left-0">
            {{ $section['label'] }}
        </div>

        @for($i = 0; $i <= $numberImage; $i++)
            @php
                $isBig   = ($i === $numberImage);
                $media   = $mediaAt($collection, $i);
                $fieldId = $input . '_' . $i;
                $boxH    = $isBig ? 180 : 120;
            @endphp

            <div class="card {{ $isBig ? 'col-md-12' : 'col-md-6' }} p-3 text-center mb-3 shadow-sm">

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
                    <input id="{{ $fieldId }}" name="{{ $input }}_file_{{ $i }}"
                           class="d-none form-control file-upload file_upload_ajax" type="file"
                           accept="image/*"/>
                    <label for="{{ $fieldId }}"
                           class="btn btn-primary custom-file-upload border border-1">
                        <i class="fa fa-cloud-upload"></i> انتخاب تصویر
                    </label>
                </div>

                <input type="hidden" id="{{ $fieldId }}_path" class="path" name="{{ $input }}[{{ $i }}][file]" value="">
                <div id="{{ $fieldId }}_src" class="show disInline"></div>

            </div>
        @endfor

    </div>
@endforeach

{{-- Legacy galleries-table fallback (read-only) — hidden once this owner has Spatie gallery media --}}
@include('Admin.partials.legacyGalleryFallback', [
    'model'       => $owner,
    'collections' => collect($sections)->pluck('collection')->all(),
])
