@php
    $businessTransferOptions = \App\Models\GhorfeOnlineList::active()
        ->orderBy('title')
        ->get(['id', 'title']);
@endphp

<div class="modal fade" id="business-transfer-modal" tabindex="-1" role="dialog"
     aria-labelledby="business-transfer-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form method="post" action="{{ route('businessTransfer.store') }}" id="business-transfer-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="business-transfer-modal-title">انتقال به کسب و کار</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"
                            aria-label="بستن">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_business_transfer" value="1">
                    <input type="hidden" name="entity" id="business-transfer-entity">
                    <div id="business-transfer-record-inputs"></div>

                    <p class="text-muted" id="business-transfer-selection-summary"></p>
                    <div class="form-group mb-0">
                        <label for="business-transfer-destination">کسب و کار مقصد</label>
                        <select name="destination_business_id" id="business-transfer-destination"
                                class="form-control select2" required>
                            <option value="">انتخاب کسب و کار فعال</option>
                            @foreach($businessTransferOptions as $business)
                                <option value="{{ $business->id }}">{{ $business->title }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            کسب و کار فعلی رکوردهای انتخاب شده از فهرست مقصد حذف می‌شود.
                        </small>
                        <div class="alert alert-warning mt-3 mb-0 d-none" id="business-transfer-no-destination">
                            کسب و کار فعال دیگری برای انتقال وجود ندارد.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal" data-bs-dismiss="modal">
                        انصراف
                    </button>
                    <button type="submit" class="btn btn-primary" id="business-transfer-submit">
                        انتقال به کسب و کار
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
