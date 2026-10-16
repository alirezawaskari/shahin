{{--
    «ارسال پیامک به نتیجه فیلتر» — sends one SMS campaign to everyone behind the requests that
    match the filter form currently on the page (ALL matching requests, not just the visible
    page). It uses the group-message endpoints, so tier gates, balance checks, opt-out
    exclusion and campaign tracking are exactly those of group sending.

    Data comes from App\View\Composers\SmsToFilterComposer. Driven by requestFilterSms.js.
--}}
@if($smsCanSend)
<div class="modal fade" id="filterSmsModal" tabindex="-1" aria-labelledby="filterSmsModalLabel" aria-hidden="true"
     data-preview-url="{{ route('admin.sms.bulk.preview') }}"
     data-send-url="{{ route('admin.sms.bulk.send') }}"
     data-business-id="{{ $smsBusiness->id }}">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterSmsModalLabel">
                    <i class="fa fa-envelope-o ms-1"></i> ارسال پیامک به نتیجه فیلتر
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="بستن"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 small">
                    پیامک برای <strong>همه درخواست‌های منطبق با فیلتر فعلی</strong> (نه فقط صفحه‌ی جاری) ارسال می‌شود.
                    درخواست‌هایی که «انصراف از دریافت پیامک» دارند هرگز پیامک دریافت نمی‌کنند.
                    <div class="mt-1">کسب‌وکار فرستنده: <strong>{{ $smsBusiness->title }}</strong></div>
                </div>

                <div id="fsms_alert" class="alert d-none py-2 small" role="alert"></div>

                @if($smsSenders->isEmpty())
                    <div class="alert alert-warning mb-0">هیچ خط تبلیغاتی فعالی به این کسب‌وکار تخصیص داده نشده است.</div>
                @else
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold" for="fsms_sender">خط فرستنده</label>
                        <select id="fsms_sender" class="form-select form-select-sm" data-no-select2>
                            @foreach($smsSenders as $sender)
                                <option value="{{ $sender->id }}">{{ $sender->number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold" for="fsms_template">قالب پیامک (اختیاری)</label>
                        <select id="fsms_template" class="form-select form-select-sm" data-no-select2>
                            <option value="">— بدون قالب —</option>
                            @foreach($smsTemplates as $template)
                                <option value="{{ $template->id }}" data-content="{{ $template->content }}">{{ $template->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold" for="fsms_message">متن پیامک</label>
                        <textarea id="fsms_message" class="form-control" rows="4" placeholder="متن پیامک را بنویسید..."></textarea>
                        <div class="form-text">عبارت «لغو 11» به‌صورت خودکار به انتهای پیامک اضافه می‌شود.</div>
                    </div>
                </div>

                <div id="fsms_summary" class="border rounded p-3 mt-3 bg-light d-none">
                    <div class="row text-center g-2">
                        <div class="col-6 col-md-3"><div class="text-muted small">مخاطب معتبر</div><div class="fw-bold" id="fsms_valid">—</div></div>
                        <div class="col-6 col-md-3"><div class="text-muted small">نامعتبر</div><div class="fw-bold" id="fsms_invalid">—</div></div>
                        <div class="col-6 col-md-3"><div class="text-muted small">تکراری (حذف‌شده)</div><div class="fw-bold" id="fsms_dup">—</div></div>
                        <div class="col-6 col-md-3"><div class="text-muted small">تعداد صفحه‌ی پیامک</div><div class="fw-bold" id="fsms_pages">—</div></div>
                    </div>
                    <hr class="my-2">
                    <div class="row text-center g-2">
                        <div class="col-md-4"><div class="text-muted small">هزینه‌ی کل</div><div class="fw-bold" id="fsms_cost">—</div></div>
                        <div class="col-md-4"><div class="text-muted small">موجودی پنل تبلیغاتی</div><div class="fw-bold" id="fsms_balance">{{ number_format($smsBalance) }} ریال</div></div>
                        <div class="col-md-4"><div class="text-muted small">وضعیت موجودی</div><div class="fw-bold" id="fsms_enough">—</div></div>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">بستن</button>
                @if($smsSenders->isNotEmpty())
                    <button type="button" class="btn btn-outline-primary" id="fsms_preview">بررسی و محاسبه هزینه</button>
                    <button type="button" class="btn btn-success" id="fsms_send" disabled>
                        <i class="fa fa-paper-plane ms-1"></i> ارسال پیامک
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
