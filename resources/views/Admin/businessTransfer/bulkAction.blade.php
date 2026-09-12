{{--
    نوار عملیات گروهی «انتقال به کسب و کار» برای فهرست‌هایی که نوار انتقال
    ندارند. دقیقاً همان الگوی فهرست پرونده‌ها است: یک «ارجاع به»، فهرست مقصد
    و یک دکمه‌ی «انتقال».

    پارامترها:
      $entity         : نوع رکورد (factor, factor_item, user, employee, callcenter)
      $recordSelector : گزینشگر چک‌باکس‌های ردیف‌های فهرست
--}}
@permission('transfer_to_business')
    <div class="card">
        <div class="card-body">
            <div class="row"
                 data-business-transfer-scope
                 data-transfer-entity="{{ $entity }}"
                 data-record-selector="{{ $recordSelector ?? '.business-transfer-record' }}">
                <div class="col-12 col-sm-6 col-lg-2 mb-2">
                    <select class="custom-select select2" data-business-transfer-type-select>
                        <option value="">ارجاع به</option>
                        <option value="business">کسب و کار</option>
                    </select>
                </div>
                @include('Admin.businessTransfer.destinationBox')
                <div class="col-12 col-sm-6 col-lg-2 mb-2">
                    <button type="button" class="btn btn-outline-primary btn-block py-2"
                            data-business-transfer-submit>
                        انتقال
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpermission
