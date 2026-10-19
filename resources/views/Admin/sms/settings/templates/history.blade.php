@extends('Admin.master')

@section('title', 'تاریخچه و مقایسه تغییرات قالب: ' . $template->title)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">تاریخچه نگارش‌های قالب: {{ $template->title }}</h3>
            <p class="text-muted small mb-0">ردیابی تمامی ویرایش‌های متن و مقایسه تفاوت‌های نگارشی (Diff)</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.sms.settings.templates.edit', $template) }}" class="btn btn-primary btn-sm">
                <i class="fa fa-pencil me-1"></i> ویرایش قالب
            </a>
            <a href="{{ route('admin.sms.settings.templates.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-right me-1"></i> بازگشت به فهرست
            </a>
        </div>
    </div>

    @include('Admin.sms.settings.navigation')

    <div class="row g-4">
        <!-- Revisions Timeline / List -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa fa-history text-primary me-1"></i> نگارش‌های ثبت شده ({{ $histories->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($histories as $idx => $hist)
                            @php
                                $isSelected = $selected_history && $selected_history->id === $hist->id;
                            @endphp
                            <a href="{{ route('admin.sms.settings.templates.history', ['template' => $template->id, 'history_id' => $hist->id]) }}" 
                               class="list-group-item list-group-item-action p-3 {{ $isSelected ? 'bg-light border-primary border-start border-4' : '' }}">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold">
                                        نگارش #{{ $histories->count() - $idx }}
                                        @if($idx === 0)
                                            <span class="badge bg-success small">آخرین نگارش</span>
                                        @endif
                                    </span>
                                    <span class="small text-muted font-monospace">
                                        {{ verta($hist->created_at)->format('Y/m/d H:i') }}
                                    </span>
                                </div>
                                <div class="small text-muted d-flex justify-content-between">
                                    <span>ویرایش توسط: {{ $hist->changer?->name ?: 'کاربر سیستم' }}</span>
                                    <span>{{ mb_strlen($hist->content, 'UTF-8') }} کاراکتر</span>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted">
                                هنوز هیچ تاریخچه‌ای برای این قالب ثبت نشده است.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Diff Viewer -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fa fa-code-fork text-success me-1"></i> مقایسه تفاوت‌ها (Diff)
                    </h5>
                    @if($previous_history && $selected_history)
                        <span class="badge bg-light text-dark border">
                            مقایسه با نگارش پیشین ({{ verta($previous_history->created_at)->format('Y/m/d H:i') }})
                        </span>
                    @elseif($selected_history)
                        <span class="badge bg-light text-dark border">نگارش اولیه (بدون نسخه پیشین)</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($selected_history)
                        <!-- Side by side preview blocks -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-muted mb-2">نسخه پیشین:</h6>
                                <div class="p-3 bg-light rounded font-monospace small" style="min-height: 120px; white-space: pre-wrap; line-height: 1.8;">
                                    {{ $previous_history ? $previous_history->content : '— (نگارش نخست)' }}
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <h6 class="fw-bold text-primary mb-2">این نگارش:</h6>
                                <div class="p-3 bg-light rounded font-monospace small border border-primary border-opacity-25" style="min-height: 120px; white-space: pre-wrap; line-height: 1.8;">
                                    {{ $selected_history->content }}
                                </div>
                            </div>
                        </div>

                        <!-- Line-by-line Diff -->
                        <h6 class="fw-bold mb-2">تغییرات سطر به سطر:</h6>
                        <div class="p-3 bg-dark text-white rounded font-monospace small" style="line-height: 2;">
                            @forelse($diff as $item)
                                @if($item['type'] === 'same')
                                    <div class="text-white-50 ps-2">  {{ $item['line'] }}</div>
                                @elseif($item['type'] === 'added')
                                    <div class="bg-success bg-opacity-25 text-success ps-2 rounded">+ {{ $item['line'] }}</div>
                                @elseif($item['type'] === 'removed')
                                    <div class="bg-danger bg-opacity-25 text-danger ps-2 rounded">- {{ $item['line'] }}</div>
                                @elseif($item['type'] === 'changed')
                                    <div class="bg-danger bg-opacity-25 text-danger ps-2 rounded">- {{ $item['old'] }}</div>
                                    <div class="bg-success bg-opacity-25 text-success ps-2 rounded">+ {{ $item['new'] }}</div>
                                @endif
                            @empty
                                <div class="text-muted">بدون تغییر در متن.</div>
                            @endforelse
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            نگارشی برای مقایسه انتخاب نشده است.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
