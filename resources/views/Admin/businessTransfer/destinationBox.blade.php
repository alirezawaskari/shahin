{{--
    ستون «کسب و کار مقصد» برای نوارهای انتقالِ فهرست‌هایی که از قبل یک
    «ارجاع به» و دکمه‌ی «انتقال» دارند (پرونده‌ها، کالسنتر).
    تا وقتی «کسب و کار» در «ارجاع به» انتخاب نشود پنهان است.
--}}
@permission('transfer_to_business')
    <div class="{{ $boxClass ?? 'col-12 col-sm-6 col-lg-2 mb-2' }} d-none" data-business-transfer-box>
        @include('Admin.businessTransfer.destinationSelect', [
            'excludedBusinessIds' => $excludedBusinessIds ?? [],
        ])
    </div>
@endpermission
