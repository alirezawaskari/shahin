{{--
    Shared SMS message editor (single send, bulk send, legacy group send).

    Params:
      $id            textarea id                         (default: messageTextarea)
      $name          textarea name                       (default: message)
      $value         initial text                        (default: '')
      $editorLabel   field label                         (default: متن پیامک; deliberately NOT `$label`, which leaks from caller loops)
      $required      bool                                (default: false)
      $rows          int                                 (default: 6)
      $placeholder   string
      $costPerPage   float|null  tariff per SMS page, from config('services.sms.cost_per_page')
      $advertising   bool        count the mandatory «لغو 11» opt-out (default: true)
      $chips         array of tokens for the dynamic-parameter palette ([] = no palette)
      $costLabel     label of the per-message cost stat   (default: هزینه هر پیامک)

    JS: public/assets/dashboard/js/sms-message-editor.js  (SmsMessageEditor.get('<id>')).
--}}
@php
    $id = $id ?? 'messageTextarea';
    $name = $name ?? 'message';
    $value = $value ?? '';
    $editorLabel = $editorLabel ?? 'متن پیامک';
    $required = $required ?? false;
    $rows = $rows ?? 6;
    $placeholder = $placeholder ?? 'متن پیام خود را اینجا بنویسید...';
    $costPerPage = $costPerPage ?? (float) config('services.sms.cost_per_page', 1200);
    $advertising = $advertising ?? true;
    $chips = $chips ?? [];
    $costLabel = $costLabel ?? 'هزینه هر پیامک';
@endphp
<div class="sms-editor" data-sms-editor data-for="{{ $id }}" data-cost-per-page="{{ $costPerPage }}" data-advertising="{{ $advertising ? '1' : '0' }}">
    <label class="sms-label" for="{{ $id }}">{{ $editorLabel }} @if($required)<span class="text-danger">*</span>@endif</label>

    @if(!empty($chips))
        <div class="sms-editor-chips">
            <span class="sms-editor-chips-title">متغیرهای پویا (با کلیک در متن درج می‌شوند):</span>
            @foreach($chips as $token)
                <span class="badge bg-secondary placeholder-chip" data-token="{{ $token }}">{{ $token }}</span>
            @endforeach
        </div>
    @endif

    <div class="sms-editor-box">
        <div class="sms-editor-field">
            <textarea name="{{ $name }}" id="{{ $id }}" rows="{{ $rows }}" class="sms-editor-input" placeholder="{{ $placeholder }}" @if($required) required @endif>{{ $value }}</textarea>
            {{-- Live page count, pinned to the physical bottom-left corner inside the text area. --}}
            <span class="sms-editor-pagebadge" data-sms-counter="badge" aria-live="polite">۱ صفحه</span>
        </div>

        {{-- Locked, read-only part of the final text (mandatory opt-out, advertising only). Not editable. --}}
        <div class="sms-editor-suffix" data-sms-counter="suffix" aria-hidden="true" @if(!$advertising) style="display: none;" @endif>
            <i class="fa fa-lock"></i>
            <span dir="ltr">لغو 11</span>
            <span class="sms-editor-suffix-note">خودکار و غیرقابل حذف</span>
        </div>

        <div class="sms-editor-footer" data-sms-counter="bar" aria-live="polite">
            <span class="sms-stat">کاراکتر: <strong data-sms-counter="used">۰</strong> <span class="text-muted" data-sms-counter="suffixnote" @if(!$advertising) style="display: none;" @endif>(شامل لغو 11)</span></span>
            <span class="sms-stat">باقی‌مانده تا صفحه بعد: <strong data-sms-counter="remaining">۷۰</strong></span>
            <span class="sms-stat">تعداد صفحه: <strong data-sms-counter="pages">۱</strong></span>
            <span class="sms-stat">کدگذاری: <strong data-sms-counter="encoding">فارسی (Unicode)</strong> <small class="text-muted" data-sms-counter="encodinghint">(متن فارسی: هر صفحه ۷۰ کاراکتر)</small></span>
            <span class="sms-stat sms-stat-end">تعرفه هر صفحه: <strong data-sms-counter="tariff">{{ number_format($costPerPage) }}</strong> ریال</span>
            <span class="sms-stat">{{ $costLabel }}: <strong data-sms-counter="cost">۰</strong> ریال</span>
        </div>
    </div>

    <div class="sms-editor-note" data-sms-counter="optout" @if(!$advertising) style="display: none;" @endif id="{{ $id }}OptOutNote">
        <span>«لغو 11» اجباری است و در شمارش کاراکتر و صفحات لحاظ می‌شود.</span>
    </div>
</div>
