{{--
    شرایط خرید کالا — همان کادر کنار گالری در صفحه تک محصول سایت (نقد، اقساط، روش‌های ارسال).

    ورودی‌ها:
      $product          — کالا (در فرم ویرایش)، یا null در فرم ثبت
      $shipping_methods — روش‌های ارسال تعریف‌شده کسب‌وکار (تب «تنظیمات مالی» ویرایش کسب‌وکار)
--}}
@php
    $product = $product ?? null;
    $shipping_methods = $shipping_methods ?? collect();
    $cash_purchase = old('purchase_info.cash_purchase', $product ? (int) $product->cash_purchase : 1);
    $installment_purchase = old('purchase_info.installment_purchase', $product ? (int) $product->installment_purchase : 0);
    $selected_shipping = array_map('intval', old('purchase_info.shipping_method_ids',
        $product ? $product->shippingMethods->pluck('id')->all() : []));
@endphp

<div class="col-12 mb-3">
    <hr />
    <h6 class="font-weight-bold mb-1">شرایط خرید (صفحه تک محصول)</h6>
    <p class="text-muted small mb-3">این اطلاعات در کادر کنار تصویر کالا در صفحه تک محصول سایت نمایش داده می‌شود.</p>
    {{-- چک‌باکس تیک‌نخورده چیزی ارسال نمی‌کند؛ این فیلد نشان می‌دهد بخش در فرم بوده است --}}
    <input type="hidden" name="purchase_info[present]" value="1">

    <div class="row">
        <div class="col-12 col-md-6 mb-3">
            <div class="checkbox mb-2">
                <label>
                    <input type="checkbox" name="purchase_info[cash_purchase]" value="1" @if($cash_purchase) checked @endif>
                    امکان خرید نقدی
                </label>
            </div>
            <input type="text" name="purchase_info[cash_purchase_note]" class="form-control" maxlength="500"
                   placeholder="مثال: ۵٪ تخفیف ویژه خرید نقدی"
                   value="{{ old('purchase_info.cash_purchase_note', optional($product)->cash_purchase_note) }}">
        </div>

        <div class="col-12 col-md-6 mb-3">
            <div class="checkbox mb-2">
                <label>
                    <input type="checkbox" name="purchase_info[installment_purchase]" value="1" @if($installment_purchase) checked @endif>
                    امکان خرید اقساطی
                </label>
            </div>
            <input type="text" name="purchase_info[installment_purchase_note]" class="form-control" maxlength="500"
                   placeholder="مثال: اقساط ۳ تا ۱۲ ماهه با پیش‌پرداخت ۳۰٪"
                   value="{{ old('purchase_info.installment_purchase_note', optional($product)->installment_purchase_note) }}">
        </div>

        <div class="col-12 mb-3">
            <label class="font-weight-bold">روش‌های ارسال</label>
            @if($shipping_methods->isEmpty())
                <p class="text-muted small mb-0">
                    هنوز روش ارسالی تعریف نشده است. روش‌های ارسال را از ویرایش کسب‌وکار، تب «تنظیمات مالی» اضافه کنید.
                </p>
            @else
                <select name="purchase_info[shipping_method_ids][]" class="form-control" multiple
                        data-placeholder="انتخاب روش‌های ارسال">
                    @foreach($shipping_methods as $shipping_method)
                        <option value="{{ $shipping_method->id }}" @if(in_array($shipping_method->id, $selected_shipping, true)) selected @endif>
                            {{ $shipping_method->title }} — {{ $shipping_method->price ? number_format($shipping_method->price) . ' ریال' : 'رایگان' }}
                        </option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>
</div>
