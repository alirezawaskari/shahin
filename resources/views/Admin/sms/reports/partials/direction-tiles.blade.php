{{-- Sent vs received counters + the outbound-only note. Needs $summary. --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon-badge badge-soft-primary"><i class="fa fa-arrow-up"></i></div>
                <div>
                    <div class="text-muted small">پیامک‌های ارسالی</div>
                    <h4 class="fw-bold mb-0">{{ number_format($summary['outbound_count']) }}</h4>
                    <small class="text-muted">مبنای هزینه و نرخ تحویل</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="kpi-icon-badge badge-soft-info"><i class="fa fa-arrow-down"></i></div>
                <div>
                    <div class="text-muted small">پیامک‌های دریافتی</div>
                    <h4 class="fw-bold mb-0">{{ number_format($summary['inbound_count']) }}</h4>
                    <small class="text-muted">بدون هزینه</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6 d-flex align-items-center">
        <div class="text-muted small">
            <i class="fa fa-info-circle ms-1"></i>
            آمار هزینه، تحویل و ناموفق فقط بر پایه پیامک‌های ارسالی محاسبه می‌شود؛ پیامک‌های دریافتی هزینه‌ای ندارند و در این آمار نمی‌آیند.
            مجموع کل ردیف‌ها: {{ number_format($summary['all_count']) }}
        </div>
    </div>
</div>
