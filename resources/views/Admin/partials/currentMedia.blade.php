{{--
    Current image of a single-file Spatie collection, with an in-place remove (✕)
    button and a "بدون تصویر" placeholder. Reusable across models.

    Params:
      $owner    HasMedia model (e.g. $service, $category)
      $field    collection name == DOM field id (e.g. 'image', 'icon', 'thumb')
      $boxH     display box height in px (optional, default 120)
      $required optional bool; when true the standalone remove (✕) button is hidden so a
                required image can only be replaced (uploading a new one), never deleted to
                empty — the remove button deletes immediately, which would bypass validation.

    Pairs with #{field}_src (temp preview) + #{field}_path (hidden temp id) and the
    global applySavedMedia() / remove_media_btn handlers in adminJs.js.
--}}
@php $__m = $owner->getFirstMedia($field); $__h = $boxH ?? 120; $__req = $required ?? false; @endphp
<div id="{{ $field }}_current" data-box-h="{{ $__h }}">
    @if($__m)
        <div class="div_remove_btn d-inline-block mb-2" id="media_{{ $__m->id }}">
            <img src="{{ $__m->getFullUrl() }}" class="rounded border"
                 style="height:{{ $__h }}px;width:100%;object-fit:contain;background:#f8f9fa;" alt="">
            @unless($__req)
            <a data-id="{{ $__m->id }}" title="حذف عکس"
               class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>
            @endunless
        </div>
    @else
        <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2"
             style="height:{{ $__h }}px;background:#f8f9fa;">بدون تصویر</div>
    @endif
</div>
