{{--
    فیلد «کسب و کار» در فرم ویرایش یک رکورد — مثل بقیه‌ی فیلدها با همان دکمه‌ی
    «ثبت» فرم ذخیره می‌شود و دکمه یا پنجره‌ی جداگانه ندارد.

    تغییر مقدار این فیلد یعنی انتقال رکورد به کسب و کار انتخاب‌شده؛ کنترلر آن را
    با BusinessTransferService و در یک تراکنش انجام می‌دهد. بدون مجوز
    transfer_to_business فقط کسب و کار فعلی به صورت خواندنی دیده می‌شود.

    پارامترها:
      $currentBusinessIds : کسب و کار فعلی رکورد
      $wrapperClass       : کلاس ستون (پیش‌فرض مثل بقیه‌ی فیلدهای فرم)
      $label              : برچسب فیلد
--}}
@php
    $businessFieldCurrentId = collect($currentBusinessIds ?? [])
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->first();

    $businessFieldOptions = \App\Models\GhorfeOnlineList::active()
        ->orderBy('title')
        ->get(['id', 'title']);

    $businessFieldCurrent = $businessFieldCurrentId
        ? \App\Models\GhorfeOnlineList::find($businessFieldCurrentId)
        : null;

    // کسب و کار فعلی ممکن است غیرفعال شده باشد؛ باز هم باید به عنوان مقدار
    // فعلی در فهرست باشد وگرنه ذخیره‌ی فرم آن را به رکورد دیگری می‌برد.
    if ($businessFieldCurrent && ! $businessFieldOptions->contains('id', $businessFieldCurrent->id)) {
        $businessFieldOptions = $businessFieldOptions->push($businessFieldCurrent);
    }
@endphp

<div class="{{ $wrapperClass ?? 'col-12 col-md-4 mb-3' }}">
    <label>{{ $label ?? 'کسب و کار' }}</label>
    @permission('transfer_to_business')
        <select class="form-control select2" name="destination_business_id"
                data-business-transfer-field
                data-current-business-id="{{ $businessFieldCurrentId }}">
            <option value="">بدون کسب و کار</option>
            @foreach($businessFieldOptions as $business)
                <option value="{{ $business->id }}"
                    @selected((int) $business->id === (int) $businessFieldCurrentId)>
                    {{ $business->title }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">
            با تغییر این فیلد و ثبت فرم، رکورد به کسب و کار انتخاب‌شده منتقل می‌شود.
        </small>
    @else
        <input type="text" class="form-control" readonly
               value="{{ optional($businessFieldCurrent)->title ?: 'بدون کسب و کار' }}">
        <small class="text-muted">
            تغییر کسب و کار نیاز به مجوز «انتقال به کسب و کار» دارد.
        </small>
    @endpermission
</div>
