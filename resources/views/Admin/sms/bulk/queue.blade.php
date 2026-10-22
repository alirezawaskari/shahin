@extends('Admin.master')

@section('title', 'صف ارسال پیامک انبوه')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
    <div>
        <h3 class="fw-bold mb-1">
            <i class="fa fa-clock-o text-primary me-2"></i> صف ارسال (پیش‌نویس‌های ارسال‌نشده)
        </h3>
        <p class="text-muted mb-0 small">
            کسب‌وکار: <strong>{{ $business?->title ?? 'همه' }}</strong> — این موارد هنوز ارسال نشده‌اند و از موجودی شما کسر نشده است.
        </p>
    </div>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.sms.bulk.index') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus me-1"></i> ایجاد کمپین جدید
        </a>
        <a href="{{ route('admin.sms.bulk.campaigns') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-list me-1"></i> تاریخچه کمپین‌ها
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card shadow-sm border-0 mb-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th style="width: 60px;">شناسه</th>
                    <th>شماره فرستنده</th>
                    <th>قالب / پیش‌نمایش</th>
                    <th>تعداد مخاطبان</th>
                    <th>هزینه</th>
                    <th>آخرین ذخیره</th>
                    <th style="width: 260px;">عملیات</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($queued as $item)
                    <tr>
                        <td class="fw-bold">#{{ $item->id }}</td>
                        <td class="font-monospace">{{ $item->senderNumber?->number ?? '-' }}</td>
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
                                    <button type="submit" class="btn btn-success btn-sm px-2">
                                        <i class="fa fa-send"></i> ارسال اکنون
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.sms.bulk.queue.destroy', $item) }}" onsubmit="return confirm('این مورد از صف ارسال حذف شود؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm px-2">
                                        <i class="fa fa-trash-o"></i> حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            صف ارسال خالی است.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($queued->hasPages())
        <div class="card-footer bg-white py-2">
            {{ $queued->links() }}
        </div>
    @endif
</div>
@endsection
