@extends('Admin.master')

@section('title', 'گزارش پیامک‌ها و کمپین‌های تبلیغاتی')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">گزارش پیامک‌ها و کمپین‌های تبلیغاتی</h3>
            <p class="text-muted small mb-0">تحلیل عملکرد بر اساس هر خط فرستنده، کمپین‌های انبوه و تکی و نرخ موفقیت ارسال‌ها</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.sms.reports.advertising.export', request()->all()) }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel-o me-1"></i> خروجی اکسل
            </a>
            @if($business)
                <span class="badge bg-info text-dark px-3 py-2">
                    <i class="fa fa-building-o me-1"></i> کسب‌وکار: {{ $business->title }}
                </span>
            @endif
        </div>
    </div>

    @include('Admin.sms.financial.navigation')

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Campaigns -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fa fa-bullhorn fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">تعداد کمپین‌های تبلیغاتی</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_campaigns']) }}</h4>
                        <small class="text-muted">مخاطبان هدف: {{ number_format($summary['total_recipients']) }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Messages -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-info bg-opacity-10 text-info me-3">
                        <i class="fa fa-paper-plane fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">کل پیامک‌های ارسالی</div>
                        <h4 class="fw-bold mb-0">{{ number_format($summary['total_messages']) }}</h4>
                        <small class="text-muted">پیام‌های تبلیغاتی</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Cost -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger me-3">
                        <i class="fa fa-money fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">مجموع هزینه تبلیغاتی</div>
                        <h4 class="fw-bold mb-0 text-danger">{{ number_format($summary['total_cost']) }} <small class="fs-6 text-muted">ریال</small></h4>
                        <small class="text-muted">مصرف شده از خطوط</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Rate -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="p-3 rounded-circle bg-success bg-opacity-10 text-success me-3">
                        <i class="fa fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <div class="text-muted small">نرخ تحویل و موفقیت</div>
                        <h4 class="fw-bold mb-0 text-success">{{ $summary['delivery_rate'] }}%</h4>
                        <small class="text-muted">{{ number_format($summary['delivered_count']) }} موفق / {{ number_format($summary['failed_count']) }} ناموفق</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Per-Sender Breakdown -->
    @if($sender_breakdown->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fa fa-list-alt me-1"></i> تفکیک عملکرد بر اساس خطوط فرستنده</h6>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 text-center small">
                    <thead class="table-light">
                        <tr>
                            <th>شماره خط</th>
                            <th>پنل</th>
                            <th>تعداد پیامک‌ها</th>
                            <th>تعداد کمپین‌ها</th>
                            <th>مجموع هزینه (ریال)</th>
                            <th>نرخ موفقیت</th>
                            <th>فیلتر اختصاصی</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sender_breakdown as $sb)
                            <tr>
                                <td class="fw-bold">{{ $sb['sender']->number }}</td>
                                <td>{{ $sb['sender']->sms_panel ?? '-' }}</td>
                                <td>{{ number_format($sb['messages_count']) }}</td>
                                <td>{{ number_format($sb['campaigns_count']) }}</td>
                                <td class="text-danger fw-bold">{{ number_format($sb['cost']) }}</td>
                                <td>
                                    <span class="badge {{ $sb['success_rate'] >= 80 ? 'bg-success' : 'bg-warning text-dark' }}">
                                        {{ $sb['success_rate'] }}%
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.sms.reports.advertising.index', ['sender_number_id' => $sb['sender']->id]) }}" 
                                       class="btn btn-sm btn-outline-primary py-0">
                                        مشاهده گزارش این خط
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sms.reports.advertising.index') }}" class="row g-3">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold">خط فرستنده</label>
                    <select name="sender_number_id" class="form-select form-select-sm">
                        <option value="">همه خطوط تبلیغاتی</option>
                        @foreach($assigned_senders as $s)
                            <option value="{{ $s->id }}" {{ request('sender_number_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">وضعیت</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">همه وضعیت‌ها</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>تحویل شده</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>ارسال شده</option>
                        <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>در صف</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ناموفق</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold">جستجو (شماره یا کد رهگیری)</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="شماره گیرنده یا متن">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">از تاریخ</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                </div>
                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">تا تاریخ</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                </div>
                <div class="col-12 text-end">
                    <a href="{{ route('admin.sms.reports.advertising.index') }}" class="btn btn-light btn-sm me-2">پاکسازی فیلترها</a>
                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="fa fa-filter me-1"></i> اعمال فیلتر
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns List -->
    @if($campaigns->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fa fa-bullhorn me-1"></i> کمپین‌های تبلیغاتی ثبت‌شده</h6>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 text-center small">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>خط فرستنده</th>
                            <th>نوع کمپین</th>
                            <th>کل مخاطبان</th>
                            <th>ارسال شده</th>
                            <th>موفق</th>
                            <th>ناموفق</th>
                            <th>هزینه برآوردی</th>
                            <th>وضعیت</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campaigns as $camp)
                            <tr>
                                <td>{{ $camp->id }}</td>
                                <td>{{ $camp->senderNumber?->number ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $camp->type }}</span>
                                </td>
                                <td class="fw-bold">{{ number_format($camp->total_recipients) }}</td>
                                <td>{{ number_format($camp->sent_count) }}</td>
                                <td class="text-success fw-bold">{{ number_format($camp->success_count) }}</td>
                                <td class="text-danger">{{ number_format($camp->failed_count) }}</td>
                                <td>{{ $camp->cost_estimate ? number_format($camp->cost_estimate) : '-' }} ریال</td>
                                <td>
                                    <span class="badge {{ $camp->status === 'completed' ? 'bg-success' : ($camp->status === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                        {{ $camp->status }}
                                    </span>
                                </td>
                                <td>{{ $camp->created_at->format('Y/m/d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($campaigns->hasPages())
                <div class="card-footer bg-white border-0 py-3">
                    {{ $campaigns->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Messages Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fa fa-envelope-o me-1"></i> پیامک‌های تبلیغاتی</h6>
            <span class="badge bg-light text-dark">{{ $messages->total() }} پیامک</span>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover align-middle mb-0 text-center small">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>خط فرستنده</th>
                        <th>گیرنده</th>
                        <th>متن پیامک</th>
                        <th>کاراکتر / صفحه</th>
                        <th>هزینه (ریال)</th>
                        <th>وضعیت</th>
                        <th>شناسه پیگیری</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                        <tr>
                            <td>{{ $msg->id }}</td>
                            <td>{{ $msg->senderNumber?->number ?? $msg->sender_phone ?? '-' }}</td>
                            <td class="fw-bold">{{ $msg->recipient_phone }}</td>
                            <td class="text-start" style="max-width: 250px;">
                                <div class="text-truncate" title="{{ $msg->content }}">{{ $msg->content }}</div>
                            </td>
                            <td>{{ $msg->character_count }} / {{ $msg->page_count }}</td>
                            <td class="fw-bold text-danger">{{ $msg->cost ? number_format($msg->cost) : '0' }}</td>
                            <td>
                                @if(in_array($msg->status, ['delivered', 'sent']))
                                    <span class="badge bg-success bg-opacity-10 text-success">موفق</span>
                                @elseif($msg->status === 'queued')
                                    <span class="badge bg-warning bg-opacity-10 text-warning">در صف</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger">ناموفق</span>
                                @endif
                            </td>
                            <td><code>{{ $msg->tracking_id ?? '-' }}</code></td>
                            <td>{{ $msg->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-muted py-4">پیامک تبلیغاتی منطبق با فیلترها یافت نشد.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
