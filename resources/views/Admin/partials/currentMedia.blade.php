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

    The ✕ is ALSO hidden while a legacy column still holds this field's path — see
    AdoptsLegacyMedia::mediaIsRemovable(): the delete would silently undo itself (the
    accessor falls back to the legacy file, and the next save re-adopts it). Replacing
    always works, so nothing is lost by hiding it.

    Pairs with #{field}_src (temp preview) + #{field}_path (hidden temp id) and the
    global applySavedMedia() / remove_media_btn handlers in adminJs.js.
--}}
@php
    $__m = $owner->getFirstMedia($field); $__h = $boxH ?? 120; $__req = $required ?? false;
    // Not yet migrated to Spatie? fall back to the legacy image so it stays visible here.
    // Read-only (no ✕): it isn't Spatie media, so there is no media id to delete by.
    $__legacy = (! $__m && method_exists($owner, 'legacyMediaUrl')) ? $owner->legacyMediaUrl($field) : '';
    // deletable only when the delete would actually hold (nothing legacy left to resurrect)
    $__removable = ! $__req
        && (! method_exists($owner, 'mediaIsRemovable') || $owner->mediaIsRemovable($field));
@endphp
<div id="{{ $field }}_current" data-box-h="{{ $__h }}">
    @if($__m)
        <div class="div_remove_btn d-inline-block mb-2" id="media_{{ $__m->id }}">
            <img src="{{ $__m->getFullUrl() }}" class="rounded border"
                 style="height:{{ $__h }}px;width:100%;object-fit:contain;background:#f8f9fa;" alt="">
            @if($__removable)
            <a data-id="{{ $__m->id }}" title="حذف عکس"
               class="btn btn-sm remove_media_btn"><i class="fa fa-close"></i></a>
            @endif
        </div>
    @elseif($__legacy)
        <img src="{{ $__legacy }}" class="rounded border"
             style="height:{{ $__h }}px;width:100%;object-fit:contain;background:#f8f9fa;" alt="">
        <small class="d-block text-warning mt-1">تصویر قدیمی - با ذخیره به سیستم جدید منتقل می‌شود</small>
    @else
        <div class="rounded border d-flex align-items-center justify-content-center text-muted mb-2"
             style="height:{{ $__h }}px;background:#f8f9fa;">بدون تصویر</div>
    @endif
</div>
