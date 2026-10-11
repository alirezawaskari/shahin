@extends('Admin.master')

@section('title', 'مدیریت سطوح دسترسی کاربران پیامک')

@section('content')
<div class="container-fluid py-3">

    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">مدیریت سطوح دسترسی کاربران پیامک</h3>
            <p class="text-muted small mb-0">مشاهده، بررسی و ارتقای سطوح دسترسی (نقره‌ای، طلایی، الماس، تیتانیوم) برای مدیران و کسب‌وکارها</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2">
                <i class="fa fa-shield me-1"></i> پنل نظارت مدیر ارشد
            </span>
        </div>
    </div>

    <!-- Navigation Bar -->
    @include('Admin.sms.settings.navigation')

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    <!-- Tier Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #64748b !important; border-radius: 10px;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">کل کاربران</span>
                    <h4 class="fw-bold mb-0 mt-1" style="color: #0f172a;">{{ number_format($tierStats['total']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #94a3b8 !important; border-radius: 10px;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">نقره‌ای (پایه)</span>
                    <h4 class="fw-bold mb-0 mt-1 text-secondary">{{ number_format($tierStats['silver']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #f59e0b !important; border-radius: 10px;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">طلایی (فیلترهای پیشرفته)</span>
                    <h4 class="fw-bold mb-0 mt-1 text-warning">{{ number_format($tierStats['gold']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #0284c7 !important; border-radius: 10px;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">الماس (ارسال تبلیغاتی)</span>
                    <h4 class="fw-bold mb-0 mt-1 text-info">{{ number_format($tierStats['diamond']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg">
            <div class="card border-0 shadow-sm h-100" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-top: 3px solid #7c3aed !important; border-radius: 10px;">
                <div class="card-body p-3">
                    <span class="text-muted small fw-bold">تیتانیوم (دسترسی کلان)</span>
                    <h4 class="fw-bold mb-0 mt-1" style="color: #7c3aed;">{{ number_format($tierStats['titanium']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 10px;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.sms.settings.access-tiers.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fa fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="جستجو بر اساس نام، شماره موبایل یا ایمیل..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <select name="tier" class="form-select form-select-sm">
                        <option value="all" {{ request('tier') === 'all' ? 'selected' : '' }}>تمام سطوح دسترسی</option>
                        <option value="silver" {{ request('tier') === 'silver' ? 'selected' : '' }}>نقره‌ای (Silver)</option>
                        <option value="gold" {{ request('tier') === 'gold' ? 'selected' : '' }}>طلایی (Gold)</option>
                        <option value="diamond" {{ request('tier') === 'diamond' ? 'selected' : '' }}>الماس (Diamond)</option>
                        <option value="titanium" {{ request('tier') === 'titanium' ? 'selected' : '' }}>تیتانیوم (Titanium)</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">
                        <i class="fa fa-filter me-1"></i> اعمال فیلتر
                    </button>
                    @if(request()->hasAny(['search', 'tier']))
                        <a href="{{ route('admin.sms.settings.access-tiers.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa fa-times"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card border-0 shadow-sm" style="background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 10px;">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0" style="color: #0f172a;"><i class="fa fa-users me-2 text-primary"></i> فهرست کاربران و سطوح دسترسی پیامک</h5>
            <span class="text-muted small">مجموع: {{ number_format($users->total()) }} کاربر</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">شناسه</th>
                        <th>نام کاربر</th>
                        <th>شماره موبایل</th>
                        <th>نقش کاربری</th>
                        <th>کسب‌وکار / غرفه</th>
                        <th>سطح دسترسی فعلی</th>
                        <th style="min-width: 220px;">تغییر سطح دسترسی</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $currentTier = $user->access_tier ?: 'silver';
                            $tierBadgeClass = match($currentTier) {
                                'titanium' => 'bg-purple text-white',
                                'diamond' => 'bg-info text-dark',
                                'gold' => 'bg-warning text-dark',
                                default => 'bg-secondary text-white',
                            };
                            $tierBadgeStyle = match($currentTier) {
                                'titanium' => 'background-color: #7c3aed !important; color: #fff !important;',
                                'diamond' => 'background-color: #0284c7 !important; color: #fff !important;',
                                'gold' => 'background-color: #f59e0b !important; color: #fff !important;',
                                default => 'background-color: #64748b !important; color: #fff !important;',
                            };
                            $tierName = match($currentTier) {
                                'titanium' => 'تیتانیوم (Titanium)',
                                'diamond' => 'الماس (Diamond)',
                                'gold' => 'طلایی (Gold)',
                                default => 'نقره‌ای (Silver)',
                            };
                        @endphp
                        <tr>
                            <td class="text-muted small">#{{ $user->id }}</td>
                            <td>
                                <strong style="color: #0f172a;">{{ $user->name }} {{ $user->family }}</strong>
                                @if($user->email)
                                    <div class="text-muted small">{{ $user->email }}</div>
                                @endif
                            </td>
                            <td>
                                <span dir="ltr" class="font-monospace fw-semibold">{{ $user->phone }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $user->level }}
                                </span>
                            </td>
                            <td>
                                @if($user->employee?->ghorfeOnline)
                                    <span class="small">{{ $user->employee->ghorfeOnline->title }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge px-2 py-1" style="{{ $tierBadgeStyle }}">
                                    <i class="fa fa-shield me-1"></i> {{ $tierName }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.sms.settings.access-tiers.update', $user->id) }}" class="d-flex gap-2 align-items-center m-0">
                                    @csrf
                                    @method('PUT')
                                    <select name="access_tier" class="form-select form-select-sm" style="max-width: 150px;">
                                        <option value="silver" {{ $currentTier === 'silver' ? 'selected' : '' }}>نقره‌ای</option>
                                        <option value="gold" {{ $currentTier === 'gold' ? 'selected' : '' }}>طلایی</option>
                                        <option value="diamond" {{ $currentTier === 'diamond' ? 'selected' : '' }}>الماس</option>
                                        <option value="titanium" {{ $currentTier === 'titanium' ? 'selected' : '' }}>تیتانیوم</option>
                                    </select>
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="fa fa-check"></i> ثبت
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                کاربری مطابق با فیلترهای جستجو یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
