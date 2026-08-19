{{--
    Shared body of the "افزودن آدرس" and "ویرایش آدرس" modals.

    Every modal on the page carries its own province/city pair, so the selects are keyed by
    class (.address-province / .address-city) instead of the page-wide #province_id handler
    in layouts.js - productOrderShow.js fills the sibling city select of whichever province
    changed. On an edit modal the city list is rendered server-side already, so the current
    value survives without an ajax round trip.
--}}
@php
    $address = $address ?? null;
    $cities = $address ? \App\Models\City::where('province_id', $address->province_id)->orderBy('name')->get() : collect();
@endphp

<div class="form-row">
    <div class="col-12 col-md-6 mb-3">
        <label>نام گیرنده</label>
        <input type="text" name="recipient_name" class="form-control" required
            value="{{ old('recipient_name', optional($address)->recipient_name) }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label>نام خانوادگی گیرنده</label>
        <input type="text" name="recipient_family" class="form-control" required
            value="{{ old('recipient_family', optional($address)->recipient_family) }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label>موبایل گیرنده</label>
        <input type="text" name="recipient_mobile" class="form-control" required
            value="{{ old('recipient_mobile', optional($address)->recipient_mobile) }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label>کدپستی</label>
        <input type="text" name="postal_code" class="form-control" required
            value="{{ old('postal_code', optional($address)->postal_code) }}">
    </div>
    <div class="col-12 col-md-4 mb-3">
        <label>استان</label>
        <select name="province_id" class="custom-select address-province" required>
            <option value="">انتخاب استان</option>
            @foreach($provinces as $province)
            <option value="{{ $province->id }}" {{ (int) optional($address)->province_id === (int) $province->id ? 'selected' : '' }}>
                {{ $province->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <label>شهر</label>
        <select name="city_id" class="custom-select address-city" required>
            <option value="">انتخاب شهر</option>
            @foreach($cities as $city)
            <option value="{{ $city->id }}" {{ (int) optional($address)->city_id === (int) $city->id ? 'selected' : '' }}>
                {{ $city->name }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-md-4 mb-3">
        <label>منطقه (اختیاری)</label>
        <select name="area_id" class="custom-select">
            <option value="">انتخاب منطقه</option>
            @foreach($areas as $area)
            <option value="{{ $area->id }}" {{ (int) optional($address)->area_id === (int) $area->id ? 'selected' : '' }}>
                {{ $area->name ?: $area->number }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-12 mb-3">
        <label>آدرس</label>
        <textarea name="address" rows="3" class="form-control" required>{{ old('address', optional($address)->address) }}</textarea>
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label>پلاک</label>
        <input type="number" name="plaque" class="form-control" required
            value="{{ old('plaque', optional($address)->plaque) }}">
    </div>
    <div class="col-12 col-md-6 mb-3">
        <label>واحد (اختیاری)</label>
        <input type="number" name="unit" class="form-control" value="{{ old('unit', optional($address)->unit) }}">
    </div>
</div>
