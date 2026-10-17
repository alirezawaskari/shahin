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
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header d-flex justify-content-between align-items-center bg-light border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div style="width: 36px; height: 36px; border-radius: 8px; background: #3b82f6; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fa fa-filter"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 fs-6" id="requestFilterModalLabel">
                            فیلتر مخاطبان بر اساس درخواست‌ها
                        </h5>
                        <small class="text-muted">مخاطبان این پیامک از روی پرونده‌ها و درخواست‌های منطبق با این فیلترها انتخاب می‌شوند.</small>
                    </div>
                </div>
                <button type="button" class="btn-close m-0" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body p-4">
                <form id="request_filter_form" onsubmit="return false;">
                    <div class="row">
                        @include('Admin.callcenter.partials.filter-fields', ['withBusinessPicker' => false])
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top py-3 px-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-danger btn-sm" id="request_filter_clear">
                    <i class="fa fa-trash-o ms-1"></i> پاکسازی فیلترها
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary btn-sm px-4 fw-bold" id="request_filter_apply">
                        <i class="fa fa-check ms-1"></i> اعمال فیلتر و شمارش
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
