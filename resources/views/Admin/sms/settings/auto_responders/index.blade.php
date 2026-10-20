@extends('Admin.master')

@section('title', 'پاسخ‌گوی خودکار پیامک')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">پاسخ‌گوی خودکار</h3>
            <p class="text-muted small mb-0">اگر مشتری پیامکی با کلمه کلیدی مشخص به خط شما بفرستد، پاسخ تعریف‌شده به‌طور خودکار برایش ارسال می‌شود.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(auth()->user()?->isSuperAdmin() && isset($all_businesses) && $all_businesses->isNotEmpty())
                <form method="GET" action="{{ route('admin.sms.settings.auto-responders.index') }}" class="d-flex flex-wrap gap-2 m-0">
                    <select name="business_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- کلیه کسب‌وکارها (نمای سراسری) --</option>
                        @foreach($all_businesses as $b)
                            <option value="{{ $b->id }}" {{ $business && $business->id === $b->id ? 'selected' : '' }}>{{ $b->title }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            @if($business)
                <span class="badge bg-info text-dark px-3 py-2">
                    <i class="fa fa-building-o ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> کسب‌وکار: {{ $business->title }}
                </span>
                <a href="{{ route('admin.sms.settings.auto-responders.create', auth()->user()?->isSuperAdmin() ? ['business_id' => $business->id] : []) }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus ms-1" style="margin-left: 6px !important; margin-right: 0 !important;"></i> قانون جدید
                </a>
            @endif
        </div>
    </div>

    @include('Admin.sms.settings.navigation')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
        </div>
    @endif

    @if($errors->has('toggle'))
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach($errors->get('toggle') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.sms.settings.auto-responders.index') }}" method="GET" class="row g-2 align-items-center">
                @if($business && auth()->user()?->isSuperAdmin())
                    <input type="hidden" name="business_id" value="{{ $business->id }}">
                @endif
                <div class="col-12 col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="جستجو در نام، کلمه کلیدی یا متن پاسخ..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-6 col-md-4">
                    <select name="status" class="form-select">
                        <option value="all">همه وضعیت‌ها</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>غیرفعال</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 text-end">
                    <button type="submit" class="btn btn-secondary w-100"><i class="fa fa-filter me-1"></i> فیلتر</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive sms-rules-table-wrap">
                <table class="table table-hover align-middle mb-0 sms-rules-table">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3 col-id">#</th>
                            <th scope="col" class="col-name">نام قانون</th>
                            @if(!$business)<th scope="col" class="col-business">کسب‌وکار</th>@endif
                            <th scope="col" class="col-keyword">کلمه کلیدی</th>
                            <th scope="col" class="col-match">نوع تطبیق</th>
                            <th scope="col" class="col-reply">متن پاسخ</th>
                            <th scope="col" class="col-status">وضعیت</th>
                            <th scope="col" class="text-end pe-3 col-actions">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rules as $rule)
                            <tr>
                                <td class="ps-3">{{ $rule->id }}</td>
                                <td class="fw-bold">{{ $rule->name }}</td>
                                @if(!$business)<td class="small">{{ $rule->business?->title }}</td>@endif
                                <td><span class="badge bg-light text-dark border font-monospace">{{ $rule->trigger_keyword }}</span></td>
                                <td class="small">{{ \App\Models\Setting::matchTypeLabel($rule->match_type) }}</td>
                                <td class="small text-muted col-reply">{{ Str::limit($rule->reply_text, 70) }}</td>
                                <td class="col-status">
                                    <form action="{{ route('admin.sms.settings.auto-responders.toggle-status', $rule) }}" method="POST" class="d-inline">
                                        @csrf
                                        @if(auth()->user()?->isSuperAdmin())<input type="hidden" name="business_id" value="{{ $rule->ghorfe_online_list_id }}">@endif
                                        <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none" title="تغییر وضعیت">
                                            @if($rule->is_active)
                                                <span class="badge bg-success">فعال</span>
                                            @else
                                                <span class="badge bg-secondary">غیرفعال</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end pe-3 col-actions">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.sms.settings.auto-responders.edit', array_merge([$rule], auth()->user()?->isSuperAdmin() ? ['business_id' => $rule->ghorfe_online_list_id] : [])) }}" class="btn btn-outline-primary" title="ویرایش">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.sms.settings.auto-responders.destroy', $rule) }}" method="POST" class="d-inline" onsubmit="return confirm('آیا از حذف این قانون اطمینان دارید؟');">
                                            @csrf
                                            @method('DELETE')
                                            @if(auth()->user()?->isSuperAdmin())<input type="hidden" name="business_id" value="{{ $rule->ghorfe_online_list_id }}">@endif
                                            <button type="submit" class="btn btn-outline-danger" title="حذف"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $business ? 7 : 8 }}" class="text-center py-4 text-muted">هیچ قانون پاسخ‌گوی خودکاری یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rules->hasPages())
            <div class="card-footer bg-white border-0 py-3">{{ $rules->links() }}</div>
        @endif
    </div>
</div>
@endsection
