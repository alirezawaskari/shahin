{{--
    «فیلتر بر اساس درخواست‌ها» — a pop-up holding the requests page's filter section (the very
    same Admin.callcenter.partials.filter-fields partial, so it stays an exact clone).

    Applying it stores the criteria as hidden filters[request_filter][...] inputs inside the
    group-message form (see requestFilterPicker.js); the audience then becomes "the people behind
    the requests matching these filters". Place this OUTSIDE the form it feeds, so its own
    fields are never submitted directly.

    Data comes from FilterServiceProvider (tasks_requires()).
--}}
<div class="modal fade" id="requestFilterModal" tabindex="-1" aria-labelledby="requestFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requestFilterModalLabel">
                    <i class="fa fa-filter ms-1"></i> فیلتر مخاطبان بر اساس درخواست‌ها
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">
                    همان فیلترهای صفحه‌ی «درخواست‌ها». مخاطبان پیامک، افراد پشت درخواست‌های منطبق با این فیلترها در همین
                    کسب‌وکار خواهند بود. درخواست‌هایی که «انصراف از دریافت پیامک» دارند هرگز پیامک نمی‌گیرند.
                </p>
                <form id="request_filter_form" onsubmit="return false;">
                    <div class="row">
                        @include('Admin.callcenter.partials.filter-fields', ['withBusinessPicker' => false])
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-outline-danger" id="request_filter_clear">حذف فیلتر درخواست‌ها</button>
                <button type="button" class="btn btn-primary" id="request_filter_apply">
                    <i class="fa fa-check ms-1"></i> اعمال فیلتر
                </button>
            </div>
        </div>
    </div>
</div>
