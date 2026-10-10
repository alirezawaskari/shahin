@extends('Admin.master')

@section('title', 'لاگ پیامک‌های ارسالی و دریافتی')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">لاگ پیامک‌های ارسالی و دریافتی</h3>
            <p class="text-muted small mb-0">مشاهده جامع تمام پیام‌های خروجی و ورودی، فیلتر پیشرفته بر اساس وضعیت، جهت، شماره، گروه و تاریخ</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($business)
                <span class="badge badge-info px-3 py-2">
                    <i class="fa fa-building-o me-1"></i> کسب‌وکار: {{ $business->title }}
                </span>
            @endif
        </div>
    </div>

    @include('Admin.sms.logs.navigation')

    <!-- KPI Summary Cards -->
    <div class="row mb-4">
        <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <span class="text-muted small">کل پیامک‌ها</span>
                <h4 class="fw-bold mb-0 text-primary mt-1">{{ number_format($summary['total']) }}</h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <span class="text-muted small">پیامک‌های ارسالی</span>
                <h4 class="fw-bold mb-0 text-success mt-1">{{ number_format($summary['outbound']) }}</h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <span class="text-muted small">پیامک‌های دریافتی</span>
                <h4 class="fw-bold mb-0 text-info mt-1">{{ number_format($summary['inbound']) }}</h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <span class="text-muted small">تحویل موفق</span>
                <h4 class="fw-bold mb-0 text-success mt-1">{{ number_format($summary['delivered']) }}</h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <span class="text-muted small">ناموفق / خطا</span>
                <h4 class="fw-bold mb-0 text-danger mt-1">{{ number_format($summary['failed']) }}</h4>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4 col-xl-2 mb-2">
            <div class="card border-0 shadow-sm text-center p-3">
                <span class="text-muted small">مجموع هزینه</span>
                <h4 class="fw-bold mb-0 text-dark mt-1">{{ number_format($summary['total_cost']) }} <small class="text-muted small">ریال</small></h4>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fa fa-filter me-2 text-primary"></i> فیلترهای جستجو</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.sms.logs.index') }}" method="GET">
                <div class="row">
                    <!-- Direction -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">جهت پیام</label>
                        <select name="direction" class="form-control form-control-sm">
                            <option value="all" {{ ($filters['direction'] ?? '') == 'all' ? 'selected' : '' }}>همه (ارسالی و دریافتی)</option>
                            <option value="outbound" {{ ($filters['direction'] ?? '') == 'outbound' ? 'selected' : '' }}>خروجی (ارسالی)</option>
                            <option value="inbound" {{ ($filters['direction'] ?? '') == 'inbound' ? 'selected' : '' }}>ورودی (دریافتی)</option>
                        </select>
                    </div>

                    <!-- Service Type -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">نوع سرویس</label>
                        <select name="service_type" class="form-control form-control-sm">
                            <option value="all" {{ ($filters['service_type'] ?? '') == 'all' ? 'selected' : '' }}>همه سرویس‌ها</option>
                            <option value="otp" {{ ($filters['service_type'] ?? '') == 'otp' ? 'selected' : '' }}>اعتبارسنجی (OTP)</option>
                            <option value="advertising" {{ ($filters['service_type'] ?? '') == 'advertising' ? 'selected' : '' }}>تبلیغاتی</option>
                            <option value="normal" {{ ($filters['service_type'] ?? '') == 'normal' ? 'selected' : '' }}>عادی / اطلاع‌رسانی</option>
                        </select>
                    </div>

                    <!-- Single / Bulk -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">نحوه ارسال</label>
                        <select name="is_bulk" class="form-control form-control-sm">
                            <option value="all" {{ ($filters['is_bulk'] ?? '') == 'all' ? 'selected' : '' }}>همه</option>
                            <option value="0" {{ ($filters['is_bulk'] ?? '') === '0' ? 'selected' : '' }}>ارسال تکی</option>
                            <option value="1" {{ ($filters['is_bulk'] ?? '') === '1' ? 'selected' : '' }}>ارسال انبوه / کمپین</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">وضعیت</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="all" {{ ($filters['status'] ?? '') == 'all' ? 'selected' : '' }}>همه وضعیت‌ها</option>
                            @foreach($statuses as $stKey => $stTitle)
                                <option value="{{ $stKey }}" {{ ($filters['status'] ?? '') == $stKey ? 'selected' : '' }}>{{ $stTitle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sender Number -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">سرشماره پیامک</label>
                        <select name="sender_number_id" class="form-control form-control-sm">
                            <option value="all">همه خطوط</option>
                            @foreach($senderNumbers as $sNum)
                                <option value="{{ $sNum->id }}" {{ ($filters['sender_number_id'] ?? '') == $sNum->id ? 'selected' : '' }}>
                                    {{ $sNum->number }} ({{ $sNum->type }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Message Group -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">گروه پیامک</label>
                        <select name="sms_message_group_id" class="form-control form-control-sm">
                            <option value="all">همه گروه‌ها</option>
                            @foreach($groups as $grp)
                                <option value="{{ $grp->id }}" {{ ($filters['sms_message_group_id'] ?? '') == $grp->id ? 'selected' : '' }}>
                                    📁 {{ $grp->name }}
                                </option>
                                @foreach($grp->subgroups as $sub)
                                    <option value="{{ $sub->id }}" {{ ($filters['sms_message_group_id'] ?? '') == $sub->id ? 'selected' : '' }}>
                                        &nbsp;&nbsp;↳ {{ $sub->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    @if($isSuperAdmin && $businesses->count() > 1)
                        <!-- Business (Super Admin only) -->
                        <div class="col-12 col-sm-6 col-md-3 mb-3">
                            <label class="form-label small fw-bold">کسب‌وکار</label>
                            <select name="business_id" class="form-control form-control-sm">
                                <option value="all">همه کسب‌وکارها</option>
                                @foreach($businesses as $b)
                                    <option value="{{ $b->id }}" {{ ($filters['business_id'] ?? '') == $b->id ? 'selected' : '' }}>
                                        {{ $b->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Recipient Phone -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">شماره گیرنده</label>
                        <input type="text" name="recipient_phone" class="form-control form-control-sm" placeholder="مثال: 09123456789" value="{{ $filters['recipient_phone'] ?? '' }}">
                    </div>

                    <!-- Sender Phone -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">شماره فرستنده</label>
                        <input type="text" name="sender_phone" class="form-control form-control-sm" placeholder="مثال: 3000..." value="{{ $filters['sender_phone'] ?? '' }}">
                    </div>

                    <!-- Date From -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">از تاریخ</label>
                        <input type="text" name="date_from" class="form-control form-control-sm" placeholder="YYYY/MM/DD یا YYYY-MM-DD" value="{{ $filters['date_from'] ?? '' }}">
                    </div>

                    <!-- Date To -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">تا تاریخ</label>
                        <input type="text" name="date_to" class="form-control form-control-sm" placeholder="YYYY/MM/DD یا YYYY-MM-DD" value="{{ $filters['date_to'] ?? '' }}">
                    </div>

                    <!-- General Search -->
                    <div class="col-12 col-sm-6 col-md-3 mb-3">
                        <label class="form-label small fw-bold">جستجو در متن / شناسه</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="متن پیامک یا شناسه رهگیری" value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-2">
                    <a href="{{ route('admin.sms.logs.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa fa-refresh me-1"></i> پاکسازی فیلترها
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-search me-1"></i> اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fa fa-envelope-o me-2 text-primary"></i> لیست پیامک‌ها ({{ number_format($messages->total()) }} مورد)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 60px;">#</th>
                            <th scope="col">جهت</th>
                            <th scope="col">نوع</th>
                            <th scope="col">فرستنده</th>
                            <th scope="col">گیرنده</th>
                            @if($isSuperAdmin)
                                <th scope="col">کسب‌وکار</th>
                            @endif
                            <th scope="col">گروه</th>
                            <th scope="col" class="text-right" style="min-width: 250px;">متن پیام</th>
                            <th scope="col">وضعیت</th>
                            <th scope="col">تاریخ و زمان</th>
                            <th scope="col">شناسه رهگیری</th>
                            <th scope="col">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                            <tr>
                                <td>{{ $msg->id }}</td>
                                <td>
                                    @if($msg->direction === 'inbound')
                                        <span class="badge badge-info px-2 py-1"><i class="fa fa-arrow-down me-1"></i> دریافتی</span>
                                    @else
                                        <span class="badge badge-success px-2 py-1"><i class="fa fa-arrow-up me-1"></i> ارسالی</span>
                                    @endif
                                </td>
                                <td>
                                    @if($msg->service_type === 'otp')
                                        <span class="badge badge-warning text-dark px-2 py-1">OTP</span>
                                    @elseif($msg->service_type === 'advertising')
                                        <span class="badge badge-primary px-2 py-1">تبلیغاتی</span>
                                    @else
                                        <span class="badge badge-secondary px-2 py-1">عادی</span>
                                    @endif
                                    @if($msg->is_bulk)
                                        <span class="badge badge-dark px-1 py-1 small" title="ارسال انبوه">انبوه</span>
                                    @endif
                                </td>
                                <td style="direction: ltr;">{{ $msg->sender_phone ?: ($msg->senderNumber?->number ?: 'سیستم') }}</td>
                                <td style="direction: ltr;">
                                    {{ $msg->recipient_phone ?: '-' }}
                                    @if($msg->user)
                                        <br><small class="text-muted">({{ $msg->user->name }} {{ $msg->user->family }})</small>
                                    @endif
                                </td>
                                @if($isSuperAdmin)
                                    <td>{{ $msg->business?->title ?: 'عمومی' }}</td>
                                @endif
                                <td>{{ $msg->group?->name ?: '-' }}</td>
                                <td class="text-right" style="direction: rtl; font-size: 0.9rem;">
                                    @if($msg->content_redacted ?? false)
                                        <span class="text-muted"><i class="fa fa-lock me-1"></i> محرمانه - دسترسی محدود</span>
                                    @else
                                        {{ Str::limit($msg->content, 65) }}
                                    @endif
                                </td>
                                <td>
                                    @if($msg->status === 'delivered' || $msg->delivery_status === 'delivered')
                                        <span class="badge badge-success px-2 py-1"><i class="fa fa-check me-1"></i> تحویل شد</span>
                                    @elseif($msg->status === 'sent')
                                        <span class="badge badge-info px-2 py-1"><i class="fa fa-paper-plane me-1"></i> ارسال شد</span>
                                    @elseif($msg->status === 'failed')
                                        <span class="badge badge-danger px-2 py-1" title="{{ $msg->error_reason }}"><i class="fa fa-times me-1"></i> ناموفق</span>
                                    @else
                                        <span class="badge badge-warning text-dark px-2 py-1"><i class="fa fa-clock-o me-1"></i> {{ $msg->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ verta($msg->created_at)->format('Y/m/d H:i') }}</small>
                                </td>
                                <td style="direction: ltr; font-family: monospace;">
                                    <small>{{ $msg->tracking_id ?: '-' }}</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-info btn-sm view-details-btn" 
                                            data-id="{{ $msg->id }}"
                                            data-content="{{ ($msg->content_redacted ?? false) ? 'محرمانه - دسترسی محدود' : e($msg->content) }}"
                                            data-sender="{{ $msg->sender_phone }}"
                                            data-recipient="{{ $msg->recipient_phone }}"
                                            data-direction="{{ $msg->direction === 'inbound' ? 'دریافتی' : 'ارسالی' }}"
                                            data-service="{{ $msg->service_type }}"
                                            data-status="{{ $msg->status }}"
                                            data-delivery="{{ $msg->delivery_status ?: '-' }}"
                                            data-cost="{{ number_format($msg->cost ?? 0) }}"
                                            data-pages="{{ $msg->page_count }}"
                                            data-chars="{{ $msg->character_count }}"
                                            data-tracking="{{ $msg->tracking_id ?: '-' }}"
                                            data-date="{{ verta($msg->created_at)->format('Y/m/d H:i:s') }}"
                                            data-delivered-at="{{ $msg->delivered_at ? verta($msg->delivered_at)->format('Y/m/d H:i:s') : '-' }}"
                                            data-error="{{ e($msg->error_reason ?: '') }}"
                                            data-request-id="{{ $msg->request_id ?: '' }}"
                                            data-toggle="modal" data-target="#messageDetailModal">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 12 : 11 }}" class="py-4 text-muted">
                                    <i class="fa fa-inbox fa-3x mb-2 text-muted"></i>
                                    <p class="mb-0">هیچ پیامکی با فیلترهای مشخص‌شده یافت نشد.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($messages->hasPages())
            <div class="card-footer bg-white py-3 d-flex justify-content-center">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="messageDetailModal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel"><i class="fa fa-info-circle me-1"></i> جزئیات کامل پیامک</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6 col-md-3"><strong>جهت:</strong> <span id="mDirection"></span></div>
                    <div class="col-6 col-md-3"><strong>نوع:</strong> <span id="mService"></span></div>
                    <div class="col-6 col-md-3"><strong>وضعیت کلی:</strong> <span id="mStatus"></span></div>
                    <div class="col-6 col-md-3"><strong>وضعیت تحویل:</strong> <span id="mDelivery"></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6 col-md-3"><strong>فرستنده:</strong> <span id="mSender" style="direction: ltr;"></span></div>
                    <div class="col-6 col-md-3"><strong>گیرنده:</strong> <span id="mRecipient" style="direction: ltr;"></span></div>
                    <div class="col-6 col-md-3"><strong>تعداد صفحات:</strong> <span id="mPages"></span></div>
                    <div class="col-6 col-md-3"><strong>تعداد کاراکتر:</strong> <span id="mChars"></span></div>
                </div>
                <div class="row mb-3">
                    <div class="col-6 col-md-3"><strong>هزینه:</strong> <span id="mCost"></span> ریال</div>
                    <div class="col-6 col-md-3"><strong>شناسه رهگیری:</strong> <span id="mTracking" style="direction: ltr; font-family: monospace;"></span></div>
                    <div class="col-6 col-md-3"><strong>تاریخ ثبت:</strong> <span id="mDate"></span></div>
                    <div class="col-6 col-md-3"><strong>زمان تحویل:</strong> <span id="mDeliveredAt"></span></div>
                </div>
                <div class="mb-3" id="mRequestRow" style="display: none;">
                    <strong>شناسه درخواست مرتبط:</strong> <span id="mRequestId"></span>
                </div>
                <div class="mb-3" id="mErrorRow" style="display: none;">
                    <div class="alert alert-danger mb-0">
                        <strong>علت خطا:</strong> <span id="mError"></span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="fw-bold">متن پیامک:</label>
                    <div class="p-3 bg-light rounded border text-right" style="white-space: pre-wrap; font-family: font;" id="mContent"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">بستن</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('.view-details-btn').on('click', function() {
        var btn = $(this);
        $('#mDirection').text(btn.data('direction'));
        $('#mService').text(btn.data('service'));
        $('#mStatus').text(btn.data('status'));
        $('#mDelivery').text(btn.data('delivery'));
        $('#mSender').text(btn.data('sender'));
        $('#mRecipient').text(btn.data('recipient'));
        $('#mPages').text(btn.data('pages'));
        $('#mChars').text(btn.data('chars'));
        $('#mCost').text(btn.data('cost'));
        $('#mTracking').text(btn.data('tracking'));
        $('#mDate').text(btn.data('date'));
        $('#mDeliveredAt').text(btn.data('delivered-at'));
        $('#mContent').text(btn.data('content'));

        var reqId = btn.data('request-id');
        if (reqId) {
            $('#mRequestId').text('#' + reqId);
            $('#mRequestRow').show();
        } else {
            $('#mRequestRow').hide();
        }

        var err = btn.data('error');
        if (err) {
            $('#mError').text(err);
            $('#mErrorRow').show();
        } else {
            $('#mErrorRow').hide();
        }
    });
});
</script>
@stop
