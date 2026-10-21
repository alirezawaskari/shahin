{{-- Forward («ارجاع») a logged message to another number and send it right away. --}}
<div class="modal fade" id="forwardDirectModal" tabindex="-1" aria-labelledby="forwardDirectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="forwardDirectModalLabel"><i class="fa fa-share me-2 text-primary"></i> ارجاع پیامک به مخاطب دیگر</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <form id="forwardForm" method="POST" action="{{ route('admin.sms.single.forward') }}">
                @csrf
                <input type="hidden" name="original_message_id" id="forwardOriginalMsgId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">متن پیامک اصلی</label>
                        <div id="forwardMsgContent" class="p-2 bg-light border rounded small" style="max-height: 120px; overflow-y: auto;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">شماره گیرنده ارجاع <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_phone" id="forwardRecipientPhone" class="form-control font-monospace text-start" required placeholder="09xxxxxxxxx">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ارجاع‌دهنده</label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user() ? (auth()->user()->name . ' ' . auth()->user()->family) : 'کاربر جاری' }}" readonly>
                        <input type="hidden" name="referrer_id" value="{{ auth()->id() }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">تاریخ و زمان ارجاع</label>
                        <input type="text" name="referred_at" class="form-control bg-light" value="{{ now()->format('Y-m-d H:i:s') }}" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                    <button type="submit" class="btn btn-primary">تایید و ارجاع پیامک</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.forward-msg-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var isRedacted = this.getAttribute('data-msg-redacted') === '1';
            document.getElementById('forwardOriginalMsgId').value = this.getAttribute('data-msg-id');
            document.getElementById('forwardMsgContent').textContent = isRedacted ? 'محرمانه - دسترسی محدود' : this.getAttribute('data-msg-content');
        });
    });
});
</script>
