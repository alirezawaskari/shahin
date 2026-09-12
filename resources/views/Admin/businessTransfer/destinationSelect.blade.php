{{--
    فهرست کسب و کارهای مقصد برای «انتقال به کسب و کار».
    فقط کسب و کارهای فعال نمایش داده می‌شوند و کسب و کار فعلی رکورد غیرفعال است.

    پارامترها:
      $excludedBusinessIds : شناسه‌هایی که نباید قابل انتخاب باشند (کسب و کار فعلی رکورد)
      $selectClass         : کلاس‌های <select> (پیش‌فرض مثل بقیه‌ی فیلترهای فهرست)

    عمداً name ندارد؛ این فیلد داخل فرم‌های دیگرِ صفحه (مثل transfer_form) قرار
    می‌گیرد و نباید به درخواست آن فرم‌ها اضافه شود. مقدارش را businessTransfer.js
    می‌خواند و خودش فرم انتقال را می‌سازد.
--}}
@php
    $businessTransferOptions = \App\Models\GhorfeOnlineList::active()
        ->orderBy('title')
        ->get(['id', 'title']);

    $businessTransferExcluded = collect($excludedBusinessIds ?? [])
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<select class="{{ $selectClass ?? 'custom-select select2' }}"
        data-business-transfer-destination
        data-placeholder="انتخاب کسب و کار مقصد">
    <option value="">انتخاب کسب و کار مقصد</option>
    @foreach($businessTransferOptions as $business)
        <option value="{{ $business->id }}"
            @disabled(in_array((int) $business->id, $businessTransferExcluded, true))>
            {{ $business->title }}
        </option>
    @endforeach
</select>
