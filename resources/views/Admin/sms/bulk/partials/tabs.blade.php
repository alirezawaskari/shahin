{{--
    Bottom of the group-send page: message log, outgoing queue and campaigns as tabs
    (they used to be separate pages).
    Params: $activeTab, $recentMessages, $queuedList, $campaignList, $business, $queuedCount
--}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white pt-3 pb-0">
        <ul class="nav nav-tabs card-header-tabs" id="bulkBottomTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'logs' ? 'active' : '' }}" id="tab-logs-btn" data-bs-toggle="tab" data-bs-target="#tab-logs" type="button" role="tab" aria-controls="tab-logs" aria-selected="{{ $activeTab === 'logs' ? 'true' : 'false' }}">
                    <i class="fa fa-list me-1"></i> لاگ پیامک‌ها
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'queue' ? 'active' : '' }}" id="tab-queue-btn" data-bs-toggle="tab" data-bs-target="#tab-queue" type="button" role="tab" aria-controls="tab-queue" aria-selected="{{ $activeTab === 'queue' ? 'true' : 'false' }}">
                    <i class="fa fa-clock-o me-1"></i> صف ارسال
                    @if(($queuedCount ?? 0) > 0)
                        <span class="badge bg-warning text-dark">{{ number_format($queuedCount) }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'campaigns' ? 'active' : '' }}" id="tab-campaigns-btn" data-bs-toggle="tab" data-bs-target="#tab-campaigns" type="button" role="tab" aria-controls="tab-campaigns" aria-selected="{{ $activeTab === 'campaigns' ? 'true' : 'false' }}">
                    <i class="fa fa-bullhorn me-1"></i> کمپین‌ها
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        {{-- Log --}}
        <div class="tab-pane fade {{ $activeTab === 'logs' ? 'show active' : '' }}" id="tab-logs" role="tabpanel" aria-labelledby="tab-logs-btn">
            @if($recentMessages->isNotEmpty())
                @include('Admin.sms.partials.recent-messages-table', ['recentMessages' => $recentMessages])
                <div class="card-footer bg-white py-2 text-end">
                    <a href="{{ route('admin.sms.logs.index') }}" class="btn btn-link btn-sm">مشاهده همه پیامک‌ها <i class="fa fa-angle-left ms-1"></i></a>
                </div>
            @else
                <div class="text-center py-4 text-muted">هنوز پیامکی ثبت نشده است.</div>
            @endif
        </div>

        {{-- Outgoing queue --}}
        <div class="tab-pane fade {{ $activeTab === 'queue' ? 'show active' : '' }}" id="tab-queue" role="tabpanel" aria-labelledby="tab-queue-btn">
            <div class="px-3 pt-3 small text-muted">این موارد هنوز ارسال نشده‌اند و از موجودی شما کسر نشده است.</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th style="width: 60px;">شناسه</th>
                            <th>نام کمپین</th>
                            <th>سرشماره</th>
                            <th>قالب / پیش‌نمایش</th>
                            <th>تعداد مخاطبان</th>
                            <th>هزینه</th>
                            <th>آخرین ذخیره</th>
                            <th style="width: 260px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($queuedList as $item)
                            <tr>
                                <td class="fw-bold">#{{ $item->id }}</td>
                                <td>{{ $item->name ?: '-' }}</td>
                                <td class="font-monospace">{{ $item->senderNumber?->number ?? ($item->service_type === 'otp' ? 'OTP' : '-') }}</td>
                                <td>
                                    @if($item->template)
                                        <span class="badge bg-light text-dark border">{{ $item->template->title }}</span>
                                    @endif
                                    <span class="text-muted">{{ Str::limit($item->filters['message_preview'] ?? 'متن آزاد', 50) }}</span>
                                </td>
                                <td class="fw-bold">{{ number_format($item->total_recipients) }}</td>
                                <td class="text-primary">{{ number_format($item->cost_estimate) }} ریال</td>
                                <td class="text-muted">{{ \App\Helpers\Helper::jalaliDate($item->updated_at, 'Y/m/d H:i') ?? '-' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="{{ route('admin.sms.bulk.index', ['queue' => $item->id]) }}" class="btn btn-outline-primary btn-sm px-2">
                                            <i class="fa fa-pencil"></i> ادامه / ویرایش
                                        </a>
                                        <form method="POST" action="{{ route('admin.sms.bulk.queue.send', $item) }}" onsubmit="return confirm('این پیامک هم‌اکنون برای مخاطبان ارسال شود؟');">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm px-2"><i class="fa fa-send"></i> ارسال اکنون</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.sms.bulk.queue.destroy', $item) }}" onsubmit="return confirm('این مورد از صف ارسال حذف شود؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-2"><i class="fa fa-trash-o"></i> حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">صف ارسال خالی است.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($queuedList->hasPages())
                <div class="card-footer bg-white py-2">{{ $queuedList->links() }}</div>
            @endif
        </div>

        {{-- Campaigns --}}
        <div class="tab-pane fade {{ $activeTab === 'campaigns' ? 'show active' : '' }}" id="tab-campaigns" role="tabpanel" aria-labelledby="tab-campaigns-btn">
            <div class="px-3 pt-3">
                <form method="GET" action="{{ route('admin.sms.bulk.index') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="campaigns">
                    <div class="col-auto"><label class="col-form-label col-form-label-sm small fw-bold">فیلتر وضعیت:</label></div>
                    <div class="col-auto">
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all">همه وضعیت‌ها</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>تکمیل شده</option>
                            <option value="completed_with_errors" {{ request('status') === 'completed_with_errors' ? 'selected' : '' }}>تکمیل ناقص (موجودی)</option>
                            <option value="sending" {{ request('status') === 'sending' ? 'selected' : '' }}>در حال ارسال</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>زمان‌بندی شده</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>ناموفق</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="table-responsive mt-2">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th style="width: 60px;">شناسه</th>
                            <th>نام کمپین</th>
                            <th>سرشماره</th>
                            <th>قالب / پیش‌نمایش</th>
                            <th>کل مخاطبان</th>
                            <th>موفق / ناموفق</th>
                            <th>وضعیت</th>
                            <th>هزینه</th>
                            <th>تاریخ ثبت</th>
                            <th style="width: 100px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($campaignList as $camp)
                            @php
                                $tot = max(1, $camp->total_recipients);
                                $perc = round(($camp->sent_count / $tot) * 100, 1);
                            @endphp
                            <tr>
                                <td class="fw-bold">#{{ $camp->id }}</td>
                                <td>{{ $camp->name ?: $camp->title }}</td>
                                <td class="font-monospace">{{ $camp->senderNumber?->number ?? ($camp->service_type === 'otp' ? 'OTP' : '-') }}</td>
                                <td>
                                    @if($camp->template)
                                        <span class="badge bg-light text-dark border">{{ $camp->template->title }}</span>
                                    @else
                                        <span class="text-muted">{{ Str::limit($camp->filters['message_preview'] ?? 'متن آزاد', 40) }}</span>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ number_format($camp->total_recipients) }}</td>
                                <td>
                                    <span class="text-success fw-bold">{{ number_format($camp->success_count) }}</span> /
                                    <span class="text-danger fw-bold">{{ number_format($camp->failed_count) }}</span>
                                </td>
                                <td>
                                    @if($camp->status === 'completed')
                                        <span class="badge bg-success">تکمیل شده</span>
                                    @elseif($camp->status === 'completed_with_errors')
                                        <span class="badge bg-warning text-dark">تکمیل ناقص (موجودی)</span>
                                    @elseif($camp->status === 'sending')
                                        <span class="badge bg-primary">در حال ارسال</span>
                                    @elseif($camp->status === 'scheduled')
                                        <span class="badge bg-info">زمان‌بندی شده</span>
                                    @elseif($camp->status === 'failed')
                                        <span class="badge bg-danger">ناموفق</span>
                                    @else
                                        <span class="badge bg-secondary">پیش‌نویس</span>
                                    @endif
                                    <div class="progress mt-1" style="height: 4px; width: 80px;">
                                        <div class="progress-bar bg-success" style="width: {{ $perc }}%;"></div>
                                    </div>
                                </td>
                                <td class="text-primary">{{ number_format($camp->cost_estimate) }} ریال</td>
                                <td class="text-muted">{{ \App\Helpers\Helper::jalaliDate($camp->created_at, 'Y/m/d H:i') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.sms.bulk.campaigns.show', $camp) }}" class="btn btn-outline-primary btn-sm px-2" title="مشاهده وضعیت زنده">
                                        <i class="fa fa-eye"></i> مشاهده
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center py-4 text-muted">هیچ کمپینی ثبت نشده است.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($campaignList->hasPages())
                <div class="card-footer bg-white py-2">{{ $campaignList->links() }}</div>
            @endif
        </div>
    </div>
</div>
